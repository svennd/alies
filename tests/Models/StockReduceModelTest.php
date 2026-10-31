<?php

declare(strict_types=1);

require_once __DIR__ . '/StockModelHelper.php';

/**
 * T200 reduce / fallback_reduce,
 * T203 move edge cases, T204 write_off.   Source: application/models/Stock_model.php
 *
 * Rules in plain words:
 *  - reduce and fallback_reduce add products.dead_volume to the volume;
 *    move and write_off never do.
 *  - state after a change: volume > 0 IN_USE (1), == 0 HISTORY (2), < 0 ERROR (3).
 */
final class StockReduceModelTest extends CodeIgniterDatabaseTestCase
{
    use StockModelHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStock();
    }

    // ------------------------------------------------------------------ T200

    public function testReduceAddsDeadVolumeAndLogsTheRemoval(): void
    {
        // dead volume 0.5, row 10, reduce 2  =>  10 - (2 + 0.5) = 7.5
        $pid = Factory::product(['dead_volume' => 0.5]);
        $sid = Factory::stock($pid, ['volume' => 10]);

        $this->assertTrue($this->stock->reduce($sid, $pid, 2.0, 1));

        $row = $this->stockRow($sid);
        $this->assertEqualsWithDelta(7.5, (float) $row['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $row['state']);

        $log = $this->stockLog($pid, 'stock/reduce');
        $this->assertCount(1, $log);
        $this->assertEqualsWithDelta(-2.5, (float) $log[0]['volume'], 0.001);
        $this->assertSame(DEBUG, (int) $log[0]['level']);
        $this->assertSame(1, (int) $log[0]['location']);
    }

    public function testReduceStateFollowsTheRemainingVolume(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);

        // 2.5 - (2 + 0.5) = 0  => HISTORY
        $zero = Factory::stock($pid, ['volume' => 2.5]);
        $this->stock->reduce($zero, $pid, 2.0, 1);
        $this->assertEqualsWithDelta(0.0, (float) $this->stockRow($zero)['volume'], 0.001);
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($zero)['state']);

        // 2 - 2.5 = -0.5  => ERROR
        $below = Factory::stock($pid, ['volume' => 2]);
        $this->stock->reduce($below, $pid, 2.0, 1);
        $this->assertEqualsWithDelta(-0.5, (float) $this->stockRow($below)['volume'], 0.001);
        $this->assertSame(STOCK_ERROR, (int) $this->stockRow($below)['state']);
    }

    public function testReduceWithoutDeadVolumeFlagLeavesTheDeadVolumeOut(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);
        $with = Factory::stock($pid, ['volume' => 10]);
        $without = Factory::stock($pid, ['volume' => 10]);

        $this->stock->reduce($with, $pid, 2.0, 1, true);
        $this->stock->reduce($without, $pid, 2.0, 1, false);

        // positive control: with flag 7.5, without flag 8.0
        $this->assertEqualsWithDelta(7.5, (float) $this->stockRow($with)['volume'], 0.001);
        $this->assertEqualsWithDelta(8.0, (float) $this->stockRow($without)['volume'], 0.001);
    }

    public function testReduceOfAnUnknownStockIdReturnsFalse(): void
    {
        $pid = Factory::product();
        $this->assertFalse($this->stock->reduce(999999999, $pid, 1.0, 1));
    }

    public function testFallbackReduceInsertsAnErrorRowWithDeadVolume(): void
    {
        // volume 2 + dead 0.5 => row of -2.5
        $pid = Factory::product(['dead_volume' => 0.5]);

        $this->assertTrue($this->stock->fallback_reduce($pid, 2.0, 1, 'NO_STOCK'));

        $rows = $this->stockRowsOf($pid);
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(-2.5, (float) $rows[0]['volume'], 0.001);
        $this->assertSame(STOCK_ERROR, (int) $rows[0]['state']);
        $this->assertSame('NO_STOCK', $rows[0]['info']);
        $this->assertSame('1', (string) $rows[0]['location']);

        // log level is DEBUG in the test run, so the removal is logged too
        $log = $this->stockLog($pid, 'stock/fallback_reduce');
        $this->assertCount(1, $log);
        $this->assertEqualsWithDelta(-2.5, (float) $log[0]['volume'], 0.001);
    }

    // ------------------------------------------------------------------ T203

    private function movable(array $overrides = [], int $pidDead = 0): array
    {
        $pid = Factory::product(['dead_volume' => $pidDead]);
        $src = Factory::stock($pid, $overrides + [
            'volume' => 10, 'location' => '1', 'lotnr' => 'MV' . $pid, 'in_price' => 2.50,
            'supplier' => 'Sup', 'eol' => '2031-05-05',
        ]);

        return [$pid, $src];
    }

    public function testMoveOfVolumeZeroReturnsFalseAndChangesNothing(): void
    {
        [$pid, $src] = $this->movable();

        $this->assertFalse($this->stock->move($src, 1, 2, 0.0, $pid));

        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($src)['volume'], 0.001);
        $this->assertCount(1, $this->stockRowsOf($pid));
        $this->assertSame([], $this->stockLog($pid));
    }

    public function testMoveMergesIntoAMatchingDestinationRowAndIgnoresDeadVolume(): void
    {
        // source 10 - 3 = 7 (no dead volume on move, even with dead_volume 0.5); destination 4 + 3 = 7
        [$pid, $src] = $this->movable([], 0);
        $this->ci->db->where('id', $pid)->update('products', ['dead_volume' => 0.5]);
        $row = $this->stockRow($src);
        $dst = Factory::stock($pid, [
            'volume' => 4, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 2.50,
            'supplier' => 'Sup', 'eol' => '2031-05-05',
        ]);

        $this->stock->move($src, 1, 2, 3.0, $pid);

        $this->assertEqualsWithDelta(7.0, (float) $this->stockRow($src)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($src)['state']);
        $this->assertEqualsWithDelta(7.0, (float) $this->stockRow($dst)['volume'], 0.001);
        $this->assertCount(2, $this->stockRowsOf($pid)); // nothing created
        $this->assertCount(1, $this->stockLog($pid, 'move_stock_from'));
        $this->assertCount(1, $this->stockLog($pid, 'move_stock_to'));
        $this->assertEqualsWithDelta(-3.0, (float) $this->stockLog($pid, 'move_stock_from')[0]['volume'], 0.001);
    }

    public function testMoveMergesWhenEolAndSupplierAreBothNull(): void
    {
        // plain "=" never matches NULL; the query has an explicit IS NULL branch
        [$pid, $src] = $this->movable(['eol' => null, 'supplier' => null]);
        $row = $this->stockRow($src);
        $dst = Factory::stock($pid, [
            'volume' => 1, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 2.50,
            'supplier' => null, 'eol' => null,
        ]);

        $this->stock->move($src, 1, 2, 3.0, $pid);

        $this->assertEqualsWithDelta(7.0, (float) $this->stockRow($src)['volume'], 0.001);
        $this->assertEqualsWithDelta(4.0, (float) $this->stockRow($dst)['volume'], 0.001);
        $this->assertCount(2, $this->stockRowsOf($pid));
    }

    public function testMoveCreatesANewDestinationRowWhenNothingMatches(): void
    {
        [$pid, $src] = $this->movable();
        $row = $this->stockRow($src);
        // near misses at the destination: other supplier, other eol, other price, other lot
        $miss1 = Factory::stock($pid, ['volume' => 1, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 2.50, 'supplier' => 'Other', 'eol' => '2031-05-05']);
        $miss2 = Factory::stock($pid, ['volume' => 1, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 2.50, 'supplier' => 'Sup', 'eol' => '2032-01-01']);
        $miss3 = Factory::stock($pid, ['volume' => 1, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 9.99, 'supplier' => 'Sup', 'eol' => '2031-05-05']);
        $miss4 = Factory::stock($pid, ['volume' => 1, 'location' => '2', 'lotnr' => 'OTHERLOT', 'in_price' => 2.50, 'supplier' => 'Sup', 'eol' => '2031-05-05']);

        $this->stock->move($src, 1, 2, 3.0, $pid);

        $this->assertEqualsWithDelta(7.0, (float) $this->stockRow($src)['volume'], 0.001);
        foreach ([$miss1, $miss2, $miss3, $miss4] as $id) {
            $this->assertEqualsWithDelta(1.0, (float) $this->stockRow($id)['volume'], 0.001);
        }
        $rows = $this->stockRowsOf($pid);
        $this->assertCount(6, $rows);
        $new = end($rows);
        $this->assertSame('2', (string) $new['location']);
        $this->assertEqualsWithDelta(3.0, (float) $new['volume'], 0.001);
        $this->assertSame($row['lotnr'], $new['lotnr']);
        $this->assertEqualsWithDelta(2.5, (float) $new['in_price'], 0.001);
        $this->assertSame('2031-05-05', $new['eol']);
        $this->assertSame('Sup', $new['supplier']);
        $this->assertSame(STOCK_IN_USE, (int) $new['state']);
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testFullMoveDoesNotMergeIntoAnInUseDestinationButRevivesAHistoryRow(): void
    {
        // KNOWN BUG: move_add compares the state of the destination with the state of the SOURCE after
        // the reduction. A full move puts the source in HISTORY (2), so only a HISTORY row matches.
        // Case A: IN_USE destination (4) is ignored, a new row of 3 is created (a duplicate for fix_duplicates).
        [$pid, $src] = $this->movable(['volume' => 3]);
        $row = $this->stockRow($src);
        $dst = Factory::stock($pid, ['volume' => 4, 'location' => '2', 'lotnr' => $row['lotnr'], 'in_price' => 2.50, 'supplier' => 'Sup', 'eol' => '2031-05-05']);

        $this->stock->move($src, 1, 2, 3.0, $pid);

        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($src)['state']);
        $this->assertEqualsWithDelta(0.0, (float) $this->stockRow($src)['volume'], 0.001);
        $this->assertEqualsWithDelta(4.0, (float) $this->stockRow($dst)['volume'], 0.001);
        $rows = $this->stockRowsOf($pid);
        $this->assertCount(3, $rows);
        $this->assertEqualsWithDelta(3.0, (float) end($rows)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) end($rows)['state']);

        // Case B: a HISTORY row at the destination is matched and revived: 0 + 3 = 3, state IN_USE.
        [$pid2, $src2] = $this->movable(['volume' => 3]);
        $row2 = $this->stockRow($src2);
        $hist = Factory::stock($pid2, ['volume' => 0, 'state' => STOCK_HISTORY, 'location' => '2', 'lotnr' => $row2['lotnr'], 'in_price' => 2.50, 'supplier' => 'Sup', 'eol' => '2031-05-05']);

        $this->stock->move($src2, 1, 2, 3.0, $pid2);

        $this->assertCount(2, $this->stockRowsOf($pid2));
        $this->assertEqualsWithDelta(3.0, (float) $this->stockRow($hist)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($hist)['state']);
    }

    public function testMoveFromAMissingSourceRowChangesNothingAndReturnsFalse(): void
    {
        // the source row vanished (removed or merged since the form was opened): nothing is moved,
        // no ERROR row or stock log is written, only an error is logged
        $pid = Factory::product();

        $this->assertFalse($this->stock->move(999999999, 1, 2, 3.0, $pid));

        $this->assertSame([], $this->stockRowsOf($pid));
        $this->assertCount(0, $this->stockLog($pid, 'move_stock_from'));
        $this->assertCount(0, $this->stockLog($pid, 'move_stock_to'));
        $this->assertCount(1, $this->logRows('move_stock'));
        $this->assertSame(ERROR, (int) $this->logRows('move_stock')[0]['level']);
    }

    // ------------------------------------------------------------------ T204

    public static function writeOffCases(): array
    {
        // [start volume, state before, written off, expected volume, expected state]
        return [
            'remaining 3 -> in use'   => [5, STOCK_IN_USE, 2, 3.0, STOCK_IN_USE],
            'exactly 0 -> history'    => [5, STOCK_IN_USE, 5, 0.0, STOCK_HISTORY],
            'below 0 -> error'        => [5, STOCK_IN_USE, 6, -1.0, STOCK_ERROR],
            'check row becomes in use' => [5, STOCK_CHECK, 2, 3.0, STOCK_IN_USE],
            'history row below 0'     => [0, STOCK_HISTORY, 1, -1.0, STOCK_ERROR],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('writeOffCases')]
    public function testWriteOffSetsTheStateFromTheRemainingVolume(float $start, int $stateBefore, float $off, float $volume, int $state): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]); // never used by write_off
        $sid = Factory::stock($pid, ['volume' => $start, 'state' => $stateBefore]);
        $bystander = Factory::stock($pid, ['volume' => 5]);

        $this->stock->write_off($sid, $off);

        $row = $this->stockRow($sid);
        $this->assertEqualsWithDelta($volume, (float) $row['volume'], 0.001);
        $this->assertSame($state, (int) $row['state']);
        $this->assertEqualsWithDelta(5.0, (float) $this->stockRow($bystander)['volume'], 0.001);
    }
}
