<?php

declare(strict_types=1);

require_once __DIR__ . '/StockModelHelper.php';

/**
 * T205 fix_duplicates, T206 remove_dead_products_from_stock + get_problems.
 * Source: application/models/Stock_model.php
 *
 * Rules in plain words:
 *  - fix_duplicates merges IN_USE rows with the same product, location, lotnr, in_price and eol into
 *    one new IN_USE row; the originals become state MERGE (4) with volume 0.
 *  - remove_dead_products_from_stock sends rows of soft-deleted products to HISTORY and keeps the volume.
 *  - get_problems lists ERROR rows and negative IN_USE rows, joined to product and location.
 */
final class StockMaintenanceModelTest extends CodeIgniterDatabaseTestCase
{
    use StockModelHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStock();
    }

    // ------------------------------------------------------------------ T205

    public function testFixDuplicatesMergesEveryGroupIntoOneNewRow(): void
    {
        // The seed has no IN_USE duplicates (5001/5002 differ in location), so the counts are exact.
        $pidA = Factory::product();
        $pidB = Factory::product();
        $common = ['location' => '1', 'lotnr' => 'DUP-A', 'in_price' => 2.00, 'eol' => '2031-01-01'];
        // group A: 3 + 4 = 7 in two rows
        $a1 = Factory::stock($pidA, $common + ['volume' => 3, 'barcode' => 'BCA' . $pidA]);
        $a2 = Factory::stock($pidA, $common + ['volume' => 4, 'barcode' => 'BCA' . $pidA]);
        // group B: 1 + 1 + 1 = 3 in three rows
        $commonB = ['location' => '2', 'lotnr' => 'DUP-B', 'in_price' => 1.00, 'eol' => '2031-02-02'];
        $b = [];
        for ($i = 0; $i < 3; $i++) {
            $b[] = Factory::stock($pidB, $commonB + ['volume' => 1, 'barcode' => 'BCB' . $pidB]);
        }
        // not duplicates: other price, other lot, other location
        $n1 = Factory::stock($pidA, array_merge($common, ['in_price' => 2.50, 'volume' => 9]));
        $n2 = Factory::stock($pidA, array_merge($common, ['lotnr' => 'OTHER', 'volume' => 9]));
        $n3 = Factory::stock($pidA, array_merge($common, ['location' => '2', 'volume' => 9]));

        $result = $this->stock->fix_duplicates();

        // 2 + 3 lines merged into 2 new rows
        $this->assertSame(['lines_merged' => 5, 'new_merged' => 2], $result);

        foreach ([$a1, $a2, ...$b] as $old) {
            $row = $this->stockRow($old);
            $this->assertSame(STOCK_MERGE, (int) $row['state']);
            $this->assertEqualsWithDelta(0.0, (float) $row['volume'], 0.001);
        }
        foreach ([[$n1, STOCK_IN_USE], [$n2, STOCK_IN_USE], [$n3, STOCK_IN_USE]] as [$id, $state]) {
            $row = $this->stockRow($id);
            $this->assertSame($state, (int) $row['state']);
            $this->assertEqualsWithDelta(9.0, (float) $row['volume'], 0.001);
        }

        $newA = array_values(array_filter($this->stockRowsOf($pidA), static fn ($r) => $r['lotnr'] === 'DUP-A' && (int) $r['state'] === STOCK_IN_USE && (string) $r['location'] === '1' && abs((float) $r['in_price'] - 2.0) < 0.001));
        $this->assertCount(1, $newA);
        $this->assertEqualsWithDelta(7.0, (float) $newA[0]['volume'], 0.001);
        $this->assertSame('2031-01-01', $newA[0]['eol']);
        $this->assertSame('BCA' . $pidA, $newA[0]['barcode']);
        $newB = array_values(array_filter($this->stockRowsOf($pidB), static fn ($r) => (int) $r['state'] === STOCK_IN_USE));
        $this->assertCount(1, $newB);
        $this->assertEqualsWithDelta(3.0, (float) $newB[0]['volume'], 0.001);
        $this->assertSame('2', (string) $newB[0]['location']);

        $this->assertCount(2, $this->logRows('merge_stock'));

        // a second run finds nothing to do
        $this->assertSame(['lines_merged' => 0, 'new_merged' => 0], $this->stock->fix_duplicates());
    }

    public function testFixDuplicatesAlsoMergesRowsWithoutEol(): void
    {
        // CodeIgniter turns where('eol' => null) into "eol IS NULL", so NULL eol groups merge: 3 + 4 = 7.
        $pid = Factory::product();
        $common = ['location' => '1', 'lotnr' => 'NOEOL', 'in_price' => 2.00, 'eol' => null];
        $a = Factory::stock($pid, $common + ['volume' => 3]);
        $b = Factory::stock($pid, $common + ['volume' => 4]);

        $result = $this->stock->fix_duplicates();

        $this->assertSame(['lines_merged' => 2, 'new_merged' => 1], $result);
        $this->assertSame(STOCK_MERGE, (int) $this->stockRow($a)['state']);
        $this->assertSame(STOCK_MERGE, (int) $this->stockRow($b)['state']);
        $inUse = array_values(array_filter($this->stockRowsOf($pid), static fn ($r) => (int) $r['state'] === STOCK_IN_USE));
        $this->assertCount(1, $inUse);
        $this->assertEqualsWithDelta(7.0, (float) $inUse[0]['volume'], 0.001);
        $this->assertNull($inUse[0]['eol']);
        $this->assertSame([], $this->logRows('merge_stock_failed_to_find_lines'));
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testFixDuplicatesWipesNonInUseRowsWithTheSameKey(): void
    {
        // KNOWN BUG: the UPDATE that retires the originals has no state filter. A HISTORY row (here volume 9,
        // e.g. kept for the record) and a CHECK row with the same product/location/lot/price/eol are set to
        // MERGE with volume 0 as well; their volume is not part of the merged total (3 + 4 = 7).
        $pid = Factory::product();
        $common = ['location' => '1', 'lotnr' => 'WIPE', 'in_price' => 2.00, 'eol' => '2031-01-01'];
        Factory::stock($pid, $common + ['volume' => 3]);
        Factory::stock($pid, $common + ['volume' => 4]);
        $history = Factory::stock($pid, $common + ['volume' => 9, 'state' => STOCK_HISTORY]);
        $check = Factory::stock($pid, $common + ['volume' => 8, 'state' => STOCK_CHECK]);

        $result = $this->stock->fix_duplicates();

        $this->assertSame(['lines_merged' => 2, 'new_merged' => 1], $result);
        foreach ([$history, $check] as $id) {
            $this->assertSame(STOCK_MERGE, (int) $this->stockRow($id)['state']);
            $this->assertEqualsWithDelta(0.0, (float) $this->stockRow($id)['volume'], 0.001);
        }
    }

    // ------------------------------------------------------------------ T206

    public function testRemoveDeadProductsMovesRowsOfDeletedProductsToHistoryAndKeepsTheVolume(): void
    {
        $deletedAt = date('Y-m-d H:i:s');
        $dead = Factory::product(['name' => 'Dead ' . Factory::unique(), 'deleted_at' => $deletedAt]);
        $live = Factory::product();
        $deadUse = Factory::stock($dead, ['volume' => 6]);
        $deadUse2 = Factory::stock($dead, ['volume' => 1.5, 'location' => '2']);
        $deadEmpty = Factory::stock($dead, ['volume' => 0]);          // volume not > 0: untouched
        $deadNeg = Factory::stock($dead, ['volume' => -2, 'state' => STOCK_ERROR]); // not > 0: untouched
        $deadCheck = Factory::stock($dead, ['volume' => 4, 'state' => STOCK_CHECK]);
        $liveUse = Factory::stock($live, ['volume' => 5]);

        // The seed has one such row too (stock 5008 of the deleted product 4006). Count it independently.
        $seeded = (int) $this->ci->db->query(
            'SELECT COUNT(*) AS c FROM stock JOIN products ON products.id = stock.product_id
             WHERE products.deleted_at IS NOT NULL AND stock.volume > 0'
        )->row()->c - 3; // minus my 3 rows (deadUse, deadUse2, deadCheck)
        $this->assertGreaterThanOrEqual(1, $seeded);

        $affected = $this->stock->remove_dead_products_from_stock();

        $this->assertSame(3 + $seeded, $affected);
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($deadUse)['state']);
        $this->assertEqualsWithDelta(6.0, (float) $this->stockRow($deadUse)['volume'], 0.001);
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($deadUse2)['state']);
        $this->assertEqualsWithDelta(1.5, (float) $this->stockRow($deadUse2)['volume'], 0.001);
        // pin: the UPDATE has no state filter, so a CHECK (not yet verified) row goes to HISTORY too
        $this->assertSame(STOCK_HISTORY, (int) $this->stockRow($deadCheck)['state']);
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($deadEmpty)['state']);
        $this->assertSame(STOCK_ERROR, (int) $this->stockRow($deadNeg)['state']);
        // positive control
        $this->assertSame(STOCK_IN_USE, (int) $this->stockRow($liveUse)['state']);

        // only the IN_USE rows (2 of mine + the seeded one) are logged, as WARN
        $logs = array_filter($this->logRows('rm_dead_products'), static fn ($r) => str_contains($r['msg'], 'Dead '));
        $this->assertCount(2, $logs);
        $this->assertSame(WARN, (int) array_values($logs)[0]['level']);

        // nothing of the dead product is IN_USE with volume any more
        $this->assertSame(0, $this->ci->db->where('state', STOCK_IN_USE)->where('product_id', $dead)->where('volume >', 0)->count_all_results('stock'));
    }

    public function testGetProblemsListsErrorRowsAndNegativeInUseRowsButHidesRowsWithoutAKnownLocation(): void
    {
        $pid = Factory::product(['unit_sell' => 'ml']);
        $error = Factory::stock($pid, ['volume' => -1, 'state' => STOCK_ERROR, 'location' => '1']);
        $negative = Factory::stock($pid, ['volume' => -2, 'state' => STOCK_IN_USE, 'location' => '2']);
        // not problems
        $positive = Factory::stock($pid, ['volume' => 3, 'state' => STOCK_IN_USE]);
        $negHistory = Factory::stock($pid, ['volume' => -2, 'state' => STOCK_HISTORY]);
        $negCheck = Factory::stock($pid, ['volume' => -2, 'state' => STOCK_CHECK]);
        // ERROR rows that cannot be joined to a location: reduce_stock without location stores ''
        $noLocation = Factory::stock($pid, ['volume' => -4, 'state' => STOCK_ERROR, 'location' => '']);
        $badLocation = Factory::stock($pid, ['volume' => -5, 'state' => STOCK_ERROR, 'location' => '99']);

        $mine = array_values(array_filter(
            $this->stock->get_problems(),
            static fn ($r) => (int) $r['product_id'] === $pid
        ));

        $ids = array_map(static fn ($r) => (int) $r['id'], $mine);
        sort($ids);
        $expected = [$error, $negative];
        sort($expected);
        $this->assertSame($expected, $ids);
        // KNOWN QUIRK: the two ERROR rows without a valid location are invisible on the problem screen
        $this->assertNotContains($noLocation, $ids);
        $this->assertNotContains($badLocation, $ids);

        $byId = array_column($mine, null, 'id');
        $this->assertSame('ml', $byId[$error]['unit_sell']);
        $this->assertSame('Praktijk', $byId[$error]['st_location']);
        $this->assertSame('Wagen', $byId[$negative]['st_location']);
        $this->assertSame($this->ci->db->where('id', $pid)->get('products')->row()->name, $byId[$error]['product_name']);

        // the seeded ERROR row (5007) is in the full list too
        $all = array_map(static fn ($r) => (int) $r['id'], $this->stock->get_problems());
        $this->assertContains(5007, $all);
    }
}
