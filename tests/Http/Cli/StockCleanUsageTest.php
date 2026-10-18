<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T327 - Cli::stock_clean and Cli::recalculate_usage.
 *
 * stock_clean: IN_USE stock with volume 0 goes to HISTORY (log WARN `stock_clean` "archived: N");
 * stock of a soft-deleted product with volume > 0 goes to HISTORY keeping its volume (log WARN
 * `rm_dead_products` per IN_USE row, WARN `remove_dead_products_from_stock` "archived: N").
 * recalculate_usage: products.usage_count = number of events_products rows of the last 90 days, for products that
 * have at least one such row; other products keep their count. Log INFO `recalculate_usage`.
 */
final class StockCleanUsageTest extends HttpTestCase
{
    public function testEmptyInUseStockIsArchivedAndFilledStockIsKept(): void
    {
        $product = Factory::product();
        $empty = Factory::stock($product, ['volume' => 0, 'state' => 1]);
        $filled = Factory::stock($product, ['volume' => 5, 'state' => 1]);
        $emptyCheck = Factory::stock($product, ['volume' => 0, 'state' => 0]);

        [$exit] = $this->cli(['cli', 'stock_clean']);

        $this->assertSame(0, $exit);
        $this->assertSame(2, (int) $this->find('stock', $empty)['state']);
        $this->assertSame(1, (int) $this->find('stock', $filled)['state']);
        $this->assertSame(0, (int) $this->find('stock', $emptyCheck)['state'], 'only IN_USE rows are archived');
        $log = $this->row("SELECT * FROM log WHERE event = 'stock_clean' ORDER BY id DESC");
        $this->assertNotNull($log);
        $this->assertSame(3, (int) $log['level']);
        $this->assertMatchesRegularExpression('/^archived: [1-9]\d*$/', $log['msg']);
    }

    public function testStockOfARemovedProductIsArchivedWithItsVolumeAndLogged(): void
    {
        $name = 'Removed ' . uniqid();
        $dead = Factory::product(['name' => $name, 'deleted_at' => Factory::raw('NOW()')]);
        $stock = Factory::stock($dead, ['volume' => 7, 'state' => 1]);
        $alive = Factory::stock(Factory::product(), ['volume' => 7, 'state' => 1]);

        $this->cli(['cli', 'stock_clean']);

        $row = $this->find('stock', $stock);
        $this->assertSame(2, (int) $row['state']);
        $this->assertEqualsWithDelta(7.0, (float) $row['volume'], 0.001);
        $this->assertSame(1, (int) $this->find('stock', $alive)['state']);

        $log = $this->row("SELECT * FROM log WHERE event = 'rm_dead_products' AND msg LIKE ?", ["removed: $name%"]);
        $this->assertNotNull($log);
        $this->assertSame(3, (int) $log['level']);
        $this->assertSame("removed: $name volume: 7.00", $log['msg']);
        $summary = $this->row("SELECT * FROM log WHERE event = 'remove_dead_products_from_stock' ORDER BY id DESC");
        $this->assertNotNull($summary);
        $this->assertMatchesRegularExpression('/^archived: [1-9]\d*$/', $summary['msg']);
    }

    #[Group('known-bug')]
    public function testStockOfARemovedProductIsArchivedWhateverItsState(): void
    {
        // KNOWN BUG: the UPDATE in remove_dead_products_from_stock has no state filter, so CHECK/ERROR rows of a
        // removed product are archived too (the log rows only mention IN_USE) - decide in CI4 whether to keep
        $dead = Factory::product(['deleted_at' => Factory::raw('NOW()')]);
        $check = Factory::stock($dead, ['volume' => 3, 'state' => 0]);
        $error = Factory::stock($dead, ['volume' => 3, 'state' => 3]);

        $this->cli(['cli', 'stock_clean']);

        $this->assertSame(2, (int) $this->find('stock', $check)['state']);
        $this->assertSame(2, (int) $this->find('stock', $error)['state']);
    }

    public function testRecalculateUsageCountsLinesOfTheLastNinetyDays(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet);
        $used = Factory::product(['usage_count' => 99]);
        $olderOnly = Factory::product(['usage_count' => 42]);
        $unused = Factory::product(['usage_count' => 7]);
        foreach (['-1 day', '-30 days', '-89 days'] as $age) {
            Factory::eventProduct($event, $used, 2.0, ['created_at' => date('Y-m-d H:i:s', strtotime($age))]);
        }
        foreach (['-91 days', '-200 days'] as $age) {
            Factory::eventProduct($event, $used, 1.0, ['created_at' => date('Y-m-d H:i:s', strtotime($age))]);
            Factory::eventProduct($event, $olderOnly, 1.0, ['created_at' => date('Y-m-d H:i:s', strtotime($age))]);
        }

        [$exit] = $this->cli(['cli', 'recalculate_usage']);

        $this->assertSame(0, $exit);
        // a line count, not the volume sum (which would be 6 + 2)
        $this->assertSame(3, (int) $this->find('products', $used)['usage_count']);
        $this->assertSame(42, (int) $this->find('products', $olderOnly)['usage_count'], 'no recent use: old count is kept');
        $this->assertSame(7, (int) $this->find('products', $unused)['usage_count']);
        $log = $this->row("SELECT * FROM log WHERE event = 'recalculate_usage'");
        $this->assertNotNull($log);
        $this->assertMatchesRegularExpression('/^recalculated usage for all products \(\d+ affected\)$/', $log['msg']);
    }
}
