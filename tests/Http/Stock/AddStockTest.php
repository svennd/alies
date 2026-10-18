<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T160-T163 - Stock::add_stock (stock intake, before verification).
 *
 * Rules: a valid post (pid, 0 < volume < 5000) inserts a stock row in state CHECK (0) at the
 * user's location, or adds the volume to an identical CHECK row; it clears the backorder flag;
 * for a product that is not linked to the wholesaler it raises buy_price to a higher catalogue price.
 * An invalid post inserts nothing and writes a `bad_stock_entry` log row.
 */
final class AddStockTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function form(int $pid, array $over = []): array
    {
        return $over + [
            'submit' => 1, 'pid' => $pid, 'new_volume' => 10, 'eol' => date('Y-m-d', strtotime('+1 year')),
            'lotnr' => 'L-' . uniqid(), 'in_price' => '2.5', 'cat_price' => '3', 'supplier' => 'Acme',
            'new_barcode_input' => 0, 'barcode_gs1' => '', 'buy_price_date' => date('Y-m-d'),
        ];
    }

    private function checkRows(int $pid): array
    {
        return $this->rows('SELECT * FROM stock WHERE product_id = ? AND state = 0', [$pid]);
    }

    // ---- T160 --------------------------------------------------------------------------------

    public function testANewLotIsInsertedInStateCheckAtTheUsersLocation(): void
    {
        $pid = Factory::product(['name' => 'AddStock Alpha', 'wholesale' => 0, 'buy_price' => 5.00, 'backorder' => 1]);
        $form = $this->form($pid, ['lotnr' => 'LOT-A1']);

        $response = $this->post('stock/add_stock', $form);

        $this->assertStatus($response, 200);
        $rows = $this->checkRows($pid);
        $this->assertCount(1, $rows);
        $this->assertSame('1', (string) $rows[0]['location']);
        $this->assertSame(0, (int) $rows[0]['state']);
        $this->assertEqualsWithDelta(10.0, (float) $rows[0]['volume'], 0.001);
        $this->assertEqualsWithDelta(2.5, (float) $rows[0]['in_price'], 0.001);
        $this->assertEqualsWithDelta(3.0, (float) $rows[0]['cat_price'], 0.001);
        $this->assertSame('LOT-A1', $rows[0]['lotnr']);
        $this->assertSame($form['eol'], $rows[0]['eol']);
        $this->assertSame('Acme', $rows[0]['supplier']);
        $this->assertNotEmpty($rows[0]['barcode']);
        // the response is the add page with the pending row listed
        $this->assertSee($response, 'AddStock Alpha');
    }

    public function testAddingStockWritesTheStockLog(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $this->post('stock/add_stock', $this->form($pid, ['new_volume' => 7]));

        $log = $this->row("SELECT * FROM log_stock WHERE event = 'add_stock' AND product = ?", [$pid]);
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(7.0, (float) $log['volume'], 0.001);
        $this->assertSame(1, (int) $log['location']);
    }

    public function testAddingStockClearsTheBackorderFlag(): void
    {
        $pid = Factory::product(['wholesale' => 0, 'backorder' => 1]);
        $other = Factory::product(['wholesale' => 0, 'backorder' => 1]);

        $this->post('stock/add_stock', $this->form($pid));

        $this->assertSame(0, (int) $this->find('products', $pid)['backorder']);
        $this->assertSame(1, (int) $this->find('products', $other)['backorder'], 'other products keep their flag');
    }

    public function testAHigherCatalogPriceRaisesTheBuyPriceOfAnUnlinkedProduct(): void
    {
        $pid = Factory::product(['wholesale' => 0, 'buy_price' => 2.00]);

        // buy_price 2.00 < cat_price 3 and wholesale = 0
        $this->post('stock/add_stock', $this->form($pid, ['cat_price' => '3', 'buy_price_date' => '2024-05-06']));

        $product = $this->find('products', $pid);
        $this->assertEqualsWithDelta(3.0, (float) $product['buy_price'], 0.001);
        $this->assertSame('2024-05-06', $product['buy_price_date']);
        $log = $this->row("SELECT * FROM log WHERE event = 'update_buy_price' AND msg LIKE ?", ['%product: ' . $pid . ' %']);
        $this->assertNotNull($log);
        $this->assertStringContainsString('old:2', $log['msg']);
    }

    public function testTheBuyPriceIsLeftAloneWhenItIsNotLowerThanTheCatalogPriceOrTheProductIsWholesaleLinked(): void
    {
        $higher = Factory::product(['wholesale' => 0, 'buy_price' => 4.00]);
        $linked = Factory::product(['wholesale' => 1, 'buy_price' => 2.00]);

        $this->post('stock/add_stock', $this->form($higher, ['cat_price' => '3']));
        $this->post('stock/add_stock', $this->form($linked, ['cat_price' => '3']));

        $this->assertEqualsWithDelta(4.0, (float) $this->find('products', $higher)['buy_price'], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $this->find('products', $linked)['buy_price'], 0.001);
        $this->assertSame(0, (int) $this->scalar("SELECT COUNT(*) FROM log WHERE event = 'update_buy_price' AND (msg LIKE ? OR msg LIKE ?)", ['%product: ' . $higher . ' %', '%product: ' . $linked . ' %']));
    }

    public function testThePageCanBePreselectedWithAProduct(): void
    {
        $pid = Factory::product(['name' => 'Preselect Me']);
        $page = $this->get('stock/add_stock/' . $pid);
        $this->assertStatus($page, 200);
        $this->assertStringContainsString('Preselect Me', (string) $page->getBody());
    }

    // ---- T161 --------------------------------------------------------------------------------

    public function testTheSameLotAddsToTheExistingCheckRow(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $form = $this->form($pid, ['new_volume' => 10, 'lotnr' => 'LOT-SAME']);

        $this->post('stock/add_stock', $form);
        $this->post('stock/add_stock', ['new_volume' => 4] + $form);

        // 10 + 4 = 14 in ONE row
        $rows = $this->checkRows($pid);
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(14.0, (float) $rows[0]['volume'], 0.001);
        $this->assertSame(1, $this->logCountStock('add_stock', $pid));
        $log = $this->row("SELECT * FROM log_stock WHERE event = 'add_stock_re' AND product = ?", [$pid]);
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(4.0, (float) $log['volume'], 0.001);
    }

    public function testADifferentLotOrPriceMakesAnotherRow(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $form = $this->form($pid, ['lotnr' => 'LOT-ONE']);

        $this->post('stock/add_stock', $form);
        $this->post('stock/add_stock', ['lotnr' => 'LOT-TWO'] + $form);
        $this->post('stock/add_stock', ['in_price' => '2.6'] + $form);

        $this->assertCount(3, $this->checkRows($pid));
        $this->assertSame(0, $this->logCountStock('add_stock_re', $pid));
    }

    #[Group('known-bug')]
    public function testAnEmptyEolIsNotMatchedAgainAndInsertsASecondRow(): void
    {
        // KNOWN BUG: the first add stores '' (not NULL) for the eol, the lookup searches eol IS NULL,
        // so the same lot is never merged — decide in CI4 whether to keep
        $pid = Factory::product(['wholesale' => 0]);
        $form = $this->form($pid, ['eol' => '', 'lotnr' => 'LOT-NOEOL']);

        $this->post('stock/add_stock', $form);
        $this->post('stock/add_stock', $form);

        $this->assertCount(2, $this->checkRows($pid));
    }

    // ---- T162 --------------------------------------------------------------------------------

    public function testAVolumeOverTheLimitIsRefused(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $this->db()->exec("DELETE FROM log WHERE event = 'bad_stock_entry'");

        $response = $this->post('stock/add_stock', $this->form($pid, ['new_volume' => 6000, 'lotnr' => 'LOT-BIG']));

        $this->assertSee($response, 'Invalid volume (>5000)');
        $this->assertCount(0, $this->checkRows($pid));
        $log = $this->row("SELECT * FROM log WHERE event = 'bad_stock_entry'");
        $this->assertNotNull($log);
        $this->assertStringContainsString('pid: ' . $pid, $log['msg']);
        $this->assertStringContainsString('lotnr:LOT-BIG', $log['msg']);
        $this->assertStringContainsString('volume:6000', $log['msg']);
    }

    public function testExactlyTheLimitAndAMissingProductGetTheGenericError(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $this->db()->exec("DELETE FROM log WHERE event = 'bad_stock_entry'");

        $atLimit = $this->post('stock/add_stock', $this->form($pid, ['new_volume' => 5000]));
        $noPid = $this->post('stock/add_stock', $this->form($pid, ['pid' => '']));

        $this->assertSee($atLimit, 'Not a valid product or no volume');
        $this->assertSee($noPid, 'Not a valid product or no volume');
        $this->assertCount(0, $this->checkRows($pid));
        $this->assertSame(2, $this->logCount('bad_stock_entry'));
    }

    public function testAnEmptyOrZeroVolumeIsRefused(): void
    {
        $pid = Factory::product(['wholesale' => 0]);
        $empty = $this->post('stock/add_stock', $this->form($pid, ['new_volume' => '']));
        $zero = $this->post('stock/add_stock', $this->form($pid, ['new_volume' => '0']));

        $this->assertSee($empty, 'Not a valid product or no volume');
        $this->assertSee($zero, 'Not a valid product or no volume');
        $this->assertCount(0, $this->checkRows($pid));
    }

    public function testAValidPostShowsNoErrorAndWritesNoBadEntryLog(): void
    {
        // positive control for the refusals above
        $pid = Factory::product(['wholesale' => 0]);
        $this->db()->exec("DELETE FROM log WHERE event = 'bad_stock_entry'");

        $response = $this->post('stock/add_stock', $this->form($pid, ['new_volume' => 4999]));

        $this->assertDontSee($response, 'Not a valid product');
        $this->assertDontSee($response, 'Invalid volume');
        $this->assertCount(1, $this->checkRows($pid));
        $this->assertSame(0, $this->logCount('bad_stock_entry'));
    }

    #[Group('known-bug')]
    public function testANegativeVolumeIsAccepted(): void
    {
        // KNOWN BUG: -5 < 5000 and not empty, so a negative intake is stored — decide in CI4 whether to keep
        $pid = Factory::product(['wholesale' => 0]);

        $this->post('stock/add_stock', $this->form($pid, ['new_volume' => -5]));

        $rows = $this->checkRows($pid);
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(-5.0, (float) $rows[0]['volume'], 0.001);
    }

    // ---- T163 --------------------------------------------------------------------------------

    public function testANewGs1BarcodeIsStoredOnTheProduct(): void
    {
        $pid = Factory::product(['wholesale' => 0, 'input_barcode' => null]);
        $gtin = '0' . substr((string) hexdec(substr(md5(uniqid()), 0, 8)), 0, 9) . '12';

        $this->post('stock/add_stock', $this->form($pid, ['new_barcode_input' => 1, 'barcode_gs1' => $gtin]));

        $this->assertSame($gtin, $this->find('products', $pid)['input_barcode']);
        $log = $this->row("SELECT * FROM log WHERE event = 'new_gs1' AND msg LIKE ?", ['%pid: ' . $pid . ' %']);
        $this->assertNotNull($log);
        $this->assertStringContainsString('gs1:' . $gtin, $log['msg']);
    }

    public function testTheBarcodeIsLeftAloneWithoutTheFlag(): void
    {
        $pid = Factory::product(['wholesale' => 0, 'input_barcode' => null]);

        $this->post('stock/add_stock', $this->form($pid, ['new_barcode_input' => 0, 'barcode_gs1' => '12345678']));

        $this->assertNull($this->find('products', $pid)['input_barcode']);
    }

    private function logCountStock(string $event, int $pid): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM log_stock WHERE event = ? AND product = ?', [$event, $pid]);
    }
}
