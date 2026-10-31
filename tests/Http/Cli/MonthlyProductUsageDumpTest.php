<?php

declare(strict_types=1);

/**
 * T328 - Cli::monthly_product_usage_dump [YYYY-MM] [force].
 *
 * Writes data/stored/reports/monthly_product_usage/monthly_product_usage_<YYYY-MM>.csv: a header row (the column
 * list of Events_model::register_out_snapshot) and one row per events_products line of events created in that
 * month (first second to last second). An existing file is not overwritten unless force=1. An invalid month prints
 * "Invalid month, expected YYYY-MM". Without a month the dump only runs on the 1st (for the previous month).
 * Uses 2016-03 and 2016-05, months nobody else uses.
 */
final class MonthlyProductUsageDumpTest extends HttpTestCase
{
    private const HEADER = [
        'snapshot_month', 'snapshot_period_start', 'snapshot_period_end', 'snapshot_generated_at',
        'product_id', 'product_name', 'product_vhbcode', 'product_cnk', 'product_supplier', 'used_volume',
        'product_unit_sell', 'product_buy_price', 'product_btw_sell',
        'event_id', 'event_created_at',
        'total_sell_price', 'total_sell_price_brut', 'unit_sell_price', 'event_product_btw',
        'stock_id', 'stock_lotnr', 'stock_eol', 'stock_in_price',
        'booking_code', 'booking_category', 'booking_btw',
        'vet_id', 'vet_ordernr', 'event_location_name',
        'pet_id', 'pet_name', 'pet_chip',
        'owner_id', 'owner_last_name', 'owner_btw_nr',
    ];

    private function file(string $month): string
    {
        return dirname(__DIR__, 3) . '/data/stored/reports/monthly_product_usage/monthly_product_usage_' . $month . '.csv';
    }

