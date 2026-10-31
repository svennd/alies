<?php

declare(strict_types=1);

require_once __DIR__ . '/ProductsHelper.php';

/**
 * T186 - Products::get_product, the autocomplete of "add stock".
 * T187 - Products::get_product_or_procedure, the autocomplete of the event lines.
 * T188 - get_lot_nr, set/unset_backorder, get_monthly_usage, search_by_booking.
 *
 * get_product: query of 1 character or less gives empty suggestions. A string of 26+ characters that
 * parses as a GS1 code is looked up on input_barcode = GTIN; otherwise the query is a LIKE on name or
 * short_name (max 20, deleted ones excluded, sellable or not). Suggestions carry the data the add-stock
 * form needs.
 * get_product_or_procedure: products (sellable, not deleted, max 15 by usage_count desc) with their
 * IN_USE stock rows with volume > 0, then procedures (type 0). A GS1 code that matches an IN_USE stock row
 * (GTIN + lot + expiry + the user's location) comes first with type 2.
 */
final class ProductAutocompleteTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function suggestions(string $path, string $query): array
    {
        $json = $this->json($this->get($path, ['query' => $query]));
        $this->assertSame($query, $json['query']);

        return $json['suggestions'];
    }

    // ---- T186 get_product ------------------------------------------------------------------

    public function testOneCharacterGivesNoSuggestions(): void
    {
        $this->assertSame([], $this->suggestions('products/get_product', 'a'));
        $this->assertSame([], $this->suggestions('products/get_product', ''));
        // positive control: two characters do search
        $this->assertNotSame([], $this->suggestions('products/get_product', 'am'));
    }

    public function testANameQueryReturnsTheFormDataOfTheProduct(): void
    {
        $name = Factory::unique('AcAdd');
        $pid = Factory::product([
            'name' => $name, 'buy_volume' => 12, 'unit_buy' => 'bx', 'sell_volume' => 2.5, 'unit_sell' => 'ml',
            'supplier' => 'Supp Z', 'buy_price' => 4.5,
        ]);

        $s = $this->suggestions('products/get_product', $name);

        $this->assertCount(1, $s);
        $this->assertSame($name, $s[0]['value']);
        $d = $s[0]['data'];
        $this->assertEqualsCanonicalizing(
            ['id', 'buy_volume', 'unit_buy', 'supplier', 'sell_volume', 'unit_sell', 'buy_price', 'last_net', 'last_brut', 'last_date'],
            array_keys($d)
        );
        $this->assertSame($pid, (int) $d['id']);
        $this->assertSame(12, (int) $d['buy_volume']);
        $this->assertSame('bx', $d['unit_buy']);
        $this->assertSame('Supp Z', $d['supplier']);
        $this->assertEqualsWithDelta(2.5, (float) $d['sell_volume'], 0.001);
        $this->assertSame('ml', $d['unit_sell']);
        $this->assertEqualsWithDelta(4.5, (float) $d['buy_price'], 0.001);
        // no delivery yet
        $this->assertEquals(0, $d['last_net']);
        $this->assertEquals(0, $d['last_brut']);
        $this->assertEquals(0, $d['last_date']);
    }

    public function testTheLastDeliveryPricesAndDateAreReturned(): void
    {
        $name = Factory::unique('AcLast');
        $pid = Factory::product(['name' => $name]);
        $now = ['created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')];
        $old = Factory::insert('delivery_slip', ['vet' => 1, 'note' => '', 'regdate' => '2025-01-10', 'location' => 1] + $now);
        $new = Factory::insert('delivery_slip', ['vet' => 1, 'note' => '', 'regdate' => '2025-03-04', 'location' => 1] + $now);
        Factory::insert('register_in', ['product' => $pid, 'volume' => 1, 'eol' => '2030-01-01', 'in_price' => 1.10, 'cat_price' => 1.50, 'lotnr' => 'A', 'delivery_slip' => $old] + $now);
        Factory::insert('register_in', ['product' => $pid, 'volume' => 1, 'eol' => '2030-01-01', 'in_price' => 2.25, 'cat_price' => 3.75, 'lotnr' => 'B', 'delivery_slip' => $new] + $now);

        $d = $this->suggestions('products/get_product', $name)[0]['data'];

        $this->assertEqualsWithDelta(2.25, (float) $d['last_net'], 0.001);
        $this->assertEqualsWithDelta(3.75, (float) $d['last_brut'], 0.001);
        $this->assertSame('04-03-2025', $d['last_date']);
    }

    public function testShortNameMatchesAndDeletedAndNonSellableProducts(): void
    {
        $key = Factory::unique('AcMix');
        Factory::product(['name' => Factory::unique('Nm'), 'short_name' => $key]);
        Factory::product(['name' => $key . ' hidden', 'sellable' => 0]);
        Factory::product(['name' => $key . ' deleted', 'deleted_at' => Factory::raw('NOW()')]);

        $values = array_column($this->suggestions('products/get_product', $key), 'value');

        $this->assertCount(2, $values, 'short name match + the non-sellable product, not the deleted one');
        $this->assertContains($key . ' hidden', $values);
        $this->assertNotContains($key . ' deleted', $values);
    }

    public function testAtMostTwentySuggestions(): void
    {
        $key = Factory::unique('AcMany');
        for ($i = 0; $i < 22; $i++) {
            Factory::product(['name' => $key . $i, 'price' => null]);
        }

        $this->assertCount(20, $this->suggestions('products/get_product', $key));
    }

    public function testAGs1CodeFindsTheProductByItsGtin(): void
    {
        $gtin = ProductsHelper::gtin();
        $pid = Factory::product(['name' => Factory::unique('AcGs1'), 'input_barcode' => $gtin, 'supplier' => 'GsSupp']);
        $query = ProductsHelper::gs1($gtin, '300630', 'LOT-1234');
        $this->assertGreaterThanOrEqual(26, strlen($query));

        $s = $this->suggestions('products/get_product', $query);

        $this->assertCount(1, $s);
        $this->assertSame($this->find('products', $pid)['name'], $s[0]['value']);
        $d = $s[0]['data'];
        $this->assertSame($pid, (int) $d['id']);
        $this->assertSame('GsSupp', $d['supplier']);
        $this->assertSame('Medicatie', $d['type'], 'the type name');
        $this->assertSame(['GTIN' => $gtin, 'EXP_DATE' => '2030-06-30', 'LOTNR' => 'LOT-1234'], $d['gs1']);
    }

    public function testASeededProductIsFoundByItsGs1Code(): void
    {
        $query = ProductsHelper::gs1(Fixtures::GTIN_TIERED, '300630', 'LOT-9');

        $s = $this->suggestions('products/get_product', $query);

        $this->assertCount(1, $s);
        $this->assertSame('Amoxi Tabs', $s[0]['value']);
        $this->assertSame(Fixtures::PRODUCT_TIERED, (int) $s[0]['data']['id']);
    }

    public function testAnUnknownGtinReturnsNoSuggestionsButTheParsedGs1(): void
    {
        $gtin = ProductsHelper::gtin();
        $query = ProductsHelper::gs1($gtin, '300630', 'LOT-UNKNOWN');

        $json = $this->json($this->get('products/get_product', ['query' => $query]));

        $this->assertSame([], $json['suggestions']);
        $this->assertSame(['GTIN' => $gtin, 'EXP_DATE' => '2030-06-30', 'LOTNR' => 'LOT-UNKNOWN'], $json['gs1']);
    }

    public function testALongStringThatIsNotAGs1CodeIsSearchedAsAName(): void
    {
        $name = Factory::unique('AcLongNameThatIsLong') . ' extra words';
        Factory::product(['name' => $name]);
        $this->assertGreaterThanOrEqual(26, strlen($name));

        $s = $this->suggestions('products/get_product', $name);

        $this->assertSame([$name], array_column($s, 'value'));
    }

    // ---- T187 get_product_or_procedure -----------------------------------------------------

    public function testAShortQueryGivesNothing(): void
    {
        $this->assertSame([], $this->suggestions('products/get_product_or_procedure', 'a'));
    }

    public function testAProductComesWithItsInUseStockRowsOnly(): void
    {
        $name = Factory::unique('AcEv');
        $pid = Factory::product([
            'name' => $name, 'unit_sell' => 'tbl', 'btw_sell' => 21, 'booking_code' => Fixtures::BOOKING_SERVICES,
            'vaccin' => 1, 'vaccin_freq' => 365, 'is_antibiotic' => 1,
        ]);
        $inUse = Factory::stock($pid, ['location' => '1', 'volume' => 7, 'lotnr' => 'LA', 'eol' => '2030-01-01']);
        Factory::stock($pid, ['location' => '2', 'volume' => 3, 'lotnr' => 'LB', 'eol' => '2031-01-01']);
        Factory::stock($pid, ['location' => '1', 'volume' => 0, 'lotnr' => 'EMPTY']);
        Factory::stock($pid, ['location' => '1', 'volume' => 9, 'lotnr' => 'HIST', 'state' => Fixtures::STATE_HISTORY]);
        Factory::stock($pid, ['location' => '1', 'volume' => 9, 'lotnr' => 'ERR', 'state' => Fixtures::STATE_ERROR]);

        $s = $this->suggestions('products/get_product_or_procedure', $name);

        $this->assertCount(1, $s);
        $this->assertSame($name, $s[0]['value']);
        $d = $s[0]['data'];
        $this->assertSame($pid, (int) $d['id']);
        $this->assertSame(1, $d['type']);
        $this->assertSame('tbl', $d['unit']);
        $this->assertEquals(21, $d['btw']);
        $this->assertEquals(Fixtures::BOOKING_SERVICES, $d['booking']);
        $this->assertSame(1, $d['vaccin']);
        $this->assertEquals(365, $d['vaccin_freq']);
        $this->assertSame(1, $d['is_antibiotic']);
        // ordered by location then eol
        $this->assertSame(['LA', 'LB'], array_column($d['stock'], 'lotnr'));
        $this->assertSame($inUse, (int) $d['stock'][0]['id']);
        $this->assertSame('1', (string) $d['stock'][0]['location']);
        $this->assertSame('2030-01-01', $d['stock'][0]['eol']);
        $this->assertEqualsWithDelta(7.0, (float) $d['stock'][0]['volume'], 0.001);
    }

    public function testAProductWithoutStockHasAnEmptyStockList(): void
    {
        $name = Factory::unique('AcNoStock');
        Factory::product(['name' => $name]);

        $s = $this->suggestions('products/get_product_or_procedure', $name);

        $this->assertCount(1, $s);
        $this->assertSame([], $s[0]['data']['stock']);
        $this->assertSame(0, $s[0]['data']['vaccin']);
        $this->assertSame(0, $s[0]['data']['is_antibiotic']);
    }

    public function testProceduresComeAfterProductsWithTypeZero(): void
    {
        $key = Factory::unique('AcBoth');
        $procId = Factory::procedure(['name' => $key . ' aaa procedure', 'booking_code' => Fixtures::BOOKING_SERVICES]);
        Factory::product(['name' => $key . ' zzz product']);

        $s = $this->suggestions('products/get_product_or_procedure', $key);

        $this->assertSame([$key . ' zzz product', $key . ' aaa procedure'], array_column($s, 'value'));
        $this->assertSame(1, $s[0]['data']['type']);
        $p = $s[1]['data'];
        $this->assertSame(0, $p['type']);
        $this->assertSame($procId, (int) $p['id']);
        $this->assertEquals(21, $p['btw']);
        $this->assertEquals(Fixtures::BOOKING_SERVICES, $p['booking']);
    }

    public function testNonSellableAndDeletedProductsAndProceduresAreExcluded(): void
    {
        $key = Factory::unique('AcEx');
        Factory::product(['name' => $key . ' ok']);
        Factory::product(['name' => $key . ' hidden', 'sellable' => 0]);
        Factory::product(['name' => $key . ' deleted', 'deleted_at' => Factory::raw('NOW()')]);
        Factory::procedure(['name' => $key . ' proc deleted', 'deleted_at' => Factory::raw('NOW()')]);

        $values = array_column($this->suggestions('products/get_product_or_procedure', $key), 'value');

        $this->assertSame([$key . ' ok'], $values);
    }

    public function testProductsAreOrderedByUsageCountThenName(): void
    {
        $key = Factory::unique('AcOrd');
        Factory::product(['name' => $key . ' a rare', 'usage_count' => 1]);
        Factory::product(['name' => $key . ' c popular', 'usage_count' => 50]);
        Factory::product(['name' => $key . ' b popular', 'usage_count' => 50]);

        $values = array_column($this->suggestions('products/get_product_or_procedure', $key), 'value');

        $this->assertSame([$key . ' b popular', $key . ' c popular', $key . ' a rare'], $values);
    }

    public function testAtMostFifteenProducts(): void
    {
        $key = Factory::unique('AcCap');
        for ($i = 0; $i < 17; $i++) {
            Factory::product(['name' => sprintf('%s %02d', $key, $i), 'price' => null]);
        }

        $this->assertCount(15, $this->suggestions('products/get_product_or_procedure', $key));
    }

    public function testAGs1CodeOfALotAtTheUsersLocationComesFirstWithTypeTwo(): void
    {
        $gtin = ProductsHelper::gtin();
        $lot = 'L' . substr(uniqid(), -8);
        $pid = Factory::product(['name' => Factory::unique('AcGsEv'), 'input_barcode' => $gtin, 'unit_sell' => 'dz']);
        $sid = Factory::stock($pid, ['location' => '1', 'lotnr' => $lot, 'eol' => '2030-06-30', 'volume' => 4]);
        $query = ProductsHelper::gs1($gtin, '300630', $lot);

        $s = $this->suggestions('products/get_product_or_procedure', $query);

        $this->assertCount(1, $s);
        $this->assertSame($this->find('products', $pid)['name'], $s[0]['value']);
        $d = $s[0]['data'];
        $this->assertSame(2, $d['type']);
        $this->assertSame($pid, (int) $d['id']);
        $this->assertSame('dz', $d['unit']);
        $this->assertCount(1, $d['stock']);
        $this->assertSame($sid, (int) $d['stock'][0]['id']);
        $this->assertSame($lot, $d['stock'][0]['lotnr']);
        $this->assertSame('2030-06-30', $d['stock'][0]['eol']);
    }

    public function testAGs1CodeOfALotAtAnotherLocationFallsBackToThatLocation(): void
    {
        // Stock_model::gs1_lookup retries without the location ("in case the location is faulty"), so a lot
        // that only exists at location 2 is still returned for a vet at location 1, with its own location.
        $gtin = ProductsHelper::gtin();
        $lot = 'L' . substr(uniqid(), -8);
        $pid = Factory::product(['name' => Factory::unique('AcGsOther'), 'input_barcode' => $gtin]);
        $sid = Factory::stock($pid, ['location' => '2', 'lotnr' => $lot, 'eol' => '2030-06-30']);

        $s = $this->suggestions('products/get_product_or_procedure', ProductsHelper::gs1($gtin, '300630', $lot));

        $this->assertCount(1, $s);
        $this->assertSame(2, $s[0]['data']['type']);
        $this->assertSame($sid, (int) $s[0]['data']['stock'][0]['id']);
        $this->assertSame('2', (string) $s[0]['data']['stock'][0]['location']);
    }

    public function testAGs1CodeWithAnotherExpiryOrLotOrAnEmptyLotFindsNothing(): void
    {
        $gtin = ProductsHelper::gtin();
        $lot = 'L' . substr(uniqid(), -8);
        $pid = Factory::product(['name' => Factory::unique('AcGsNone'), 'input_barcode' => $gtin]);
        Factory::stock($pid, ['location' => '1', 'lotnr' => $lot, 'eol' => '2030-06-30', 'volume' => 0]);
        Factory::stock($pid, ['location' => '1', 'lotnr' => $lot . 'H', 'eol' => '2030-06-30', 'state' => Fixtures::STATE_HISTORY]);

        $this->assertSame([], $this->suggestions('products/get_product_or_procedure', ProductsHelper::gs1($gtin, '310630', $lot)), 'other expiry');
        $this->assertSame([], $this->suggestions('products/get_product_or_procedure', ProductsHelper::gs1($gtin, '300630', 'NOPE')), 'other lot');
        $this->assertSame([], $this->suggestions('products/get_product_or_procedure', ProductsHelper::gs1($gtin, '300630', $lot . 'H')), 'history rows do not count');
        // positive control: the in-use row with volume 0 is still matched (no volume filter on a scan)
        $this->assertCount(1, $this->suggestions('products/get_product_or_procedure', ProductsHelper::gs1($gtin, '300630', $lot)));
    }

    // ---- T188 ------------------------------------------------------------------------------

    public function testLotNumbersListEveryStockRowOfTheProduct(): void
    {
        $pid = Factory::product();
        Factory::stock($pid, ['lotnr' => 'LOT-X', 'eol' => '2030-02-03', 'volume' => 5, 'barcode' => 'BC-X', 'location' => '2']);
        Factory::stock($pid, ['lotnr' => 'LOT-OLD', 'state' => Fixtures::STATE_HISTORY, 'volume' => 0]);

        $response = $this->post('products/get_lot_nr', ['pid' => $pid]);

        $rows = $this->json($response);
        $this->assertCount(2, $rows, 'no filter on state');
        $x = array_values(array_filter($rows, static fn (array $r): bool => $r['lotnr'] === 'LOT-X'))[0];
        $this->assertEqualsCanonicalizing(['lotnr', 'eol', 'volume', 'barcode', 'location'], array_keys($x));
        $this->assertSame('2030-02-03', $x['eol']);
        $this->assertEqualsWithDelta(5.0, (float) $x['volume'], 0.001);
        $this->assertSame('BC-X', $x['barcode']);
        $this->assertSame('2', (string) $x['location']);
    }

    public function testLotNumbersOfAProductWithoutStockIsAnEmptyList(): void
    {
        $response = $this->post('products/get_lot_nr', ['pid' => Factory::product()]);

        $this->assertSame('[]', trim((string) $response->getBody()));
    }

    public function testBackorderCanBeSetAndUnsetByAnAdminAndNotByAVet(): void
    {
        $pid = Factory::product(['backorder' => 0]);

        $this->assertRedirectsTo($this->get('products/unset_backorder/' . $pid), '');
        $this->assertRedirectsTo($this->get('products/set_backorder/' . $pid), '');
        $this->assertSame(0, (int) $this->find('products', $pid)['backorder']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertRedirectsTo($this->get('products/set_backorder/' . $pid), 'limits/global');
        $this->assertSame(1, (int) $this->find('products', $pid)['backorder']);
        $this->assertRedirectsTo($this->get('products/unset_backorder/' . $pid), 'limits/order');
        $this->assertSame(0, (int) $this->find('products', $pid)['backorder']);
    }

    public function testMonthlyUsageIsSummedPerMonthInOrder(): void
    {
        $pid = Factory::product();
        $event = Factory::event(Fixtures::PET_REX, 1, 1);
        $month = static fn (int $back): string => date('Y-m', strtotime('first day of -' . $back . ' months'));
        $at = static fn (int $back, int $day): string => date('Y-m-', strtotime('first day of -' . $back . ' months')) . sprintf('%02d 12:00:00', $day);
        Factory::eventProduct($event, $pid, 2, ['created_at' => $at(2, 10)]);
        Factory::eventProduct($event, $pid, 3, ['created_at' => $at(2, 20)]);
        Factory::eventProduct($event, $pid, 4, ['created_at' => $at(4, 5)]);
        Factory::eventProduct($event, $pid, 100, ['created_at' => $at(14, 5)]);

        $twelve = $this->json($this->get('products/get_monthly_usage/' . $pid . '/12'));
        $default = $this->json($this->get('products/get_monthly_usage/' . $pid));
        $three = $this->json($this->get('products/get_monthly_usage/' . $pid . '/3'));

        $this->assertCount(2, $twelve);
        $this->assertSame($month(4), $twelve[0]['month_year']);
        $this->assertEqualsWithDelta(4.0, (float) $twelve[0]['total_volume'], 0.001);
        $this->assertSame($month(2), $twelve[1]['month_year']);
        $this->assertEqualsWithDelta(5.0, (float) $twelve[1]['total_volume'], 0.001);
        $this->assertSame($twelve, $default, 'default is 12 months');
        $this->assertCount(1, $three);
        $this->assertSame($month(2), $three[0]['month_year']);
    }

    public function testMonthlyUsageWithoutUsageIsAnEmptyList(): void
    {
        $response = $this->get('products/get_monthly_usage/' . Factory::product());

        $this->assertSame('[]', trim((string) $response->getBody()));
    }

    public function testSearchByBookingListsTheProductsAndProceduresOfThatCode(): void
    {
        $now = ['created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')];
        $cat = Factory::unique('Cat');
        $booking = Factory::insert('booking_codes', ['category' => $cat, 'code' => '799991', 'btw' => 9] + $now);
        $in = Factory::product(['name' => Factory::unique('BkIn'), 'booking_code' => $booking]);
        $out = Factory::product(['name' => Factory::unique('BkOut')]);
        $proc = Factory::procedure(['name' => Factory::unique('BkProc'), 'booking_code' => $booking]);
        $name = fn (int $id): string => $this->find('products', $id)['name'];

        $page = $this->get('products/search_by_booking/' . $booking);

        $this->assertStatus($page, 200);
        $this->assertSee($page, $cat);
        $this->assertSee($page, $name($in));
        $this->assertSee($page, $this->find('procedures', $proc)['name']);
        $this->assertDontSee($page, $name($out));
    }
}
