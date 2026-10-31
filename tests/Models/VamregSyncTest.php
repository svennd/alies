<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHarness.php';

use PHPUnit\Framework\Attributes\Group;

/**
 * T284 - Vamreg::refresh and Vamreg_sync::sync_medicinal_products with a recording client.
 *
 * Rules: refresh asks the register for GET /medicinal-product; on 200 with a list it upserts one
 * vamreg_index row per product (key: cti), then Vamreg_index_model::set_ab_status_on_product flags
 * the products with the same CNK; the answer is {status:"success"}. On any other answer, or without
 * an api key, nothing is stored and the answer is {status:"failure"} (the flagging still runs).
 */
final class VamregSyncTest extends CodeIgniterDatabaseTestCase
{
    private VamregHarness $h;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->h = new VamregHarness(['vamreg_push' => VamregHarness::setting('0')]);
    }

    protected function tearDown(): void
    {
        $this->h->release();
        parent::tearDown();
    }

    private function product(string $cti, string $cnk, array $over = []): array
    {
        return $over + ['cti' => $cti, 'cnk' => $cnk, 'ppnNl' => "Name $cti", 'packSize' => 30, 'usage' => 'VETERINARY',
            'maName' => 'MA ' . $cti, 'maNumber' => 'BE-' . $cti, 'mahName' => 'MAH ' . $cti];
    }

    private function index(string $cti): ?array
    {
        return $this->ci->db->get_where('vamreg_index', ['cti' => $cti])->row_array() ?: null;
    }

    public function testRefreshStoresTheProductsAndFlagsTheLinkedOnes(): void
    {
        $linked = Factory::product(['cnk' => '8800001', 'is_antibiotic' => 0, 'buy_volume' => 10, 'unit_sell' => 'stuk']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [
            $this->product('SYNC-1', '8800001', ['packSize' => 30, 'ppnNl' => 'First product']),
            $this->product('SYNC-2', '8800002', ['usage' => 'HUMAN']),
        ])]);

        $this->h->controller->refresh();

        $answer = $this->h->answer();
        $this->assertSame('success', $answer['status']);
        $this->assertSame('Product list refreshed.', $answer['message']);
        $this->assertSame('GET', $this->h->client->requests[0]['method']);
        $this->assertSame('/medicinal-product', $this->h->client->requests[0]['path']);

        $one = $this->index('SYNC-1');
        $this->assertSame('8800001', $one['cnk']);
        $this->assertSame('First product', $one['ppnNl']);
        $this->assertSame('30', (string) $one['packSize']);
        $this->assertSame('VETERINARY', $one['susage']);
        $this->assertSame('MA SYNC-1', $one['maName']);
        $this->assertSame('BE-SYNC-1', $one['maNumber']);
        $this->assertSame('MAH SYNC-1', $one['mahName']);
        $this->assertSame('HUMAN', $this->index('SYNC-2')['susage']);

        // linked product: 30 / 10 = 3 pieces per pack
        $p = $this->ci->db->get_where('products', ['id' => $linked])->row_array();
        $this->assertSame(1, (int) $p['is_antibiotic']);
        $this->assertSame('PIECE', $p['ab_unit']);
        $this->assertEqualsWithDelta(3.0, (float) $p['ab_unit_volume'], 0.001);
    }

    public function testASecondRefreshUpdatesTheRowsInsteadOfDuplicatingThem(): void
    {
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [$this->product('SYNC-3', '8800003', ['maName' => 'old'])])]);
        $this->h->controller->refresh();
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [$this->product('SYNC-3', '8800003', ['maName' => 'new'])])]);
        $this->h->controller->refresh();

        $this->assertSame(1, (int) $this->ci->db->where('cti', 'SYNC-3')->count_all_results('vamreg_index'));
        $this->assertSame('new', $this->index('SYNC-3')['maName']);
    }

    public function testAMissingPpnNlBecomesAnEmptyString(): void
    {
        $row = $this->product('SYNC-4', '8800004');
        unset($row['ppnNl']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [$row])]);

        $this->h->controller->refresh();

        $this->assertSame('', $this->index('SYNC-4')['ppnNl']);
    }

    public function testAFailedAnswerStoresNothingAndReportsFailure(): void
    {
        foreach ([VamregModelsRecordingClient::answer(500, []), VamregModelsRecordingClient::answer(403, ['x']),
            VamregModelsRecordingClient::answer(200, null)] as $answer) {
            $this->h->client->answers([$answer]);
            $before = $this->ci->db->count_all('vamreg_index');

            $this->h->controller->refresh();

            $body = $this->h->answer();
            $this->assertSame('failure', $body['status']);
            $this->assertSame('Product refresh failed. Please try again.', $body['message']);
            $this->assertSame($before, $this->ci->db->count_all('vamreg_index'));
        }
    }

    public function testWithoutAnApiKeyNoRequestIsMade(): void
    {
        $this->h->controller->conf['vamreg_api_key'] = VamregHarness::setting('');

        $this->h->controller->refresh();

        $this->assertSame('failure', $this->h->answer()['status']);
        $this->assertCount(0, $this->h->client->requests);
    }

    #[Group('known-bug')]
    public function testARefreshAlsoFlagsProductsWhenTheSyncFailed(): void
    {
        // KNOWN BUG: refresh() runs set_ab_status_on_product() whatever the sync returned
        $p = Factory::product(['cnk' => '1234567', 'is_antibiotic' => 0]);
        $this->h->client->answers([VamregModelsRecordingClient::answer(500, [])]);

        $this->h->controller->refresh();

        $this->assertSame(1, (int) $this->ci->db->get_where('products', ['id' => $p])->row_array()['is_antibiotic']);
    }

    #[Group('known-bug')]
    public function testRefreshReadsVamregPushWhilePostAllReadsVamregProd(): void
    {
        // KNOWN BUG: refresh() reads conf['vamreg_push'] (seeded) and post_all() reads conf['vamreg_prod'] (never
        // seeded); without vamreg_push refresh raises an undefined key warning but carries on
        unset($this->h->controller->conf['vamreg_push']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [$this->product('SYNC-5', '8800005')])]);

        $this->h->capture(fn () => $this->h->controller->refresh());

        $this->assertNotEmpty($this->h->warnings);
        $this->assertStringContainsString('vamreg_push', $this->h->warnings[0]);
        $this->assertSame('success', $this->h->answer()['status']);
    }
}
