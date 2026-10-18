<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/ReportsHelper.php';

/**
 * T273 - Reports::usage and Reports::usage_csv (usage of one product).
 *
 * Rules: reports/usage/{pid} lists the usage lines of the product on events created in the last 3 months. A POST
 * with submit=usage and both dates chooses the range (on events.created_at, > from 00:00 and < to 23:59). A POST
 * with summary=1 shows a per location summary (on the creation date of the LINE) with a "total" row from WITH ROLLUP.
 * reports/usage_csv/{pid}/{from}/{to} downloads header volume,vet,pet_id,client_id,lotnr,eol,created_at.
 *
 * Data: one product of our own. Detail rows: 2 days ago (recent), 6 months ago (old), 2017-05-10 (pair of lines).
 * Summary (all on 2017-05-10, product unit "stuk"): Praktijk 2.00 + 3.00 = 5 volume, net 20 + 30 = 50, 2 lines;
 * Wagen 4.00, net 40, 1 line (MANUAL reduction); total 9 volume, net 90, 3 lines.
 */
final class UsageReportTest extends HttpTestCase
{
    private static int $product;
    private static int $pet;
    private static int $owner;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$owner = Factory::owner(['last_name' => 'Usageowner']);
        self::$pet = Factory::pet(self::$owner, ['name' => 'Usagepet']);
        self::$product = Factory::product(['name' => 'USG Product', 'unit_sell' => 'stuk']);

        self::line(date('Y-m-d 12:00:00', strtotime('-2 days')), 1.0, 10.0, 'USG-RECENT', 1);
        self::line(date('Y-m-d 12:00:00', strtotime('-6 months')), 2.0, 20.0, 'USG-OLD', 1);

        self::line('2017-05-10 12:00:00', 2.0, 20.0, 'USG-2017-A', Fixtures::LOCATION_PRACTICE);
        self::line('2017-05-10 12:00:00', 3.0, 30.0, 'USG-2017-B', Fixtures::LOCATION_PRACTICE);
        self::line('2017-05-10 12:00:00', 4.0, 40.0, 'USG-2017-C', Fixtures::LOCATION_CAR, ['reduction_reason' => 'MANUAL']);

