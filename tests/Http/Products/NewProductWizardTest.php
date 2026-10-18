<?php

declare(strict_types=1);

/**
 * T182 - Products::new, the 4 step wizard (admin only).
 *
 * Step 1 inserts the product with defaults (booking code = the first booking code, sellable 1, ...)
 * and redirects to step 2 of the new id; step 2 sets volumes, units, VAT and booking code (btw_sell is
 * the VAT of the chosen booking code); step 3 sets limit_stock and inserts local limits (a limit with
 * volume 0 is skipped); step 4 sets dead volume, vaccine and antibiotic data and the flags.
 * Every step redirects to the next, step 4 to "5" which is the "done" screen.
 * Each step is a separate POST with only that step's fields; fields of other steps stay as they were.
 */
final class NewProductWizardTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function step1(string $name, array $extra = []): int
    {
        $response = $this->post('products/new', $extra + [
            'submit' => 1,
            'name' => $name,
            'type' => Fixtures::TYPE_FOOD,
            'producer' => 'Producer X',
            'supplier' => 'Supplier X',
            'input_wh_name' => 'Wholesale name X',
            'input_barcode' => '',
            'vhbcode' => 'VHB-X',
            'cnk' => '7654321',
            'cti_e' => 'CTI-X',
            'wholesale' => '0',
        ]);
        $id = (int) $this->scalar('SELECT id FROM products WHERE name = ?', [$name]);
        $this->assertGreaterThan(0, $id, 'the product exists');
        $this->assertRedirectsTo($response, 'products/new/2/' . $id);

        return $id;
    }

    public function testTheWizardStartPageShowsStepOne(): void
    {
        $page = $this->get('products/new');

        $this->assertStatus($page, 200);
    }

    public function testStepOneInsertsTheProductWithDefaults(): void
    {
        $name = Factory::unique('Wiz1');
        $this->db()->exec('DELETE FROM log');

        $id = $this->step1($name, ['input_barcode' => '05414567000099']);

        $p = $this->find('products', $id);
        $this->assertSame($name, $p['name']);
        $this->assertSame(Fixtures::TYPE_FOOD, (int) $p['type']);
        $this->assertSame('Producer X', $p['producer']);
        $this->assertSame('Supplier X', $p['supplier']);
        $this->assertSame('Wholesale name X', $p['wholesale_name']);
        $this->assertSame('05414567000099', $p['input_barcode']);
        $this->assertSame('VHB-X', $p['vhbcode']);
        $this->assertSame('7654321', $p['cnk']);
        $this->assertSame('CTI-X', $p['cti_e']);
        // defaults of step 1
        $this->assertSame(1, (int) $p['sellable']);
        $this->assertSame(0, (int) $p['limit_stock']);
        $this->assertSame(0, (int) $p['vaccin']);
        $this->assertSame(1, (int) $p['buy_volume']);
        $this->assertEqualsWithDelta(1.0, (float) $p['sell_volume'], 0.001);
        $this->assertEqualsWithDelta(1.0, (float) $p['buy_price'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $p['dead_volume'], 0.001);
        $this->assertSame('', $p['unit_sell']);
        // the first booking code (id 1, 6 %)
        $this->assertSame(Fixtures::BOOKING_PRODUCTS, (int) $p['booking_code']);
        $this->assertSame(6, (int) $p['btw_sell']);
        $this->assertNull($p['deleted_at']);

        $log = $this->row("SELECT * FROM log WHERE event = 'new_product'");
        $this->assertNotNull($log, 'new_product is logged');
        $this->assertStringContainsString($name, $log['msg']);
        $this->assertStringContainsString((string) $id, $log['msg']);
    }

    public function testAnEmptyBarcodeIsStoredAsNull(): void
    {
        $id = $this->step1(Factory::unique('WizNullBc'));

        $this->assertNull($this->find('products', $id)['input_barcode']);
    }

    public function testStepTwoSetsVolumesUnitsAndTakesTheVatFromTheBookingCode(): void
    {
        $id = $this->step1(Factory::unique('Wiz2'));

        $response = $this->post('products/new/2/' . $id, [
            'submit' => 1,
            'buy_volume' => '24',
            'unit_buy' => 'box',
            'sell_volume' => '2.5',
            'unit_sell' => 'tab',
            'btw_buy' => '12',
            'booking_code' => (string) Fixtures::BOOKING_SERVICES,
        ]);

        $this->assertRedirectsTo($response, 'products/new/3/' . $id);
        $p = $this->find('products', $id);
        $this->assertSame(24, (int) $p['buy_volume']);
        $this->assertSame('box', $p['unit_buy']);
        $this->assertEqualsWithDelta(2.5, (float) $p['sell_volume'], 0.001);
        $this->assertSame('tab', $p['unit_sell']);
        $this->assertSame(12, (int) $p['btw_buy']);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $p['booking_code']);
        $this->assertSame(21, (int) $p['btw_sell'], 'booking code 2 is 21 %');
        // step 1 data stays
        $this->assertSame('Producer X', $p['producer']);
    }

    public function testStepTwoFallsBackToTwentyOnePercentForAnUnknownBookingCode(): void
    {
        $id = $this->step1(Factory::unique('Wiz2b'));

        $this->post('products/new/2/' . $id, [
            'submit' => 1, 'buy_volume' => '1', 'unit_buy' => 'a', 'sell_volume' => '1', 'unit_sell' => 'b',
            'btw_buy' => '6', 'booking_code' => '99',
        ]);

        $this->assertSame(21, (int) $this->find('products', $id)['btw_sell']);
    }

    public function testStepThreeSetsTheGlobalLimitAndInsertsLocalLimits(): void
    {
        $id = $this->step1(Factory::unique('Wiz3'));

        $response = $this->post('products/new/3/' . $id, [
            'submit' => 1,
            'limit_stock' => '12',
            'limit' => [
                Fixtures::LOCATION_PRACTICE => [-1 => '5'],
                Fixtures::LOCATION_CAR => [-1 => '0'],
            ],
        ]);

        $this->assertRedirectsTo($response, 'products/new/4/' . $id);
        $this->assertSame(12, (int) $this->find('products', $id)['limit_stock']);
        $limits = $this->rows('SELECT stock, volume FROM stock_limit WHERE product_id = ?', [$id]);
        $this->assertCount(1, $limits, 'the limit of 0 is skipped');
        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $limits[0]['stock']);
        $this->assertSame(5, (int) $limits[0]['volume']);
    }

    public function testStepFourSetsDeadVolumeVaccineAndAntibioticFields(): void
    {
        $id = $this->step1(Factory::unique('Wiz4'));

        $response = $this->post('products/new/4/' . $id, [
            'submit' => 1,
            'dead_volume' => '0.5',
            'vaccin' => '1',
            'vaccin_freq' => '365',
            'vaccin_disease' => 'Parvo',
            'is_antibiotic' => '1',
            'default_indication' => 'RESP',
            'ab_unit' => 'TUBE',
            'ab_unit_volume' => '2.5',
            'sellable' => '1',
            'comment_admin' => 'admin note',
        ]);

        $this->assertRedirectsTo($response, 'products/new/5/' . $id);
        $p = $this->find('products', $id);
        $this->assertEqualsWithDelta(0.5, (float) $p['dead_volume'], 0.001);
        $this->assertSame(1, (int) $p['vaccin']);
        $this->assertSame(365, (int) $p['vaccin_freq']);
        $this->assertSame('Parvo', $p['vaccin_disease']);
        $this->assertSame(1, (int) $p['is_antibiotic']);
        $this->assertSame('RESP', $p['default_indication']);
        $this->assertSame('TUBE', $p['ab_unit']);
        $this->assertEqualsWithDelta(2.5, (float) $p['ab_unit_volume'], 0.001);
        $this->assertSame(1, (int) $p['sellable']);
        $this->assertSame(0, (int) $p['discontinued']);
        $this->assertSame('admin note', $p['comment_admin']);
        $this->assertStatus($this->get('products/new/5/' . $id), 200);
    }

    public function testTheStringNullMeansNoDefaultIndication(): void
    {
        $id = $this->step1(Factory::unique('Wiz4n'));

        $this->post('products/new/4/' . $id, [
            'submit' => 1, 'dead_volume' => '0', 'vaccin_freq' => '0', 'vaccin_disease' => '',
            'default_indication' => 'null', 'ab_unit' => '', 'ab_unit_volume' => '', 'comment_admin' => '',
        ]);

        $p = $this->find('products', $id);
        $this->assertNull($p['default_indication']);
        // unticked checkboxes: flags go to 0, "sellable" included
        $this->assertSame(0, (int) $p['sellable']);
        $this->assertSame(0, (int) $p['vaccin']);
        $this->assertSame(0, (int) $p['is_antibiotic']);
    }

    public function testAFullRunLeavesOneProductWithAllTheStepsApplied(): void
    {
        $name = Factory::unique('WizFull');
        $id = $this->step1($name);
        $this->post('products/new/2/' . $id, [
            'submit' => 1, 'buy_volume' => '10', 'unit_buy' => 'box', 'sell_volume' => '1', 'unit_sell' => 'pil',
            'btw_buy' => '6', 'booking_code' => '1',
        ]);
        $this->post('products/new/3/' . $id, ['submit' => 1, 'limit_stock' => '3', 'limit' => [1 => [-1 => '2']]]);
        $this->post('products/new/4/' . $id, [
            'submit' => 1, 'dead_volume' => '0', 'vaccin_freq' => '0', 'default_indication' => 'null',
            'sellable' => '1',
        ]);

        $this->assertSame(1, $this->countWhere('products', ['name' => $name]));
        $p = $this->find('products', $id);
        $this->assertSame('box', $p['unit_buy']);
        $this->assertSame('pil', $p['unit_sell']);
        $this->assertSame(3, (int) $p['limit_stock']);
        $this->assertSame(1, (int) $p['sellable']);
        $this->assertSame(1, $this->countWhere('stock_limit', ['product_id' => $id]));
    }

    public function testASubmitWithoutAProductIdDoesNotRunStepTwo(): void
    {
        $before = $this->countWhere('products');

        $response = $this->post('products/new/2', ['submit' => 1, 'name' => 'x']);

        // step 2 without a pid falls through to the page itself (step 2 view without product)
        $this->assertStatus($response, 200);
        $this->assertNotRedirect($response);
        $this->assertSame($before, $this->countWhere('products'));
    }
}
