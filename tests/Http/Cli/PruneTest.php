<?php

declare(strict_types=1);

/**
 * T323 - Cli::prune.
 *
 * Gate: setting `pruning` must be truthy. Then, each only when its setting is a non-zero number:
 * `prune_global_log` days -> rows of table `log` older than that are deleted (log row `prune_global_log`);
 * `prune_stock` years -> `stock` rows in state HISTORY older than that are deleted, other states are kept
 * (log row `prune_stock`); `prune_stock_log` days -> `log_stock` rows older than that (log row `prune_stock_log`).
 */
final class PruneTest extends HttpTestCase
{
    private int $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Factory::product();
        $this->db()->exec("DELETE FROM log WHERE event LIKE 'prune\\_%'");
    }

    private function settings(string $pruning): void
    {
        Factory::setting('pruning', $pruning);
        Factory::setting('prune_global_log', '30');
        Factory::setting('prune_stock', '2');
        Factory::setting('prune_stock_log', '30');
    }

    private function logRow(string $event, int $daysAgo): int
    {
        return Factory::insert('log', [
            'user_id' => 1, 'event' => $event . '_' . uniqid(), 'level' => 4, 'msg' => 'probe', 'location' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days")),
            'updated_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days")),
        ]);
    }

    private function stockLogRow(int $daysAgo): int
    {
        return Factory::insert('log_stock', [
            'user_id' => 1, 'product' => $this->product, 'event' => 'probe', 'volume' => 1, 'location' => 1, 'level' => 4,
            'created_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days")),
            'updated_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days")),
        ]);
    }

    private function stockRow(int $state, string $age): int
    {
        return Factory::stock($this->product, [
            'state' => $state, 'volume' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime($age)),
        ]);
    }

    public function testOldLogsHistoryStockAndStockLogsAreDeleted(): void
    {
        $this->settings('1');
        $oldLog = $this->logRow('probe', 40);
        $newLog = $this->logRow('probe', 10);
        $oldHistory = $this->stockRow(2, '-3 years');
        $newHistory = $this->stockRow(2, '-1 year');
        $oldInUse = $this->stockRow(1, '-3 years');
        $oldCheck = $this->stockRow(0, '-3 years');
        $oldStockLog = $this->stockLogRow(40);
        $newStockLog = $this->stockLogRow(10);

        [$exit] = $this->cli(['cli', 'prune']);

        $this->assertSame(0, $exit);
        $this->assertRowMissing('log', ['id' => $oldLog]);
        $this->assertRowExists('log', ['id' => $newLog]);
        $this->assertRowMissing('stock', ['id' => $oldHistory]);
        $this->assertRowExists('stock', ['id' => $newHistory]);
        $this->assertRowExists('stock', ['id' => $oldInUse]);
        $this->assertRowExists('stock', ['id' => $oldCheck]);
        $this->assertRowMissing('log_stock', ['id' => $oldStockLog]);
        $this->assertRowExists('log_stock', ['id' => $newStockLog]);

        foreach (['prune_global_log', 'prune_stock', 'prune_stock_log'] as $event) {
            $log = $this->row('SELECT * FROM log WHERE event = ?', [$event]);
            $this->assertNotNull($log, $event);
            $this->assertSame(4, (int) $log['level']);
            $this->assertMatchesRegularExpression('/^deleted: [1-9]\d*$/', $log['msg']);
        }
        $this->assertSame('deleted: 1', $this->row("SELECT msg FROM log WHERE event = 'prune_stock'")['msg']);
        $this->assertSame('deleted: 1', $this->row("SELECT msg FROM log WHERE event = 'prune_stock_log'")['msg']);
    }

    public function testNoLogRowIsWrittenForAStepThatDeletedNothing(): void
    {
        $this->settings('1');
        $this->cli(['cli', 'prune']);   // deletes everything old that exists
        $this->db()->exec("DELETE FROM log WHERE event LIKE 'prune\\_%'");

        $this->cli(['cli', 'prune']);

        $this->assertSame(0, $this->countWhere('log', ['event' => 'prune_stock']));
        $this->assertSame(0, $this->countWhere('log', ['event' => 'prune_stock_log']));
    }

    public function testPruningOffDeletesNothing(): void
    {
        $this->settings('0');
        $oldLog = $this->logRow('probe', 400);
        $oldHistory = $this->stockRow(2, '-5 years');
        $oldStockLog = $this->stockLogRow(400);

        [$exit] = $this->cli(['cli', 'prune']);

        $this->assertSame(0, $exit);
        $this->assertRowExists('log', ['id' => $oldLog]);
        $this->assertRowExists('stock', ['id' => $oldHistory]);
        $this->assertRowExists('log_stock', ['id' => $oldStockLog]);
        $this->assertSame(0, $this->countWhere('log', ['event' => 'prune_global_log']));

        // positive control: the same rows go when pruning is on
        $this->settings('1');
        $this->cli(['cli', 'prune']);
        $this->assertRowMissing('log', ['id' => $oldLog]);
        $this->assertRowMissing('stock', ['id' => $oldHistory]);
        $this->assertRowMissing('log_stock', ['id' => $oldStockLog]);
    }

    public function testAStepWithASettingOfZeroIsSkipped(): void
    {
        $this->settings('1');
        Factory::setting('prune_global_log', '0');
        Factory::setting('prune_stock', '0');
        $oldLog = $this->logRow('probe', 400);
        $oldHistory = $this->stockRow(2, '-5 years');
        $oldStockLog = $this->stockLogRow(400);

        $this->cli(['cli', 'prune']);

        $this->assertRowExists('log', ['id' => $oldLog]);
        $this->assertRowExists('stock', ['id' => $oldHistory]);
        $this->assertRowMissing('log_stock', ['id' => $oldStockLog]);   // prune_stock_log is still 30
    }
}
