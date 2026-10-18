<?php

declare(strict_types=1);

/**
 * T183 - Products::product (edit a product).
 *
 * Only admins are saved: a POST with `submit` by a vet renders the page and changes nothing.
 * The form must be complete (the code reads every field; unticked checkboxes mean 0), `btw_sell` comes
 * from the chosen booking code, `labels[]` replaces all label links, and `limit[<location>][<limitId>]`
 * updates an existing stock_limit row (id -1 inserts a new one, a new one with volume 0 is skipped).
 * KNOWN BUG candidates are tagged in the tests below.
 */
final class EditProductTest extends HttpTestCase
{
    private function form(array $over = []): array
    {
        return $over + [
            'submit' => 1,
            'name' => 'Edited name',
            'input_wh_name' => 'Edited wh name',
            'producer' => 'Edited producer',
            'supplier' => 'Edited supplier',
            'type' => (string) Fixtures::TYPE_FOOD,
            'dead_volume' => '0.25',
            'vaccin_disease' => 'Rabies',
            'buy_volume' => '50',
            'sell_volume' => '2',
            'unit_buy' => 'bx',
            'unit_sell' => 'ml',
            'input_barcode' => '',
            'btw_buy' => '21',
            'vaccin_freq' => '730',
            'default_indication' => 'null',
            'ab_unit' => '',
            'ab_unit_volume' => '',
            'booking_code' => (string) Fixtures::BOOKING_SERVICES,
            'comment_admin' => 'edited admin comment',
            'vhbcode' => 'VHB-E',
            'cnk' => '1112223',
            'cti_e' => 'CTIE',
            'wholesale' => '0',
            'limit_stock' => '9',
            // an empty array is not sent by a browser; the real form always has one field per location
            'limit' => [Fixtures::LOCATION_CAR => [-1 => '0']],
        ];
    }

    public function testAnAdminCanEditAProduct(): void
    {
        $pid = Factory::product(['name' => Factory::unique('EditMe')]);
        $name = Factory::unique('Renamed');
        $this->loginAs('admin');
        $this->db()->exec('DELETE FROM log');

        $page = $this->post('products/product/' . $pid, $this->form([
            'name' => $name,
            'vaccin' => '1',
            'is_antibiotic' => '1',
            'default_indication' => 'RESP',
            'ab_unit' => 'PACKS',
            'ab_unit_volume' => '1.5',
            'sellable' => '1',
            'discontinued' => '1',
        ]));

        $this->assertStatus($page, 200);
        $p = $this->find('products', $pid);
        $this->assertSame($name, $p['name']);
        $this->assertSame('Edited wh name', $p['wholesale_name']);
        $this->assertSame('Edited producer', $p['producer']);
        $this->assertSame('Edited supplier', $p['supplier']);
        $this->assertSame(Fixtures::TYPE_FOOD, (int) $p['type']);
        $this->assertEqualsWithDelta(0.25, (float) $p['dead_volume'], 0.001);
        $this->assertSame('Rabies', $p['vaccin_disease']);
        $this->assertSame(50, (int) $p['buy_volume']);
        $this->assertEqualsWithDelta(2.0, (float) $p['sell_volume'], 0.001);
        $this->assertSame('bx', $p['unit_buy']);
        $this->assertSame('ml', $p['unit_sell']);
        $this->assertNull($p['input_barcode'], 'empty barcode becomes NULL');
        $this->assertSame(21, (int) $p['btw_buy']);
        $this->assertSame(730, (int) $p['vaccin_freq']);
        $this->assertSame(1, (int) $p['vaccin']);
        $this->assertSame(1, (int) $p['is_antibiotic']);
        $this->assertSame('RESP', $p['default_indication']);
        $this->assertSame('PACKS', $p['ab_unit']);
        $this->assertEqualsWithDelta(1.5, (float) $p['ab_unit_volume'], 0.001);
        $this->assertSame('edited admin comment', $p['comment_admin']);
        $this->assertSame('VHB-E', $p['vhbcode']);
        $this->assertSame('1112223', $p['cnk']);
        $this->assertSame('CTIE', $p['cti_e']);
        $this->assertSame(1, (int) $p['sellable']);
        $this->assertSame(1, (int) $p['discontinued']);
        $this->assertSame(9, (int) $p['limit_stock']);

        $this->assertSame(1, $this->logCount('update_product'));
        $this->assertSee($page, $name);
    }

