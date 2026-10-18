<?php

declare(strict_types=1);

/**
 * T362 Breeds_model::search_by_name and get_breed_stats.   Source: application/models/Breeds_model.php
 *
 * Rules in plain words:
 *  - search_by_name: fewer than 4 characters gives []; the name is a prefix match; only alive, not lost pets whose
 *    owner has a non-empty last_bill; newest last_bill first; the owner columns come with the row.
 *  - get_breed_stats: pets of the breed that are alive, not lost, younger than 20 (calendar year difference) and
 *    have a last_weight other than 0.
 */
final class BreedsStatisticsTest extends CodeIgniterDatabaseTestCase
{
    private $breeds;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->breeds = $this->model('Breeds_model', 'breeds_stat');
    }

    private function uniqueBreed(): array
    {
        $prefix = 'Zq' . substr(bin2hex(random_bytes(4)), 0, 6);
        $id = Factory::breed(['name' => $prefix . ' terrier']);

        return array($id, $prefix);
    }

    public function testNamesShorterThanFourCharactersGiveAnEmptyList(): void
    {
        [$id, $prefix] = $this->uniqueBreed();
        Factory::pet(Factory::owner(), ['breed' => $id]);

        $this->assertSame(array(), $this->breeds->search_by_name(''));
        $this->assertSame(array(), $this->breeds->search_by_name(substr($prefix, 0, 3)));
        // positive control: four characters search
        $this->assertNotEmpty($this->breeds->search_by_name(substr($prefix, 0, 4)));
    }

    public function testSearchIsAPrefixMatchAndReturnsPetAndOwner(): void
    {
        [$id, $prefix] = $this->uniqueBreed();
        $owner = Factory::owner(['last_name' => 'Vindmij']);
        Factory::pet($owner, ['breed' => $id, 'name' => 'Fikkie', 'type' => 0]);

        $rows = $this->breeds->search_by_name($prefix);

        $this->assertCount(1, $rows);
        $this->assertSame($prefix . ' terrier', $rows[0]['breed']);
        $this->assertSame('Fikkie', $rows[0]['name']);
        $this->assertSame('Vindmij', $rows[0]['last_name']);
        $this->assertSame($owner, (int) $rows[0]['id']);
        $this->assertCount(0, $this->breeds->search_by_name('terrier ' . $prefix));
        $this->assertCount(0, $this->breeds->search_by_name(substr($prefix, 2)));
    }

    public function testSearchLeavesOutDeadLostPetsAndOwnersWithoutLastBill(): void
    {
        [$id, $prefix] = $this->uniqueBreed();
        $owner = Factory::owner();
        $good = Factory::pet($owner, ['breed' => $id, 'name' => 'Goed']);
        Factory::pet($owner, ['breed' => $id, 'name' => 'Dood', 'death' => 1]);
        Factory::pet($owner, ['breed' => $id, 'name' => 'Weg', 'lost' => 1]);
        $noBill = Factory::owner(['last_bill' => null]);
        Factory::pet($noBill, ['breed' => $id, 'name' => 'ZonderFactuur']);

        $rows = $this->breeds->search_by_name($prefix);

        $this->assertSame(array('Goed'), array_column($rows, 'name'));
        $this->assertNotSame(0, $good);
    }

    public function testSearchOrdersByLastBillNewestFirst(): void
    {
        [$id, $prefix] = $this->uniqueBreed();
        $old = Factory::owner(['last_bill' => '2020-01-01']);
        $new = Factory::owner(['last_bill' => '2024-01-01']);
        $mid = Factory::owner(['last_bill' => '2022-01-01']);
        foreach ([$old, $new, $mid] as $o) {
            Factory::pet($o, ['breed' => $id]);
        }

        $rows = $this->breeds->search_by_name($prefix);

        $this->assertSame([$new, $mid, $old], array_map('intval', array_column($rows, 'id')));
    }

    public function testSearchEscapesLikeWildcards(): void
    {
        [$id, $prefix] = $this->uniqueBreed();
        Factory::pet(Factory::owner(), ['breed' => $id]);

        $this->assertSame(array(), $this->breeds->search_by_name('%%%%'));
        $this->assertSame(array(), $this->breeds->search_by_name('____'));
    }

    public function testBreedStatsKeepsOnlyAliveYoungWeighedPets(): void
    {
        $id = Factory::breed();
        $owner = Factory::owner();
        $year = (int) date('Y');
        $ok = Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 3) . '-06-01', 'last_weight' => 12.5, 'gender' => 1]);
        Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 3) . '-06-01', 'last_weight' => 0]);
        Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 3) . '-06-01', 'last_weight' => 5, 'death' => 1]);
        Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 3) . '-06-01', 'last_weight' => 5, 'lost' => 1]);
        Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 20) . '-01-01', 'last_weight' => 5]); // age 20: out
        Factory::pet($owner, ['breed' => Factory::breed(), 'birth' => ($year - 3) . '-06-01', 'last_weight' => 5]); // other breed
        $edge = Factory::pet($owner, ['breed' => $id, 'birth' => ($year - 19) . '-12-31', 'last_weight' => 30, 'gender' => 0]);

        $rows = $this->breeds->get_breed_stats($id);

        $this->assertCount(2, $rows);
        usort($rows, fn ($a, $b) => (float) $a['last_weight'] <=> (float) $b['last_weight']);
        $this->assertEqualsWithDelta(12.5, (float) $rows[0]['last_weight'], 0.001);
        $this->assertSame(1, (int) $rows[0]['gender']);
        $this->assertSame(3, (int) $rows[0]['age']);
        // age is the difference of calendar years, so born 31 December 19 years ago is 19
        $this->assertEqualsWithDelta(30.0, (float) $rows[1]['last_weight'], 0.001);
        $this->assertSame(19, (int) $rows[1]['age']);
        $this->assertSame(['last_weight', 'gender', 'age'], array_keys($rows[0]));
        $this->assertNotSame($ok, $edge);
    }

    public function testBreedStatsOfABreedWithoutPetsIsEmpty(): void
    {
        $this->assertSame(array(), $this->breeds->get_breed_stats(Factory::breed()));
    }
}
