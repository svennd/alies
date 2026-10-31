<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T195 (Layer M) - Stock_limit_model::global_shortage() and local_shortage().
 *
 * global_shortage(): products with limit_stock > 0, not deleted, backorder 0 or NULL, whose IN_USE volume
 * summed over all locations is strictly below the limit. Products with no stock rows count as 0 (LEFT JOIN).
 * Also returns the use in the last 30 and 90 days and the wholesale article.
 * local_shortage($location): stock_limit rows for that location whose IN_USE volume there is strictly below
 * the limit, with the global IN_USE volume and the 30 day use (global and at that location's events).
 * Each test creates its own products; results are filtered on those ids because the seed has rows too.
 */
final class StockLimitTest extends CodeIgniterDatabaseTestCase
{
    private $limits;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->limits = $this->model('Stock_limit_model', 'stock_limit');
    }

    /** @return array<int, array> rows keyed by product id */
    private function global(): array
    {
        $out = [];
        foreach ($this->limits->global_shortage() as $row) {
            $out[(int) $row['id']] = $row;
        }

        return $out;
    }

    /** @return array<int, array> rows keyed by product id (the first one when the product is listed twice) */
    private function local(int $location): array
    {
        $out = [];
        foreach ($this->limits->local_shortage($location) as $row) {
            $out[(int) $row['product_detail']] ??= $row;
        }

        return $out;
    }

    private function limit(int $pid, int $location, int $volume): int
    {
        return Factory::insert('stock_limit', [
            'stock' => $location, 'product_id' => $pid, 'volume' => $volume,
            'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    // ---- global_shortage -------------------------------------------------------------------

    public function testAProductBelowItsLimitIsReturnedWithTheInUseVolume(): void
    {
        $pid = Factory::product(['limit_stock' => 10, 'unit_sell' => 'zz']);
        Factory::stock($pid, ['location' => '1', 'volume' => 4]);
        Factory::stock($pid, ['location' => '2', 'volume' => 3]);
        Factory::stock($pid, ['location' => '1', 'volume' => 100, 'state' => Fixtures::STATE_HISTORY]);
        Factory::stock($pid, ['location' => '1', 'volume' => 100, 'state' => Fixtures::STATE_ERROR]);
        Factory::stock($pid, ['location' => '1', 'volume' => 100, 'state' => Fixtures::STATE_CHECK]);

        $rows = $this->global();

        $this->assertArrayHasKey($pid, $rows);
        $this->assertEqualsWithDelta(7.0, (float) $rows[$pid]['all_volume'], 0.001, '4 + 3, other states ignored');
        $this->assertSame(10, (int) $rows[$pid]['limit_stock']);
        $this->assertSame('zz', $rows[$pid]['unit_sell']);
        $this->assertNull($rows[$pid]['global_use_30d']);
    }

    public function testAProductAtOrAboveItsLimitIsNotReturned(): void
    {
        $equal = Factory::product(['limit_stock' => 10]);
        Factory::stock($equal, ['volume' => 10]);
        $above = Factory::product(['limit_stock' => 10]);
        Factory::stock($above, ['volume' => 11]);
        $below = Factory::product(['limit_stock' => 10]);
        Factory::stock($below, ['volume' => 9.99]);

        $rows = $this->global();

        $this->assertArrayNotHasKey($equal, $rows, 'the comparison is strict');
        $this->assertArrayNotHasKey($above, $rows);
        $this->assertArrayHasKey($below, $rows, 'positive control');
    }

    public function testAProductWithoutAnyStockRowIsReturnedWithVolumeZero(): void
    {
        $pid = Factory::product(['limit_stock' => 3]);

        $rows = $this->global();

        $this->assertArrayHasKey($pid, $rows);
        $this->assertEqualsWithDelta(0.0, (float) $rows[$pid]['all_volume'], 0.001);
    }

    public function testNoLimitBackorderAndDeletedProductsAreSkipped(): void
    {
        $noLimit = Factory::product(['limit_stock' => 0]);
        $ordered = Factory::product(['limit_stock' => 5, 'backorder' => 1]);
        $notOrdered = Factory::product(['limit_stock' => 5, 'backorder' => 0]);
        $nullOrder = Factory::product(['limit_stock' => 5, 'backorder' => null]);
        $deleted = Factory::product(['limit_stock' => 5, 'deleted_at' => Factory::raw('NOW()')]);

        $rows = $this->global();

        $this->assertArrayNotHasKey($noLimit, $rows);
        $this->assertArrayNotHasKey($ordered, $rows);
        $this->assertArrayNotHasKey($deleted, $rows);
        $this->assertArrayHasKey($notOrdered, $rows);
        $this->assertArrayHasKey($nullOrder, $rows);
    }

    public function testTheUseOfThirtyAndNinetyDaysAndTheWholesaleArticleAreReturned(): void
    {
        $wh = Factory::wholesale(['vendor_id' => 'WS-LIM-1', 'description' => 'Limit article']);
        $pid = Factory::product(['limit_stock' => 50, 'wholesale' => $wh]);
        $event = Factory::event(Fixtures::PET_REX, 1, 1);
        Factory::eventProduct($event, $pid, 2, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 10 DAY)')]);
        Factory::eventProduct($event, $pid, 5, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 60 DAY)')]);
        Factory::eventProduct($event, $pid, 100, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 120 DAY)')]);

        $row = $this->global()[$pid];

        $this->assertEqualsWithDelta(2.0, (float) $row['global_use_30d'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $row['global_use_90d'], 0.001);
        $this->assertSame('WS-LIM-1', $row['wsid']);
        $this->assertSame('Limit article', $row['wsname']);
    }

    // ---- local_shortage --------------------------------------------------------------------

    public function testALocalLimitAboveTheLocalVolumeIsReturned(): void
    {
        $pid = Factory::product(['limit_stock' => 30, 'unit_sell' => 'zz']);
        Factory::stock($pid, ['location' => '1', 'volume' => 5]);
        Factory::stock($pid, ['location' => '1', 'volume' => 2]);
        Factory::stock($pid, ['location' => '1', 'volume' => 50, 'state' => Fixtures::STATE_HISTORY]);
        Factory::stock($pid, ['location' => '2', 'volume' => 40]);
        $this->limit($pid, 1, 20);

        $row = $this->local(1)[$pid] ?? null;

        $this->assertNotNull($row);
        $this->assertEqualsWithDelta(7.0, (float) $row['available_volume'], 0.001, '5 + 2 at location 1, history ignored');
        $this->assertSame(20, (int) $row['required_volume']);
        $this->assertSame(1, (int) $row['stock']);
        $this->assertSame(30, (int) $row['global_limit']);
        $this->assertSame('zz', $row['unit_sell']);
        $this->assertEqualsWithDelta(47.0, (float) $row['all_volume'], 0.001, 'in use over all locations: 5 + 2 + 40');
        $this->assertNull($row['global_use']);
        $this->assertNull($row['local_use']);
    }

    public function testALimitIsOnlyReturnedForItsOwnLocationAndOnlyBelowTheLimit(): void
    {
        $equal = Factory::product();
        Factory::stock($equal, ['location' => '1', 'volume' => 8]);
        $this->limit($equal, 1, 8);
        $car = Factory::product();
        Factory::stock($car, ['location' => '2', 'volume' => 1]);
        Factory::stock($car, ['location' => '1', 'volume' => 1]);
        $this->limit($car, 2, 5);

        $one = $this->local(1);
        $two = $this->local(2);

        $this->assertArrayNotHasKey($equal, $one, 'equal is not a shortage');
        $this->assertArrayNotHasKey($car, $one, 'its limit is for location 2');
        $this->assertArrayHasKey($car, $two);
        $this->assertEqualsWithDelta(1.0, (float) $two[$car]['available_volume'], 0.001);
    }

    public function testDeletedProductsAreSkippedLocally(): void
    {
        $pid = Factory::product(['deleted_at' => Factory::raw('NOW()')]);
        Factory::stock($pid, ['location' => '1', 'volume' => 1]);
        $this->limit($pid, 1, 9);

        $this->assertArrayNotHasKey($pid, $this->local(1));
    }

    public function testLocalUseCountsOnlyTheEventsOfThatLocation(): void
    {
        $pid = Factory::product();
        Factory::stock($pid, ['location' => '1', 'volume' => 0.5]);
        $this->limit($pid, 1, 9);
        $here = Factory::event(Fixtures::PET_REX, 1, 1, ['location' => 1]);
        $there = Factory::event(Fixtures::PET_REX, 1, 1, ['location' => 2]);
        Factory::eventProduct($here, $pid, 3, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 5 DAY)')]);
        Factory::eventProduct($there, $pid, 4, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 5 DAY)')]);
        Factory::eventProduct($here, $pid, 50, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 45 DAY)')]);

        $row = $this->local(1)[$pid];

        $this->assertEqualsWithDelta(3.0, (float) $row['local_use'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $row['global_use'], 0.001);
    }

    public function testAProductWithoutStockAtTheLocationIsReturnedWithZeroAvailable(): void
    {
        $pid = Factory::product();
        $this->limit($pid, 1, 4);

        $row = $this->local(1)[$pid] ?? null;

        $this->assertNotNull($row, 'a new product that was given a limit but has no stock yet');
        $this->assertEqualsWithDelta(0.0, (float) $row['available_volume'], 0.001);
    }

    #[Group('known-bug')]
    public function testTwoLimitsWithoutAnyStockRowCollapseIntoOneResultRow(): void
    {
        // KNOWN BUG: local_shortage() groups by stock.product_id, stock.location (the joined stock table).
        // Products with no stock row at the location all have NULL/NULL, so they fall into ONE group and only
        // one of them is reported. Decide in CI4 whether to keep.
        $a = Factory::product();
        $b = Factory::product();
        $c = Factory::product();
        Factory::stock($c, ['location' => '1', 'volume' => 1]);
        $this->limit($a, 1, 4);
        $this->limit($b, 1, 4);
        $this->limit($c, 1, 4);

        $ids = array_map('intval', array_column($this->limits->local_shortage(1), 'product_detail'));

        $this->assertContains($c, $ids, 'a product with a stock row is reported normally');
        $missing = array_diff([$a, $b], $ids);
        $this->assertGreaterThanOrEqual(1, count($missing), 'at least one of the stockless products is lost');
        $this->assertCount(1, array_intersect([$a, $b], $ids), 'exactly one of them is reported, the group is shared');
    }

    public function testTheLimitTableAllowsOnlyOneLimitPerProductAndLocation(): void
    {
        $pid = Factory::product();
        $this->limit($pid, 1, 4);

        $this->ci->db->db_debug = false;
        $ok = $this->ci->db->insert('stock_limit', ['stock' => 1, 'product_id' => $pid, 'volume' => 9]);
        $this->ci->db->db_debug = true;

        $this->assertFalse($ok, 'unique key (stock, product_id)');
    }
}
