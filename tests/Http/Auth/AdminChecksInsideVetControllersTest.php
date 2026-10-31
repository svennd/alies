<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T039 - some controllers are open to every vet but check `in_group('admin')` inside single methods
 * (and redirect to the home page). This class checks that the admin-only ones stay closed to a vet,
 * and pins the ones that look like admin actions but have NO check at all (known bugs).
 *
 * Every test creates its own rows with Factory: the admin actions here delete, merge and rewrite data.
 */
final class AdminChecksInsideVetControllersTest extends HttpTestCase
{
    // ---- admin only: a vet is sent home, an admin gets through -------------------------------

    public function testDeletingAPetIsForAdminsOnly(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('pets/delete/' . $pet), '');
        $this->assertNull($this->find('pets', $pet)['deleted_at']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertRedirectsTo($this->get('pets/delete/' . $pet), 'owners/detail/' . $owner);
        $this->assertNotNull($this->find('pets', $pet)['deleted_at'], 'soft deleted');
    }

    public function testEditingABreedIsForAdminsOnly(): void
    {
        $breed = Factory::breed(['name' => 'Editable breed']);

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('breeds/edit/' . $breed), '');
        $this->assertRedirectsTo($this->post('breeds/edit/' . $breed, ['submit' => 1, 'name' => 'Hacked', 'type' => 0]), '');
        $this->assertSame('Editable breed', $this->find('breeds', $breed)['name']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $page = $this->get('breeds/edit/' . $breed);
        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Editable breed');
    }

