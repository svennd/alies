<?php

declare(strict_types=1);

/**
 * T352 MY_Model relations (with_<relation>).   Source: application/core/MY_Model.php (join_temporary_results)
 * Subjects: Bills_model (has_one owner / vet / location) and Pets_model (has_one owners, has_many pets_weight).
 *
 * Rules in plain words:
 *  - with_owner('fields:last_name') puts the related row (id + the chosen fields) under the key 'owner'.
 *  - If the relation key is also a local column (bills.vet, bills.location) a match replaces the scalar by the
 *    related row; a row without a match keeps its scalar (when at least one other row matched).
 *  - has_many gives a list per row. The related model's soft delete filter applies: a soft deleted owner is not attached.
 *  - 'where:' inside with_ drops the rows without a match.
 *
 * Note: Pets_model declares foreign_table 'Pets_weight' (capital P). with_pets_weight('fields:...') selects from that
 * spelling and gives a database error on a case sensitive file system (Linux), so these tests use it without fields.
 */
final class MyModelRelationsTest extends CodeIgniterDatabaseTestCase
{
    private $bills;
    private $pets;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->bills = $this->model('Bills_model', 'bills_rel');
        $this->pets = $this->model('Pets_model', 'pets_rel');
    }

    // ------------------------------------------------------------------ has_one

    public function testHasOnePutsTheRelatedRowUnderTheRelationKey(): void
    {
        $owner = Factory::owner(['last_name' => 'Relatie']);
        $bill = Factory::bill($owner);

        $row = $this->bills->with_owner('fields:last_name')->get($bill);

        $this->assertSame($bill, (int) $row['id']);
        $this->assertSame($owner, (int) $row['owner']['id']);
        $this->assertSame('Relatie', $row['owner']['last_name']);
        // only the foreign key and the requested field are selected
        $this->assertSame(['id', 'last_name'], array_keys($row['owner']));
        // local column is untouched
        $this->assertSame($owner, (int) $row['owner_id']);
    }

    public function testHasOneWithoutFieldsReturnsTheWholeRelatedRow(): void
    {
        $owner = Factory::owner(['last_name' => 'Volledig', 'city' => 'Mol']);
        $bill = Factory::bill($owner);

        $row = $this->bills->with_owner()->get($bill);

        $this->assertSame('Volledig', $row['owner']['last_name']);
        $this->assertSame('Mol', $row['owner']['city']);
    }

    public function testRelationKeyWithTheSameNameAsTheLocalColumnIsReplacedWhenMatched(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 0, ['vet' => Fixtures::USER_VET, 'location' => Fixtures::LOCATION_PRACTICE]);

        $row = $this->bills->with_vet('fields:username')->with_location('fields:name')->get($bill);

        $this->assertIsArray($row['vet']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['vet']['id']);
        $this->assertArrayHasKey('username', $row['vet']);
        $this->assertIsArray($row['location']);
        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $row['location']['id']);
        $this->assertSame('Praktijk', $row['location']['name']);
    }

    public function testRelationKeyWithTheSameNameKeepsTheScalarForAnUnmatchedRow(): void
    {
        $owner = Factory::owner();
        $matched = Factory::bill($owner, 0, ['vet' => Fixtures::USER_VET]);
        $unmatched = Factory::bill($owner, 0, ['vet' => 987654]);

        $rows = $this->bills->with_vet('fields:username')->where('owner_id', $owner)->order_by('id', 'ASC')->get_all();

        $this->assertCount(2, $rows);
        $this->assertSame($matched, (int) $rows[0]['id']);
        $this->assertIsArray($rows[0]['vet']);
        $this->assertSame($unmatched, (int) $rows[1]['id']);
        $this->assertSame(987654, (int) $rows[1]['vet']);
        $this->assertFalse(is_array($rows[1]['vet']));
    }

    public function testASingleRowWithoutAnyMatchGetsNullUnderTheRelationKey(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 0, ['vet' => 987654]);

        $row = $this->bills->with_vet('fields:username')->get($bill);

        // no related row at all: the scalar is replaced by null
        $this->assertNull($row['vet']);
    }

    public function testHasOneForSeveralRowsAttachesTheRightOwnerToEachRow(): void
    {
        $o1 = Factory::owner(['last_name' => 'Een']);
        $o2 = Factory::owner(['last_name' => 'Twee']);
        $b1 = Factory::bill($o1);
        $b2 = Factory::bill($o2);

        $rows = $this->bills->with_owner('fields:last_name')->where('id', [$b1, $b2])->order_by('id', 'ASC')->get_all();

        $this->assertSame('Een', $rows[0]['owner']['last_name']);
        $this->assertSame('Twee', $rows[1]['owner']['last_name']);
    }

    // ------------------------------------------------------------------ has_many

    public function testHasManyReturnsAListPerRow(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $lonely = Factory::pet($owner);
        $w1 = Factory::petWeight($pet, 10.5);
        $w2 = Factory::petWeight($pet, 11.0);

        $rows = $this->pets->with_pets_weight()->where('id', [$pet, $lonely])->order_by('id', 'ASC')->get_all();

        $this->assertSame($pet, (int) $rows[0]['id']);
        $this->assertCount(2, $rows[0]['pets_weight']);
        $this->assertSame([0, 1], array_keys($rows[0]['pets_weight']));
        $weights = array_map('floatval', array_column($rows[0]['pets_weight'], 'weight'));
        sort($weights);
        $this->assertEqualsWithDelta(10.5, $weights[0], 0.001);
        $this->assertEqualsWithDelta(11.0, $weights[1], 0.001);
        // a pet without weights has no entry (not an empty list)
        $this->assertSame($lonely, (int) $rows[1]['id']);
        $this->assertArrayNotHasKey('pets_weight', $rows[1]);
        $this->assertNotSame($w1, $w2);
    }

    public function testHasManyOfASingleRowIsAList(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::petWeight($pet, 3.0);

        $row = $this->pets->with_pets_weight()->get($pet);

        $this->assertCount(1, $row['pets_weight']);
        $this->assertSame(0, array_key_first($row['pets_weight']));
    }

    // ------------------------------------------------------------------ soft deletes on the related model

    public function testASoftDeletedOwnerIsNotAttached(): void
    {
        $alive = Factory::owner(['last_name' => 'Levend']);
        $dead = Factory::owner(['last_name' => 'Weg', 'deleted_at' => Factory::raw('NOW()')]);
        $billAlive = Factory::bill($alive);
        $billDead = Factory::bill($dead);

        $rows = $this->bills->with_owner('fields:last_name')->where('id', [$billAlive, $billDead])->order_by('id', 'ASC')->get_all();

        $this->assertCount(2, $rows, 'the bill itself stays');
        $this->assertSame('Levend', $rows[0]['owner']['last_name']);
        $this->assertArrayNotHasKey('owner', $rows[1]);
    }

    public function testASingleBillOfASoftDeletedOwnerGetsNullAsOwner(): void
    {
        $dead = Factory::owner(['deleted_at' => Factory::raw('NOW()')]);
        $bill = Factory::bill($dead);

        $row = $this->bills->with_owner('fields:last_name')->get($bill);

        $this->assertSame($bill, (int) $row['id']);
        $this->assertNull($row['owner']);
    }

    // ------------------------------------------------------------------ where: inside with_

    public function testWhereInsideWithDropsRowsWithoutAMatch(): void
    {
        $match = Factory::owner(['last_name' => 'Zoekmij']);
        $other = Factory::owner(['last_name' => 'Anders']);
        $bMatch = Factory::bill($match);
        $bOther = Factory::bill($other);

        $rows = $this->bills
            ->with_owner("fields:last_name|where:owners.last_name = 'Zoekmij'")
            ->where('id', [$bMatch, $bOther])
            ->get_all();

        $this->assertCount(1, $rows);
        $rows = array_values($rows);
        $this->assertSame($bMatch, (int) $rows[0]['id']);
        $this->assertSame('Zoekmij', $rows[0]['owner']['last_name']);
        // positive control: without the where both bills come back
        $all = $this->bills->with_owner('fields:last_name')->where('id', [$bMatch, $bOther])->get_all();
        $this->assertCount(2, $all);
    }

    #[PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testWhereInsideWithDoesNotDropRowsWhenNothingAtAllMatches(): void
    {
        // KNOWN BUG: the drop only happens when at least one related row was found; when none matches, the rows
        // stay (the last one gets null as owner) - decide in CI4 whether to keep
        $owner = Factory::owner(['last_name' => 'Anders']);
        $bill = Factory::bill($owner);

        $rows = $this->bills
            ->with_owner("fields:last_name|where:owners.last_name = 'NietBestaand'")
            ->where('id', $bill)
            ->get_all();

        $this->assertCount(1, $rows);
        $this->assertSame($bill, (int) $rows[0]['id']);
        $this->assertNull($rows[0]['owner']);
    }
}
