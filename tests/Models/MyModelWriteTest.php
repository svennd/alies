<?php

declare(strict_types=1);

/**
 * T351 MY_Model writes.   Source: application/core/MY_Model.php
 * Subjects: Sticky_model (soft deletes) and Breeds_model (hard deletes).
 *
 * Rules in plain words:
 *  - insert() returns the new id and sets created_at; an array of rows returns an array of ids.
 *  - update($data, $id) / update($data, [col => v]) sets updated_at and returns the number of changed rows.
 *  - update($data) without where changes EVERY row.
 *  - delete() sets deleted_at on soft delete models and removes the row on the others; restore() clears deleted_at.
 *
 * Not tested: insert() with an unknown column calls show_error('MY_Model: Unknown column ...'), which prints the
 * error page and calls exit() in the CLI. That would kill the PHPUnit process.
 */
final class MyModelWriteTest extends CodeIgniterDatabaseTestCase
{
    private $sticky;
    private $breeds;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->sticky = $this->model('Sticky_model', 'sticky_write');
        $this->breeds = $this->model('Breeds_model', 'breeds_write');
    }

    private function stickyRow(int $id): ?array
    {
        return $this->ci->db->where('id', $id)->get('sticky')->row_array();
    }

    private function breedRow(int $id): ?array
    {
        return $this->ci->db->where('id', $id)->get('breeds')->row_array();
    }

    private function stickyData(string $note): array
    {
        return array('user_id' => Fixtures::USER_VET, 'location' => Fixtures::LOCATION_PRACTICE, 'note' => $note, 'private' => 0);
    }

    private function assertJustNow(?string $datetime): void
    {
        $this->assertNotNull($datetime);
        $this->assertEqualsWithDelta(time(), strtotime($datetime), 5);
    }

    // ------------------------------------------------------------------ insert

    public function testInsertReturnsTheNewIdAndSetsCreatedAt(): void
    {
        $note = Factory::unique('ins');

        $id = $this->sticky->insert($this->stickyData($note));

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        $row = $this->stickyRow($id);
        $this->assertSame($note, $row['note']);
        $this->assertJustNow($row['created_at']);
        $this->assertNull($row['updated_at']);
        $this->assertNull($row['deleted_at']);
    }

    public function testInsertOfSeveralRowsReturnsAnArrayOfIds(): void
    {
        $token = Factory::unique('multi');

        $ids = $this->sticky->insert(array(
            $this->stickyData($token . '1'),
            $this->stickyData($token . '2'),
            $this->stickyData($token . '3'),
        ));

        $this->assertIsArray($ids);
        $this->assertCount(3, $ids);
        $this->assertCount(3, array_unique($ids));
        foreach ($ids as $i => $id) {
            $row = $this->stickyRow((int) $id);
            $this->assertSame($token . ($i + 1), $row['note']);
            $this->assertJustNow($row['created_at']);
        }
    }

    public function testInsertCanSetAColumnOfAModelWithoutSoftDeletes(): void
    {
        $name = Factory::unique('breed');

        $id = $this->breeds->insert(array(
            'name' => $name, 'type' => 0, 'freq' => 3,
            'male_min_weight' => 1, 'male_max_weight' => 2, 'female_min_weight' => 1, 'female_max_weight' => 2,
        ));

        $row = $this->breedRow($id);
        $this->assertSame($name, $row['name']);
        $this->assertSame(3, (int) $row['freq']);
        $this->assertJustNow($row['created_at']);
    }

    // ------------------------------------------------------------------ update

    public function testUpdateByIdSetsUpdatedAtAndReturnsAffectedRows(): void
    {
        $token = Factory::unique('upd');
        $id = $this->sticky->insert($this->stickyData($token));
        $other = $this->sticky->insert($this->stickyData($token . 'other'));

        $affected = $this->sticky->update(array('note' => $token . 'new'), $id);

        $this->assertSame(1, $affected);
        $row = $this->stickyRow($id);
        $this->assertSame($token . 'new', $row['note']);
        $this->assertJustNow($row['updated_at']);
        // positive control: the other row is untouched
        $this->assertSame($token . 'other', $this->stickyRow($other)['note']);
        $this->assertNull($this->stickyRow($other)['updated_at']);
    }

    public function testUpdateWithAWhereArrayOnlyChangesMatchingRows(): void
    {
        $token = Factory::unique('uw');
        $a = $this->sticky->insert($this->stickyData($token . 'a'));
        $b = $this->sticky->insert($this->stickyData($token . 'b'));

        $affected = $this->sticky->update(array('private' => 1), array('note' => $token . 'b'));

        $this->assertSame(1, $affected);
        $this->assertSame('0', (string) $this->stickyRow($a)['private']);
        $this->assertSame('1', (string) $this->stickyRow($b)['private']);
    }

    public function testUpdateWithoutWhereChangesEveryRow(): void
    {
        // KNOWN BUG: update($data) with no where is an UPDATE on the whole table - decide in CI4 whether to keep
        Factory::breed(array('freq' => 1));
        Factory::breed(array('freq' => 2));
        $total = (int) $this->ci->db->count_all('breeds');
        $this->assertGreaterThanOrEqual(2, $total);

        $affected = $this->breeds->update(array('freq' => 77));

        $this->assertSame($total, $affected);
        $this->assertSame(0, (int) $this->ci->db->where('freq <>', 77)->count_all_results('breeds'));
        $this->assertSame($total, (int) $this->ci->db->where('freq', 77)->count_all_results('breeds'));
    }

    #[PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testUpdateWithoutWhereAlsoTouchesUpdatedAtOfEveryRow(): void
    {
        // KNOWN BUG: see above - the whole table gets the same updated_at
        $this->breeds->update(array('freq' => 5));

        $this->assertSame(0, (int) $this->ci->db->where('updated_at IS NULL', null, false)->count_all_results('breeds'));
    }

    // ------------------------------------------------------------------ delete / restore

    public function testDeleteOnASoftDeleteModelSetsDeletedAtAndKeepsTheRow(): void
    {
        $token = Factory::unique('del');
        $id = $this->sticky->insert($this->stickyData($token));
        $other = $this->sticky->insert($this->stickyData($token . 'keep'));

        $affected = $this->sticky->delete($id);

        $this->assertSame(1, $affected);
        $row = $this->stickyRow($id);
        $this->assertNotNull($row, 'row is still there');
        $this->assertJustNow($row['deleted_at']);
        $this->assertNull($this->stickyRow($other)['deleted_at']);
        $this->assertFalse($this->sticky->get($id));
    }

    public function testDeleteOnAModelWithoutSoftDeletesRemovesTheRow(): void
    {
        $id = Factory::breed();
        $other = Factory::breed();

        $affected = $this->breeds->delete($id);

        $this->assertSame(1, $affected);
        $this->assertNull($this->breedRow($id));
        $this->assertNotNull($this->breedRow($other));
    }

    public function testRestoreClearsDeletedAt(): void
    {
        $id = $this->sticky->insert($this->stickyData(Factory::unique('rest')));
        $this->sticky->delete($id);
        $this->assertNotNull($this->stickyRow($id)['deleted_at']);

        // restore() returns the result of the UPDATE query (true), not a row count
        $this->assertTrue($this->sticky->restore($id));
        $this->assertNull($this->stickyRow($id)['deleted_at']);
        $this->assertNotFalse($this->sticky->get($id));
    }

    public function testRestoreOnlyAffectsTheGivenRow(): void
    {
        $a = $this->sticky->insert($this->stickyData(Factory::unique('ra')));
        $b = $this->sticky->insert($this->stickyData(Factory::unique('rb')));
        $this->sticky->delete($a);
        $this->sticky->delete($b);

        $this->sticky->restore($a);

        $this->assertNull($this->stickyRow($a)['deleted_at']);
        $this->assertNotNull($this->stickyRow($b)['deleted_at']);
    }

    public function testForceDeleteRemovesASoftDeleteRowForGood(): void
    {
        $id = $this->sticky->insert($this->stickyData(Factory::unique('force')));

        $affected = $this->sticky->force_delete($id);

        $this->assertSame(1, $affected);
        $this->assertNull($this->stickyRow($id));
    }

    public function testTrashedIsTrueForADeletedRowAndFalseForAMissingRow(): void
    {
        $dead = $this->sticky->insert($this->stickyData(Factory::unique('td')));
        $this->sticky->delete($dead);

        $this->assertTrue($this->sticky->trashed($dead));
        $this->assertFalse($this->sticky->trashed(999999999));
    }

    #[PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testTrashedIsAlsoTrueForALiveRowAndLeavesOnlyTrashedSwitchedOn(): void
    {
        // KNOWN BUG: trashed() sets only_trashed() but never applies the filter, so any existing row counts as
        // "trashed", and the 'only' state is not reset: the next read only sees deleted rows - decide in CI4
        $live = $this->sticky->insert($this->stickyData(Factory::unique('tl')));

        $this->assertTrue($this->sticky->trashed($live));
        $this->assertFalse($this->sticky->get($live), 'the following read is silently restricted to deleted rows');
        $this->assertNotFalse($this->sticky->get($live), 'and the switch is gone after that read');
    }
}
