<?php

declare(strict_types=1);

/**
 * T184 - Products::product_list (page) and Products::get (DataTables JSON) per product type.
 * T185 - Products::delete_product.
 *
 * `get/{typeId}` returns {"aaData": [[nameLink, stockText, icons, "", editLink], ...]} sorted by name,
 * only sellable products of that type or of its child types, no deleted ones. Special ids: 0 = non-sellable
 * products, -1 = antibiotics, -2 = vaccines (both only when sellable). stockText is "local / global unit"
 * and only filled when both are above zero. An unknown type, or a type without matching products, gives
 * {"aaData":[]}.
 * Deleting a product has NO admin check and no confirmation (see the test).
 */
final class ProductListTest extends HttpTestCase
{
    private function names(string $path): array
    {
        $json = $this->json($this->get($path));
        $this->assertArrayHasKey('aaData', $json);

        return array_map(static fn (array $row): string => strip_tags($row[0]), $json['aaData']);
    }

    private function type(string $name, ?int $root = null): int
    {
        return Factory::insert('products_type', [
            'name' => $name, 'root' => $root,
            'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    // ---- T184 ------------------------------------------------------------------------------

    public function testTheListPageOpensForRootChildAndUnknownTypes(): void
    {
        foreach ([Fixtures::TYPE_MEDICATION, Fixtures::TYPE_ANTIBIOTICS, 0, -1, -2, 99] as $type) {
            $page = $this->get('products/product_list/' . $type);
            $this->assertStatus($page, 200, 'type ' . $type);
        }
        $page = $this->get('products/product_list');
        $this->assertSee($page, 'Medicatie');
        $this->assertSee($page, 'Voeding');
        $this->assertSee($page, 'Antibiotic');
        $this->assertSee($page, 'Vaccin');
    }

    public function testTheJsonHasFiveColumnsPerRow(): void
    {
        $type = $this->type(Factory::unique('TypeCols'));
        $name = Factory::unique('ColsP');
        $pid = Factory::product(['name' => $name, 'type' => $type, 'unit_sell' => 'zq']);
        Factory::stock($pid, ['location' => '1', 'volume' => 3]);
        Factory::stock($pid, ['location' => '2', 'volume' => 4]);

        $response = $this->get('products/get/' . $type);

        $json = $this->json($response);
        $this->assertCount(1, $json['aaData']);
        $row = $json['aaData'][0];
        $this->assertCount(5, $row);
        $this->assertStringContainsString('products/profile/' . $pid, $row[0]);
        $this->assertStringContainsString($name, $row[0]);
        // the vet is at location 1: local 3, global 7
        $this->assertSame('3 / 7 zq', $row[1]);
        $this->assertStringContainsString('fa-bag-shopping', $row[2]);
        $this->assertSame('', $row[3]);
        $this->assertStringContainsString('products/product/' . $pid, $row[4]);
    }

    public function testTheStockTextIsEmptyWhenThereIsNoLocalStock(): void
    {
        $type = $this->type(Factory::unique('TypeNoLocal'));
        $pid = Factory::product(['type' => $type]);
        Factory::stock($pid, ['location' => '2', 'volume' => 4]);
        $none = Factory::product(['type' => $type]);

        $rows = $this->json($this->get('products/get/' . $type))['aaData'];

        $this->assertCount(2, $rows);
        $this->assertSame('', $rows[0][1]);
        $this->assertSame('', $rows[1][1]);
    }

    public function testProductsAreSortedByNameAndSellableOnly(): void
    {
        $type = $this->type(Factory::unique('TypeSort'));
        $key = Factory::unique('Srt');
        Factory::product(['name' => $key . ' B', 'type' => $type]);
        Factory::product(['name' => $key . ' A', 'type' => $type]);
        Factory::product(['name' => $key . ' C hidden', 'type' => $type, 'sellable' => 0]);
        Factory::product(['name' => $key . ' D deleted', 'type' => $type, 'deleted_at' => Factory::raw('NOW()')]);

        $this->assertSame([$key . ' A', $key . ' B'], $this->names('products/get/' . $type));
    }

    public function testARootTypeAlsoListsTheProductsOfItsChildTypes(): void
    {
        $root = $this->type(Factory::unique('Root'));
        $child = $this->type(Factory::unique('Child'), $root);
        $other = $this->type(Factory::unique('Other'));
        $a = Factory::product(['name' => Factory::unique('InRoot'), 'type' => $root]);
        $b = Factory::product(['name' => Factory::unique('InChild'), 'type' => $child]);
        $c = Factory::product(['name' => Factory::unique('InOther'), 'type' => $other]);

        $rootNames = $this->names('products/get/' . $root);
        $childNames = $this->names('products/get/' . $child);

        $expected = [$this->find('products', $a)['name'], $this->find('products', $b)['name']];
        sort($expected); // the list is ordered by name
        $this->assertSame($expected, $rootNames);
        $this->assertSame([$this->find('products', $b)['name']], $childNames);
        $this->assertNotContains($this->find('products', $c)['name'], $rootNames);
    }

    public function testNonSellableProductsAreListedUnderTypeZero(): void
    {
        $sellable = Factory::product(['name' => Factory::unique('Sell0')]);
        $hidden = Factory::product(['name' => Factory::unique('Hide0'), 'sellable' => 0]);

        $names = $this->names('products/get/0');

        $this->assertContains($this->find('products', $hidden)['name'], $names);
        $this->assertContains('Niet verkoopbaar', $names, 'seeded product 4007');
        $this->assertNotContains($this->find('products', $sellable)['name'], $names);
        $json = $this->json($this->get('products/get/0'));
        foreach ($json['aaData'] as $row) {
            $this->assertStringContainsString('fa-xmark', $row[2]);
        }
    }

    public function testTypeMinusOneListsSellableAntibioticsAndMinusTwoVaccines(): void
    {
        $ab = Factory::product(['name' => Factory::unique('Ab'), 'is_antibiotic' => 1]);
        $abHidden = Factory::product(['name' => Factory::unique('AbHid'), 'is_antibiotic' => 1, 'sellable' => 0]);
        $vac = Factory::product(['name' => Factory::unique('Vac'), 'vaccin' => 1, 'vaccin_freq' => 365]);
        $plain = Factory::product(['name' => Factory::unique('Plain')]);
        $name = fn (int $id): string => $this->find('products', $id)['name'];

        $abs = $this->names('products/get/-1');
        $vacs = $this->names('products/get/-2');

        $this->assertContains($name($ab), $abs);
        $this->assertContains('Clavubactin AB', $abs);
        $this->assertNotContains($name($abHidden), $abs);
        $this->assertNotContains($name($vac), $abs);
        $this->assertNotContains($name($plain), $abs);
        $this->assertContains($name($vac), $vacs);
        $this->assertContains('Nobivac Vaccin', $vacs);
        $this->assertNotContains($name($ab), $vacs);
        $row = $this->json($this->get('products/get/-1'))['aaData'];
        $this->assertStringContainsString('fa-bacteria', json_encode($row));
        $this->assertStringContainsString('fa-syringe', json_encode($this->json($this->get('products/get/-2'))['aaData']));
    }

    public function testAnUnknownOrEmptyTypeGivesAnEmptyList(): void
    {
        $empty = $this->type(Factory::unique('TypeEmpty'));

        $this->assertSame(['aaData' => []], $this->json($this->get('products/get/99')));
        $this->assertSame(['aaData' => []], $this->json($this->get('products/get/' . $empty)));
    }

    // ---- T185 ------------------------------------------------------------------------------

    public function testDeletingAProductSoftDeletesItAndRedirectsToTheList(): void
    {
        $pid = Factory::product(['name' => Factory::unique('Del')]);
        $stock = Factory::stock($pid, ['volume' => 5]);
        $this->db()->exec('DELETE FROM log');

        $response = $this->get('products/delete_product/' . $pid);

        $this->assertRedirectsTo($response, 'products/product_list');
        $this->assertNotNull($this->find('products', $pid)['deleted_at']);
        $this->assertNotNull($this->find('stock', $stock), 'stock rows stay until `cli stock_clean`');
        $log = $this->row("SELECT * FROM log WHERE event = 'delete_product'");
        $this->assertNotNull($log);
        $this->assertStringContainsString((string) $pid, $log['msg']);
    }

    public function testADeletedProductDisappearsFromTheListsAndTheAutocomplete(): void
    {
        $name = Factory::unique('DelGone');
        $pid = Factory::product(['name' => $name, 'type' => Fixtures::TYPE_FOOD]);
        $other = Factory::product(['name' => $name . ' kept']);
        $this->assertContains($name, $this->names('products/get/' . Fixtures::TYPE_FOOD), 'visible before');
        $before = $this->json($this->get('products/get_product_or_procedure', ['query' => $name]));
        $this->assertCount(2, $before['suggestions']);

        $this->get('products/delete_product/' . $pid);

        $this->assertNotContains($name, $this->names('products/get/' . Fixtures::TYPE_FOOD));
        $after = $this->json($this->get('products/get_product_or_procedure', ['query' => $name]));
        $this->assertSame([$name . ' kept'], array_column($after['suggestions'], 'value'));
        $this->assertSame(1, $this->json($this->get('products/search_catalog', ['q' => $name]))['count'], 'the catalogue search drops it too');
        $this->assertNull($this->find('products', $other)['deleted_at']);
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testAVetCanDeleteAProductBecauseThereIsNoAdminCheck(): void
    {
        // KNOWN BUG: delete_product has no `in_group('admin')` check (and works on GET) - decide in CI4 whether to keep.
        $pid = Factory::product();

        $this->assertRedirectsTo($this->get('products/delete_product/' . $pid), 'products/product_list');

        $this->assertNotNull($this->find('products', $pid)['deleted_at']);
    }
}
