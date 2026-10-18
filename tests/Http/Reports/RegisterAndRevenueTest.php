<?php

declare(strict_types=1);

require_once __DIR__ . '/ReportsHelper.php';

/**
 * T275 - Reports::register_in, register_out and revenue.
 *
 * register_in: the register_in rows created in the range (> from 00:00, < to 23:59) joined with their delivery slip
 * and the wholesale catalogue price (bruto). Default range: the last month.
 * register_out: one row per usage line of every event created in the range (any status), with the money maths of
 * the view (see the docblock of the test). Default range: the last month.
 * revenue: without a post, the last 12 months (calendar months up to but excluding the last day of this month) of
 * invoiced bills; with submit and a range the same per month inside the range. Every row links to the Kluwer export
 * of the whole posted range.
 */
final class RegisterAndRevenueTest extends HttpTestCase
{
    private static int $pet;
    private static int $owner;
    private static int $registerProduct;
    private static int $outProduct;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // ---- register in: March 2019 (own product, wholesale bruto 12.34)
        $wholesale = Factory::wholesale(['bruto' => 12.34]);
        self::$registerProduct = Factory::product(['name' => 'REG In Product', 'wholesale' => $wholesale, 'unit_buy' => 'doos', 'vhbcode' => 'BE-V123', 'producer' => 'Prodco', 'btw_buy' => 6, 'btw_sell' => 21]);
        $slip = Factory::insert('delivery_slip', ['vet' => Fixtures::USER_VET, 'note' => '', 'regdate' => '2019-03-12', 'location' => Fixtures::LOCATION_PRACTICE, 'created_at' => '2019-03-12 09:00:00', 'updated_at' => '2019-03-12 09:00:00']);
        self::registerIn($slip, 'REGIN-A', 10.0, 7.25, '2019-03-12 09:00:00');
        self::registerIn($slip, 'REGIN-EDGE', 1.0, 1.0, '2019-03-01 00:00:00');
        self::registerIn($slip, 'REGIN-LATE', 1.0, 1.0, '2019-03-31 23:59:30');
        self::registerIn($slip, 'REGIN-NOW', 3.0, 2.0, date('Y-m-d H:i:s'));

        // ---- register out: May 2019
        self::$owner = Factory::owner();
        self::$pet = Factory::pet(self::$owner, ['name' => 'Regpet']);
        // pack of 4 bought at catalogue 10.00; the stock row was bought at 3.00 (lot REGOUT-1)
        self::$outProduct = Factory::product(['name' => 'REG Out Product', 'buy_volume' => 4, 'buy_price' => 10.00, 'unit_sell' => 'tab', 'btw_buy' => 6]);
        $stock = Factory::stock(self::$outProduct, ['lotnr' => 'REGOUT-1', 'in_price' => 3.00]);
        $closed = Factory::event(self::$pet, 1, 0, ['created_at' => '2019-05-10 10:00:00']);
        Factory::eventProduct($closed, self::$outProduct, 2.0, ['stock_id' => $stock, 'price_net' => 20.00, 'booking' => Fixtures::BOOKING_PRODUCTS, 'created_at' => '2019-05-10 10:00:00']);
        $open = Factory::event(self::$pet, 0, 0, ['created_at' => '2019-05-11 10:00:00']);
        Factory::eventProduct($open, self::$outProduct, 1.0, ['stock_id' => $stock, 'price_net' => 5.00, 'created_at' => '2019-05-11 10:00:00']);

