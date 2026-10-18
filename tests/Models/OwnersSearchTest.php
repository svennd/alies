<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T086 - Owners_model search methods.
 *
 * Rules: names are matched on their start, the street anywhere in it, the phone on the start of any
 * of the four number columns; the raw-SQL methods do not look at deleted_at or disabled (they return
 * soft-deleted owners), the query-builder methods search_for_lab_assignment / is_assignable do.
 * Every row has a unique token in its name, so rows from other tests do not matter.
 */
final class OwnersSearchTest extends CodeIgniterDatabaseTestCase
{
    private $owners;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owners = $this->model('Owners_model', 'owners_search_test');
    }

    private function ids(array $rows): array
    {
        $ids = array_map(static fn ($row) => (int) $row['id'], $rows);
        sort($ids);

        return $ids;
    }

    public function testFirstAndLastNameMatchOnTheStartOnly(): void
    {
        $token = Factory::unique('Tok');
        $owner = Factory::owner(['first_name' => $token . 'Anna', 'last_name' => $token . 'Bakker']);
        Factory::owner(['first_name' => 'Anna' . $token, 'last_name' => 'Bakker' . $token]);

        $this->assertSame([$owner], $this->ids($this->owners->search_by_first_name($token)));
        $this->assertSame([$owner], $this->ids($this->owners->search_by_last_name($token)));
        $this->assertSame([$owner], $this->ids($this->owners->search_by_first_name(strtolower($token . 'ann'))), 'case does not matter');
        $this->assertSame([], $this->owners->search_by_last_name('nobodyhasthisname' . $token));
    }

    public function testLikeCharactersInTheQueryAreEscaped(): void
    {
        $token = Factory::unique('Pct');
        $owner = Factory::owner(['last_name' => $token . '50%off']);
        Factory::owner(['last_name' => $token . '50xoff']);

        $this->assertSame([$owner], $this->ids($this->owners->search_by_last_name($token . '50%')));
        $this->assertSame([], $this->owners->search_by_last_name("'; DROP TABLE owners; --"));
    }

    public function testResultsAreSortedByLastBillNewestFirst(): void
    {
        $token = Factory::unique('Ord');
        $old = Factory::owner(['last_name' => $token . 'a', 'last_bill' => date('Y-m-d', strtotime('-3 years'))]);
        $new = Factory::owner(['last_name' => $token . 'b', 'last_bill' => date('Y-m-d')]);
        $mid = Factory::owner(['last_name' => $token . 'c', 'last_bill' => date('Y-m-d', strtotime('-1 year'))]);

        $found = array_map(static fn ($row) => (int) $row['id'], $this->owners->search_by_last_name($token));

        $this->assertSame([$new, $mid, $old], $found);
    }

    public function testTheStreetIsMatchedAnywhereInTheName(): void
    {
        $token = Factory::unique('Str');
        $owner = Factory::owner(['street' => 'Grote ' . $token . 'laan']);

        $this->assertSame([$owner], $this->ids($this->owners->search_by_street_ex($token)));
        $this->assertSame([$owner], $this->ids($this->owners->search_by_street_ex('Grote ' . $token)));
        $this->assertSame([$owner], $this->ids($this->owners->search_by_street_ex($token . 'laan')));
    }

    public function testThePhoneIsMatchedOnTheStartOfEachOfTheFourNumbers(): void
    {
        $n = (string) random_int(1000000, 9999999);
        $landline = Factory::owner(['telephone' => '03' . $n]);
        $mobile = Factory::owner(['mobile' => '0471' . $n]);
        $phone2 = Factory::owner(['phone2' => '0472' . $n]);
        $phone3 = Factory::owner(['phone3' => '0473' . $n]);

        $this->assertSame([$landline], $this->ids($this->owners->search_by_phone_ex('03' . $n)));
        $this->assertSame([$mobile], $this->ids($this->owners->search_by_phone_ex('0471' . $n)));
        $this->assertSame([$phone2], $this->ids($this->owners->search_by_phone_ex('0472' . $n)));
        $this->assertSame([$phone3], $this->ids($this->owners->search_by_phone_ex('0473' . $n)));
        $this->assertSame([$mobile], $this->ids($this->owners->search_by_phone_ex('0471' . substr($n, 0, 5))), 'prefix');
        $this->assertSame([], $this->owners->search_by_phone_ex('1' . $n), 'not a suffix match');
    }

    public function testPhoneSearchHonoursTheLimitAndIgnoresAnEmptyNumber(): void
    {
        $n = (string) random_int(1000000, 9999999);
        for ($i = 0; $i < 3; $i++) {
            Factory::owner(['mobile' => '0474' . $n . $i]);
        }

        $this->assertCount(3, $this->owners->search_by_phone_ex('0474' . $n));
        $this->assertCount(2, $this->owners->search_by_phone_ex('0474' . $n, 2));
        $this->assertSame([], $this->owners->search_by_phone_ex(false));
        $this->assertSame([], $this->owners->search_by_phone_ex(''));
    }

    public function testAnAddressNeedsStreetNumberAndZipToAllMatch(): void
    {
        $token = Factory::unique('Adr');
        $owner = Factory::owner(['street' => $token . 'weg', 'nr' => '12', 'zip' => '9999']);
        Factory::owner(['street' => $token . 'weg', 'nr' => '13', 'zip' => '9999']);
        Factory::owner(['street' => $token . 'weg', 'nr' => '12', 'zip' => '8888']);

        $this->assertSame([$owner], $this->ids($this->owners->search_by_addr($token . 'weg', '12', '9999')));
        $this->assertSame([], $this->owners->search_by_addr($token, '12', '9999'), 'exact street, not a prefix');
    }

    public function testAddressSearchHonoursTheLimit(): void
    {
        $token = Factory::unique('Lim');
        for ($i = 0; $i < 3; $i++) {
            Factory::owner(['street' => $token . 'pad', 'nr' => '1', 'zip' => '7777']);
        }

        $this->assertCount(3, $this->owners->search_by_addr($token . 'pad', '1', '7777'));
        $this->assertCount(1, $this->owners->search_by_addr($token . 'pad', '1', '7777', 1));
    }

    public function testSearchByNameMatchesFirstOrLastNameAndAddsAtMostTwoAlivePets(): void
    {
        $token = Factory::unique('Nam');
        $owner = Factory::owner(['last_name' => $token . 'Peters']);
        $byFirst = Factory::owner(['first_name' => $token . 'Piet', 'last_name' => 'Other']);
        Factory::pet($owner, ['name' => 'PetA', 'birth' => '2020-01-01']);
        Factory::pet($owner, ['name' => 'PetB', 'birth' => '2021-01-01']);
        Factory::pet($owner, ['name' => 'PetC', 'birth' => '2022-01-01']);
        Factory::pet($byFirst, ['name' => 'Deadpet', 'death' => 1]);
        Factory::pet($byFirst, ['name' => 'Lostpet', 'lost' => 1]);

        $result = $this->owners->search_by_name($token);

        $this->assertSame([$owner, $byFirst], $this->ids($result), 'found by last name and by first name');
        $byId = array_column($result, null, 'id');
        $this->assertCount(2, $byId[$owner]['pets'], 'three alive pets, only two are listed');
        $this->assertSame([], $byId[$byFirst]['pets'], 'dead and lost pets are left out');
    }

    #[Group('known-bug')]
    public function testTheRawSqlSearchesStillReturnSoftDeletedAndDisabledOwners(): void
    {
        // KNOWN BUG: owners.deleted_at / disabled are not filtered in the raw SQL searches - decide in CI4 whether to keep
        $token = Factory::unique('Del');
        $deleted = Factory::owner(['last_name' => $token . 'a', 'deleted_at' => date('Y-m-d H:i:s'), 'street' => $token . 'laan', 'mobile' => '0479' . random_int(1000000, 9999999)]);
        $disabled = Factory::owner(['last_name' => $token . 'b', 'disabled' => 1]);

        $this->assertSame([$deleted, $disabled], $this->ids($this->owners->search_by_last_name($token)));
        $this->assertSame([$deleted], $this->ids($this->owners->search_by_street_ex($token)));
        $this->assertSame([$deleted], $this->ids($this->owners->search_by_name($token . 'a')));
        $this->assertSame([$deleted], $this->ids($this->owners->search_by_phone_ex($this->ci->db->get_where('owners', ['id' => $deleted])->row_array()['mobile'])));
    }

    public function testLabAssignmentFindsByIdOrNamePrefixAndSkipsDeletedOwners(): void
    {
        $token = Factory::unique('Lab');
        $owner = Factory::owner(['last_name' => $token . 'Visser']);
        $deleted = Factory::owner(['last_name' => $token . 'Weg', 'deleted_at' => date('Y-m-d H:i:s')]);
        $byFirst = Factory::owner(['first_name' => $token . 'Eva']);

        $byName = $this->owners->search_for_lab_assignment($token);
        $this->assertSame([$owner, $byFirst], $this->ids($byName));
        $this->assertNotContains($deleted, $this->ids($byName));
        $this->assertSame(['id', 'first_name', 'last_name', 'street', 'nr', 'zip'], array_keys($byName[0]));

        $byId = $this->owners->search_for_lab_assignment((string) $owner);
        $this->assertContains($owner, $this->ids($byId), 'a numeric term is also an owner id');
        $this->assertSame([], $this->owners->search_for_lab_assignment((string) $deleted . 'x'));
        $this->assertNotContains($deleted, $this->ids($this->owners->search_for_lab_assignment((string) $deleted)));
    }

    public function testLabAssignmentHonoursTheLimit(): void
    {
        $token = Factory::unique('Lm');
        for ($i = 0; $i < 3; $i++) {
            Factory::owner(['last_name' => $token . $i]);
        }

        $this->assertCount(3, $this->owners->search_for_lab_assignment($token));
        $this->assertCount(2, $this->owners->search_for_lab_assignment($token, 2));
    }

    public function testAnOwnerIsAssignableWhenItExistsAndIsNotDeleted(): void
    {
        $owner = Factory::owner();
        $deleted = Factory::owner(['deleted_at' => date('Y-m-d H:i:s')]);
        $disabled = Factory::owner(['disabled' => 1]);

        $this->assertTrue($this->owners->is_assignable($owner));
        $this->assertFalse($this->owners->is_assignable($deleted));
        $this->assertTrue($this->owners->is_assignable($disabled), 'disabled owners can still be assigned');
        $this->assertFalse($this->owners->is_assignable(0));
        $this->assertFalse($this->owners->is_assignable(-5));
        $this->assertFalse($this->owners->is_assignable(99999999));
    }
}
