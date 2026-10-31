<?php

declare(strict_types=1);

/**
 * T370  Sources: application/models/Events_products_model.php, Events_procedures_model.php
 *
 * Rules in plain words:
 *  - Events_products_model::get_monthly_earning(DateTime): sum of price_net per btw from the GIVEN DAY (not the
 *    first of the month) to LAST_DAY of that month; array keyed by btw plus 'total'; only VAT rates that occur are
 *    keys; no rows at all gives [6 => 0, 21 => 0, 'total' => 0].
 *  - get_monthly_usage(product, months): SUM(volume) per 'Y-m' for the last N months, oldest first.
 *  - usage_summary(product, from, to): one row per event location (name) with volume / net / brut / count and the
 *    reduction counters, then a WITH ROLLUP row whose location is 'total'. The range is created_at >= from 00:00
 *    and <= to 23:59:00.
 *  - Events_procedures_model::get_monthly_earning(DateTime): same window, one number rounded to 2, 0 when empty.
 *  - get_net_income(procedure): counts, volume and net price of that procedure in the last 3 months and last year.
 * All rows use dates in 2011 (nothing in the seed lives there) or fresh products/procedures.
 */
final class EventsLinesAggregatesTest extends CodeIgniterDatabaseTestCase
{
    private $lines;
    private $procLines;
    private int $pet;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->lines = $this->model('Events_products_model', 'ep_t370');
        $this->procLines = $this->model('Events_procedures_model', 'epr_t370');
        $this->pet = Factory::pet(Factory::owner());
    }

    private function event(int $location = Fixtures::LOCATION_PRACTICE): int
    {
        return Factory::event($this->pet, 1, 0, ['location' => $location]);
    }

    // ------------------------------------------------------------ get_monthly_earning (products)

    public function testMonthlyEarningSumsNetPerVatFromTheGivenDayUntilEndOfMonth(): void
    {
        $product = Factory::product();
        $event = $this->event();
        // window 2011-05-10 .. 2011-05-31
        Factory::eventProduct($event, $product, 1, ['btw' => 6, 'price_net' => 10.00, 'created_at' => '2011-05-10 00:00:00']);
        Factory::eventProduct($event, $product, 1, ['btw' => 6, 'price_net' => 5.50, 'created_at' => '2011-05-31 23:59:59']);
        Factory::eventProduct($event, $product, 1, ['btw' => 21, 'price_net' => 20.25, 'created_at' => '2011-05-20 12:00:00']);
        // outside: day before the window and first day of June
        Factory::eventProduct($event, $product, 1, ['btw' => 6, 'price_net' => 1000, 'created_at' => '2011-05-09 23:59:59']);
        Factory::eventProduct($event, $product, 1, ['btw' => 21, 'price_net' => 2000, 'created_at' => '2011-06-01 00:00:00']);

        $result = $this->lines->get_monthly_earning(new DateTime('2011-05-10'));

        // 6 %: 10.00 + 5.50 = 15.50; 21 %: 20.25; total 15.50 + 20.25 = 35.75
        $this->assertEqualsWithDelta(15.50, (float) $result[6], 0.001);
        $this->assertEqualsWithDelta(20.25, (float) $result[21], 0.001);
        $this->assertEqualsWithDelta(35.75, (float) $result['total'], 0.001);
        $this->assertCount(3, $result);
    }

    public function testMonthlyEarningOnlyHasKeysForVatRatesThatOccur(): void
    {
        $event = $this->event();
        Factory::eventProduct($event, Factory::product(), 1, ['btw' => 21, 'price_net' => 7.00, 'created_at' => '2011-07-05 10:00:00']);

        $result = $this->lines->get_monthly_earning(new DateTime('2011-07-01'));

        $this->assertArrayNotHasKey(6, $result);
        $this->assertEqualsWithDelta(7.00, (float) $result[21], 0.001);
        $this->assertEqualsWithDelta(7.00, (float) $result['total'], 0.001);
    }

    public function testMonthlyEarningWithoutDataHasTheZeroShape(): void
    {
        // positive control for the shape: february 2011 has no rows, may 2011 (previous tests rolled back) neither
        $this->assertSame([6 => 0, 21 => 0, 'total' => 0], $this->lines->get_monthly_earning(new DateTime('2011-02-01')));
    }

    // ------------------------------------------------------------ get_monthly_usage

    public function testMonthlyUsageSumsVolumePerMonthOfOneProductWithinTheWindow(): void
    {
        $product = Factory::product();
        $other = Factory::product();
        $event = $this->event();
        $lastMonth = date('Y-m', strtotime('first day of last month'));
        $threeAgo = date('Y-m', strtotime('first day of -3 months'));
        // inserted newest first to prove the ordering is by month
        Factory::eventProduct($event, $product, 2, ['created_at' => $lastMonth . '-01 12:00:00']);
        Factory::eventProduct($event, $product, 1.5, ['created_at' => $lastMonth . '-02 12:00:00']);
        Factory::eventProduct($event, $product, 4, ['created_at' => $threeAgo . '-01 12:00:00']);
        Factory::eventProduct($event, $product, 100, ['created_at' => date('Y-m-d H:i:s', strtotime('-8 months'))]);
        Factory::eventProduct($event, $other, 50, ['created_at' => $lastMonth . '-01 12:00:00']);

        $six = $this->lines->get_monthly_usage($product);

        // last month: 2 + 1.5 = 3.5; three months ago: 4; the 8 month old line (100) and the other product are out
        $this->assertSame([$threeAgo, $lastMonth], array_column($six, 'month_year'));
        $this->assertEqualsWithDelta(4.0, (float) $six[0]['total_volume'], 0.001);
        $this->assertEqualsWithDelta(3.5, (float) $six[1]['total_volume'], 0.001);

        // a window of 2 months drops the three-months-ago line
        $two = $this->lines->get_monthly_usage($product, 2);
        $this->assertSame([$lastMonth], array_column($two, 'month_year'));
    }

    // ------------------------------------------------------------ usage_summary

    private function seedUsage(int $product): void
    {
        $praktijk = $this->event(Fixtures::LOCATION_PRACTICE);
        $wagen = $this->event(Fixtures::LOCATION_CAR);
        $at = '2017-09-10 12:00:00';
        Factory::eventProduct($praktijk, $product, 2, ['price_net' => 20.00, 'price_brut' => 21.20, 'reduction_reason' => 'TIER_REDUCTION', 'created_at' => $at]);
        Factory::eventProduct($praktijk, $product, 1, ['price_net' => 10.00, 'price_brut' => 10.60, 'reduction_reason' => 'AUTO_REDUCTION', 'created_at' => $at]);
        Factory::eventProduct($praktijk, $product, 3, ['price_net' => 30.00, 'price_brut' => 31.80, 'reduction_reason' => 'MANUAL', 'created_at' => $at]);
        Factory::eventProduct($wagen, $product, 4, ['price_net' => 40.00, 'price_brut' => 42.40, 'reduction_reason' => null, 'created_at' => $at]);
        Factory::eventProduct($wagen, $product, 5, ['price_net' => 50.00, 'price_brut' => 53.00, 'reduction_reason' => 'TIER_REDUCTION', 'created_at' => $at]);
        // outside the range, and another product: must not count
        Factory::eventProduct($wagen, $product, 100, ['price_net' => 999, 'price_brut' => 999, 'created_at' => '2017-10-01 10:00:00']);
        Factory::eventProduct($wagen, Factory::product(), 100, ['price_net' => 999, 'price_brut' => 999, 'created_at' => $at]);
    }

    /** @return array<string,array<string,mixed>> */
    private function byLocation(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$row['location']] = $row;
        }

        return $out;
    }

    public function testUsageSummaryHasOneRowPerLocationAndARollupTotalRow(): void
    {
        $product = Factory::product();
        $this->seedUsage($product);

        $rows = $this->lines->usage_summary($product, '2017-09-01', '2017-09-30');

        // GROUP BY name WITH ROLLUP: Praktijk, Wagen (alphabetical), then the total row last
        $this->assertSame(['Praktijk', 'Wagen', 'total'], array_column($rows, 'location'));
        $loc = $this->byLocation($rows);

        // Praktijk: volume 2 + 1 + 3 = 6; net 20 + 10 + 30 = 60; brut 21.20 + 10.60 + 31.80 = 63.60; 3 lines
        $this->assertEqualsWithDelta(6.0, (float) $loc['Praktijk']['total_volume'], 0.001);
        $this->assertEqualsWithDelta(60.0, (float) $loc['Praktijk']['total_netto_price'], 0.001);
        $this->assertEqualsWithDelta(63.60, (float) $loc['Praktijk']['total_bruto_price'], 0.001);
        $this->assertSame(3, (int) $loc['Praktijk']['total_transactions']);
        $this->assertSame([1, 1, 1], [
            (int) $loc['Praktijk']['tier_reduction_count'],
            (int) $loc['Praktijk']['auto_reduction_count'],
            (int) $loc['Praktijk']['manual_reduction_count'],
        ]);

        // Wagen: volume 4 + 5 = 9; net 40 + 50 = 90; brut 42.40 + 53 = 95.40; 2 lines; one TIER, NULL reason counts nowhere
        $this->assertEqualsWithDelta(9.0, (float) $loc['Wagen']['total_volume'], 0.001);
        $this->assertEqualsWithDelta(90.0, (float) $loc['Wagen']['total_netto_price'], 0.001);
        $this->assertEqualsWithDelta(95.40, (float) $loc['Wagen']['total_bruto_price'], 0.001);
        $this->assertSame(2, (int) $loc['Wagen']['total_transactions']);
        $this->assertSame([1, 0, 0], [
            (int) $loc['Wagen']['tier_reduction_count'],
            (int) $loc['Wagen']['auto_reduction_count'],
            (int) $loc['Wagen']['manual_reduction_count'],
        ]);

        // total: volume 15; net 150; brut 159.00; 5 lines; tier 2, auto 1, manual 1
        $this->assertEqualsWithDelta(15.0, (float) $loc['total']['total_volume'], 0.001);
        $this->assertEqualsWithDelta(150.0, (float) $loc['total']['total_netto_price'], 0.001);
        $this->assertEqualsWithDelta(159.0, (float) $loc['total']['total_bruto_price'], 0.001);
        $this->assertSame(5, (int) $loc['total']['total_transactions']);
        $this->assertSame([2, 1, 1], [
            (int) $loc['total']['tier_reduction_count'],
            (int) $loc['total']['auto_reduction_count'],
            (int) $loc['total']['manual_reduction_count'],
        ]);
    }

    public function testUsageSummaryRangeIncludesFirstMidnightButStopsAtTwentyThreeFiftyNine(): void
    {
        $product = Factory::product();
        $event = $this->event();
        Factory::eventProduct($event, $product, 1, ['price_net' => 1, 'price_brut' => 1, 'created_at' => '2017-09-01 00:00:00']);
        Factory::eventProduct($event, $product, 2, ['price_net' => 2, 'price_brut' => 2, 'created_at' => '2017-09-30 23:59:00']);
        Factory::eventProduct($event, $product, 4, ['price_net' => 4, 'price_brut' => 4, 'created_at' => '2017-09-30 23:59:30']);
        Factory::eventProduct($event, $product, 8, ['price_net' => 8, 'price_brut' => 8, 'created_at' => '2017-08-31 23:59:59']);

        $rows = $this->byLocation($this->lines->usage_summary($product, '2017-09-01', '2017-09-30'));

        // KNOWN BUG: a line at 23:59:30 on the last day is not counted (<= 23:59:00); volumes 1 + 2 = 3, not 7
        $this->assertEqualsWithDelta(3.0, (float) $rows['total']['total_volume'], 0.001);
        $this->assertSame(2, (int) $rows['total']['total_transactions']);
    }

    public function testUsageSummaryOfAProductWithoutLinesIsEmpty(): void
    {
        // WITH ROLLUP over zero groups with a GROUP BY gives no row at all
        $this->assertSame([], $this->lines->usage_summary(Factory::product(), '2017-09-01', '2017-09-30'));
    }

    public function testUsageSummaryLinesWithoutAnEventShareTheNullLocationGroupNamedTotal(): void
    {
        $product = Factory::product();
        Factory::eventProduct(2147000000, $product, 3, ['price_net' => 3, 'price_brut' => 3, 'created_at' => '2017-09-10 12:00:00']);

        $rows = $this->lines->usage_summary($product, '2017-09-01', '2017-09-30');

        // KNOWN BUG: an orphan line has no location, IFNULL(name,'total') labels its group 'total', and the
        // rollup adds a second row 'total' — the two cannot be told apart
        $this->assertSame(['total', 'total'], array_column($rows, 'location'));
        $this->assertSame(1, (int) $rows[0]['total_transactions']);
        $this->assertSame(1, (int) $rows[1]['total_transactions']);
    }

    // ------------------------------------------------------------ Events_procedures_model

    public function testProcedureMonthlyEarningSumsNetFromTheGivenDayAndRoundsToTwoDecimals(): void
    {
        $proc = Factory::procedure();
        $event = $this->event();
        Factory::eventProcedure($event, $proc, 1, ['price_net' => 10.10, 'created_at' => '2011-09-15 08:00:00']);
        Factory::eventProcedure($event, $proc, 1, ['price_net' => 20.25, 'created_at' => '2011-09-30 23:59:59']);
        Factory::eventProcedure($event, $proc, 1, ['price_net' => 500, 'created_at' => '2011-09-14 23:59:59']);
        Factory::eventProcedure($event, $proc, 1, ['price_net' => 700, 'created_at' => '2011-10-01 00:00:00']);

        // window 2011-09-15 .. 2011-09-30: 10.10 + 20.25 = 30.35
        $this->assertEqualsWithDelta(30.35, $this->procLines->get_monthly_earning(new DateTime('2011-09-15')), 0.0001);
    }

    public function testProcedureMonthlyEarningWithoutRowsIsIntegerZero(): void
    {
        $this->assertSame(0, $this->procLines->get_monthly_earning(new DateTime('2011-03-01')));
    }

    public function testNetIncomeSplitsThreeMonthAndOneYearWindowsForOneProcedure(): void
    {
        $proc = Factory::procedure();
        $other = Factory::procedure();
        $event = $this->event();
        Factory::eventProcedure($event, $proc, 2, ['price_net' => 50.00, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 month'))]);
        Factory::eventProcedure($event, $proc, 1.5, ['price_net' => 30.25, 'created_at' => date('Y-m-d H:i:s', strtotime('-6 months'))]);
        Factory::eventProcedure($event, $proc, 4, ['price_net' => 99.00, 'created_at' => date('Y-m-d H:i:s', strtotime('-2 years'))]);
        Factory::eventProcedure($event, $other, 9, ['price_net' => 9.00, 'created_at' => date('Y-m-d H:i:s')]);

        $r = $this->procLines->get_net_income($proc);

        // 3 months: the 1-month line only: count 1, volume 2, net 50.00
        $this->assertSame(1, (int) $r['total_count_3m']);
        $this->assertEqualsWithDelta(2.0, (float) $r['total_amount_3m'], 0.001);
        $this->assertEqualsWithDelta(50.00, (float) $r['total_net_price_3_months'], 0.001);
        // 1 year: 1-month and 6-month lines: count 2, volume 2 + 1.5 = 3.5, net 50.00 + 30.25 = 80.25
        $this->assertSame(2, (int) $r['total_count_1y']);
        $this->assertEqualsWithDelta(3.5, (float) $r['total_amount_1y'], 0.001);
        $this->assertEqualsWithDelta(80.25, (float) $r['total_net_price_1_year'], 0.001);
    }

    public function testNetIncomeOfAProcedureWithoutLinesIsZero(): void
    {
        $event = $this->event();
        Factory::eventProcedure($event, Factory::procedure(), 1, ['price_net' => 5.00, 'created_at' => date('Y-m-d H:i:s')]);

        $r = $this->procLines->get_net_income(Factory::procedure());

        $this->assertSame(0, (int) $r['total_count_3m']);
        $this->assertSame(0, (int) $r['total_count_1y']);
        $this->assertEqualsWithDelta(0.0, (float) $r['total_net_price_1_year'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $r['total_amount_3m'], 0.001);
    }
}
