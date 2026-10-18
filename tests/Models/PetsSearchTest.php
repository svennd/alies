<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T087 - Pets_model search and lookup methods.
 *
 * Rules: search_by_name (prefix; dead only with dead_allowed; transferred never; lost included),
 * search_by_chip_ex (chip prefix, limit 5), search_by_id (transferred excluded) - both return the
 * columns of the OWNER (`id` is the owner id) plus the pet name and type; the "assignable" methods
 * need an alive, non-transferred, non-deleted pet; the lab ingestion finders compare names
 * upper-case. Rows carry unique tokens so rows from other tests do not matter.
 */
final class PetsSearchTest extends CodeIgniterDatabaseTestCase
{
    private $pets;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->pets = $this->model('Pets_model', 'pets_search_test');
    }

    private function column(array $rows, string $column): array
    {
        $values = array_map(static fn ($row) => (int) $row[$column], $rows);
        sort($values);

        return $values;
    }

    // ---- search_by_name ---------------------------------------------------------------------

    public function testNameSearchMatchesThePrefixAndReturnsPetAndOwnerColumns(): void
    {
        $token = Factory::unique('Pn');
        $owner = Factory::owner(['last_name' => 'Ownerlast']);
        $pet = Factory::pet($owner, ['name' => $token . 'Rex', 'type' => 1]);
        Factory::pet($owner, ['name' => 'Rex' . $token]);

        $rows = $this->pets->search_by_name($token);

        $this->assertCount(1, $rows);
        $this->assertSame($pet, (int) $rows[0]['pet_id']);
        $this->assertSame($token . 'Rex', $rows[0]['name']);
        $this->assertSame(1, (int) $rows[0]['type']);
        $this->assertSame($owner, (int) $rows[0]['id'], '`id` is the owner id (owners.* overrides it)');
        $this->assertSame('Ownerlast', $rows[0]['last_name']);
    }

    public function testNameSearchSkipsDeadAndTransferredPetsButIncludesLostOnes(): void
    {
        $token = Factory::unique('Pd');
        $owner = Factory::owner();
        $alive = Factory::pet($owner, ['name' => $token . 'alive']);
        $lost = Factory::pet($owner, ['name' => $token . 'lost', 'lost' => 1]);
        $dead = Factory::pet($owner, ['name' => $token . 'dead', 'death' => 1]);
        Factory::pet($owner, ['name' => $token . 'moved', 'transfered' => 1]);

        $this->assertSame([$alive, $lost], $this->column($this->pets->search_by_name($token), 'pet_id'));
        $this->assertSame(
            [$alive, $lost, $dead],
            $this->column($this->pets->search_by_name($token, 250, true), 'pet_id'),
            'dead_allowed adds the dead pet, never the transferred one'
        );
    }

    public function testNameSearchHonoursTheLimit(): void
    {
        $token = Factory::unique('Pl');
        $owner = Factory::owner();
        for ($i = 0; $i < 3; $i++) {
            Factory::pet($owner, ['name' => $token . $i]);
        }

        $this->assertCount(2, $this->pets->search_by_name($token, 2));
    }

    #[Group('known-bug')]
    public function testNameSearchStillReturnsSoftDeletedPets(): void
    {
        // KNOWN BUG: pets.deleted_at is not filtered in the raw SQL search - decide in CI4 whether to keep
        $token = Factory::unique('Pg');
        $pet = Factory::pet(Factory::owner(), ['name' => $token . 'gone', 'deleted_at' => date('Y-m-d H:i:s')]);

        $this->assertSame([$pet], $this->column($this->pets->search_by_name($token), 'pet_id'));
    }

    // ---- chip and id --------------------------------------------------------------------------

    public function testChipSearchMatchesThePrefixAndReturnsTheOwnerId(): void
    {
        $chip = '9820' . random_int(100000000, 999999999);
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Chipped', 'chip' => $chip]);

        $rows = $this->pets->search_by_chip_ex(substr($chip, 0, 12));

        $this->assertCount(1, $rows);
        $this->assertSame('Chipped', $rows[0]['name']);
        $this->assertSame($owner, (int) $rows[0]['id'], '`id` is the owner id');
        $this->assertSame([], $this->pets->search_by_chip_ex('1' . $chip), 'not a suffix match');
    }

    public function testChipSearchReturnsAtMostFiveRows(): void
    {
        $prefix = '9830' . random_int(1000000, 9999999);
        $owner = Factory::owner();
        for ($i = 0; $i < 6; $i++) {
            Factory::pet($owner, ['chip' => $prefix . $i]);
        }

        $this->assertCount(5, $this->pets->search_by_chip_ex($prefix));
    }

    public function testIdSearchReturnsTheOwnerOfThatPetUnlessItWasTransferred(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['name' => 'Byid']);
        $moved = Factory::pet($owner, ['name' => 'Moved', 'transfered' => 1]);

        $rows = $this->pets->search_by_id($pet);

        $this->assertCount(1, $rows);
        $this->assertSame('Byid', $rows[0]['name']);
        $this->assertSame($owner, (int) $rows[0]['id'], '`id` is the owner id, not the pet id');
        $this->assertNotSame($pet, (int) $rows[0]['id']);
        $this->assertSame([], $this->pets->search_by_id($moved));
        $this->assertSame([], $this->pets->search_by_id(99999999));
    }

    // ---- owner side lists ---------------------------------------------------------------------

    public function testOtherPetsListsTheAliveNotLostNotTransferredSiblings(): void
    {
        $owner = Factory::owner();
        $current = Factory::pet($owner);
        $sibling = Factory::pet($owner, ['name' => 'Sibling', 'type' => 1]);
        Factory::pet($owner, ['death' => 1]);
        Factory::pet($owner, ['lost' => 1]);
        Factory::pet($owner, ['transfered' => 1]);
        Factory::pet($owner, ['deleted_at' => date('Y-m-d H:i:s')]);
        Factory::pet(Factory::owner());

        $rows = $this->pets->other_pets($owner, $current);

        $this->assertSame([$sibling], array_map('intval', array_column($rows, 'id')));
        $this->assertSame(['id', 'type', 'name'], array_keys($rows[0]));
        $this->assertCount(1, $this->pets->other_pets($owner, $sibling), 'seen from the sibling the first pet is the other one');
        $this->assertFalse($this->pets->other_pets(Factory::owner(), 1), 'nothing found gives false');
    }

    public function testOtherPetsHonoursTheLimit(): void
    {
        $owner = Factory::owner();
        $current = Factory::pet($owner);
        for ($i = 0; $i < 6; $i++) {
            Factory::pet($owner);
        }

        $this->assertCount(5, $this->pets->other_pets($owner, $current));
        $this->assertCount(2, $this->pets->other_pets($owner, $current, 2));
    }

    public function testAllPetsOfAnOwnerIncludeDeadOnesButNotTransferredOrDeleted(): void
    {
        $owner = Factory::owner();
        $old = Factory::pet($owner, ['birth' => '2010-01-01', 'breed' => 2]);
        $young = Factory::pet($owner, ['birth' => '2022-01-01', 'breed' => 5, 'breed2' => 2]);
        $dead = Factory::pet($owner, ['birth' => '2016-01-01', 'death' => 1]);
        Factory::pet($owner, ['transfered' => 1]);
        Factory::pet($owner, ['deleted_at' => date('Y-m-d H:i:s')]);

        $rows = $this->pets->get_all_pets($owner);

        $byId = array_column($rows, null, 'id');
        $this->assertSame([$old, $young, $dead], array_map('intval', array_keys($byId)));
        $this->assertSame('Labrador', $byId[$old]['breeds']['name']);
        $this->assertSame('Europese korthaar', $byId[$young]['breeds']['name']);
        $this->assertSame('Labrador', $byId[$young]['breeds2']['name']);
        $this->assertFalse($this->pets->get_all_pets(Factory::owner()));
    }

    #[Group('known-bug')]
    public function testAllPetsAreNotSortedByBirthDespiteTheOrderByInTheCode(): void
    {
        // KNOWN BUG: order_by(array("birth, death"), "desc") is ignored, rows come in id order
        // (the intent was newest birth first) - decide in CI4 whether to keep
        $owner = Factory::owner();
        $old = Factory::pet($owner, ['birth' => '2010-01-01']);
        $young = Factory::pet($owner, ['birth' => '2022-01-01']);
        $middle = Factory::pet($owner, ['birth' => '2016-01-01']);

        $rows = $this->pets->get_all_pets($owner);

        $this->assertSame([$old, $young, $middle], array_map(static fn ($row) => (int) $row['id'], $rows));
    }

    // ---- assignable --------------------------------------------------------------------------

    public function testAssignablePetsOfAnOwnerAreAliveNotTransferredNotDeletedAndSortedByName(): void
    {
        $owner = Factory::owner();
        $b = Factory::pet($owner, ['name' => 'Bobbel']);
        $a = Factory::pet($owner, ['name' => 'Anouk', 'type' => 1]);
        Factory::pet($owner, ['name' => 'Dead', 'death' => 1]);
        Factory::pet($owner, ['name' => 'Moved', 'transfered' => 1]);
        Factory::pet($owner, ['name' => 'Deleted', 'deleted_at' => date('Y-m-d H:i:s')]);
        $lost = Factory::pet($owner, ['name' => 'Lostone', 'lost' => 1]);
        Factory::pet(Factory::owner(), ['name' => 'Anybody']);

        $rows = $this->pets->search_assignable_for_owner($owner);

        $this->assertSame([$a, $b, $lost], array_map(static fn ($row) => (int) $row['id'], $rows), 'lost pets are assignable');
        $this->assertSame(['id', 'name', 'type'], array_keys($rows[0]));
    }

    public function testAssignableSearchTakesANamePrefixOrAnId(): void
    {
        $owner = Factory::owner();
        $anouk = Factory::pet($owner, ['name' => 'Anouk']);
        $bob = Factory::pet($owner, ['name' => 'Bobbel']);

        $this->assertSame([$anouk], array_map(static fn ($row) => (int) $row['id'], $this->pets->search_assignable_for_owner($owner, 'ano')));
        $this->assertSame([], $this->pets->search_assignable_for_owner($owner, 'nouk'), 'prefix only');
        $this->assertSame([$bob], array_map(static fn ($row) => (int) $row['id'], $this->pets->search_assignable_for_owner($owner, (string) $bob)), 'a numeric term is also a pet id');
        $this->assertCount(1, $this->pets->search_assignable_for_owner($owner, '', 1));
    }

    public function testIsAssignableToOwnerChecksOwnerLifeTransferAndDeletion(): void
    {
        $owner = Factory::owner();
        $other = Factory::owner();
        $pet = Factory::pet($owner);

        $this->assertTrue($this->pets->is_assignable_to_owner($pet, $owner));
        $this->assertFalse($this->pets->is_assignable_to_owner($pet, $other), 'wrong owner');
        $this->assertFalse($this->pets->is_assignable_to_owner(Factory::pet($owner, ['death' => 1]), $owner));
        $this->assertFalse($this->pets->is_assignable_to_owner(Factory::pet($owner, ['transfered' => 1]), $owner));
        $this->assertFalse($this->pets->is_assignable_to_owner(Factory::pet($owner, ['deleted_at' => date('Y-m-d H:i:s')]), $owner));
        $this->assertTrue($this->pets->is_assignable_to_owner(Factory::pet($owner, ['lost' => 1]), $owner), 'lost is fine');
        $this->assertFalse($this->pets->is_assignable_to_owner(0, $owner));
        $this->assertFalse($this->pets->is_assignable_to_owner($pet, 0));
    }

    // ---- lab ingestion finders --------------------------------------------------------------------

    public function testFindByOwnerAndPetComparesUpperCaseNames(): void
    {
        $token = strtoupper(Factory::unique('Lf'));
        $owner = Factory::owner(['last_name' => $token . 'JANSEN']);
        $pet = Factory::pet($owner, ['name' => $token . 'FLUF']);
        Factory::pet($owner, ['name' => $token . 'ANDERS']);

        $rows = $this->pets->findByOwnerAndPet(strtolower($token . 'Jansen'), strtolower($token . 'Fluf'));

        $this->assertCount(1, $rows);
        $this->assertSame($pet, (int) $rows[0]->pet_id);
        $this->assertSame($owner, (int) $rows[0]->owner_id);
        $this->assertSame($token . 'FLUF', $rows[0]->pet_name);
        $this->assertSame([], $this->pets->findByOwnerAndPet($token . 'JANSEN', $token . 'FLU'), 'exact, not a prefix');
    }

    public function testFindByOwnerPhoneAndPetAlsoNeedsAMatchOnOneOfTheFourNumbers(): void
    {
        $token = strtoupper(Factory::unique('Lp'));
        $number = '0495' . random_int(100000, 999999);
        $owner = Factory::owner(['last_name' => $token . 'DE', 'phone3' => $number]);
        $pet = Factory::pet($owner, ['name' => $token . 'MIES']);
        $stranger = Factory::owner(['last_name' => $token . 'DE', 'mobile' => '0496' . random_int(100000, 999999)]);
        Factory::pet($stranger, ['name' => $token . 'MIES']);

        $rows = $this->pets->findByOwnerPhoneAndPet($number, $token . 'de', $token . 'mies');

        $this->assertCount(1, $rows);
        $this->assertSame($pet, (int) $rows[0]->pet_id);
        $this->assertSame([], $this->pets->findByOwnerPhoneAndPet('0000000000', $token . 'DE', $token . 'MIES'));
    }

    public function testFindByChipNumberNeedsTheFullChipAndGivesFalseWhenThereIsNone(): void
    {
        $chip = '9840' . random_int(100000000, 999999999);
        $pet = Factory::pet(Factory::owner(), ['chip' => $chip]);

        $rows = $this->pets->findByChipNumber($chip);

        $this->assertSame([$pet], array_map(static fn ($row) => (int) $row['id'], $rows));
        $this->assertFalse($this->pets->findByChipNumber(substr($chip, 0, 12)));
    }

    public function testFindByPetNameIsExactAndLimitedToThree(): void
    {
        $name = Factory::unique('Same');
        $owner = Factory::owner();
        for ($i = 0; $i < 4; $i++) {
            Factory::pet($owner, ['name' => $name]);
        }
        Factory::pet($owner, ['name' => $name . 'x']);

        $this->assertCount(3, $this->pets->findByPetName($name));
        $this->assertFalse($this->pets->findByPetName($name . 'nope'));
    }

    public function testDoesPetExistReturnsTheIdOrNull(): void
    {
        $pet = Factory::pet(Factory::owner());
        $deleted = Factory::pet(Factory::owner(), ['deleted_at' => date('Y-m-d H:i:s')]);
        $dead = Factory::pet(Factory::owner(), ['death' => 1]);

        $this->assertSame($pet, (int) $this->pets->does_pet_exist($pet));
        $this->assertSame($dead, (int) $this->pets->does_pet_exist($dead), 'dead pets exist');
        $this->assertNull($this->pets->does_pet_exist($deleted), 'soft-deleted pets do not');
        $this->assertNull($this->pets->does_pet_exist(99999999));
    }
}