    protected function tearDown(): void
    {
        foreach (['2016-03', '2016-05'] as $month) {
            if (is_file($this->file($month))) {
                unlink($this->file($month));
            }
        }
        parent::tearDown();
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ',', '"', '');
        $this->assertSame(self::HEADER, $header, 'the header row is the documented column list');
        $rows = [];
        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rows[] = array_combine($header, $line);
        }
        fclose($handle);

        return $rows;
    }

    public function testTheFileHasOneRowPerLineInThatMonthOnly(): void
    {
        $owner = Factory::owner(['last_name' => 'Dumpowner']);
        $pet = Factory::pet($owner, ['name' => 'Dumppet']);
        $product = Factory::product(['name' => 'Dump product', 'supplier' => 'Acme']);
        $at = fn (string $ts) => Factory::event($pet, 0, 0, ['created_at' => $ts, 'updated_at' => $ts]);
        $mid = $at('2016-03-15 10:00:00');
        $first = $at('2016-03-01 00:00:00');
        $last = $at('2016-03-31 23:59:59');
        $feb = $at('2016-02-29 23:59:59');
        $apr = $at('2016-04-01 00:00:00');
        $l1 = Factory::eventProduct($mid, $product, 2.0, ['unit_price' => 5.00, 'btw' => 6]);
        $l2 = Factory::eventProduct($mid, $product, 1.0, ['unit_price' => 10.00, 'btw' => 6]);
        $l3 = Factory::eventProduct($first, $product, 1.0);
        $l4 = Factory::eventProduct($last, $product, 1.0);
        Factory::eventProduct($feb, $product, 1.0);
        Factory::eventProduct($apr, $product, 1.0);
        $this->assertFileDoesNotExist($this->file('2016-03'));

        [$exit, $out] = $this->cli(['cli', 'monthly_product_usage_dump', '2016-03', '1']);

        $this->assertSame(0, $exit);
        $this->assertFileExists($this->file('2016-03'));
        $this->assertStringContainsString('created monthly_product_usage_2016-03.csv with 4 rows for 2016-03', $out);
        $rows = $this->readCsv($this->file('2016-03'));
        $this->assertCount(4, $rows);
        // ordered by event date, then line id
        $this->assertSame([(string) $first, (string) $mid, (string) $mid, (string) $last], array_column($rows, 'event_id'));
        $row = $rows[1];
        $this->assertSame('2016-03', $row['snapshot_month']);
        $this->assertSame('2016-03-01', $row['snapshot_period_start']);
        $this->assertSame('2016-03-31', $row['snapshot_period_end']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $row['snapshot_generated_at']);
        $this->assertSame((string) $product, $row['product_id']);
        $this->assertSame('Dump product', $row['product_name']);
        $this->assertSame('Acme', $row['product_supplier']);
        $this->assertEqualsWithDelta(2.0, (float) $row['used_volume'], 0.001);
        $this->assertSame('2016-03-15 10:00:00', $row['event_created_at']);
        // 2 x 5.00 net = 10.00; gross at 6% = 10.60
        $this->assertEqualsWithDelta(10.00, (float) $row['total_sell_price'], 0.001);
        $this->assertEqualsWithDelta(10.60, (float) $row['total_sell_price_brut'], 0.001);
        $this->assertEqualsWithDelta(5.00, (float) $row['unit_sell_price'], 0.001);
        $this->assertSame('6', $row['event_product_btw']);
        $this->assertSame((string) $pet, $row['pet_id']);
        $this->assertSame('Dumppet', $row['pet_name']);
        $this->assertSame((string) $owner, $row['owner_id']);
        $this->assertSame('Dumpowner', $row['owner_last_name']);
        $this->assertSame((string) Fixtures::USER_VET, $row['vet_id']);
        $this->assertEqualsWithDelta(10.00, (float) $rows[2]['total_sell_price'], 0.001);

        $log = $this->row("SELECT * FROM log WHERE event = 'monthly_product_usage_dump' AND msg LIKE '%2016-03'");
        $this->assertNotNull($log);
        $this->assertSame('created monthly_product_usage_2016-03.csv with 4 rows for 2016-03', $log['msg']);
        $this->assertNotEmpty([$l1, $l2, $l3, $l4]);
    }

    public function testAnExistingFileIsKeptUnlessForced(): void
    {
        $dir = dirname($this->file('2016-05'));
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        file_put_contents($this->file('2016-05'), "sentinel\n");

        [$exit, $out] = $this->cli(['cli', 'monthly_product_usage_dump', '2016-05']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Monthly product usage dump already exists: data/stored/reports/monthly_product_usage/monthly_product_usage_2016-05.csv', $out);
        $this->assertSame("sentinel\n", file_get_contents($this->file('2016-05')));
        $this->assertSame(0, $this->countWhere('log', ['event' => 'monthly_product_usage_dump', 'msg' => 'created monthly_product_usage_2016-05.csv with 0 rows for 2016-05']));

        // positive control: force rewrites it
        [, $out] = $this->cli(['cli', 'monthly_product_usage_dump', '2016-05', '1']);
        $this->assertStringContainsString('created monthly_product_usage_2016-05.csv with 0 rows for 2016-05', $out);
        $this->assertStringNotContainsString('sentinel', file_get_contents($this->file('2016-05')));
    }

    public function testAMonthWithoutLinesGivesAFileWithoutHeaderColumns(): void
    {
        // the header comes from the first data row, so an empty month has no columns
        [, $out] = $this->cli(['cli', 'monthly_product_usage_dump', '2016-05', '1']);

        $this->assertStringContainsString('with 0 rows for 2016-05', $out);
        $this->assertFileExists($this->file('2016-05'));
        $this->assertSame('', trim((string) file_get_contents($this->file('2016-05'))));
    }

    public function testAnInvalidMonthIsRefused(): void
    {
        foreach (['2026-13', 'abc', '2026-1x'] as $month) {
            [$exit, $out] = $this->cli(['cli', 'monthly_product_usage_dump', $month, '1']);

            $this->assertSame(0, $exit);
            $this->assertStringContainsString("Invalid month, expected YYYY-MM\n", $out, $month);
        }
        $this->assertFileDoesNotExist($this->file('2026-13'));
    }

    public function testWithoutArgumentsItSkipsOnAnyDayButTheFirst(): void
    {
        if (date('d') === '01') {
            $this->markTestSkipped('today is the 1st: the no-argument dump would really run');
        }

        [$exit, $out] = $this->cli(['cli', 'monthly_product_usage_dump']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Skip monthly product usage dump: today is not the first of the month.', $out);
    }
}
