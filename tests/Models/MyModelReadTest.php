<?php

declare(strict_types=1);

/**
 * T350 MY_Model reads.   Source: application/core/MY_Model.php
 * Subjects: Sticky_model (soft deletes, table sticky) and Breeds_model (hard deletes, table breeds).
 *
 * Rules in plain words:
 *  - get() / get_all() return false (not [] or null) when nothing matches.
 *  - get(int) is a primary key lookup, get(array) filters by column.
 *  - soft delete models hide rows with deleted_at; with_trashed() shows all, only_trashed() only the deleted ones,
 *    and the choice is forgotten after one read.
 *  - fields('a, b') limits the columns for one read.
 *  - where('col', [..]) is an IN, where('col', 'like', 'x') a LIKE.
 */
final class MyModelReadTest extends CodeIgniterDatabaseTestCase
{
    private $sticky;
    private $breeds;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->sticky = $this->model('Sticky_model', 'sticky_read');
        $this->breeds = $this->model('Breeds_model', 'breeds_read');
    }

    private function sticky(string $note, array $extra = []): int
    {
        return Factory::insert('sticky', $extra + [
            'user_id' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'note' => $note,
            'private' => 0,
            'created_at' => Factory::raw('NOW()'),
        ]);
    }

    // ------------------------------------------------------------------ get / get_all

    public function testGetAndGetAllReturnFalseWhenNothingMatches(): void
    {
        $token = Factory::unique('none');

        $this->assertFalse($this->sticky->get(['note' => $token]));
        $this->assertFalse($this->sticky->get_all(['note' => $token]));
        $this->assertFalse($this->breeds->get(['name' => $token]));
        $this->assertFalse($this->breeds->get_all(['name' => $token]));
        $this->assertFalse($this->breeds->get(999999999));
    }

    public function testGetByIntegerUsesThePrimaryKey(): void
    {
        $token = Factory::unique('pk');
        $a = $this->sticky($token . 'a');
        $b = $this->sticky($token . 'b');

        $row = $this->sticky->get($b);

        $this->assertIsArray($row);
        $this->assertSame($b, (int) $row['id']);
        $this->assertSame($token . 'b', $row['note']);
        $this->assertNotSame($a, (int) $row['id']);
    }

    public function testGetByArrayFiltersByColumn(): void
    {
        $token = Factory::unique('col');
        $this->sticky($token, ['private' => 0]);
        $private = $this->sticky($token, ['private' => 1]);

        $row = $this->sticky->get(['note' => $token, 'private' => 1]);

        $this->assertSame($private, (int) $row['id']);
    }

    public function testGetAllReturnsAListOfRowArrays(): void
    {
        $token = Factory::unique('all');
        $this->sticky($token . '1');
        $this->sticky($token . '2');

        $rows = $this->sticky->where('note', 'like', $token)->get_all();

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertSame([0, 1], array_keys($rows));
        $this->assertIsArray($rows[0]);
    }

    // ------------------------------------------------------------------ soft deletes

    public function testSoftDeletedRowsAreHiddenByDefault(): void
    {
        $token = Factory::unique('sd');
        $live = $this->sticky($token . 'live');
        $dead = $this->sticky($token . 'dead', ['deleted_at' => Factory::raw('NOW()')]);

        $rows = $this->sticky->where('note', 'like', $token)->get_all();
        $this->assertCount(1, $rows);
        $this->assertSame($live, (int) $rows[0]['id']);

        // get by primary key respects it too, and count_rows as well
        $this->assertFalse($this->sticky->get($dead));
        $this->assertSame(0, $this->sticky->count_rows(array('note' => $token . 'dead')));
    }

    public function testWithTrashedIncludesAndOnlyTrashedSelectsTheDeletedRows(): void
    {
        $token = Factory::unique('tr');
        $live = $this->sticky($token . 'live');
        $dead = $this->sticky($token . 'dead', ['deleted_at' => Factory::raw('NOW()')]);

        $with = $this->sticky->with_trashed()->where('note', 'like', $token)->get_all();
        $this->assertEqualsCanonicalizing([$live, $dead], array_map('intval', array_column($with, 'id')));

        $only = $this->sticky->only_trashed()->where('note', 'like', $token)->get_all();
        $this->assertCount(1, $only);
        $this->assertSame($dead, (int) $only[0]['id']);

        $this->assertSame($dead, (int) $this->sticky->with_trashed()->get($dead)['id']);
        $this->assertFalse($this->sticky->only_trashed()->get($live));
    }

    public function testTrashedChoiceIsForgottenAfterOneRead(): void
    {
        $token = Factory::unique('rs');
        $dead = $this->sticky($token, ['deleted_at' => Factory::raw('NOW()')]);

        $this->assertNotFalse($this->sticky->with_trashed()->get($dead));
        // same call without with_trashed(): hidden again
        $this->assertFalse($this->sticky->get($dead));
    }

    public function testCountRowsHonoursSoftDeletesAndWhere(): void
    {
        $token = Factory::unique('cnt');
        $this->sticky($token);
        $this->sticky($token);
        $this->sticky($token, ['deleted_at' => Factory::raw('NOW()')]);

        $this->assertSame(2, $this->sticky->count_rows(array('note' => $token)));
        $this->assertSame(3, $this->sticky->with_trashed()->count_rows(array('note' => $token)));
        $this->assertSame(1, $this->sticky->only_trashed()->count_rows(array('note' => $token)));
        $this->assertSame(0, $this->sticky->count_rows(array('note' => $token . 'x')));
    }

    public function testModelWithoutSoftDeletesIgnoresTrashedSwitches(): void
    {
        $name = Factory::unique('hard');
        $id = Factory::breed(['name' => $name]);

        $this->assertSame($id, (int) $this->breeds->get(['name' => $name])['id']);
        $this->assertSame($id, (int) $this->breeds->with_trashed()->get(['name' => $name])['id']);
        // no deleted_at column: only_trashed() is ignored, nothing is added to the query
        $this->assertSame($id, (int) $this->breeds->only_trashed()->get(['name' => $name])['id']);
        $this->assertSame(1, $this->breeds->count_rows(array('name' => $name)));
    }

    // ------------------------------------------------------------------ fields

    public function testFieldsLimitsTheColumnsOfOneRead(): void
    {
        $token = Factory::unique('fld');
        $id = $this->sticky($token, ['private' => 1]);

        $limited = $this->sticky->fields('note, private')->get($id);
        $this->assertSame(['note', 'private'], array_keys($limited));
        $this->assertSame($token, $limited['note']);
        $this->assertSame('1', (string) $limited['private']);

        // the selection is reset: next read has every column again
        $full = $this->sticky->get($id);
        $this->assertContains('user_id', array_keys($full));
        $this->assertContains('id', array_keys($full));
        $this->assertGreaterThan(2, count($full));
    }

    public function testFieldsAlsoWorkWithGetAll(): void
    {
        $token = Factory::unique('fla');
        $this->sticky($token);

        $rows = $this->sticky->fields('id')->where('note', $token)->get_all();

        $this->assertSame(['id'], array_keys($rows[0]));
    }

    // ------------------------------------------------------------------ where variants

    public function testWhereWithAnArrayOfValuesBecomesAnInClause(): void
    {
        $token = Factory::unique('in');
        $a = $this->sticky($token . 'a');
        $b = $this->sticky($token . 'b');
        $c = $this->sticky($token . 'c');

        $rows = $this->sticky->where('id', [$a, $c])->get_all();

        $this->assertEqualsCanonicalizing([$a, $c], array_map('intval', array_column($rows, 'id')));
        $this->assertNotContains($b, array_map('intval', array_column($rows, 'id')));
    }

    public function testWhereLikeMatchesASubstring(): void
    {
        $token = Factory::unique('lk');
        $hit1 = $this->sticky('xx-' . $token . '-1');
        $hit2 = $this->sticky($token . '-2');
        $this->sticky('no match ' . substr($token, 0, 3));

        $rows = $this->sticky->where('note', 'like', $token)->get_all();

        $this->assertEqualsCanonicalizing([$hit1, $hit2], array_map('intval', array_column($rows, 'id')));
    }

    public function testWhereWithOperatorAndValue(): void
    {
        $token = Factory::unique('op');
        $a = $this->sticky($token . 'a');
        $b = $this->sticky($token . 'b');
        $this->assertGreaterThan($a, $b);

        $rows = $this->sticky->where('note', 'like', $token)->where('id >', $a)->get_all();

        $this->assertCount(1, $rows);
        $this->assertSame($b, (int) $rows[0]['id']);
    }

    public function testWhereWithANumberIsAPrimaryKeyLookup(): void
    {
        $id = $this->sticky(Factory::unique('num'));

        $row = $this->sticky->where($id)->get();

        $this->assertSame($id, (int) $row['id']);
    }

    // ------------------------------------------------------------------ order_by / limit

    public function testOrderByAndLimit(): void
    {
        $token = Factory::unique('ord');
        $a = $this->sticky($token . 'b');
        $b = $this->sticky($token . 'c');
        $c = $this->sticky($token . 'a');

        $asc = $this->sticky->where('note', 'like', $token)->order_by('note', 'ASC')->get_all();
        $this->assertSame([$c, $a, $b], array_map('intval', array_column($asc, 'id')));

        $desc = $this->sticky->where('note', 'like', $token)->order_by('note', 'DESC')->get_all();
        $this->assertSame([$b, $a, $c], array_map('intval', array_column($desc, 'id')));

        $limited = $this->sticky->where('note', 'like', $token)->order_by('note', 'ASC')->limit(2)->get_all();
        $this->assertSame([$c, $a], array_map('intval', array_column($limited, 'id')));

        $offset = $this->sticky->where('note', 'like', $token)->order_by('note', 'ASC')->limit(2, 2)->get_all();
        $this->assertSame([$b], array_map('intval', array_column($offset, 'id')));
    }

    public function testGetReturnsOnlyTheFirstRowByOrder(): void
    {
        $token = Factory::unique('one');
        $this->sticky($token . 'b');
        $first = $this->sticky($token . 'a');

        $row = $this->sticky->where('note', 'like', $token)->order_by('note', 'ASC')->get();

        $this->assertSame($first, (int) $row['id']);
    }
}
