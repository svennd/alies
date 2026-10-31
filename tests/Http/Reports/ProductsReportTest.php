<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/ReportsHelper.php';

/**
 * T274 - Reports::products and Reports::products_csv (sold volume and net income per product and purchase price).
 *
 * Rules: the lines are taken on the creation date of the usage LINE (> from 00:00 and < to 23:59). The page groups
 * per product and, inside a product, per purchase price of the stock row the line was taken from (lines without
 * stock get in_price -1). products_csv has the header name,volume,unit,inprice,net_price,barcode and one row per line.
 *
 * Data (all in 2018-02, own products):
 *   Prod X (unit "ml"): stock S1 in_price 2.50: lines 2 ml/20.00 and 3 ml/30.00; stock S2 in_price 3.00: 1 ml/12.00;
 *                       a line without stock: 4 ml/8.00.
 *   Prod Y (unit "stuk"): stock S3 in_price 1.00: 4 stuk/8.00.
 * Page: X has three sub rows: in_price 2.50 -> 5 ml / 50 (the sums are PHP floats, so printed as "5" and "50"),
 * in_price 3.00 -> 1.00 ml / 12.00, in_price -1 -> 4.00 ml / 8.00; Y one sub row 4.00 stuk / 1.00 / 8.00.
 */
final class ProductsReportTest extends HttpTestCase
{
    private static int $x;
    private static int $y;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $pet = Factory::pet(Factory::owner());
        self::$x = Factory::product(['name' => 'PRD Xray', 'unit_sell' => 'ml']);
        self::$y = Factory::product(['name' => 'PRD Yankee', 'unit_sell' => 'stuk']);
        $s1 = Factory::stock(self::$x, ['in_price' => 2.50]);
        $s2 = Factory::stock(self::$x, ['in_price' => 3.00]);
        $s3 = Factory::stock(self::$y, ['in_price' => 1.00]);
        $stamp = '2018-02-10 12:00:00';
        $event = Factory::event($pet, 1, 0, ['created_at' => $stamp]);
        Factory::eventProduct($event, self::$x, 2.0, ['stock_id' => $s1, 'price_net' => 20.00, 'created_at' => $stamp]);
        Factory::eventProduct($event, self::$x, 3.0, ['stock_id' => $s1, 'price_net' => 30.00, 'created_at' => $stamp]);
        Factory::eventProduct($event, self::$x, 1.0, ['stock_id' => $s2, 'price_net' => 12.00, 'created_at' => $stamp]);
        Factory::eventProduct($event, self::$x, 4.0, ['stock_id' => null, 'price_net' => 8.00, 'created_at' => $stamp]);
        Factory::eventProduct($event, self::$y, 4.0, ['stock_id' => $s3, 'price_net' => 8.00, 'created_at' => $stamp]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testThePageGroupsPerProductAndPurchasePrice(): void
    {
        $page = $this->post('reports/products', ['search_from' => '2018-02-01', 'search_to' => '2018-02-28']);

        $this->assertStatus($page, 200);
        $text = preg_replace('/\s+/', ' ', strip_tags(str_replace('</td>', ' </td>', $this->text($page))));
        $this->assertStringContainsString('PRD Xray 5 ml 2.50 € 50 €', $text);
        $this->assertStringContainsString('3.00 € 12.00 €', $text);
        $this->assertStringContainsString('-1 € 8.00 €', $text);
        $this->assertStringContainsString('PRD Yankee 4.00 stuk 1.00 € 8.00 €', $text);
        // X has 3 sub rows: only the first row repeats nothing, the others start with the volume
        $rows = ReportsHelper::tableRows($page);
        $this->assertContains(['1.00 ml', '3.00 €', '12.00 €'], $rows);
        $this->assertContains(['4.00 ml', '-1 €', '8.00 €'], $rows);
        $this->assertContains(['PRD Xray', '5 ml', '2.50 €', '50 €'], $rows);
        $this->assertStringContainsString('reports/products_csv/2018-02-01/2018-02-28', (string) $page->getBody());
        $this->assertStringContainsString('rowspan="3"', (string) $page->getBody());
    }

    public function testARangeWithoutUsageShowsNoTable(): void
    {
        $page = $this->post('reports/products', ['search_from' => '2015-01-01', 'search_to' => '2015-01-31']);

        $this->assertStatus($page, 200);
        $this->assertDontSee($page, 'Sold Volume');
        $this->assertDontSee($page, 'Export to CSV');
    }

    public function testWithoutAPostTheLastMonthIsShown(): void
    {
        $page = $this->get('reports/products');

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-1 months')) . '"', $body);
        $this->assertStringContainsString('value="' . date('Y-m-d') . '"', $body);
        $this->assertDontSee($page, 'PRD Xray');
    }

    public function testTheBoundsAreStrict(): void
    {
        $pet = Factory::pet(Factory::owner());
        $product = Factory::product(['name' => 'PRD Edge']);
        $event = Factory::event($pet, 1, 0, ['created_at' => '2018-04-10 12:00:00']);
        Factory::eventProduct($event, $product, 1.0, ['created_at' => '2018-04-01 00:00:00']); // exactly from 00:00
        Factory::eventProduct($event, $product, 1.0, ['created_at' => '2018-04-30 23:59:30', 'price_net' => 7.00]); // after to 23:59

        $inside = $this->post('reports/products', ['search_from' => '2018-04-01', 'search_to' => '2018-04-30']);
        $wider = $this->post('reports/products', ['search_from' => '2018-03-31', 'search_to' => '2018-05-01']);

        $this->assertDontSee($inside, 'PRD Edge');
        $this->assertSee($wider, 'PRD Edge');
        // both lines (1 + 1 stuk, 10.00 + 7.00 net, no stock -> in_price -1) are merged into one sub row
        $this->assertContains(['PRD Edge', '2 stuk', '-1 €', '17 €'], ReportsHelper::tableRows($wider));
    }

    public function testTheCsvHasTheHeaderAndOneRowPerLine(): void
    {
        $response = $this->get('reports/products_csv/2018-02-01/2018-02-28');

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/csv', $response->getHeaderLine('Content-Type'));
        $lines = explode("\n", (string) $response->getBody());
        $this->assertSame('name,volume,unit,inprice,net_price,barcode', $lines[0]);
        $this->assertSame('', array_pop($lines), 'ends with one newline');
        $this->assertCount(6, $lines, 'header and five usage lines');
        // name, volume, unit, inprice: the first four columns are right
        $rows = array_map(static fn (string $l): array => explode(',', $l), array_slice($lines, 1));
        $first4 = array_map(static fn (array $r): string => implode(',', array_slice($r, 0, 4)), $rows);
        sort($first4);
        $this->assertSame([
            'PRD Xray,1.00,ml,3.00',
            'PRD Xray,2.00,ml,2.50',
            'PRD Xray,3.00,ml,2.50',
            'PRD Xray,4.00,ml,-1',
            'PRD Yankee,4.00,stuk,1.00',
        ], $first4);
    }

    #[Group('known-bug')]
    public function testTheNetPriceAndBarcodeColumnsOfTheCsvAreEmpty(): void
    {
        // KNOWN BUG: products_csv reads $p['net_price'] and $p['barcode'], but the query only selects volume and
        // price_net (and the stock's in_price). Both cells are empty (PHP warnings about undefined keys may be
        // printed into the body when display_errors is on, so the rows are checked on their tail only).
        $response = $this->get('reports/products_csv/2018-02-01/2018-02-28');
        $body = (string) $response->getBody();

        $dataLines = array_values(array_filter(explode("\n", $body), static fn (string $l): bool => str_starts_with($l, 'PRD ')));
        $this->assertCount(5, $dataLines);
        foreach ($dataLines as $line) {
            $this->assertStringEndsWith(',,', $line, 'net_price and barcode are empty: ' . $line);
        }
        // the net amounts that exist (20.00, 30.00 ...) are nowhere in the file
        $this->assertStringNotContainsString('30.00', $body);
    }

    public function testTheCsvOfAnEmptyRangeIsJustTheHeader(): void
    {
        $response = $this->get('reports/products_csv/2015-01-01/2015-01-31');

        $this->assertStatus($response, 200);
        $this->assertSame("name,volume,unit,inprice,net_price,barcode\n", (string) $response->getBody());
    }
}
