<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T329 - Cli::cleanup_rate_limits and the part of Cli::vamreg_update that runs without a network.
 *
 * cleanup_rate_limits: deletes `api_rate_limit` rows whose `minute` (YYYYMMDDHHMM) is older than 2 days.
 * vamreg_update: Vamreg_sync::sync_medicinal_products returns false without calling the API when the api key is
 * empty; the update then logs "... : failure", and Vamreg_index_model::set_ab_status_on_product marks products
 * whose cnk is in `vamreg_index` as antibiotic (is_antibiotic 1, ab_unit PACKS when buy_volume is 1) and logs the
 * number of affected rows. With a real key the API is called: that part is skipped (needs F07 stub: Vamreg API).
 * With NO `vamreg_api_key` setting at all the call receives null for a string parameter: TypeError, non-zero exit.
 */
final class RateLimitVamregTest extends HttpTestCase
{
    private function minute(string $when): string
    {
        return date('YmdHi', strtotime($when));
    }

    public function testOldRateLimitRowsAreDeletedAndRecentOnesKept(): void
    {
        $old = [[1, '201601010000'], [2, $this->minute('-3 days')], [1, $this->minute('-10 days')]];
        $recent = [[1, $this->minute('-1 day')], [2, $this->minute('now')], [1, $this->minute('-1 hour')]];
        foreach (array_merge($old, $recent) as [$key, $minute]) {
            Factory::insert('api_rate_limit', ['api_key_id' => $key, 'minute' => $minute, 'count' => 5]);
        }

        [$exit] = $this->cli(['cli', 'cleanup_rate_limits']);

        $this->assertSame(0, $exit);
        foreach ($old as [$key, $minute]) {
            $this->assertRowMissing('api_rate_limit', ['api_key_id' => $key, 'minute' => $minute]);
        }
        foreach ($recent as [$key, $minute]) {
            $this->assertRowExists('api_rate_limit', ['api_key_id' => $key, 'minute' => $minute]);
        }
    }

    public function testWithAnEmptyKeyTheSyncFailsQuietlyAndAntibioticsAreFlagged(): void
    {
        Factory::setting('vamreg_api_key', '');
        Factory::setting('vamreg_push', '0');
        $cnk = (string) random_int(3000000, 9999999);
        Factory::insert('vamreg_index', [
            'cti' => 'cti-' . uniqid(), 'cnk' => $cnk, 'ppnNl' => 'x', 'packSize' => '10', 'susage' => 'VETERINARY',
        ]);
        $match = Factory::product(['cnk' => $cnk, 'is_antibiotic' => 0, 'buy_volume' => 1, 'unit_sell' => 'stuk']);
        $other = Factory::product(['cnk' => (string) ($cnk + 1), 'is_antibiotic' => 0]);

        [$exit, $out, $err] = $this->cli(['cli', 'vamreg_update']);

        $this->assertSame(0, $exit, $err);
        $row = $this->find('products', $match);
        $this->assertSame(1, (int) $row['is_antibiotic']);
        $this->assertSame('PACKS', $row['ab_unit']);
        $this->assertEqualsWithDelta(1.0, (float) $row['ab_unit_volume'], 0.001);
        $this->assertSame('NONE', $row['default_indication']);
        $this->assertSame(0, (int) $this->find('products', $other)['is_antibiotic']);

        $messages = array_column($this->rows("SELECT msg FROM log WHERE event = 'vamreg'"), 'msg');
        $this->assertContains('synchronized medicinal products from Vamreg : failure', $messages);
        $this->assertNotEmpty(preg_grep('/^set AB status for buffer items \(\d+ affected\)$/', $messages));
    }

    public function testTheVamregApiItselfIsNotTestedHere(): void
    {
        $this->markTestSkipped('needs F07 stub: Vamreg API (sync_medicinal_products with a real key)');
    }

    #[Group('known-bug')]
    public function testWithoutAnyKeySettingTheCommandCrashesWithATypeError(): void
    {
        // KNOWN BUG: $this->conf['vamreg_api_key'] is undefined, so null goes into a `string` parameter - decide in CI4
        $this->db()->exec("DELETE FROM config WHERE name = 'vamreg_api_key'");
        $logs = $this->logCount('vamreg');

        [$exit, $out, $err] = $this->cli(['cli', 'vamreg_update']);

        $this->assertNotSame(0, $exit);
        // the fatal error is only written to the PHP/CI log, nothing is printed
        unset($out, $err);
        $this->assertSame($logs, $this->logCount('vamreg'), 'nothing after the crash runs');
    }
}
