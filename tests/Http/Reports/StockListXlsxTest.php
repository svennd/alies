<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/ReportsHelper.php';

/**
 * T271 - Reports::stock_list (stock valuation list as xlsx).
 *
 * Rules: reports/stock_list shows one export link per location plus "All". reports/stock_list/1/{loc}/{btw}
 * downloads an xlsx with one row per (product, lotnr, in_price) group of IN_USE stock at that location
 * (location 0 = all, btw 0 = all, otherwise only products with that btw_buy), ordered by product name. The
 * header row is the column list of Stock_model::get_list with the first letter upper-cased. A purchase price of 0
 * is valued as 0.01 per pack (and shown as 0.1 per pack).
 */
final class StockListXlsxTest extends HttpTestCase
{
    private static int $location;
    private static int $emptyLocation;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$location = Factory::insert('stock_location', ['name' => 'SLX Magazijn', 'color' => '#123456', 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        self::$emptyLocation = Factory::insert('stock_location', ['name' => 'SLX Leeg', 'color' => '#654321', 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $loc = (string) self::$location;

        // A: pack of 10, bought at 5.00 -> 0.50 per piece; two stock rows of the same lot and price form one group: 4 + 6
        $a = Factory::product(['name' => 'SLX Alpha', 'buy_volume' => 10, 'btw_buy' => 6, 'unit_sell' => 'tab']);
        Factory::stock($a, ['location' => $loc, 'lotnr' => 'SLX-A1', 'in_price' => 5.00, 'volume' => 4, 'eol' => '2030-01-31']);
        Factory::stock($a, ['location' => $loc, 'lotnr' => 'SLX-A1', 'in_price' => 5.00, 'volume' => 6, 'eol' => '2030-01-31']);
        // B: pack of 4, in_price 0 -> valued as 0.01 per pack
        $b = Factory::product(['name' => 'SLX Beta', 'buy_volume' => 4, 'btw_buy' => 6, 'unit_sell' => 'ml']);
        Factory::stock($b, ['location' => $loc, 'lotnr' => 'SLX-B1', 'in_price' => 0, 'volume' => 8, 'eol' => '2031-06-30']);
        // C: 21 % product
        $c = Factory::product(['name' => 'SLX Gamma', 'buy_volume' => 2, 'btw_buy' => 21, 'unit_sell' => 'stuk']);
        Factory::stock($c, ['location' => $loc, 'lotnr' => 'SLX-C1', 'in_price' => 3.00, 'volume' => 5, 'eol' => '2032-12-31']);
        // never listed: history state, and stock at another location
        Factory::stock($a, ['location' => $loc, 'lotnr' => 'SLX-HIST', 'in_price' => 5.00, 'volume' => 99, 'state' => Fixtures::STATE_HISTORY]);
        Factory::stock($a, ['location' => (string) Fixtures::LOCATION_CAR, 'lotnr' => 'SLX-ELSEWHERE', 'in_price' => 5.00, 'volume' => 77]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private const HEADER = ['Name', 'AANTAL', 'VERKOOPSEENHEID', 'INKOOPPRIJS_STUK', 'Lotnr', 'Eol', 'BTW', 'WAARDE', 'INKOOPPRIJS_VERPAKKING'];

    public function testThePickerListsEveryLocationWithAnExportLink(): void
    {
        $page = $this->get('reports/stock_list');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Praktijk');
        $this->assertSee($page, 'SLX Magazijn');
        $this->assertSee($page, 'All');
        $body = (string) $page->getBody();
        $this->assertStringContainsString('reports/stock_list/1/' . self::$location, $body);
        $this->assertStringContainsString('reports/stock_list/1/0/21', $body);
        $this->assertStringContainsString('reports/clients_list/1', $body);
    }

    public function testALocationExportIsAnXlsxWithOneRowPerStockGroup(): void
    {
        $response = $this->get('reports/stock_list/1/' . self::$location . '/0');

        $this->assertDownload($response, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'stock_list.xlsx');
        $this->assertSame('PK', substr((string) $response->getBody(), 0, 2));
        $rows = ReportsHelper::xlsxRows($response);

        $this->assertSame(self::HEADER, $rows[0]);
        // 3 groups (history and other-location stock left out), ordered by name
        $this->assertCount(4, $rows);
        $this->assertSame(['SLX Alpha', 'SLX Beta', 'SLX Gamma'], array_column(array_slice($rows, 1), 0));

        // Alpha: 4 + 6 = 10 tab; 5.00 / 10 = 0.50 per piece; value 0.50 * 10 = 5.00; pack price 5
        [$name, $qty, $unit, $piece, $lot, $eol, $btw, $value, $pack] = $rows[1];
        $this->assertSame('tab', $unit);
        $this->assertEqualsWithDelta(10.0, (float) $qty, 0.001);
        $this->assertEqualsWithDelta(0.5, (float) $piece, 0.001);
        $this->assertSame('SLX-A1', $lot);
        $this->assertSame('2030-01-31', (string) $eol);
        $this->assertEquals(6, $btw);
        $this->assertEqualsWithDelta(5.0, (float) $value, 0.001);
        $this->assertEqualsWithDelta(5.0, (float) $pack, 0.001);

        // Beta: in_price 0 counts as 0.01: piece ROUND(0.01 / 4, 2) = 0.00, value ROUND(0.0025 * 8, 2) = 0.02, pack shown as 0.1
        [, $qty, $unit, $piece, , , , $value, $pack] = $rows[2];
        $this->assertSame('ml', $unit);
        $this->assertEqualsWithDelta(8.0, (float) $qty, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $piece, 0.001);
        $this->assertEqualsWithDelta(0.02, (float) $value, 0.001);
        $this->assertEqualsWithDelta(0.1, (float) $pack, 0.001);

        // Gamma: 3.00 / 2 = 1.50 per piece, 5 pieces = 7.50
        [, $qty, , $piece, , , $btw, $value, $pack] = $rows[3];
        $this->assertEquals(21, $btw);
        $this->assertEqualsWithDelta(5.0, (float) $qty, 0.001);
        $this->assertEqualsWithDelta(1.5, (float) $piece, 0.001);
        $this->assertEqualsWithDelta(7.5, (float) $value, 0.001);
        $this->assertEqualsWithDelta(3.0, (float) $pack, 0.001);
    }

    public function testThePercentageFiltersOnTheBuyVat(): void
    {
        $only21 = ReportsHelper::xlsxRows($this->get('reports/stock_list/1/' . self::$location . '/21'));
        $only6 = ReportsHelper::xlsxRows($this->get('reports/stock_list/1/' . self::$location . '/6'));

        $this->assertSame(['SLX Gamma'], array_column(array_slice($only21, 1), 0));
        $this->assertSame(['SLX Alpha', 'SLX Beta'], array_column(array_slice($only6, 1), 0));
    }

    public function testLocationZeroListsEveryLocation(): void
    {
        $rows = ReportsHelper::xlsxRows($this->get('reports/stock_list/1/0/0'));
        $lots = array_column(array_slice($rows, 1), 4);

        $this->assertContains('SLX-A1', $lots);
        $this->assertContains('SLX-ELSEWHERE', $lots, 'stock of the other location is included when no location is given');
        $this->assertNotContains('SLX-HIST', $lots);
    }

    public function testWithoutTheGetFlagThePickerIsShownInsteadOfAFile(): void
    {
        $page = $this->get('reports/stock_list/0');

        $this->assertStatus($page, 200);
        $this->assertStringStartsWith('text/html', $page->getHeaderLine('Content-Type'));
    }

    #[Group('known-bug')]
    public function testALocationWithoutStockGivesAServerError(): void
    {
        // KNOWN BUG: ExcelExporter::exportToExcel throws "No data provided for export." when get_list is empty,
        // so the user sees a 500 instead of an empty sheet or a message.
        $empty = $this->get('reports/stock_list/1/' . self::$emptyLocation . '/0');
        $this->assertStatus($empty, 500);

        // positive control: the same URL shape works for a location that has stock
        $this->assertStatus($this->get('reports/stock_list/1/' . self::$location . '/0'), 200);
    }
}
