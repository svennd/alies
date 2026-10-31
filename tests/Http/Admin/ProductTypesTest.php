<?php

declare(strict_types=1);

/**
 * T303 - Admin::product_types, product_types_rm, product_labels, product_labels_rm (admin only).
 *
 * Rules: types are limited to two levels (a root that is itself a child, or unknown, becomes NULL);
 * names are trimmed; the icon keeps letters, digits, dash and single spaces; icon_color must be
 * #rrggbb (stored lower case) else NULL; labels are trimmed, lower cased and unique; deleting a
 * type detaches its children, hard deletes it and sets products.type to 0; deleting a label
 * removes its product links.
 */
final class ProductTypesTest extends HttpTestCase
{
    private function addType(array $fields): void
    {
        $this->post('admin/product_types', ['submit' => 'add_product_type'] + $fields);
    }

    private function typeByName(string $name): ?array
    {
        return $this->row('SELECT * FROM products_type WHERE name = ?', [$name]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    // ---- types -------------------------------------------------------------------------------

    public function testATypeIsStoredTrimmedWithSanitisedIconAndLowerCaseColour(): void
    {
        $name = Factory::unique('Typ');

        $this->addType(['name' => " $name ", 'root' => (string) Fixtures::TYPE_MEDICATION, 'icon' => 'Fa-Dog!!  x', 'icon_color' => '#ABCDEF']);

        $type = $this->typeByName($name);
        $this->assertNotNull($type, 'name trimmed');
        // "Fa-Dog!!  x": '!' removed, the double space collapsed, case kept
        $this->assertSame('Fa-Dog x', $type['icon']);
        $this->assertSame('#abcdef', $type['icon_color']);
        $this->assertSame(Fixtures::TYPE_MEDICATION, (int) $type['root']);
    }

    public function testAnInvalidColourIsStoredAsNull(): void
    {
        foreach (['red', '#abc', '#12345g', 'abcdef'] as $i => $color) {
            $name = Factory::unique('Col' . $i);
            $this->addType(['name' => $name, 'root' => '0', 'icon' => 'pills', 'icon_color' => $color]);
            $this->assertNull($this->typeByName($name)['icon_color'], $color);
        }
    }

    public function testAnEmptyOrAllPunctuationIconIsStoredAsNull(): void
    {
        $a = Factory::unique('IconA');
        $b = Factory::unique('IconB');
        $this->addType(['name' => $a, 'root' => '0', 'icon' => '', 'icon_color' => '']);
        $this->addType(['name' => $b, 'root' => '0', 'icon' => '!!!', 'icon_color' => '']);

        $this->assertNull($this->typeByName($a)['icon']);
        $this->assertNull($this->typeByName($b)['icon']);
        $this->assertNull($this->typeByName($a)['root'], 'root 0 = top level');
    }

    public function testAnEmptyNameAddsNothing(): void
    {
        $before = $this->countWhere('products_type');

        $this->addType(['name' => '   ', 'root' => '0', 'icon' => '', 'icon_color' => '']);

        $this->assertSame($before, $this->countWhere('products_type'));
    }

    public function testARootThatIsItselfAChildIsNormalisedToNull(): void
    {
        $name = Factory::unique('Grandchild');

        // TYPE_ANTIBIOTICS (3) is a child of Medication, so it cannot be a parent
        $this->addType(['name' => $name, 'root' => (string) Fixtures::TYPE_ANTIBIOTICS, 'icon' => '', 'icon_color' => '']);

        $this->assertNull($this->typeByName($name)['root']);
    }

    public function testAnUnknownRootIsNormalisedToNull(): void
    {
        $name = Factory::unique('Orphan');

        $this->addType(['name' => $name, 'root' => '200', 'icon' => '', 'icon_color' => '']);

        $this->assertNull($this->typeByName($name)['root']);
    }

    public function testUpdatingATypeChangesNameIconColourAndRoot(): void
    {
        $id = Factory::insert('products_type', ['name' => Factory::unique('Old'), 'icon' => null, 'icon_color' => null, 'root' => null, 'created_at' => Factory::raw('NOW()')]);
        $new = Factory::unique('New');

        $this->post('admin/product_types', ['submit' => 'update_product_type', 'id' => $id, 'name' => " $new ", 'root' => (string) Fixtures::TYPE_FOOD, 'icon' => 'bone', 'icon_color' => '#00FF00']);

        $type = $this->find('products_type', $id);
        $this->assertSame($new, $type['name']);
        $this->assertSame('bone', $type['icon']);
        $this->assertSame('#00ff00', $type['icon_color']);
        $this->assertSame(Fixtures::TYPE_FOOD, (int) $type['root']);
    }

    public function testAnUpdateCannotMakeATypeItsOwnParentOrParentOfItsParent(): void
    {
        $parent = Factory::insert('products_type', ['name' => Factory::unique('P'), 'root' => null, 'created_at' => Factory::raw('NOW()')]);
        $child = Factory::insert('products_type', ['name' => Factory::unique('C'), 'root' => $parent, 'created_at' => Factory::raw('NOW()')]);
        $other = Factory::insert('products_type', ['name' => Factory::unique('O'), 'root' => null, 'created_at' => Factory::raw('NOW()')]);

        $this->post('admin/product_types', ['submit' => 'update_product_type', 'id' => $parent, 'name' => 'ParentX', 'root' => (string) $parent, 'icon' => '', 'icon_color' => '']);
        $this->assertNull($this->find('products_type', $parent)['root'], 'own parent -> NULL');

        // a type that has children cannot be moved under another root
        $this->post('admin/product_types', ['submit' => 'update_product_type', 'id' => $parent, 'name' => 'ParentX', 'root' => (string) $other, 'icon' => '', 'icon_color' => '']);
        $this->assertNull($this->find('products_type', $parent)['root']);
        $this->assertSame($parent, (int) $this->find('products_type', $child)['root']);

        // positive control: a childless type may move under a root
        $this->post('admin/product_types', ['submit' => 'update_product_type', 'id' => $child, 'name' => 'ChildX', 'root' => (string) $other, 'icon' => '', 'icon_color' => '']);
        $this->assertSame($other, (int) $this->find('products_type', $child)['root']);
    }

    public function testDeletingATypeDetachesChildrenDeletesItAndResetsItsProducts(): void
    {
        $parent = Factory::insert('products_type', ['name' => Factory::unique('P'), 'root' => null, 'created_at' => Factory::raw('NOW()')]);
        $child = Factory::insert('products_type', ['name' => Factory::unique('C'), 'root' => $parent, 'created_at' => Factory::raw('NOW()')]);
        $product = Factory::product(['type' => $parent]);
        $keeper = Factory::product(['type' => $child]);

        $response = $this->get('admin/product_types_rm/' . $parent);

        $this->assertRedirectsTo($response, 'admin/product_types');
        $this->assertNull($this->find('products_type', $parent), 'hard deleted');
        $this->assertNull($this->find('products_type', $child)['root'], 'child becomes a top level type');
        $this->assertSame(0, (int) $this->find('products', $product)['type']);
        $this->assertSame($child, (int) $this->find('products', $keeper)['type'], 'products of other types untouched');
    }

    public function testThePageShowsTypesWithTheirParentName(): void
    {
        $parentName = Factory::unique('Ouder');
        $childName = Factory::unique('Kind');
        $parent = Factory::insert('products_type', ['name' => $parentName, 'root' => null, 'created_at' => Factory::raw('NOW()')]);
        Factory::insert('products_type', ['name' => $childName, 'root' => $parent, 'created_at' => Factory::raw('NOW()')]);

        $page = $this->get('admin/product_types');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $parentName);
        $this->assertSee($page, $childName);
        $this->assertSee($page, 'Medicatie');
    }

    // ---- labels ------------------------------------------------------------------------------

    public function testALabelIsTrimmedAndLowerCased(): void
    {
        $this->post('admin/product_types', ['submit' => 'add_product_label', 'name' => ' PijnX ']);

        $this->assertRowExists('products_label', ['name' => 'pijnx']);
    }

    public function testAddingTheSameLabelAgainInsertsNothing(): void
    {
        $this->post('admin/product_types', ['submit' => 'add_product_label', 'name' => 'Dubbel']);
        $this->post('admin/product_types', ['submit' => 'add_product_label', 'name' => ' DUBBEL']);
        // 'pijn' is a seeded label
        $this->post('admin/product_types', ['submit' => 'add_product_label', 'name' => 'Pijn']);

        $this->assertRowCount(1, 'products_label', ['name' => 'dubbel']);
        $this->assertRowCount(1, 'products_label', ['name' => 'pijn']);
    }

    public function testAnEmptyLabelNameAddsNothing(): void
    {
        $before = $this->countWhere('products_label');

        $this->post('admin/product_types', ['submit' => 'add_product_label', 'name' => '  ']);

        $this->assertSame($before, $this->countWhere('products_label'));
    }

    public function testRenamingALabelWorksUnlessTheNameIsTaken(): void
    {
        $a = Factory::insert('products_label', ['name' => 'lab' . uniqid(), 'created_at' => Factory::raw('NOW()')]);
        $b = Factory::insert('products_label', ['name' => 'lab' . uniqid(), 'created_at' => Factory::raw('NOW()')]);
        $oldB = $this->find('products_label', $b)['name'];

        $this->post('admin/product_types', ['submit' => 'update_product_label', 'id' => $b, 'name' => $this->find('products_label', $a)['name']]);
        $this->assertSame($oldB, $this->find('products_label', $b)['name'], 'taken name refused');

        $this->post('admin/product_types', ['submit' => 'update_product_label', 'id' => $b, 'name' => ' Nieuwnaam ' . $b]);
        $this->assertSame('nieuwnaam ' . $b, $this->find('products_label', $b)['name']);
    }

    public function testDeletingALabelRemovesItAndItsProductLinksOnly(): void
    {
        $label = Factory::insert('products_label', ['name' => 'rm' . uniqid(), 'created_at' => Factory::raw('NOW()')]);
        $product = Factory::product();
        foreach ([$label, Fixtures::LABEL_PAIN] as $l) {
            Factory::insert('products_product_label', ['product_id' => $product, 'label_id' => $l, 'created_at' => Factory::raw('NOW()')]);
        }

        $response = $this->get('admin/product_labels_rm/' . $label);

        $this->assertRedirectsTo($response, 'admin/product_types');
        $this->assertNull($this->find('products_label', $label));
        $this->assertRowCount(0, 'products_product_label', ['label_id' => $label]);
        $this->assertRowCount(1, 'products_product_label', ['product_id' => $product, 'label_id' => Fixtures::LABEL_PAIN]);
        $this->assertRowExists('products', ['id' => $product]);
    }

    public function testTheLabelsPageRedirectsToTheTypesPage(): void
    {
        $this->assertRedirectsTo($this->get('admin/product_labels'), 'admin/product_types');
    }

    // ---- access ------------------------------------------------------------------------------

    public function testOnlyAdminsMayManageTypesAndLabels(): void
    {
        $name = Factory::unique('Denied');
        $type = Factory::insert('products_type', ['name' => Factory::unique('Keep'), 'root' => null, 'created_at' => Factory::raw('NOW()')]);
        $this->cookies->clear();
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->post('admin/product_types', ['submit' => 'add_product_type', 'name' => $name, 'root' => '0', 'icon' => '', 'icon_color' => '']), '');
        $this->assertRedirectsTo($this->get('admin/product_types_rm/' . $type), '');
        $this->assertRedirectsTo($this->get('admin/product_labels_rm/' . Fixtures::LABEL_SKIN), '');
        $this->assertNull($this->typeByName($name));
        $this->assertNotNull($this->find('products_type', $type));
        $this->assertNotNull($this->find('products_label', Fixtures::LABEL_SKIN));

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->addType(['name' => $name, 'root' => '0', 'icon' => '', 'icon_color' => '']);
        $this->assertNotNull($this->typeByName($name), 'positive control');
    }
}
