<?php

declare(strict_types=1);

require_once __DIR__ . '/StockModelHelper.php';

/**
 * T200 reduce / fallback_reduce, T201 reduce_stock, T202 increase_stock,
 * T203 move edge cases, T204 write_off.   Source: application/models/Stock_model.php
 *
 * Rules in plain words:
 *  - reduce, fallback_reduce, reduce_stock and increase_stock add products.dead_volume to the volume;
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

    // ------------------------------------------------------------------ T201

    public function testReduceStockWithVolumeZeroReturnsFalseBeforeAnythingHappens(): void
    {
        // QUIRK: the early return comes before the dead volume is added, so a 0 line costs nothing.
        $pid = Factory::product(['dead_volume' => 0.5]);
        $sid = Factory::stock($pid, ['volume' => 10, 'barcode' => 'ZERO' . $pid]);

        $this->assertFalse($this->stock->reduce_stock($pid, 0, 1, 'ZERO' . $pid));

        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($sid)['volume'], 0.001);
        $this->assertCount(1, $this->stockRowsOf($pid));
        $this->assertSame([], $this->stockLog($pid));
    }

    public function testReduceStockWithBarcodeAndLocationReducesTheMatchingInUseRow(): void
    {
        // 10 - (3 + 0.5) = 6.5; the same barcode at location 2 is not touched
        $pid = Factory::product(['dead_volume' => 0.5]);
        $bc = 'RS' . $pid;
        $here = Factory::stock($pid, ['volume' => 10, 'barcode' => $bc, 'location' => '1']);
        $there = Factory::stock($pid, ['volume' => 10, 'barcode' => $bc, 'location' => '2']);

        $this->stock->reduce_stock($pid, 3, 1, $bc);

        $this->assertEqualsWithDelta(6.5, (float) $this->stockRow($here)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($here)['state']);
        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($there)['volume'], 0.001);
        $this->assertCount(2, $this->stockRowsOf($pid));
        // reduce_product logged the removal
        $this->assertCount(1, $this->stockLog($pid, 'reduce_product'));
    }

    public function testReduceStockWithUnknownBarcodeInsertsAnErrorRowWithTheBarcode(): void
    {
        // The DEBUG log inside reduce_product crashes for an unknown barcode (see the next test), so this
        // runs with the log level at INFO, as production normally would.
        $pid = Factory::product(['dead_volume' => 0.5]);
        $other = Factory::stock($pid, ['volume' => 10, 'barcode' => 'HAVE' . $pid]);
        $old = $this->ci->logs->min_log_level;
        $this->ci->logs->min_log_level = INFO;

        try {
            $this->stock->reduce_stock($pid, 3, 1, 'MISSING' . $pid);
        } finally {
            $this->ci->logs->min_log_level = $old;
        }

        // existing row untouched, new ERROR row of -(3 + 0.5)
        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($other)['volume'], 0.001);
        $rows = $this->stockRowsOf($pid);
        $this->assertCount(2, $rows);
        $error = $rows[1];
        $this->assertSame('MISSING' . $pid, $error['barcode']);
        $this->assertEqualsWithDelta(-3.5, (float) $error['volume'], 0.001);
        $this->assertSame(STOCK_ERROR, (int) $error['state']);
        $this->assertSame('1', (string) $error['location']);
        $this->assertCount(1, $this->stockLog($pid, 'reduce_stock_barcode_location'));
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testReduceStockWithUnknownBarcodeCrashesWhenDebugLoggingIsOn(): void
    {
        // KNOWN BUG: reduce_product looks the product up by barcode only to log it; for an unknown
        // barcode that lookup is false, so Logs_model::stock() gets product null and throws a TypeError
        // before the ERROR row is written. Only with min_log_level == DEBUG (the model default).
        $pid = Factory::product();

        try {
            $this->stock->reduce_stock($pid, 3, 1, 'MISSING' . $pid);
            $this->fail('TypeError expected');
        } catch (\TypeError $e) {
            $this->assertStringContainsString('Logs_model::stock()', $e->getMessage());
        }

        $this->assertSame([], $this->stockRowsOf($pid));
    }

    public function testReduceStockWithLocationOnlyReducesTheOnlyRowThere(): void
    {
        // one row at location 1 (plus one at 2): 10 - 4 = 6
        $pid = Factory::product();
        $here = Factory::stock($pid, ['volume' => 10, 'location' => '1']);
        $there = Factory::stock($pid, ['volume' => 10, 'location' => '2']);

        $this->stock->reduce_stock($pid, 4, 1);

        $this->assertEqualsWithDelta(6.0, (float) $this->stockRow($here)['volume'], 0.001);
        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($there)['volume'], 0.001);
        $this->assertCount(2, $this->stockRowsOf($pid));
    }

    public function testReduceStockWithLocationOnlyAndSeveralRowsInsertsAnErrorRow(): void
    {
        $pid = Factory::product();
        $a = Factory::stock($pid, ['volume' => 10, 'location' => '1']);
        $b = Factory::stock($pid, ['volume' => 10, 'location' => '1']);

        $this->stock->reduce_stock($pid, 4, 1);

        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($a)['volume'], 0.001);
        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($b)['volume'], 0.001);
        $rows = $this->stockRowsOf($pid);
        $this->assertCount(3, $rows);
        $this->assertEqualsWithDelta(-4.0, (float) $rows[2]['volume'], 0.001);
        $this->assertSame(STOCK_ERROR, (int) $rows[2]['state']);
        $this->assertSame('1', (string) $rows[2]['location']);
        $this->assertCount(1, $this->stockLog($pid, 'reduce_stock_location'));
    }

    public function testReduceStockWithoutLocationInsertsAnErrorRowWithoutLocation(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);
        $sid = Factory::stock($pid, ['volume' => 10]);
        // log_stock.location is NOT NULL and falls back to the session location; without a session
        // location the log insert itself fails (a database error), so give the session one.
        $this->ci->session->set_userdata('location', 2);

        try {
            $this->stock->reduce_stock($pid, 2);
        } finally {
            $this->ci->session->unset_userdata('location');
        }

        // -(2 + 0.5); location column has no value (empty string)
        $rows = $this->stockRowsOf($pid);
        $this->assertCount(2, $rows);
        $this->assertEqualsWithDelta(10.0, (float) $this->stockRow($sid)['volume'], 0.001);
        $this->assertEqualsWithDelta(-2.5, (float) $rows[1]['volume'], 0.001);
        $this->assertSame(STOCK_ERROR, (int) $rows[1]['state']);
        $this->assertSame('', (string) $rows[1]['location']);
        $log = $this->stockLog($pid, 'reduce_stock_unknown');
        $this->assertCount(1, $log);
        $this->assertSame(ERROR, (int) $log[0]['level']);
        $this->assertSame(2, (int) $log[0]['location']); // the session location
    }

    public function testReduceStockMaintainsTheStateOfEveryRowOfTheProduct(): void
    {
        $pid = Factory::product();
        $bc = 'MS' . $pid;
        $target = Factory::stock($pid, ['volume' => 5, 'barcode' => $bc]);
        // empty bottle that is still IN_USE elsewhere => swept to HISTORY
        $empty = Factory::stock($pid, ['volume' => 0, 'location' => '2']);
        // negative row that is not ERROR yet => ERROR (a CHECK row here)
        $negative = Factory::stock($pid, ['volume' => -1, 'state' => STOCK_CHECK]);
        // negative HISTORY row also becomes ERROR (the sweep is on volume only)
        $negHistory = Factory::stock($pid, ['volume' => -1, 'state' => STOCK_HISTORY]);
        // positive control: a normal other row stays
        $fine = Factory::stock($pid, ['volume' => 3]);
        // another product is not touched
        $otherPid = Factory::product();
        $otherEmpty = Factory::stock($otherPid, ['volume' => 0]);

        $this->stock->reduce_stock($pid, 5, 1, $bc); // 5 - 5 = 0 => HISTORY via reduce_product

        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($target)['state']);
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($empty)['state']);
        $this->assertSame(STOCK_ERROR, (int) $this->stockRow($negative)['state']);
        $this->assertSame(STOCK_ERROR, (int) $this->stockRow($negHistory)['state']);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($fine)['state']);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($otherEmpty)['state']);
    }

    // ------------------------------------------------------------------ T202

    public function testIncreaseStockAddsToTheMatchingInUseRowWithDeadVolume(): void
    {
        // 4 + (2 + 0.5) = 6.5
        $pid = Factory::product(['dead_volume' => 0.5]);
        $sid = Factory::stock($pid, ['volume' => 4, 'barcode' => 'IN' . $pid, 'location' => '1']);

        $this->stock->increase_stock($pid, 2.0, 1, 'IN' . $pid);

        $this->assertEqualsWithDelta(6.5, (float) $this->stockRow($sid)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($sid)['state']);
        $log = $this->stockLog($pid, 'increase_stock');
        $this->assertCount(1, $log);
        $this->assertEqualsWithDelta(2.5, (float) $log[0]['volume'], 0.001);
    }

    public function testIncreaseStockWithoutTheFlagDoesNotAddDeadVolume(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);
        $sid = Factory::stock($pid, ['volume' => 4, 'barcode' => 'ND' . $pid, 'location' => '1']);

        $this->stock->increase_stock($pid, 2.0, 1, 'ND' . $pid, false);

        $this->assertEqualsWithDelta(6.0, (float) $this->stockRow($sid)['volume'], 0.001);
    }

    public function testIncreaseStockRevivesTheSmallestRowOfBarcodeAndLocationInAnyState(): void
    {
        // no IN_USE row. Candidates: HISTORY row 0 and ERROR row -1 (same barcode and location);
        // ORDER BY volume picks -1:  -1 + 2 = 1, state becomes IN_USE. The HISTORY row stays.
        $pid = Factory::product();
        $bc = 'RV' . $pid;
        $history = Factory::stock($pid, ['volume' => 0, 'barcode' => $bc, 'location' => '1', 'state' => STOCK_HISTORY]);
        $error = Factory::stock($pid, ['volume' => -1, 'barcode' => $bc, 'location' => '1', 'state' => STOCK_ERROR]);
        $otherLocation = Factory::stock($pid, ['volume' => -9, 'barcode' => $bc, 'location' => '2', 'state' => STOCK_ERROR]);

        $this->stock->increase_stock($pid, 2.0, 1, $bc);

        $this->assertEqualsWithDelta(1.0, (float) $this->stockRow($error)['volume'], 0.001);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($error)['state']);
        $this->assertEqualsWithDelta(0.0, (float) $this->stockRow($history)['volume'], 0.001);
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($history)['state']);
        $this->assertEqualsWithDelta(-9.0, (float) $this->stockRow($otherLocation)['volume'], 0.001);
        $this->assertCount(1, $this->logRows('increase_stock'));
        $this->assertSame(INFO, (int) $this->logRows('increase_stock')[0]['level']);
    }

    public function testIncreaseStockInsertsANewRowWhenNothingMatches(): void
    {
        // 3 + 0.5 dead
        $pid = Factory::product(['dead_volume' => 0.5]);

        $this->stock->increase_stock($pid, 3.0, 2, 'NEW' . $pid);

        $rows = $this->stockRowsOf($pid);
        $this->assertCount(1, $rows);
        $this->assertSame('NEW' . $pid, $rows[0]['barcode']);
        $this->assertSame('2', (string) $rows[0]['location']);
        $this->assertEqualsWithDelta(3.5, (float) $rows[0]['volume'], 0.001);
        // "to little info": no lotnr, no price, and no state given (column default 0 = CHECK)
        $this->assertSame('', (string) $rows[0]['lotnr']);
        $this->assertSame(STOCK_CHECK, (int) $rows[0]['state']);
        $warn = array_filter($this->logRows('increase_stock'), static fn ($r) => (int) $r['level'] === WARN);
        $this->assertCount(1, $warn);
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

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testMoveFromAMissingSourceRowThrowsATypeError(): void
    {
        // KNOWN BUG: the destination row is built from $this->get($stock_id)['lotnr']; when that is
        // not a string (source gone, or a NULL lotnr) the typed new() throws. stock.lotnr is NOT NULL in
        // the test schema, so a vanished source row is the way to reach this. The failed reduction has
        // already inserted an ERROR row (and the logs) before the exception.
        $pid = Factory::product();

        try {
            $this->stock->move(999999999, 1, 2, 3.0, $pid);
            $this->fail('TypeError expected');
        } catch (\TypeError $e) {
            $this->assertStringContainsString('lotnr', $e->getMessage() . ' new');
        }

        $rows = $this->stockRowsOf($pid);
        $this->assertCount(1, $rows);
        $this->assertSame(STOCK_ERROR, (int) $rows[0]['state']);
        $this->assertEqualsWithDelta(-3.0, (float) $rows[0]['volume'], 0.001);
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