    public function testMergingBreedsIsForAdminsOnly(): void
    {
        $old = Factory::breed(['name' => 'Old breed']);
        $new = Factory::breed(['name' => 'New breed']);
        $pet = Factory::pet(Factory::owner(), ['breed' => $old]);

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->post('breeds/merge/' . $old, ['new_breed' => $new]), '');
        $this->assertSame($old, (int) $this->find('pets', $pet)['breed']);
        $this->assertNotNull($this->find('breeds', $old));

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertRedirectsTo($this->post('breeds/merge/' . $old, ['new_breed' => $new]), 'breeds');
        $this->assertSame($new, (int) $this->find('pets', $pet)['breed']);
        $this->assertNull($this->find('breeds', $old), 'the old breed is deleted');
    }

    public function testEditingStockIsForAdminsOnly(): void
    {
        $stock = Factory::stock(Fixtures::PRODUCT_TIERED, ['volume' => 7, 'lotnr' => 'EDIT-ME']);

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('stock/edit/' . $stock), '');
        $this->assertRedirectsTo($this->post('stock/edit/' . $stock, ['submit' => 1, 'new_volume' => 99, 'ori_volume' => 7, 'state' => 1, 'lotnr' => 'X', 'eol' => '2030-01-01', 'in_price' => 1]), '');
        $this->assertSame('7.00', $this->find('stock', $stock)['volume']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $page = $this->get('stock/edit/' . $stock);
        $this->assertStatus($page, 200);
        $this->assertSee($page, 'EDIT-ME');
    }

    public function testTheStockProblemScreenIsForAdminsOnly(): void
    {
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('stock/stock_check'), '');

        $this->cookies->clear();
        $this->loginAs('admin');
        $page = $this->get('stock/stock_check');
        $this->assertStatus($page, 200);
        // the seeded stock row in state ERROR
        $this->assertSee($page, 'AMX-ERR');
    }

    public function testTheNewProductWizardIsForAdminsOnly(): void
    {
        $before = $this->countWhere('products');

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('products/new'), '');
        $this->assertRedirectsTo($this->post('products/new', ['submit' => 1, 'name' => 'Vet product']), '');
        $this->assertSame($before, $this->countWhere('products'));

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('products/new'), 200);
    }

    public function testTheBackorderSwitchIsForAdminsOnly(): void
    {
        $product = Factory::product(['backorder' => 0]);

        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('products/set_backorder/' . $product), '');
        $this->assertSame(0, (int) $this->find('products', $product)['backorder']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertRedirectsTo($this->get('products/set_backorder/' . $product), 'limits/global');
        $this->assertSame(1, (int) $this->find('products', $product)['backorder']);
        $this->assertRowExists('log', ['event' => 'set_product_in_backorder']);

        $this->assertRedirectsTo($this->get('products/unset_backorder/' . $product), 'limits/order');
        $this->assertSame(0, (int) $this->find('products', $product)['backorder']);
    }

    public function testUnsettingTheBackorderIsAlsoForAdminsOnly(): void
    {
        $product = Factory::product(['backorder' => 1]);

        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('products/unset_backorder/' . $product), '');
        $this->assertSame(1, (int) $this->find('products', $product)['backorder']);
    }

    public function testSavingAProductIsSilentlyIgnoredForAVet(): void
    {
        $product = Factory::product(['name' => 'Original name']);
        $form = [
            'submit' => 1, 'name' => 'Changed name', 'input_wh_name' => '', 'producer' => 'p', 'supplier' => 's',
            'type' => 1, 'dead_volume' => 0, 'vaccin_disease' => '', 'buy_volume' => 1, 'sell_volume' => 1,
            'unit_buy' => 'x', 'unit_sell' => 'x', 'btw_buy' => 6, 'vaccin_freq' => 0, 'default_indication' => 'null',
            'ab_unit' => 'PACKS', 'ab_unit_volume' => 1, 'booking_code' => 1, 'comment_admin' => '', 'vhbcode' => '',
            'cnk' => '', 'cti_e' => '', 'wholesale' => 0, 'limit_stock' => 0, 'sellable' => 1,
            'limit' => [Fixtures::LOCATION_PRACTICE => [-1 => 0]], 'labels' => [Fixtures::LABEL_PAIN],
        ];

        $this->loginAs('vet');
        $response = $this->post('products/product/' . $product, $form);

        // no redirect, no error: the page is just shown again
        $this->assertStatus($response, 200);
        $this->assertSame('Original name', $this->find('products', $product)['name']);

        // control: the same form saved by an admin does change it
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('products/product/' . $product, $form);
        $this->assertSame('Changed name', $this->find('products', $product)['name']);
    }

    public function testTheVaccineBoundaryIsForAdminsOnly(): void
    {
        $this->db()->exec('DELETE FROM log');

        $this->loginAs('vet');
        $this->assertStatus($this->get('vaccine/index/2'), 200, 'two months ahead is fine for a vet');
        $this->assertRedirectsTo($this->get('vaccine/index/5'), '');
        $this->assertRedirectsTo($this->get('vaccine/index/-5'), '');
        $this->assertRedirectsTo($this->get('vaccine/index/3'), '');
        $warning = $this->row("SELECT * FROM log WHERE event = 'boundary_vaccine_attack' ORDER BY id");
        $this->assertNotNull($warning);
        $this->assertSame(3, (int) $warning['level'], 'WARN level');
        $this->assertSame('month : 5', $warning['msg']);
        $this->assertSame(3, $this->countWhere('log', ['event' => 'boundary_vaccine_attack']));

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('vaccine/index/5'), 200);
    }

    // ---- known bugs ---------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testTheAdminHelpPageSendsAVetToAPageThatDoesNotExist(): void
    {
        // KNOWN BUG: Help::admin redirects non-admins to vet/dashboard, which does not exist
        $this->loginAs('vet');

        $response = $this->get('help/admin');

        $this->assertRedirectsTo($response, 'vet/dashboard');
        $this->assertStatus($this->followRedirect($response), 404);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('help/admin'), 200);
    }

    #[Group('known-bug')]
    public function testAVetCanDeleteStockThatIsStillBeingChecked(): void
    {
        // KNOWN BUG: no admin check (the line is only protected by being in the "check" state)
        $check = Factory::stock(Fixtures::PRODUCT_TIERED, ['state' => Fixtures::STATE_CHECK]);
        $inUse = Factory::stock(Fixtures::PRODUCT_TIERED, ['state' => Fixtures::STATE_IN_USE]);
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('stock/delete_stock/' . $check), 'stock/add_stock');

        $this->assertNull($this->find('stock', $check));
        $this->get('stock/delete_stock/' . $inUse);
        $this->assertNotNull($this->find('stock', $inUse), 'only rows in the check state can be removed');
    }

    #[Group('known-bug')]
    public function testAVetCanWriteOffAWholeStockRow(): void
    {
        // KNOWN BUG: no admin check
        $stock = Factory::stock(Fixtures::PRODUCT_TIERED, ['volume' => 4, 'lotnr' => 'WRITE-OFF']);
        $this->loginAs('vet');

        $response = $this->get('stock/write_off_full/' . $stock);

        $this->assertRedirectsTo($response, 'stock/expired_stock');
        $this->assertRowExists('liquidate', ['stock_id' => $stock, 'reason' => 'EXPIRED', 'lotnr' => 'WRITE-OFF']);
        $this->assertSame('0.00', $this->find('stock', $stock)['volume']);
        $this->assertSame(Fixtures::STATE_HISTORY, (int) $this->find('stock', $stock)['state']);
    }

    #[Group('known-bug')]
    public function testAVetCanClearAStockError(): void
    {
        // KNOWN BUG: no admin check; the missing volume simply disappears
        $stock = Factory::stock(Fixtures::PRODUCT_TIERED, ['volume' => -3, 'state' => Fixtures::STATE_ERROR]);
        $this->loginAs('vet');

        $response = $this->get('stock/clear_error/' . $stock);

        $this->assertRedirectsTo($response, 'stock/stock_check');
        $this->assertSame('0.00', $this->find('stock', $stock)['volume']);
        $this->assertSame(Fixtures::STATE_HISTORY, (int) $this->find('stock', $stock)['state']);
        $this->assertRowExists('log', ['event' => 'clear_error']);
    }

    #[Group('known-bug')]
    public function testAVetCanDeleteAProduct(): void
    {
        // KNOWN BUG: no admin check, and no check for stock that is still in use
        $product = Factory::product();
        Factory::stock($product, ['volume' => 25]);
        $this->loginAs('vet');

        $response = $this->get('products/delete_product/' . $product);

        $this->assertRedirectsTo($response, 'products/product_list');
        $this->assertNotNull($this->find('products', $product)['deleted_at'], 'soft deleted');
        $this->assertRowExists('log', ['event' => 'delete_product', 'level' => 3]);
    }

    #[Group('known-bug')]
    public function testAVetCanAddABreed(): void
    {
        // KNOWN BUG (or by design): adding a breed needs no admin check
        $this->loginAs('vet');

        $response = $this->post('breeds/add', [
            'submit' => 1, 'name' => 'Vet breed', 'type' => 0,
            'male_min_weight' => 1, 'male_max_weight' => 2, 'female_min_weight' => 1, 'female_max_weight' => 2,
        ]);

        $this->assertRedirectsTo($response, 'breeds');
        $this->assertRowExists('breeds', ['name' => 'Vet breed', 'freq' => 0]);
    }

    #[Group('known-bug')]
    public function testAVetCanRebuildTheBreedFrequencies(): void
    {
        // KNOWN BUG: no admin check, and it is a GET that rewrites the freq of every breed
        $breed = Factory::breed(['name' => 'Popular breed', 'freq' => 0]);
        $owner = Factory::owner();
        for ($i = 0; $i < 6; $i++) {
            Factory::pet($owner, ['breed' => $breed, 'birth' => date('Y-m-d', strtotime('-1 year'))]);
        }
        $this->db()->exec('UPDATE breeds SET freq = 99 WHERE id = ' . Fixtures::BREED_LABRADOR);
        $this->loginAs('vet');

        $response = $this->get('breeds/rebuild_frequenty');

        $this->assertStatus($response, 200);
        $this->assertSame(6, (int) $this->find('breeds', $breed)['freq']);
        $this->assertSame(0, (int) $this->find('breeds', Fixtures::BREED_LABRADOR)['freq'], 'every other breed was reset');
        $this->assertRowExists('log', ['event' => 'rebuild_freq']);
    }
}
