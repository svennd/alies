<?php

declare(strict_types=1);

require_once __DIR__ . '/StockModelHelper.php';

/**
 * T207 stock read queries, T208 Stock_value_model::record_value.
 * Source: application/models/Stock_model.php, application/models/Stock_value_model.php
 *
 * The seed has stock rows 5001-5008 (products 4001-4006), so list-style queries are asserted
 * through rows of fresh products only.
 */
final class StockQueriesModelTest extends CodeIgniterDatabaseTestCase
{
    use StockModelHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStock();
    }

    private function mine(array $rows, int ...$productIds): array
    {
        return array_values(array_filter(
            $rows,
            static fn ($r) => in_array((int) ($r['product_id'] ?? 0), $productIds, true)
        ));
    }

    // ---------------------------------------------------------------- get_stock_levels

    public function testGetStockLevelsReturnsLocalAndTotalOfInUseRowsOnly(): void
    {
        $pid = Factory::product();
        Factory::stock($pid, ['volume' => 5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 2.5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 7, 'location' => '2']);
        Factory::stock($pid, ['volume' => 100, 'location' => '1', 'state' => STOCK_HISTORY]);
        Factory::stock($pid, ['volume' => 100, 'location' => '1', 'state' => STOCK_CHECK]);
        Factory::stock($pid, ['volume' => -3, 'location' => '2', 'state' => STOCK_ERROR]);

        // location 1: 5 + 2.5 = 7.5 ; location 2: 7 ; total 14.5
        [$local1, $total] = $this->stock->get_stock_levels($pid, 1);
        $this->assertEqualsWithDelta(7.5, (float) $local1, 0.001);
        $this->assertEqualsWithDelta(14.5, (float) $total, 0.001);

        [$local2, $total2] = $this->stock->get_stock_levels($pid, 2);
        $this->assertEqualsWithDelta(7.0, (float) $local2, 0.001);
        $this->assertEqualsWithDelta(14.5, (float) $total2, 0.001);

        // a location without stock: local 0, total still 14.5
        [$local3, $total3] = $this->stock->get_stock_levels($pid, 3);
        $this->assertEquals(0, $local3);
        $this->assertEqualsWithDelta(14.5, (float) $total3, 0.001);
    }

    public function testGetStockLevelsIsZeroZeroWithoutAnyInUseStock(): void
    {
        $none = Factory::product();
        $this->assertSame([0, 0], $this->stock->get_stock_levels($none, 1));

        $onlyHistory = Factory::product();
        Factory::stock($onlyHistory, ['volume' => 4, 'state' => STOCK_HISTORY]);
        $this->assertSame([0, 0], $this->stock->get_stock_levels($onlyHistory, 1));
    }

    // ---------------------------------------------------------------- get_all_products

    public function testGetAllProductsListsInUseRowsOfOneLocationNewestFirst(): void
    {
        $pid = Factory::product(['unit_sell' => 'ml']);
        $old = Factory::stock($pid, ['volume' => 1, 'location' => '1', 'updated_at' => '2020-01-01 10:00:00']);
        $new = Factory::stock($pid, ['volume' => 2, 'location' => '1', 'updated_at' => '2030-01-01 10:00:00']);
        $mid = Factory::stock($pid, ['volume' => 3, 'location' => '1', 'updated_at' => '2025-01-01 10:00:00']);
        $elsewhere = Factory::stock($pid, ['volume' => 4, 'location' => '2']);
        $history = Factory::stock($pid, ['volume' => 5, 'location' => '1', 'state' => STOCK_HISTORY]);

        $rows = $this->mine($this->stock->get_all_products(1), $pid);

        $this->assertSame([$new, $mid, $old], array_map(static fn ($r) => (int) $r['id'], $rows));
        $this->assertSame('ml', $rows[0]['unit_sell']);
        $this->assertSame('Medicatie', $rows[0]['type']);
        $this->assertNotEmpty($rows[0]['product_name']);

        $at2 = $this->mine($this->stock->get_all_products(2), $pid);
        $this->assertSame([$elsewhere], array_map(static fn ($r) => (int) $r['id'], $at2));
        $this->assertNotContains($history, array_map(static fn ($r) => (int) $r['id'], $rows));
    }

    // ---------------------------------------------------------------- get_all_products_count

    public function testGetAllProductsCountGroupsInUseRowsPerProduct(): void
    {
        $pid = Factory::product(['unit_sell' => 'ml']);
        Factory::stock($pid, ['volume' => 5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 2.5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 7, 'location' => '2']);
        Factory::stock($pid, ['volume' => 100, 'state' => STOCK_HISTORY]);
        $untyped = Factory::product(['type' => 0]);       // no matching type: left join gives NULL
        Factory::stock($untyped, ['volume' => 1]);

        $rows = $this->mine($this->stock->get_all_products_count(), $pid, $untyped);
        $byPid = array_column($rows, null, 'product_id');

        // 5 + 2.5 + 7 = 14.5 in 3 rows (the count is of rows, not of locations)
        $this->assertEqualsWithDelta(14.5, (float) $byPid[$pid]['total_volume'], 0.001);
        $this->assertSame(3, (int) $byPid[$pid]['total_stock_locations']);
        $this->assertSame('ml', $byPid[$pid]['unit_sell']);
        $this->assertSame('Medicatie', $byPid[$pid]['type']);
        $this->assertNull($byPid[$untyped]['type']);
        $this->assertCount(2, $rows);
    }

    // ---------------------------------------------------------------- get_list

    private function listRow(array $rows, string $name, string $lot): array
    {
        $hit = array_values(array_filter($rows, static fn ($r) => $r['name'] === $name && $r['lotnr'] === $lot));
        $this->assertCount(1, $hit, 'one row for ' . $name . ' / ' . $lot);

        return $hit[0];
    }

    public function testGetListComputesPieceValueAndFiltersOnVatAndLocation(): void
    {
        // Products with a purchase VAT of 12, which no seeded product uses, so the filter isolates them.
        // Product A (buy_volume 10):
        //   lot X in_price 5.00: rows 4 + 2 at location 1 and 1 at location 2 (GROUP BY merges them: 7)
        //     per piece ROUND(5/10,2)=0.50 ; value ROUND(0.5*7,2)=3.50 (location 1 only: 6 -> 3.00,
        //     location 2 only: 1 -> 0.50) ; pack price 5.00
        //   lot Y in_price 4.00 volume 3 at location 1: piece 0.40, value 1.20, pack 4.00
        //   lot OLD in_price 5.00 volume 2, expired 30 days ago: still listed (no eol filter), piece 0.50, value 1.00
        // Product B (buy_volume 10): lot Z in_price 10.00 volume 1 at location 2: piece 1.00, value 1.00
        $nameA = 'AAA-' . Factory::unique();
        $nameB = 'BBB-' . Factory::unique();
        $a = Factory::product(['name' => $nameA, 'btw_buy' => 12, 'buy_volume' => 10, 'unit_sell' => 'tab']);
        $x = ['lotnr' => 'X', 'in_price' => 5.00, 'eol' => '2031-01-01'];
        Factory::stock($a, $x + ['volume' => 4, 'location' => '1']);
        Factory::stock($a, $x + ['volume' => 2, 'location' => '1']);
        Factory::stock($a, $x + ['volume' => 1, 'location' => '2']);
        Factory::stock($a, ['lotnr' => 'Y', 'in_price' => 4.00, 'volume' => 3, 'location' => '1', 'eol' => '2031-01-01']);
        Factory::stock($a, ['lotnr' => 'OLD', 'in_price' => 5.00, 'volume' => 2, 'location' => '1', 'eol' => Factory::raw('DATE_SUB(CURDATE(), INTERVAL 30 DAY)')]);
        Factory::stock($a, $x + ['volume' => 50, 'location' => '1', 'state' => STOCK_HISTORY]); // ignored
        $b = Factory::product(['name' => $nameB, 'btw_buy' => 12, 'buy_volume' => 10]);
        Factory::stock($b, ['lotnr' => 'Z', 'in_price' => 10.00, 'volume' => 1, 'location' => '2', 'eol' => '2031-01-01']);

        $all = $this->stock->get_list(0, 12);
        $this->assertCount(4, $all);
        // ordered by product name
        $this->assertSame([$nameA, $nameA, $nameA, $nameB], array_column($all, 'name'));

        $ax = $this->listRow($all, $nameA, 'X');
        $this->assertEqualsWithDelta(7.0, (float) $ax['AANTAL'], 0.001);
        $this->assertEqualsWithDelta(0.50, (float) $ax['INKOOPPRIJS_STUK'], 0.001);
        $this->assertEqualsWithDelta(3.50, (float) $ax['WAARDE'], 0.001);
        $this->assertEqualsWithDelta(5.00, (float) $ax['INKOOPPRIJS_VERPAKKING'], 0.001);
        $this->assertSame('tab', $ax['VERKOOPSEENHEID']);
        $this->assertSame('12', (string) $ax['BTW']);
        $ay = $this->listRow($all, $nameA, 'Y');
        $this->assertEqualsWithDelta(3.0, (float) $ay['AANTAL'], 0.001);
        $this->assertEqualsWithDelta(0.40, (float) $ay['INKOOPPRIJS_STUK'], 0.001);
        $this->assertEqualsWithDelta(1.20, (float) $ay['WAARDE'], 0.001);
        $old = $this->listRow($all, $nameA, 'OLD');
        $this->assertEqualsWithDelta(1.00, (float) $old['WAARDE'], 0.001);
        $bz = $this->listRow($all, $nameB, 'Z');
        $this->assertEqualsWithDelta(1.00, (float) $bz['INKOOPPRIJS_STUK'], 0.001);
        $this->assertEqualsWithDelta(1.00, (float) $bz['WAARDE'], 0.001);

        // location 1: X is 6 (value 3.00), Y, OLD; B is only at location 2
        $loc1 = $this->stock->get_list(1, 12);
        $this->assertCount(3, $loc1);
        $this->assertEqualsWithDelta(6.0, (float) $this->listRow($loc1, $nameA, 'X')['AANTAL'], 0.001);
        $this->assertEqualsWithDelta(3.00, (float) $this->listRow($loc1, $nameA, 'X')['WAARDE'], 0.001);
        // location 2: X is 1 (value 0.50) and B/Z
        $loc2 = $this->stock->get_list(2, 12);
        $this->assertCount(2, $loc2);
        $this->assertEqualsWithDelta(0.50, (float) $this->listRow($loc2, $nameA, 'X')['WAARDE'], 0.001);
        $this->assertSame($nameB, $this->listRow($loc2, $nameB, 'Z')['name']);

        // another VAT rate finds none of them (positive control for the filter)
        $this->assertSame([], $this->mineByName($this->stock->get_list(0, 21), [$nameA, $nameB]));
        // without the VAT filter they are present next to the seeded rows
        $this->assertNotSame([], $this->mineByName($this->stock->get_list(0, 0), [$nameA, $nameB]));
    }

    private function mineByName(array $rows, array $names): array
    {
        return array_values(array_filter($rows, static fn ($r) => in_array($r['name'], $names, true)));
    }

    public function testGetListUsesDifferentFallbackPricesForAZeroInPrice(): void
    {
        // PIN (inconsistent on purpose of the code): an in_price of 0 is replaced by 0.01 in the
        // per-piece and value maths, but by 0.1 in the pack-price column.
        //   C: buy_volume 4, in_price 0, volume 8:  piece ROUND(0.01/4,2)=ROUND(0.0025,2)=0.00 ; value ROUND(0.0025*8,2)=0.02
        //   D: buy_volume 1, in_price 0, volume 3:  piece 0.01 ; value 0.03
        //   both: pack price 0.10
        $nameC = 'CCC-' . Factory::unique();
        $nameD = 'DDD-' . Factory::unique();
        $c = Factory::product(['name' => $nameC, 'btw_buy' => 13, 'buy_volume' => 4]);
        Factory::stock($c, ['lotnr' => 'Q', 'in_price' => 0, 'volume' => 8, 'eol' => '2031-01-01']);
        $d = Factory::product(['name' => $nameD, 'btw_buy' => 13, 'buy_volume' => 1]);
        Factory::stock($d, ['lotnr' => 'Q', 'in_price' => 0, 'volume' => 3, 'eol' => '2031-01-01']);

        $rows = $this->stock->get_list(0, 13);

        $this->assertCount(2, $rows);
        $rc = $this->listRow($rows, $nameC, 'Q');
        $this->assertEqualsWithDelta(0.00, (float) $rc['INKOOPPRIJS_STUK'], 0.0001);
        $this->assertEqualsWithDelta(0.02, (float) $rc['WAARDE'], 0.0001);
        $this->assertEqualsWithDelta(0.10, (float) $rc['INKOOPPRIJS_VERPAKKING'], 0.0001);
        $rd = $this->listRow($rows, $nameD, 'Q');
        $this->assertEqualsWithDelta(0.01, (float) $rd['INKOOPPRIJS_STUK'], 0.0001);
        $this->assertEqualsWithDelta(0.03, (float) $rd['WAARDE'], 0.0001);
        $this->assertEqualsWithDelta(0.10, (float) $rd['INKOOPPRIJS_VERPAKKING'], 0.0001);
    }

    // ---------------------------------------------------------------- gs1_lookup

    public function testGs1LookupFindsTheLotAtTheLocationFirst(): void
    {
        $gtin = 'GT' . substr(Factory::unique(), 1);
        $pid = Factory::product(['input_barcode' => $gtin, 'unit_sell' => 'ml']);
        $eol = '2031-03-03';
        $one = Factory::stock($pid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '1', 'volume' => 5]);
        $two = Factory::stock($pid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '2', 'volume' => 6]);
        $before = count($this->logRows('wrong_gs1_location'));

        $at1 = $this->stock->gs1_lookup($gtin, 'L1', $eol, 1);
        $at2 = $this->stock->gs1_lookup($gtin, 'L1', $eol, 2);

        $this->assertCount(1, $at1);
        $this->assertSame($one, (int) $at1[0]['stock_id']);
        $this->assertSame($pid, (int) $at1[0]['pid']);
        $this->assertSame('ml', $at1[0]['unit_sell']);
        $this->assertEqualsWithDelta(5.0, (float) $at1[0]['volume'], 0.001);
        $this->assertSame($gtin, $at1[0]['input_barcode']);
        $this->assertCount(1, $at2);
        $this->assertSame($two, (int) $at2[0]['stock_id']);
        // no fallback was needed, so nothing logged
        $this->assertCount($before, $this->logRows('wrong_gs1_location'));
    }

    public function testGs1LookupFallsBackToAnyLocationAndLogsWhenSeveralMatch(): void
    {
        $gtin = 'GT' . substr(Factory::unique(), 1);
        $pid = Factory::product(['input_barcode' => $gtin]);
        $eol = '2031-03-03';
        $one = Factory::stock($pid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '1']);
        $two = Factory::stock($pid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '2']);
        $before = count($this->logRows('wrong_gs1_location'));

        // location 3 has no stock: the fallback ignores the location and returns both rows
        $result = $this->stock->gs1_lookup($gtin, 'L1', $eol, 3);
        $ids = array_map(static fn ($r) => (int) $r['stock_id'], $result);
        sort($ids);
        $this->assertSame([$one, $two], $ids);
        $this->assertCount($before + 1, $this->logRows('wrong_gs1_location'));

        // a single match at another location is returned without a log row
        $single = Factory::product(['input_barcode' => $gtin . 'S']);
        $only = Factory::stock($single, ['lotnr' => 'L9', 'eol' => $eol, 'location' => '2']);
        $result = $this->stock->gs1_lookup($gtin . 'S', 'L9', $eol, 1);
        $this->assertCount(1, $result);
        $this->assertSame($only, (int) $result[0]['stock_id']);
        $this->assertCount($before + 1, $this->logRows('wrong_gs1_location'));
    }

    public function testGs1LookupFindsNothingForAWrongLotEolBarcodeOrState(): void
    {
        $gtin = 'GT' . substr(Factory::unique(), 1);
        $pid = Factory::product(['input_barcode' => $gtin]);
        $eol = '2031-03-03';
        Factory::stock($pid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '1']);
        $historyPid = Factory::product(['input_barcode' => $gtin . 'H']);
        Factory::stock($historyPid, ['lotnr' => 'L1', 'eol' => $eol, 'location' => '1', 'state' => STOCK_HISTORY]);

        // positive control
        $this->assertCount(1, $this->stock->gs1_lookup($gtin, 'L1', $eol, 1));
        $this->assertSame([], $this->stock->gs1_lookup($gtin, 'L2', $eol, 1));
        $this->assertSame([], $this->stock->gs1_lookup($gtin, 'L1', '2031-03-04', 1));
        $this->assertSame([], $this->stock->gs1_lookup($gtin . 'X', 'L1', $eol, 1));
        $this->assertSame([], $this->stock->gs1_lookup($gtin . 'H', 'L1', $eol, 1));
    }

    // ---------------------------------------------------------------- get_product_stock

    public function testGetProductStockGroupsTheInUseRowsOfOneLocationPerProductName(): void
    {
        $prefix = 'ZQ' . Factory::unique();
        $p1 = Factory::product(['name' => $prefix . 'one', 'unit_sell' => 'ml']);
        $p2 = Factory::product(['name' => $prefix . 'two']);
        $a = Factory::stock($p1, ['volume' => 5, 'lotnr' => 'L1', 'eol' => '2031-01-01', 'location' => '1']);
        $b = Factory::stock($p1, ['volume' => 2.5, 'lotnr' => 'L2', 'eol' => '2032-02-02', 'location' => '1']);
        Factory::stock($p1, ['volume' => 9, 'location' => '2']);                       // other location
        Factory::stock($p1, ['volume' => 9, 'location' => '1', 'state' => STOCK_HISTORY]); // other state
        $c = Factory::stock($p2, ['volume' => 1, 'lotnr' => 'L3', 'eol' => '2033-03-03', 'location' => '1']);

        $rows = $this->stock->get_product_stock($prefix, 1);

        $this->assertCount(2, $rows);
        $byName = array_column($rows, null, 'name');
        $one = $byName[$prefix . 'one'];
        $this->assertSame('ml', $one['unit_sell']);
        // the four GROUP_CONCAT columns line up row by row
        $ids = explode(',', $one['stock_ids']);
        $volumes = array_combine($ids, explode(',', $one['stock_volumes']));
        $lots = array_combine($ids, explode(',', $one['stock_lotnrs']));
        $eols = array_combine($ids, explode(',', $one['stock_eols']));
        $this->assertEqualsCanonicalizing([(string) $a, (string) $b], $ids);
        $this->assertEqualsWithDelta(5.0, (float) $volumes[(string) $a], 0.001);
        $this->assertEqualsWithDelta(2.5, (float) $volumes[(string) $b], 0.001);
        $this->assertSame('L1', $lots[(string) $a]);
        $this->assertSame('L2', $lots[(string) $b]);
        $this->assertSame('2031-01-01', $eols[(string) $a]);
        $this->assertSame('2032-02-02', $eols[(string) $b]);
        $this->assertSame((string) $c, $byName[$prefix . 'two']['stock_ids']);

        // narrower prefix and other location
        $this->assertCount(1, $this->stock->get_product_stock($prefix . 'tw', 1));
        $this->assertSame([], $this->stock->get_product_stock($prefix . 'two', 2));
        $this->assertSame([], $this->stock->get_product_stock('NOPE' . $prefix, 1));
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testGetProductStockDoesNotEscapeLikeWildcards(): void
    {
        // KNOWN BUG: the query goes into LIKE '<query>%' as is, so "_" and "%" act as wildcards.
        $prefix = 'ZW' . Factory::unique();
        $pid = Factory::product(['name' => $prefix . 'abc']);
        Factory::stock($pid, ['volume' => 1, 'location' => '1']);

        $this->assertCount(1, $this->stock->get_product_stock($prefix . 'abc', 1)); // exact: fine
        $this->assertCount(1, $this->stock->get_product_stock($prefix . 'a_c', 1));  // "_" matches "b"
        $this->assertCount(1, $this->stock->get_product_stock('%' . substr($prefix, 2) . 'abc', 1)); // leading %
    }

    // ---------------------------------------------------------------- T208 record_value

    private function lastStockValue(): array
    {
        return $this->ci->db->order_by('id', 'DESC')->limit(1)->get('stock_value')->row_array();
    }

    public function testRecordValueSumsInUseNonExpiredRowsByHand(): void
    {
        // Park every seeded row so only the rows below count (rolled back with the test).
        $this->ci->db->query('UPDATE stock SET state = ' . STOCK_HISTORY);
        $before = (int) $this->ci->db->count_all('stock_value');

        // value of a row = in_price / (buy_volume / volume)
        $p12 = Factory::product(['buy_volume' => 12]);
        $p3 = Factory::product(['buy_volume' => 3]);
        $p4 = Factory::product(['buy_volume' => 4]);
        $p1 = Factory::product(['buy_volume' => 1]);
        // A: 6 / (12/6) = 3.00   (eol in the future)
        Factory::stock($p12, ['in_price' => 6.00, 'volume' => 6]);
        // B: 1 / (3/2)  = 0.666667 (eol NULL counts as not expired)
        Factory::stock($p3, ['in_price' => 1.00, 'volume' => 2, 'eol' => null]);
        // C: 2 / (4/1)  = 0.50   (eol '0000-00-00' counts as not expired)
        Factory::stock($p4, ['in_price' => 2.00, 'volume' => 1, 'eol' => Factory::raw("'0000-00-00'")]);
        // D: 1 / (1/1)  = 1.00   (eol today is not expired: eol >= CURRENT_DATE)
        Factory::stock($p1, ['in_price' => 1.00, 'volume' => 1, 'eol' => Factory::raw('CURDATE()')]);
        // excluded: expired yesterday, ERROR state, HISTORY state, CHECK state
        Factory::stock($p1, ['in_price' => 99.00, 'volume' => 1, 'eol' => Factory::raw('DATE_SUB(CURDATE(), INTERVAL 1 DAY)')]);
        Factory::stock($p1, ['in_price' => 99.00, 'volume' => 1, 'state' => STOCK_ERROR]);
        Factory::stock($p1, ['in_price' => 99.00, 'volume' => 1, 'state' => STOCK_HISTORY]);
        Factory::stock($p1, ['in_price' => 99.00, 'volume' => 1, 'state' => STOCK_CHECK]);

        $this->assertTrue((bool) (new Stock_value_model())->record_value());

        // 3.00 + 0.666667 + 0.50 + 1.00 = 5.166667 -> 5.17 (rounded once, over the sum)
        $this->assertSame($before + 1, (int) $this->ci->db->count_all('stock_value'));
        $row = $this->lastStockValue();
        $this->assertEqualsWithDelta(5.17, (float) $row['value'], 0.0001);
        $this->assertEqualsWithDelta(time(), strtotime($row['created_at']), 5);
    }

    public function testRecordValueOnTheSeedExcludesTheExpiredErrorRowAndCountsTheDeletedProduct(): void
    {
        // By hand from the seed (in_price / (buy_volume / volume)):
        //   5001 0.28/(100/100) = 0.28     5002 0.28/(100/20) = 0.056   5003 8/(10/10) = 8
        //   5004 2/(20/30) = 3             5005 0.20/(100/50) = 0.10
        //   5006 expired 30 days ago: excluded    5007 ERROR: excluded
        //   5008 product 4006 is soft deleted but still IN_USE: 0.10/(1/7) = 0.70 (counted, no deleted filter)
        // sum 12.136 -> 12.14
        (new Stock_value_model())->record_value();

        $this->assertEqualsWithDelta(12.14, (float) $this->lastStockValue()['value'], 0.0001);
    }
}
