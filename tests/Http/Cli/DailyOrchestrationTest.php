<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T321 - Cli::daily runs, in this order: autoclose, prune, auto_death, stock_clean, stock_value->record_value,
 * recalculate_usage, monthly_product_usage_dump (only on the 1st), cleanup_rate_limits, vamreg_update.
 *
 * vamreg_update is the last step. With `vamreg_api_key` set to an EMPTY value it makes no network call (the sync
 * returns false) so the whole run can be tested; with a real key it would call the Vamreg API, which tests never do.
 * Without any `vamreg_api_key` setting the run dies in that last step with a TypeError, after all other steps ran.
 */
final class DailyOrchestrationTest extends HttpTestCase
{
    private ?string $createdDump = null;

    protected function setUp(): void
    {
        parent::setUp();
        // on the 1st the daily job also writes last month's dump: remember it so only our own file is removed
        $last = dirname(__DIR__, 3) . '/data/stored/reports/monthly_product_usage/monthly_product_usage_'
            . (new DateTimeImmutable('first day of last month'))->format('Y-m') . '.csv';
        $this->createdDump = is_file($last) ? null : $last;
    }

    protected function tearDown(): void
    {
        if ($this->createdDump !== null && is_file($this->createdDump)) {
            unlink($this->createdDump);
        }
        parent::tearDown();
    }

    private function settings(?string $vamregKey): void
    {
        Factory::setting('autoclose', '7');
        Factory::setting('autodisable', '1');
        Factory::setting('autotemplate', '<p>tpl</p>');
        Factory::setting('pruning', '1');
        Factory::setting('prune_global_log', '30');
        Factory::setting('prune_stock', '2');
        Factory::setting('prune_stock_log', '30');
        Factory::setting('autdeath', '1');
        for ($type = 0; $type <= 5; $type++) {
            Factory::setting('auto_dead_' . $type, $type === 0 ? '15' : '0');
        }
        Factory::setting('vamreg_push', '0');
        if ($vamregKey === null) {
            $this->db()->exec("DELETE FROM config WHERE name = 'vamreg_api_key'");
        } else {
            Factory::setting('vamreg_api_key', $vamregKey);
        }
    }

    /** @return array<string, int|string> the rows every step must act on */
    private function seedWork(): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $product = Factory::product(['usage_count' => 99]);
        $deadProduct = Factory::product(['deleted_at' => Factory::raw('NOW()')]);
        $ago = fn (string $when) => date('Y-m-d H:i:s', strtotime($when));
        $event = Factory::event($pet, 0, 0, ['anamnese' => 'tekst', 'created_at' => $ago('-10 days')]);
        Factory::eventProduct(Factory::event($pet), $product, 1.0);
        Factory::eventProduct(Factory::event($pet), $product, 1.0);

        return [
            'event' => $event,
            'oldLog' => Factory::insert('log', ['user_id' => 1, 'event' => 'daily_probe', 'level' => 4, 'msg' => 'x', 'location' => 1, 'created_at' => $ago('-60 days')]),
            'oldDog' => Factory::pet($owner, ['type' => 0, 'note' => '', 'birth' => date('Y-m-d', strtotime('-20 years'))]),
            'emptyStock' => Factory::stock($product, ['volume' => 0, 'state' => 1]),
            'deadStock' => Factory::stock($deadProduct, ['volume' => 4, 'state' => 1]),
            'product' => $product,
            'rate' => Factory::insert('api_rate_limit', ['api_key_id' => 1, 'minute' => '201601010000', 'count' => 1]),
        ];
    }

    private function assertStepsBeforeVamregRan(array $w, int $stockValues): void
    {
        $this->assertSame(2, (int) $this->find('events', $w['event'])['report'], 'autoclose');
        $this->assertSame(' [Auto-Closed]', substr($this->find('events', $w['event'])['anamnese'], -14), 'autoclose marker');
        $this->assertRowMissing('log', ['id' => $w['oldLog']]);                       // prune
        $this->assertSame(1, (int) $this->find('pets', $w['oldDog'])['death'], 'auto_death');
        $this->assertSame(2, (int) $this->find('stock', $w['emptyStock'])['state'], 'stock_clean');
        $this->assertSame(2, (int) $this->find('stock', $w['deadStock'])['state'], 'removed product stock');
        $this->assertSame($stockValues + 1, $this->countWhere('stock_value'), 'one new stock_value row');
        $this->assertSame(2, (int) $this->find('products', $w['product'])['usage_count'], 'recalculate_usage');
        $this->assertRowMissing('api_rate_limit', ['api_key_id' => 1, 'minute' => '201601010000']);
        foreach (['autoclose', 'auto_death', 'recalculate_usage'] as $event) {
            $this->assertGreaterThan(0, $this->logCount($event), $event);
        }
    }

    public function testEveryStepRunsWhenTheVamregKeyIsEmpty(): void
    {
        $this->settings('');
        $work = $this->seedWork();
        $stockValues = $this->countWhere('stock_value');

        [$exit, $out, $err] = $this->cli(['cli', 'daily']);

        $this->assertSame(0, $exit, $out . $err);
        $this->assertStepsBeforeVamregRan($work, $stockValues);
        $messages = array_column($this->rows("SELECT msg FROM log WHERE event = 'vamreg'"), 'msg');
        $this->assertContains('synchronized medicinal products from Vamreg : failure', $messages);
        $this->assertNotEmpty(preg_grep('/^set AB status for buffer items/', $messages), 'the last step ran');
    }

    public function testTheMonthlyDumpIsOnlyPartOfTheRunOnTheFirstOfTheMonth(): void
    {
        $this->settings('');
        $before = $this->logCount('monthly_product_usage_dump');

        $this->cli(['cli', 'daily']);

        $after = $this->logCount('monthly_product_usage_dump');
        if (date('d') === '01') {
            $this->assertSame($before + 1, $after);
        } else {
            $this->assertSame($before, $after);
        }
    }

    #[Group('known-bug')]
    public function testWithoutAVamregKeySettingTheRunAbortsAtTheLastStep(): void
    {
        // KNOWN BUG: a missing vamreg_api_key setting throws a TypeError in vamreg_update - decide in CI4
        $this->settings(null);
        $work = $this->seedWork();
        $stockValues = $this->countWhere('stock_value');
        $vamregLogs = $this->logCount('vamreg');

        [$exit] = $this->cli(['cli', 'daily']);

        $this->assertNotSame(0, $exit);
        $this->assertStepsBeforeVamregRan($work, $stockValues);
        $this->assertSame($vamregLogs, $this->logCount('vamreg'), 'nothing is logged by the failing step');
    }
}