        // ---- revenue: 2016, bills with an invoice number (deleted, not invoiced and out of range are left out)
        $o = Factory::owner();
        $bill = static fn (string $date, float $net, float $brut, array $over = []) => Factory::bill($o, 4, array_merge(['invoice_id' => random_int(1, 9999), 'invoice_date' => $date, 'total_net' => $net, 'total_brut' => $brut, 'created_at' => $date], $over));
        $bill('2016-01-10 10:00:00', 100.00, 106.00);
        $bill('2016-01-20 10:00:00', 50.00, 53.00);
        $bill('2016-03-05 10:00:00', 1000.50, 1060.53);
        $bill('2016-03-06 10:00:00', 234.00, 248.04);
        $bill('2016-01-15 10:00:00', 999.00, 999.00, ['deleted_at' => '2016-02-01 00:00:00']);
        $bill('2016-01-16 10:00:00', 888.00, 888.00, ['invoice_id' => null]);
        $bill('2016-05-05 10:00:00', 777.00, 777.00);
        // a month of its own for the default view: 3 months ago, the 10th
        $recent = date('Y-m-10 10:00:00', strtotime('first day of -3 months'));
        $bill($recent, 40.00, 42.40);
        $bill($recent, 60.00, 63.60);
    }

    private static function registerIn(int $slip, string $lot, float $volume, float $price, string $created): void
    {
        Factory::insert('register_in', ['product' => self::$registerProduct, 'volume' => $volume, 'eol' => '2030-05-31', 'in_price' => $price, 'cat_price' => 9.99, 'lotnr' => $lot, 'supplier' => 'Supco', 'delivery_slip' => $slip, 'created_at' => $created, 'updated_at' => $created]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    // ---- register in ------------------------------------------------------------------------------

    public function testRegisterInListsTheRowsOfTheRange(): void
    {
        $page = $this->post('reports/register_in', ['search_from' => '2019-03-01', 'search_to' => '2019-03-31']);

        $this->assertStatus($page, 200);
        $rows = ReportsHelper::rowsStartingWith($page, 'REG In Product');
        // name, volume, delivery date, eol, lot, vhbcode, in_price, bruto, supplier, producer, btw_buy, btw_sell
        $this->assertSame(['REG In Product', '10.00 doos', '2019-03-12', '2030-05-31', 'REGIN-A', 'BE-V123', '7.25', '12.34', 'Supco', 'Prodco', '6', '21'], $rows[0]);
        $this->assertCount(1, $rows, 'the rows at exactly from 00:00:00 and after to 23:59 are left out');
        $this->assertDontSee($page, 'REGIN-EDGE');
        $this->assertDontSee($page, 'REGIN-LATE');
        $this->assertDontSee($page, 'REGIN-NOW');
    }

    public function testRegisterInBoundsAreStrictAndWideningTheRangeAddsTheEdges(): void
    {
        $page = $this->post('reports/register_in', ['search_from' => '2019-02-28', 'search_to' => '2019-04-01']);

        $lots = array_column(ReportsHelper::rowsStartingWith($page, 'REG In Product'), 4);
        sort($lots);
        $this->assertSame(['REGIN-A', 'REGIN-EDGE', 'REGIN-LATE'], $lots);
    }

    public function testRegisterInDefaultsToTheLastMonth(): void
    {
        $page = $this->get('reports/register_in');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'REGIN-NOW');
        $this->assertDontSee($page, 'REGIN-A');
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-1 months')) . '"', (string) $page->getBody());
    }

    public function testRegisterInWithoutBrutoShowsADash(): void
    {
        $slip = Factory::insert('delivery_slip', ['vet' => Fixtures::USER_VET, 'note' => '', 'regdate' => '2019-07-02', 'location' => 1, 'created_at' => '2019-07-02 09:00:00', 'updated_at' => '2019-07-02 09:00:00']);
        $plain = Factory::product(['name' => 'REG Plain', 'wholesale' => 0, 'supplier' => 'Fallback', 'producer' => '', 'vhbcode' => '']);
        Factory::insert('register_in', ['product' => $plain, 'volume' => 1.0, 'eol' => '2030-01-01', 'in_price' => 2.0, 'cat_price' => null, 'lotnr' => 'REGIN-PLAIN', 'supplier' => '', 'delivery_slip' => $slip, 'created_at' => '2019-07-02 09:00:00', 'updated_at' => '2019-07-02 09:00:00']);

        $page = $this->post('reports/register_in', ['search_from' => '2019-07-01', 'search_to' => '2019-07-31']);

        $row = ReportsHelper::rowsStartingWith($page, 'REG Plain')[0];
        // vhbcode '-', no wholesale -> bruto '-', empty row supplier falls back to the product's supplier, producer '-'
        $this->assertSame(['REG Plain', '1.00 stuk', '2019-07-02', '2030-01-01', 'REGIN-PLAIN', '-', '2.00', '-', 'Fallback', '-', '6', '6'], $row);
    }

    // ---- register out -----------------------------------------------------------------------------

    public function testRegisterOutListsTheUsageLinesOfEveryEventInTheRange(): void
    {
        $page = $this->post('reports/register_out', ['search_from' => '2019-05-01', 'search_to' => '2019-05-31']);

        $this->assertStatus($page, 200);
        $rows = ReportsHelper::rowsStartingWith($page, 'REG Out Product');
        $this->assertCount(2, $rows, 'the open event is listed too: there is no status filter');
        $byVolume = array_column($rows, null, 1);

        // Closed event: 2 tab sold for 20.00. Catalogue price per tab 10.00 / 4 = 2.50 -> 5.00 (price_alies);
        // bought at 3.00 per pack of 4 -> 0.75 per tab -> 1.50 (day price).
        // name, volume, lot, date, total sale, alies price, day price, vhb, type, booking, btw_buy, btw_sell, vet, location, pet, client
        $this->assertSame(
            ['REG Out Product', '2.00 tab', 'REGOUT-1', '10-05-2019', '20.00 €', '5 €', '1.5 €', '-', 'Medicatie', '700000 (Producten)', '6', '6', 'Victor', 'Praktijk', (string) self::$pet, (string) self::$owner],
            $byVolume['2.00 tab']
        );
        // Open event: 1 tab for 5.00: 2.50 and 0.75
        $this->assertSame(['5.00 €', '2.5 €', '0.75 €'], [$byVolume['1.00 tab'][4], $byVolume['1.00 tab'][5], $byVolume['1.00 tab'][6]]);
        $this->assertSame('11-05-2019', $byVolume['1.00 tab'][3]);
    }

    public function testRegisterOutRangeOutsideTheUsageIsEmpty(): void
    {
        $page = $this->post('reports/register_out', ['search_from' => '2019-06-01', 'search_to' => '2019-06-30']);

        $this->assertStatus($page, 200);
        $this->assertSame([], ReportsHelper::rowsStartingWith($page, 'REG Out Product'));
        $this->assertStringContainsString('value="2019-06-01"', (string) $page->getBody());
    }

    public function testRegisterOutDefaultsToTheLastMonth(): void
    {
        $page = $this->get('reports/register_out');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-1 months')) . '"', (string) $page->getBody());
        $this->assertDontSee($page, 'REG Out Product');
    }

    // ---- revenue ----------------------------------------------------------------------------------

    public function testRevenueWithARangeShowsOneRowPerMonthAndLinksTheWholeRange(): void
    {
        $page = $this->post('reports/revenue', ['submit' => 'usage', 'search_from' => '2016-01-01', 'search_to' => '2016-04-30']);

        $this->assertStatus($page, 200);
        $rows = ReportsHelper::tableRows($page);
        $months = array_values(array_filter($rows, static fn (array $r): bool => preg_match("/^[A-Z][a-z]{2} '16$/", $r[0]) === 1));
        // month, bills, net, brut, options: Jan 100 + 50 (the deleted and the not invoiced bill are left out),
        // Mar 1000.50 + 234.00 = 1234.50 and 1060.53 + 248.04 = 1308.57 (thousands separated by a space)
        $this->assertSame([
            ["Jan '16", '2', '150,00', '159,00', 'Kluwer'],
            ["Mar '16", '2', '1 234,50', '1 308,57', 'Kluwer'],
        ], $months);
        $this->assertStringContainsString('export/kluwer/2016-01-01/2016-04-30', (string) $page->getBody());
    }

    public function testRevenueRangeBoundsAreStrict(): void
    {
        $o = Factory::owner();
        Factory::bill($o, 4, ['invoice_id' => 7001, 'invoice_date' => '2015-06-01 00:00:00', 'total_net' => 11.00, 'total_brut' => 11.00, 'created_at' => '2015-06-01 00:00:00']);
        Factory::bill($o, 4, ['invoice_id' => 7002, 'invoice_date' => '2015-06-15 12:00:00', 'total_net' => 22.00, 'total_brut' => 22.00, 'created_at' => '2015-06-15 12:00:00']);

        $page = $this->post('reports/revenue', ['submit' => 'usage', 'search_from' => '2015-06-01', 'search_to' => '2015-06-30']);

        $this->assertContains(["Jun '15", '1', '22,00', '22,00', 'Kluwer'], ReportsHelper::tableRows($page), 'the bill at exactly 00:00:00 of the first day is left out');
    }

    public function testRevenueWithoutAPostShowsTheLastTwelveMonths(): void
    {
        $page = $this->get('reports/revenue');

        $this->assertStatus($page, 200);
        $rows = ReportsHelper::tableRows($page);
        $label = date("M 'y", strtotime('first day of -3 months'));
        // two bills of 40.00 + 60.00 net and 42.40 + 63.60 gross three months ago
        $this->assertContains([$label, '2', '100,00', '106,00', 'Kluwer'], $rows);
        $this->assertDontSee($page, "Jan '16", '2016 is more than 12 months ago');
        // 12 months at most
        $monthRows = array_filter($rows, static fn (array $r): bool => preg_match("/^[A-Z][a-z]{2} '\d\d$/", $r[0]) === 1);
        $this->assertLessThanOrEqual(12, count($monthRows));
        // the links are for that month only
        $this->assertStringContainsString('export/kluwer/' . date('Y-m-01', strtotime('first day of -3 months')) . '/' . date('Y-m-t', strtotime('first day of -3 months')), (string) $page->getBody());
    }
}