    public function testVatOfTheSellPriceComesFromTheBookingCode(): void
    {
        $pid = Factory::product(['btw_sell' => 6, 'booking_code' => Fixtures::BOOKING_PRODUCTS]);
        $this->loginAs('admin');

        $this->post('products/product/' . $pid, $this->form(['booking_code' => (string) Fixtures::BOOKING_SERVICES, 'btw_buy' => '6']));

        $p = $this->find('products', $pid);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $p['booking_code']);
        $this->assertSame(21, (int) $p['btw_sell']);
        $this->assertSame(6, (int) $p['btw_buy'], 'the buy VAT is a separate field');
    }

    public function testUntickedCheckboxesSwitchTheFlagsOff(): void
    {
        $pid = Factory::product(['vaccin' => 1, 'is_antibiotic' => 1, 'sellable' => 1, 'discontinued' => 1]);
        $this->loginAs('admin');

        $this->post('products/product/' . $pid, $this->form());

        $p = $this->find('products', $pid);
        $this->assertSame(0, (int) $p['vaccin']);
        $this->assertSame(0, (int) $p['is_antibiotic']);
        $this->assertSame(0, (int) $p['sellable']);
        $this->assertSame(0, (int) $p['discontinued']);
        $this->assertNull($p['default_indication'], 'the string "null" is stored as NULL');
    }

    public function testLabelsAreReplaced(): void
    {
        $pid = Factory::product();
        Factory::insert('products_product_label', ['product_id' => $pid, 'label_id' => Fixtures::LABEL_PAIN, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $this->loginAs('admin');

        $this->post('products/product/' . $pid, $this->form(['labels' => [(string) Fixtures::LABEL_SKIN, '9999']]));

        $labels = array_map('intval', array_column($this->rows('SELECT label_id FROM products_product_label WHERE product_id = ?', [$pid]), 'label_id'));
        $this->assertSame([Fixtures::LABEL_SKIN], $labels, 'old link removed, unknown label id 9999 ignored');
    }

    public function testWithoutLabelsAllLinksAreRemoved(): void
    {
        $pid = Factory::product();
        Factory::insert('products_product_label', ['product_id' => $pid, 'label_id' => Fixtures::LABEL_PAIN, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $this->loginAs('admin');

        $this->post('products/product/' . $pid, $this->form());

        $this->assertSame(0, $this->countWhere('products_product_label', ['product_id' => $pid]));
    }

    public function testAnExistingLocalLimitIsUpdatedAndANewOneInserted(): void
    {
        $pid = Factory::product();
        $limitId = Factory::insert('stock_limit', ['stock' => 1, 'product_id' => $pid, 'volume' => 3, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $this->loginAs('admin');

        $this->post('products/product/' . $pid, $this->form(['limit' => [
            1 => [$limitId => '8'],
            2 => [-1 => '4'],
        ]]));

        $this->assertSame(8, (int) $this->find('stock_limit', $limitId)['volume']);
        $new = $this->row('SELECT * FROM stock_limit WHERE product_id = ? AND stock = 2', [$pid]);
        $this->assertNotNull($new);
        $this->assertSame(4, (int) $new['volume']);
        $this->assertSame(2, $this->countWhere('stock_limit', ['product_id' => $pid]));
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testAnEditWithoutTheLimitFieldIsAServerError(): void
    {
        // KNOWN BUG: set_local_limits(array $limits) gets NULL when the form has no `limit` field -> TypeError.
        // The real form always sends it when a location exists. Decide in CI4 whether to keep.
        $pid = Factory::product(['name' => 'before']);
        $this->loginAs('admin');
        $form = $this->form(['name' => 'after']);
        unset($form['limit']);

        $response = $this->post('products/product/' . $pid, $form);

        $this->assertStatus($response, 500);
        $this->assertSame('after', $this->find('products', $pid)['name'], 'the product update itself already happened');
    }

    public function testAVetSendingTheSamePostChangesNothing(): void
    {
        $pid = Factory::product(['name' => Factory::unique('VetEdit'), 'limit_stock' => 2]);
        $limitId = Factory::insert('stock_limit', ['stock' => 1, 'product_id' => $pid, 'volume' => 3, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $before = $this->find('products', $pid);
        $this->loginAs('vet');

        $page = $this->post('products/product/' . $pid, $this->form(['name' => 'Vet was here', 'limit' => [1 => [$limitId => '99']], 'labels' => ['1']]));

        $this->assertStatus($page, 200);
        $after = $this->find('products', $pid);
        $this->assertSame($before['name'], $after['name']);
        $this->assertSame($before['limit_stock'], $after['limit_stock']);
        $this->assertSame($before['unit_sell'], $after['unit_sell']);
        $this->assertSame(3, (int) $this->find('stock_limit', $limitId)['volume']);
        $this->assertSame(0, $this->countWhere('products_product_label', ['product_id' => $pid]));
        $this->assertSee($page, $before['name']);
    }

    public function testTheEditPageShowsTheProduct(): void
    {
        $this->loginAs('admin');

        $page = $this->get('products/product/' . Fixtures::PRODUCT_ANTIBIOTIC);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Clavubactin AB');
        $this->assertSee($page, '1234567');
    }
}
