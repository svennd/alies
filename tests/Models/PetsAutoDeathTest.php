<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T088 - Pets_model::auto_death(type, years), the cron job that marks very old animals as dead.
 *
 * Rule: an alive pet of that type whose age in whole years is MORE than `years` and that has no
 * event created in the last 2 years gets death = 1, death_date = today - 14 days and the note
 * suffix " [auto-death]". The method returns the number of rows it changed.
 */
final class PetsAutoDeathTest extends CodeIgniterDatabaseTestCase
{
    private $pets;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->pets = $this->model('Pets_model', 'pets_auto_death_test');
    }

    private function row(int $id): array
    {
        return $this->ci->db->get_where('pets', ['id' => $id])->row_array();
    }

    private function bornYearsAgo(int $years, string $extra = '-1 day'): string
    {
        return date('Y-m-d', strtotime('-' . $years . ' years ' . $extra));
    }

    public function testAnOldDogWithoutRecentEventsIsMarkedDead(): void
    {
        $owner = Factory::owner();
        $dog = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(16), 'note' => 'oud hondje']);
        // an event older than two years does not protect the pet
        Factory::event($dog, 1, 0, ['created_at' => date('Y-m-d H:i:s', strtotime('-3 years'))]);

        $changed = $this->pets->auto_death(DOG, 15);

        $row = $this->row($dog);
        $this->assertSame(1, (int) $row['death']);
        $this->assertSame(date('Y-m-d', strtotime('-14 days')), $row['death_date']);
        $this->assertSame('oud hondje [auto-death]', $row['note']);
        $this->assertGreaterThanOrEqual(1, $changed);
    }

    public function testPetsThatShouldNotBeTouchedAreLeftAlone(): void
    {
        $owner = Factory::owner();
        $recentEvent = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(17), 'note' => '']);
        Factory::event($recentEvent, 1, 0, ['created_at' => date('Y-m-d H:i:s', strtotime('-1 year'))]);
        $young = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(5), 'note' => '']);
        $exactly15 = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(15), 'note' => '']);
        $cat = Factory::pet($owner, ['type' => CAT, 'birth' => $this->bornYearsAgo(18), 'note' => '']);
        $alreadyDead = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(18), 'note' => '', 'death' => 1, 'death_date' => '2020-02-02']);
        // positive control: a dog that qualifies
        $qualifies = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(18), 'note' => '']);

        $this->pets->auto_death(DOG, 15);

        $this->assertSame(0, (int) $this->row($recentEvent)['death'], 'event a year ago');
        $this->assertSame(0, (int) $this->row($young)['death']);
        $this->assertSame(0, (int) $this->row($exactly15)['death'], 'exactly 15 is not more than 15');
        $this->assertSame(0, (int) $this->row($cat)['death'], 'other type');
        $this->assertSame('2020-02-02', $this->row($alreadyDead)['death_date'], 'already dead rows are not rewritten');
        $this->assertSame('', $this->row($recentEvent)['note']);
        $this->assertSame(1, (int) $this->row($qualifies)['death']);
    }

    public function testTheCatVariantUsesItsOwnTypeAndAge(): void
    {
        $owner = Factory::owner();
        $cat = Factory::pet($owner, ['type' => CAT, 'birth' => $this->bornYearsAgo(20), 'note' => '']);
        $dog = Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(20), 'note' => '']);

        $this->pets->auto_death(CAT, 19);

        $this->assertSame(1, (int) $this->row($cat)['death']);
        $this->assertSame(0, (int) $this->row($dog)['death']);
    }

    public function testItReturnsTheNumberOfAffectedRows(): void
    {
        $owner = Factory::owner();
        // clear the way: whatever old dog already is in the database is not ours
        $this->pets->auto_death(DOG, 15);
        for ($i = 0; $i < 3; $i++) {
            Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(16 + $i), 'note' => 'n']);
        }
        Factory::pet($owner, ['type' => DOG, 'birth' => $this->bornYearsAgo(3), 'note' => 'n']);

        $this->assertSame(3, $this->pets->auto_death(DOG, 15));
        $this->assertSame(0, $this->pets->auto_death(DOG, 15), 'a second run finds nothing left');
    }

    #[Group('known-bug')]
    public function testAPetWithoutANoteIsMarkedDeadButItsNoteStaysEmpty(): void
    {
        // KNOWN BUG: CONCAT(NULL, ' [auto-death]') is NULL, so the suffix is lost when pets.note is NULL
        // (the column default) - decide in CI4 whether to keep
        $dog = Factory::pet(Factory::owner(), ['type' => DOG, 'birth' => $this->bornYearsAgo(16)]);

        $this->pets->auto_death(DOG, 15);

        $row = $this->row($dog);
        $this->assertSame(1, (int) $row['death']);
        $this->assertNull($row['note']);
    }
}