        // the event is from 2017-06 but the line was created later: the detail report looks at the event, the summary at the line
        $event = Factory::event(self::$pet, 1, 0, ['created_at' => '2017-06-15 12:00:00']);
        Factory::eventProduct($event, self::$product, 1.0, ['stock_id' => Factory::stock(self::$product, ['lotnr' => 'USG-LATE', 'eol' => '2029-02-28']), 'created_at' => '2017-08-01 12:00:00']);
    }

    /** One closed event with a single usage line (stock row with the given lot). */
    private static function line(string $stamp, float $volume, float $net, string $lot, int $location, array $over = []): void
    {
        $event = Factory::event(self::$pet, 1, 0, ['created_at' => $stamp, 'location' => $location]);
        $stock = Factory::stock(self::$product, ['lotnr' => $lot, 'eol' => '2028-01-31', 'location' => (string) $location]);
        Factory::eventProduct($event, self::$product, $volume, ['stock_id' => $stock, 'unit_price' => $net / $volume, 'price_net' => $net, 'created_at' => $stamp] + $over);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testTheLastThreeMonthsAreShownByDefault(): void
    {
        $page = $this->get('reports/usage/' . self::$product);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'USG Product');
        $this->assertSee($page, 'USG-RECENT');
        $this->assertDontSee($page, 'USG-OLD', 'older than 3 months');
        $this->assertDontSee($page, 'USG-2017-A');
        // the form is prefilled with the default range
        $body = (string) $page->getBody();
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-3 months')) . '"', $body);
        $this->assertStringContainsString('value="' . date('Y-m-d') . '"', $body);
        // detail row: volume with unit, lot, pet, client, vet
        $rows = ReportsHelper::rowsStartingWith($page, date('d-m-Y', strtotime('-2 days')));
        $this->assertCount(1, $rows);
        $this->assertSame(['1.00 stuk', 'USG-RECENT', '2028-01-31', '1.00', 'Usagepet', 'Usageowner', 'Victor', 'Praktijk'], array_slice($rows[0], 1, 8));
    }

    public function testASubmittedRangeFiltersTheRows(): void
    {
        $page = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-05-01', 'search_to' => '2017-05-31']);

        $this->assertStatus($page, 200);
        foreach (['USG-2017-A', 'USG-2017-B', 'USG-2017-C'] as $lot) {
            $this->assertSee($page, $lot);
        }
        $this->assertDontSee($page, 'USG-RECENT');
        $this->assertDontSee($page, 'USG-OLD');
        $this->assertDontSee($page, 'USG-LATE', 'event from June');
        $this->assertStringContainsString('value="2017-05-01"', (string) $page->getBody());
        // the stock location is only appended to the lot when it differs from the event's location (same here)
        $this->assertDontSee($page, '(Wagen)');
    }

    public function testTheRangeIsIgnoredWithoutTheSubmitValueOrWithOnlyOneDate(): void
    {
        $noSubmit = $this->post('reports/usage/' . self::$product, ['search_from' => '2017-05-01', 'search_to' => '2017-05-31']);
        $oneDate = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-05-01']);

        foreach ([$noSubmit, $oneDate] as $page) {
            $this->assertSee($page, 'USG-RECENT');
            $this->assertDontSee($page, 'USG-2017-A');
        }
    }

    public function testTheDetailReportUsesTheEventDateAndStrictBounds(): void
    {
        // the late line belongs to a June event
        $june = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-06-01', 'search_to' => '2017-06-30']);
        $august = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-08-01', 'search_to' => '2017-08-31']);

        $this->assertSee($june, 'USG-LATE');
        $this->assertDontSee($august, 'USG-LATE');
        // boundary: events at exactly "from 00:00:00" are not part of the range (strict >)
        $event = Factory::event(self::$pet, 1, 0, ['created_at' => '2017-09-01 00:00:00']);
        Factory::eventProduct($event, self::$product, 1.0, ['stock_id' => Factory::stock(self::$product, ['lotnr' => 'USG-EDGE']), 'created_at' => '2017-09-01 00:00:00']);
        $edge = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-09-01', 'search_to' => '2017-09-30']);
        $before = $this->post('reports/usage/' . self::$product, ['submit' => 'usage', 'search_from' => '2017-08-31', 'search_to' => '2017-09-30']);
        $this->assertDontSee($edge, 'USG-EDGE');
        $this->assertSee($before, 'USG-EDGE');
    }

    public function testTheSummaryShowsOneRowPerLocationAndATotal(): void
    {
        $page = $this->post('reports/usage/' . self::$product, ['summary' => '1', 'search_from' => '2017-05-01', 'search_to' => '2017-05-31']);

        $this->assertStatus($page, 200);
        $rows = array_values(array_filter(ReportsHelper::tableRows($page), static fn (array $r): bool => count($r) === 7 && $r[0] !== 'Location'));
        $byLocation = array_column($rows, null, 0);

        // location, volume, netto, transactions, tier, auto, manual
        $this->assertSame(['Praktijk', '5.00 stuk', '50.00', '2', '0', '0', '0'], $byLocation['Praktijk']);
        $this->assertSame(['Wagen', '4.00 stuk', '40.00', '1', '0', '0', '1'], $byLocation['Wagen']);
        $this->assertSame(['total', '9.00 stuk', '90.00', '3', '0', '0', '1'], $byLocation['total']);
        $this->assertCount(3, $rows);
        // the summary is a different view: no lot numbers
        $this->assertDontSee($page, 'USG-2017-A');
    }

    public function testTheSummaryLooksAtTheDateOfTheLineNotOfTheEvent(): void
    {
        $june = $this->post('reports/usage/' . self::$product, ['summary' => '1', 'search_from' => '2017-06-01', 'search_to' => '2017-06-30']);
        $august = $this->post('reports/usage/' . self::$product, ['summary' => '1', 'search_from' => '2017-08-01', 'search_to' => '2017-08-31']);

        $this->assertSame([], ReportsHelper::rowsStartingWith($june, 'total'), 'no line was created in June');
        $total = ReportsHelper::rowsStartingWith($august, 'total');
        $this->assertSame(['total', '1.00 stuk', '10.00', '1', '0', '0', '0'], $total[0]);
    }

    #[Group('known-bug')]
    public function testTheSummaryWithoutDatesShowsNothing(): void
    {
        // KNOWN BUG: the defaults (last 3 months) are only used for the form fields; usage_summary gets the null
        // dates and builds STR_TO_DATE(' 00:00', ...) = NULL, so nothing matches, although USG-RECENT is 2 days old.
        $page = $this->post('reports/usage/' . self::$product, ['summary' => '1']);

        $this->assertStatus($page, 200);
        $this->assertDontSee($page, '# Transactions');
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-3 months')) . '"', (string) $page->getBody());
        // positive control: with dates the same product has a summary
        $with = $this->post('reports/usage/' . self::$product, ['summary' => '1', 'search_from' => date('Y-m-d', strtotime('-3 months')), 'search_to' => date('Y-m-d')]);
        $this->assertSee($with, '# Transactions');
    }

    public function testTheCsvDownloadHasTheHeaderAndOneRowPerLine(): void
    {
        $response = $this->get('reports/usage_csv/' . self::$product . '/2017-05-01/2017-05-31');

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/csv', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('attachment', $response->getHeaderLine('Content-Disposition'));
        $lines = explode("\n", (string) $response->getBody());
        $this->assertSame('volume,vet,pet_id,client_id,lotnr,eol,created_at', $lines[0]);
        $this->assertSame('', array_pop($lines), 'the body ends with one newline');
        $rows = array_slice($lines, 1);
        $this->assertCount(3, $rows);
        sort($rows);
        $pet = self::$pet;
        $owner = self::$owner;
        $this->assertSame([
            "2.00,Victor,{$pet},{$owner},USG-2017-A,2028-01-31,2017-05-10 12:00:00",
            "3.00,Victor,{$pet},{$owner},USG-2017-B,2028-01-31,2017-05-10 12:00:00",
            "4.00,Victor,{$pet},{$owner},USG-2017-C,2028-01-31,2017-05-10 12:00:00",
        ], $rows);
    }

    public function testTheCsvOfAnEmptyRangeIsJustTheHeader(): void
    {
        $response = $this->get('reports/usage_csv/' . self::$product . '/2015-01-01/2015-01-31');

        $this->assertStatus($response, 200);
        $this->assertSame("volume,vet,pet_id,client_id,lotnr,eol,created_at\n", (string) $response->getBody());
    }
}
