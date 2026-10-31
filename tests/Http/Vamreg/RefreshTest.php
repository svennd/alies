<?php

declare(strict_types=1);

/**
 * T284 (HTTP part) - Vamreg::refresh when the sync cannot start.
 *
 * With an empty `vamreg_api_key` Vamreg_sync::sync_medicinal_products returns false before any
 * request, so this is the one refresh path that never touches the network. The answer is JSON
 * {status:"failure"} with HTTP 500 and the index is unchanged. The successful run (stub returns
 * medicinal products) is tested in-process with a recording client in tests/Models/VamregSyncTest.php.
 */
final class RefreshTest extends HttpTestCase
{
    public function testWithAnEmptyApiKeyTheRefreshFailsWithoutCallingTheServer(): void
    {
        $this->loginAs('admin');
        Factory::setting('vamreg_api_key', '');
        $before = $this->countWhere('vamreg_index');

        $response = $this->get('vamreg/refresh');

        $this->assertStatus($response, 500);
        $body = $this->json($response);
        $this->assertSame('failure', $body['status']);
        $this->assertSame('Product refresh failed. Please try again.', $body['message']);
        $this->assertSame($before, $this->countWhere('vamreg_index'));
    }

    public function testTheRefreshIsLoggedAsAFailure(): void
    {
        $this->loginAs('admin');
        Factory::setting('vamreg_api_key', '');
        $logs = $this->logCount('vamreg');

        $this->get('vamreg/refresh');

        $this->assertSame($logs + 1, $this->logCount('vamreg'));
        $this->assertStringContainsString('failure', (string) $this->scalar("SELECT msg FROM log WHERE event = 'vamreg' ORDER BY id DESC LIMIT 1"));
    }

    public function testRefreshSetsTheAntibioticStatusOnLinkedProductsEvenWhenTheSyncFails(): void
    {
        // the controller does not stop on a failed sync: set_ab_status_on_product runs anyway
        $this->loginAs('admin');
        Factory::setting('vamreg_api_key', '');
        $product = Factory::product(['cnk' => '1234567', 'is_antibiotic' => 0, 'buy_volume' => 1, 'unit_sell' => 'stuk']);

        $this->get('vamreg/refresh');

        $row = $this->find('products', $product);
        $this->assertSame(1, (int) $row['is_antibiotic']);
        $this->assertSame('NONE', $row['default_indication']);
    }

    public function testAVetCannotRefresh(): void
    {
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('vamreg/refresh'), '');
    }
}
