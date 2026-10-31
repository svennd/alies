<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T216 - Logs::product, nlog, delivery (list and detail), software_version. Admin only.
 *
 * Rules: product shows the log_stock rows of one product (event, signed volume with the sell unit,
 * vet, location or "invalid", date). nlog shows rows of table `log` from the last 3 days, newest
 * id first; a POSTed search_from / search_to replaces the range. delivery lists every delivery
 * slip with its products; delivery/{id} shows one slip with its register_in rows.
 * software_version prints the migration version.
 */
final class LogPagesTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function day(int $offset, string $time = '12:00:00'): string
    {
        return date('Y-m-d', strtotime(($offset >= 0 ? '+' : '') . $offset . ' days')) . ' ' . $time;
    }

    private function stockLog(int $product, string $event, float $volume, array $overrides = []): int
    {
        return Factory::insert('log_stock', array_merge([
            'user_id' => Fixtures::USER_VET,
            'product' => $product,
            'event' => $event,
            'volume' => $volume,
            'location' => Fixtures::LOCATION_PRACTICE,
            'level' => 5,
        ], $overrides));
    }

    private function log(string $event, string $msg, array $overrides = []): int
    {
        return Factory::insert('log', array_merge([
            'user_id' => Fixtures::USER_VET,
            'event' => $event,
            'level' => 4,
            'msg' => $msg,
            'location' => Fixtures::LOCATION_PRACTICE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));
    }

    public function testNonAdminsAreSentHome(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');
        foreach (['logs/product/4001', 'logs/nlog', 'logs/delivery', 'logs/delivery/1', 'logs/software_version'] as $path) {
            $this->assertRedirectsTo($this->get($path), '', $path);
        }

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('logs/nlog'), 200);
    }

    // ---- product ----------------------------------------------------------------------------

    public function testTheProductLogListsOnlyThatProductsRows(): void
    {
        $this->stockLog(Fixtures::PRODUCT_VACCINE, 'sold_event_ZZ1', -1.5);
        $this->stockLog(Fixtures::PRODUCT_VACCINE, 'delivery_in_ZZ2', 4.0, ['location' => 99]);
        $this->stockLog(Fixtures::PRODUCT_DEAD_VOLUME, 'other_product_ZZ3', -1.0);

        $response = $this->get('logs/product/' . Fixtures::PRODUCT_VACCINE);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Nobivac Vaccin');
        $this->assertSee($response, 'detail transaction log');
        $this->assertSee($response, 'sold_event_ZZ1');
        $this->assertSee($response, 'delivery_in_ZZ2');
        $this->assertDontSee($response, 'other_product_ZZ3');
        $this->assertSee($response, 'Victor');
    }

    public function testVolumesAreSignedColouredAndCarryTheSellUnit(): void
    {
        $this->stockLog(Fixtures::PRODUCT_VACCINE, 'signs_ZZ', -1.5);
        $this->stockLog(Fixtures::PRODUCT_VACCINE, 'signs_ZZ', 4.0);

        $body = (string) $this->get('logs/product/' . Fixtures::PRODUCT_VACCINE)->getBody();

        // seeded unit_sell of 4002 is "dosis"
        $this->assertStringContainsString('<span style="color:red">-1.50 dosis</span>', $body);
        $this->assertStringContainsString('<span style="color:green">4.00 dosis</span>', $body);
    }

    public function testLocationNamesAndTheInvalidFallback(): void
    {
        $this->stockLog(Fixtures::PRODUCT_NOT_SELLABLE, 'loc_known_ZZ', 1, ['location' => Fixtures::LOCATION_CAR]);
        $this->stockLog(Fixtures::PRODUCT_NOT_SELLABLE, 'loc_unknown_ZZ', 1, ['location' => 99]);

        $text = preg_replace('/\s+/', ' ', strip_tags((string) $this->get('logs/product/' . Fixtures::PRODUCT_NOT_SELLABLE)->getBody()));

        $this->assertMatchesRegularExpression('/loc_known_ZZ \S+ \S+ Victor Wagen /', $text);
        $this->assertMatchesRegularExpression('/loc_unknown_ZZ \S+ \S+ Victor invalid /', $text);
    }

    public function testAProductWithoutLogRowsStillRenders(): void
    {
        $response = $this->get('logs/product/' . Fixtures::PRODUCT_NO_STOCK);

        $this->assertStatus($response, 200);
        $this->assertDontSee($response, 'Event');
    }

    // ---- nlog -------------------------------------------------------------------------------

    public function testTheLogbookShowsTheLastThreeDaysOnly(): void
    {
        $this->log('evt_today_ZZ', 'message today');
        $this->log('evt_two_days_ZZ', 'message two days', ['created_at' => $this->day(-2)]);
        $this->log('evt_five_days_ZZ', 'message five days', ['created_at' => $this->day(-5)]);

        $response = $this->get('logs/nlog');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'evt_today_ZZ');
        $this->assertSee($response, 'message today');
        $this->assertSee($response, 'evt_two_days_ZZ');
        $this->assertDontSee($response, 'evt_five_days_ZZ');
        $body = (string) $response->getBody();
        $this->assertStringContainsString('value="' . date('Y-m-d') . '" max=', $body);
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-3 days')) . '" id="search_from"', $body);
    }

    public function testAPostedRangeFiltersTheLogbook(): void
    {
        $this->log('evt_range_in_ZZ', 'in range', ['created_at' => $this->day(-10)]);
        $this->log('evt_range_late_ZZ', 'too late', ['created_at' => $this->day(-8)]);
        $this->log('evt_range_early_ZZ', 'too early', ['created_at' => $this->day(-14)]);
        $this->log('evt_range_today_ZZ', 'today');

        $response = $this->post('logs/nlog', ['search_from' => $this->day(-12, '00:00:00'), 'search_to' => $this->day(-9, '00:00:00')]);

        $this->assertSee($response, 'evt_range_in_ZZ');
        $this->assertDontSee($response, 'evt_range_late_ZZ');
        $this->assertDontSee($response, 'evt_range_early_ZZ');
        $this->assertDontSee($response, 'evt_range_today_ZZ');
    }

    public function testTheToDateIsInclusive(): void
    {
        $this->log('evt_edge_ZZ', 'edge', ['created_at' => $this->day(-20, '23:30:00')]);

        $in = $this->post('logs/nlog', ['search_from' => date('Y-m-d', strtotime('-21 days')), 'search_to' => date('Y-m-d', strtotime('-20 days'))]);
        $out = $this->post('logs/nlog', ['search_from' => date('Y-m-d', strtotime('-21 days')), 'search_to' => date('Y-m-d', strtotime('-21 days'))]);

        $this->assertSee($in, 'evt_edge_ZZ');
        $this->assertDontSee($out, 'evt_edge_ZZ');
    }

    public function testRowsShowLevelVetAndLocationNewestFirst(): void
    {
        $this->log('evt_order_ZZ', 'first <b>bold</b>', ['level' => 2, 'user_id' => Fixtures::USER_VET2, 'location' => Fixtures::LOCATION_CAR]);
        $this->log('evt_order_ZZ', 'second', ['level' => 4]);

        $response = $this->get('logs/nlog');

        $body = (string) $response->getBody();
        $rows = array_values(array_filter(
            preg_split('#<tr>#', $body) ?: [],
            static fn (string $chunk): bool => str_contains($chunk, 'evt_order_ZZ')
        ));
        $this->assertCount(2, $rows);
        // highest id first
        $this->assertStringContainsString('second', $rows[0]);
        $this->assertStringContainsString('first', $rows[1]);
        // level is shown as an icon: INFO = info, ERROR = exclamation
        $this->assertStringContainsString('fa-info', $rows[0]);
        $this->assertStringContainsString('fa-circle-exclamation', $rows[1]);
        $text0 = preg_replace('/\s+/', ' ', strip_tags($rows[0]));
        $text1 = preg_replace('/\s+/', ' ', strip_tags($rows[1]));
        $this->assertStringContainsString('Victor evt_order_ZZ second Praktijk', $text0);
        $this->assertStringContainsString('Veerle evt_order_ZZ first &lt;b&gt;bold&lt;/b&gt; Wagen', $text1, 'vet, event, escaped message, location');
        $this->assertStringNotContainsString('<b>bold</b>', $body);
    }

    // ---- delivery ---------------------------------------------------------------------------

    public function testTheDeliveryLogListsTheSlipsWithTheirProducts(): void
    {
        $slip = Factory::insert('delivery_slip', [
            'vet' => Fixtures::USER_VET2, 'note' => 'Slip note ZZ', 'regdate' => date('Y-m-d', strtotime('-30 days')),
            'location' => Fixtures::LOCATION_CAR, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ([Fixtures::PRODUCT_DEAD_VOLUME, Fixtures::PRODUCT_NO_STOCK] as $pid) {
            $this->registerIn($pid, $slip, 'LOT-' . $pid);
        }

        $response = $this->get('logs/delivery');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('logs/delivery/' . $slip . '"', $body);
        $this->assertSee($response, 'Slip note ZZ');
        $this->assertSee($response, 'Dead Volume Drops');
        $this->assertSee($response, date('d-m-Y', strtotime('-30 days')));
        // seeded slip 1 (note "Test levering", products 4001 and 4002)
        $this->assertSee($response, 'Test levering');
        $this->assertSee($response, 'Amoxi Tabs');
        $this->assertSee($response, 'Nobivac Vaccin');
    }

    public function testTheDeliveryDetailShowsTheSlipAndItsRegisterInRows(): void
    {
        $slip = Factory::insert('delivery_slip', [
            'vet' => Fixtures::USER_VET2, 'note' => 'Detail note ZZ', 'regdate' => date('Y-m-d', strtotime('-31 days')),
            'location' => Fixtures::LOCATION_CAR, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->registerIn(Fixtures::PRODUCT_DEAD_VOLUME, $slip, 'DET-LOT-A', 6.5, 1.25);
        $this->registerIn(Fixtures::PRODUCT_NO_STOCK, $slip, 'DET-LOT-B', 2.0, 3.75);
        // a row of another slip must not show up
        $this->registerIn(Fixtures::PRODUCT_NOT_SELLABLE, 1, 'DET-FOREIGN');

        $response = $this->get('logs/delivery/' . $slip);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Detail note ZZ');
        $this->assertSee($response, 'Wagen');
        $this->assertSee($response, date('d-m-Y', strtotime('-31 days')));
        $this->assertSee($response, 'DET-LOT-A');
        $this->assertSee($response, 'DET-LOT-B');
        $this->assertSee($response, '6.50');
        $this->assertSee($response, '1.25');
        $this->assertSee($response, 'Dead Volume Drops');
        $this->assertDontSee($response, 'DET-FOREIGN');
        $this->assertStringContainsString('products/profile/' . Fixtures::PRODUCT_DEAD_VOLUME, (string) $response->getBody());
    }

    public function testTheSeededSlipDetailListsItsTwoRows(): void
    {
        $response = $this->get('logs/delivery/1');

        $this->assertSee($response, 'Test levering');
        $this->assertSee($response, 'Victor');
        $this->assertSee($response, 'AMX-001');
        $this->assertSee($response, 'VAC-001');
    }

    private function registerIn(int $product, int $slip, string $lot, float $volume = 1.0, float $price = 1.0): int
    {
        return Factory::insert('register_in', [
            'product' => $product, 'volume' => $volume, 'eol' => date('Y-m-d', strtotime('+1 year')),
            'in_price' => $price, 'cat_price' => $price, 'lotnr' => $lot, 'supplier' => 'S', 'delivery_slip' => $slip,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ---- software version -------------------------------------------------------------------

    public function testTheVersionPageShowsTheMigrationVersion(): void
    {
        $expected = (string) $this->scalar('SELECT version FROM migrations LIMIT 1');

        $response = $this->get('logs/software_version');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Database Version');
        $this->assertSee($response, 'Changelog');
        $this->assertMatchesRegularExpression('#Database Version</h4>\s*' . preg_quote($expected, '#') . '\s*<br#', (string) $response->getBody());
    }
}
