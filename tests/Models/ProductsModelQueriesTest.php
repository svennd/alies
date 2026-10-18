<?php

declare(strict_types=1);

require_once __DIR__ . '/StockModelHelper.php';

/**
 * T209 Products_model queries, Product_type_model, Product_label_model.
 * Source: application/models/{Products_model,Product_type_model,Product_label_model}.php
 *
 * Each test searches with a unique name prefix so seeded products (4001-4007) never interfere.
 */
final class ProductsModelQueriesTest extends CodeIgniterDatabaseTestCase
{
    use StockModelHelper;

    private $products;
    private $types;
    private $labels;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStock();
        $this->products = $this->model('Products_model', 'products');
        $this->types = $this->model('Product_type_model', 'prod_type');
        $this->labels = $this->model('Product_label_model', 'prod_label');
    }

    private function ids(array $rows, string $key = 'id'): array
    {
        return array_map(static fn ($r) => (int) $r[$key], $rows);
    }

    private function type(string $name, ?int $root = null): int
    {
        return Factory::insert('products_type', [
            'name' => $name, 'root' => $root,
            'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    // ---------------------------------------------------------------- search_product

    public function testSearchProductMatchesNameWholesaleNameAndShortNameAndSkipsDeleted(): void
    {
        $key = 'SP' . Factory::unique();
        $byName = Factory::product(['name' => 'x' . $key . 'y']);
        $byWholesale = Factory::product(['wholesale_name' => 'a' . $key]);
        $byShort = Factory::product(['short_name' => $key . 'z']);
        $deleted = Factory::product(['name' => $key . ' deleted', 'deleted_at' => date('Y-m-d H:i:s')]);
        Factory::product(['name' => 'unrelated ' . Factory::unique()]);

        $found = $this->products->search_product($key);

        $this->assertEqualsCanonicalizing([$byName, $byWholesale, $byShort], $this->ids($found));
        $this->assertNotContains($deleted, $this->ids($found));
    }

    public function testSearchProductReturnsFalseForAnEmptyOrUnmatchedNameAndCapsAtFifty(): void
    {
        $this->assertFalse($this->products->search_product(''));
        // PIN: the string "0" is falsy in PHP, so searching for "0" finds nothing
        $this->assertFalse($this->products->search_product('0'));
        $this->assertFalse($this->products->search_product('NOPE' . Factory::unique()));

        $key = 'CAP' . Factory::unique();
        for ($i = 0; $i < 51; $i++) {
            Factory::product(['name' => $key . '-' . $i]);
        }
        $this->assertCount(50, $this->products->search_product($key));
    }

    // ---------------------------------------------------------------- search_product_with_stock

    public function testSearchProductWithStockReturnsLocalAndGlobalStockOfInUseRows(): void
    {
        $key = 'SW' . Factory::unique();
        $pid = Factory::product(['name' => $key . 'b', 'unit_sell' => 'ml', 'sellable' => 0]);
        $sibling = Factory::product(['name' => $key . 'a']);
        $deleted = Factory::product(['name' => $key . 'c', 'deleted_at' => date('Y-m-d H:i:s')]);
        Factory::stock($pid, ['volume' => 5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 2.5, 'location' => '1']);
        Factory::stock($pid, ['volume' => 7, 'location' => '2']);
        Factory::stock($pid, ['volume' => 100, 'location' => '1', 'state' => STOCK_HISTORY]);

        $rows = $this->products->search_product_with_stock($key, 1);

        // ordered by name, deleted product absent, a product without stock still listed with zeros
        $this->assertSame([$sibling, $pid], $this->ids($rows));
        $this->assertEqualsWithDelta(0.0, (float) $rows[0]['local_stock'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $rows[0]['global_stock'], 0.001);
        // local 5 + 2.5 = 7.5 ; global 5 + 2.5 + 7 = 14.5 (HISTORY ignored)
        $this->assertEqualsWithDelta(7.5, (float) $rows[1]['local_stock'], 0.001);
        $this->assertEqualsWithDelta(14.5, (float) $rows[1]['global_stock'], 0.001);
        $this->assertSame('ml', $rows[1]['unit_sell']);
        $this->assertSame(0, (int) $rows[1]['sellable']); // not filtered on sellable
        $this->assertNotContains($deleted, $this->ids($rows));

        // location 2 flips local and global
        $rows2 = $this->products->search_product_with_stock($key, 2);
        $this->assertEqualsWithDelta(7.0, (float) $rows2[1]['local_stock'], 0.001);
    }

    public function testSearchProductWithStockEscapesLikeWildcardsAndHonoursLimitAndEmptyInput(): void
    {
        $key = 'SE' . Factory::unique();
        $underscore = Factory::product(['name' => $key . '_a']);
        $other = Factory::product(['name' => $key . 'Xa']);       // would match an unescaped "_"
        $percent = Factory::product(['name' => $key . '100%']);
        $plain = Factory::product(['name' => $key . '100 plain']); // would match an unescaped "%"

        $this->assertSame([], $this->products->search_product_with_stock('', 1));
        $this->assertSame([$underscore], $this->ids($this->products->search_product_with_stock($key . '_a', 1)));
        $this->assertSame([$percent], $this->ids($this->products->search_product_with_stock($key . '100%', 1)));
        // positive control: the plain prefix finds all four, the limit cuts them
        $this->assertCount(4, $this->products->search_product_with_stock($key, 1));
        $this->assertCount(2, $this->products->search_product_with_stock($key, 1, 2));
        $this->assertNotContains($other, $this->ids($this->products->search_product_with_stock($key . '_a', 1)));
        $this->assertNotContains($plain, $this->ids($this->products->search_product_with_stock($key . '100%', 1)));
    }

    // ---------------------------------------------------------------- get_products_stocks

    public function testGetProductsStocksListsSellableProductsByUsageWithTheirPositiveInUseRows(): void
    {
        $key = 'GP' . Factory::unique();
        $popular = Factory::product(['name' => $key . 'zz', 'usage_count' => 50]);
        $alpha = Factory::product(['name' => $key . 'aa', 'usage_count' => 1]);
        $beta = Factory::product(['name' => $key . 'bb', 'usage_count' => 1]);
        $notSellable = Factory::product(['name' => $key . 'ns', 'sellable' => 0, 'usage_count' => 99]);
        $deleted = Factory::product(['name' => $key . 'del', 'deleted_at' => date('Y-m-d H:i:s'), 'usage_count' => 99]);
        $s1 = Factory::stock($popular, ['volume' => 4, 'location' => '2', 'eol' => '2031-01-01']);
        $s2 = Factory::stock($popular, ['volume' => 3, 'location' => '1', 'eol' => '2032-01-01']);
        $s3 = Factory::stock($popular, ['volume' => 2, 'location' => '1', 'eol' => '2030-01-01']);
        Factory::stock($popular, ['volume' => 0, 'location' => '1']);                    // volume not > 0
        Factory::stock($popular, ['volume' => 8, 'location' => '1', 'state' => STOCK_HISTORY]);
        Factory::stock($notSellable, ['volume' => 8]);

        $rows = $this->products->get_products_stocks($key);

        // usage_count DESC then name, then location ASC, eol ASC: zz(3 rows) then aa, bb (no stock: NULL row)
        $this->assertSame([$popular, $popular, $popular, $alpha, $beta], $this->ids($rows, 'product_id'));
        $this->assertSame([$s3, $s2, $s1], array_map(static fn ($r) => (int) $r['stock_id'], array_slice($rows, 0, 3)));
        $this->assertNull($rows[3]['stock_id']);
        $this->assertNull($rows[4]['stock_id']);
        $this->assertSame($key . 'zz', $rows[0]['name']);
        $this->assertEqualsWithDelta(2.0, (float) $rows[0]['volume'], 0.001);
        $this->assertNotContains($notSellable, $this->ids($rows, 'product_id'));
        $this->assertNotContains($deleted, $this->ids($rows, 'product_id'));
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testGetProductsStocksDoesNotEscapeLikeWildcardsAndStopsAtFifteenProducts(): void
    {
        // KNOWN BUG: the search text is put into LIKE as is, so "_" and "%" are wildcards
        // (search_product_with_stock escapes them, this method does not).
        $key = 'GW' . Factory::unique();
        $a = Factory::product(['name' => $key . '_a']);
        $b = Factory::product(['name' => $key . 'Xa']);
        $this->assertEqualsCanonicalizing([$a, $b], $this->ids($this->products->get_products_stocks($key . '_a'), 'product_id'));
        $this->assertEqualsCanonicalizing([$a], $this->ids($this->products->search_product_with_stock($key . '_a', 1)));

        // 15 products at most (PRODUCT_SEARCH_LIMIT), counted before the stock join
        $cap = 'GC' . Factory::unique();
        for ($i = 0; $i < 16; $i++) {
            Factory::product(['name' => $cap . sprintf('%02d', $i)]);
        }
        $this->assertCount(15, $this->products->get_products_stocks($cap));
    }

    // ---------------------------------------------------------------- get_products_by_type_with_stock

    public function testGetProductsByTypeIncludesTheChildrenOfAParentTypeAndSumsInUseStock(): void
    {
        $parent = $this->type('Parent ' . Factory::unique());
        $child = $this->type('Child ' . Factory::unique(), $parent);
        $other = $this->type('Other ' . Factory::unique());
        $inParent = Factory::product(['name' => 'b-parent', 'type' => $parent]);
        $inChild = Factory::product(['name' => 'a-child', 'type' => $child]);
        $inOther = Factory::product(['name' => 'c-other', 'type' => $other]);
        $hidden = Factory::product(['type' => $parent, 'sellable' => 0]);
        $deleted = Factory::product(['type' => $parent, 'deleted_at' => date('Y-m-d H:i:s')]);
        Factory::stock($inParent, ['volume' => 5, 'location' => '1']);
        Factory::stock($inParent, ['volume' => 2, 'location' => '2']);
        Factory::stock($inParent, ['volume' => 0, 'location' => '1']);
        Factory::stock($inParent, ['volume' => 9, 'location' => '1', 'state' => STOCK_HISTORY]);

        $rows = $this->products->get_products_by_type_with_stock($parent, 1);
        $this->assertSame([$inChild, $inParent], $this->ids($rows, 'product_id')); // by name
        $byId = array_column($rows, null, 'product_id');
        $this->assertEqualsWithDelta(7.0, (float) $byId[$inParent]['volume_count'], 0.001);
        $this->assertEqualsWithDelta(5.0, (float) $byId[$inParent]['volume_location'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $byId[$inChild]['volume_count'], 0.001);

        // the child type alone does not include the parent's products
        $this->assertSame([$inChild], $this->ids($this->products->get_products_by_type_with_stock($child, 1), 'product_id'));
        $this->assertSame([$inOther], $this->ids($this->products->get_products_by_type_with_stock($other, 1), 'product_id'));
        // no location: nothing counts as local
        $noLoc = array_column($this->products->get_products_by_type_with_stock($parent), null, 'product_id');
        $this->assertEqualsWithDelta(0.0, (float) $noLoc[$inParent]['volume_location'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $noLoc[$inParent]['volume_count'], 0.001);
        // not sellable / deleted never show up for a normal type
        $this->assertNotContains($hidden, $this->ids($rows, 'product_id'));
        $this->assertNotContains($deleted, $this->ids($rows, 'product_id'));
    }

    public function testGetProductsByTypeSpecialTypesFilterOnFlags(): void
    {
        $key = Factory::unique();
        $notSellable = Factory::product(['name' => 'S0-' . $key, 'sellable' => 0]);
        $notSellableAb = Factory::product(['name' => 'S0b-' . $key, 'sellable' => 0, 'is_antibiotic' => 1, 'vaccin' => 1]);
        $antibiotic = Factory::product(['name' => 'S1-' . $key, 'is_antibiotic' => 1]);
        $vaccine = Factory::product(['name' => 'S2-' . $key, 'vaccin' => 1]);
        $both = Factory::product(['name' => 'S3-' . $key, 'is_antibiotic' => 1, 'vaccin' => 1]);
        $plain = Factory::product(['name' => 'S4-' . $key]);
        $deleted = Factory::product(['name' => 'S5-' . $key, 'sellable' => 0, 'deleted_at' => date('Y-m-d H:i:s')]);
        Factory::stock($antibiotic, ['volume' => 3, 'location' => '1']);

        $mine = static fn (array $rows) => array_values(array_filter($rows, static fn ($r) => str_ends_with($r['name'], $key)));

        // 0: not sellable (also when it is an antibiotic), deleted excluded
        $zero = $mine($this->products->get_products_by_type_with_stock(0, 1));
        $this->assertEqualsCanonicalizing([$notSellable, $notSellableAb], $this->ids($zero, 'product_id'));
        // -1: antibiotics that are sellable
        $ab = $mine($this->products->get_products_by_type_with_stock(-1, 1));
        $this->assertEqualsCanonicalizing([$antibiotic, $both], $this->ids($ab, 'product_id'));
        $this->assertEqualsWithDelta(3.0, (float) array_column($ab, null, 'product_id')[$antibiotic]['volume_location'], 0.001);
        // -2: vaccines that are sellable
        $vac = $mine($this->products->get_products_by_type_with_stock(-2, 1));
        $this->assertEqualsCanonicalizing([$vaccine, $both], $this->ids($vac, 'product_id'));
        $this->assertNotContains($plain, $this->ids($vac, 'product_id'));
        $this->assertNotContains($plain, $this->ids($ab, 'product_id'));
        $this->assertNotContains($deleted, $this->ids($zero, 'product_id'));
    }

    // ---------------------------------------------------------------- small queries

    public function testGetProductIdFindsTheProductLinkedToAWholesaleRow(): void
    {
        $w = Factory::wholesale();
        $pid = Factory::product(['wholesale' => $w]);
        $deletedW = Factory::wholesale();
        Factory::product(['wholesale' => $deletedW, 'deleted_at' => date('Y-m-d H:i:s')]);

        $this->assertSame($pid, $this->products->get_product_id($w));
        $this->assertFalse($this->products->get_product_id(Factory::wholesale())); // not linked
        $this->assertFalse($this->products->get_product_id($deletedW));             // linked to a deleted product
    }

    public function testUpdateCommentStoresTextAndIgnoresEmptyMessages(): void
    {
        $pid = Factory::product(['comment' => 'old']);

        $this->assertFalse($this->products->update_comment($pid, ''));
        $this->assertFalse($this->products->update_comment($pid, null));
        // PIN: PHP empty() treats "0" as empty too
        $this->assertFalse($this->products->update_comment($pid, '0'));
        $this->assertSame('old', $this->ci->db->where('id', $pid)->get('products')->row()->comment);

        $this->assertNotFalse($this->products->update_comment($pid, 'new text'));
        $this->assertSame('new text', $this->ci->db->where('id', $pid)->get('products')->row()->comment);
        // only that product changed
        $other = Factory::product(['comment' => 'untouched']);
        $this->products->update_comment($pid, 'again');
        $this->assertSame('untouched', $this->ci->db->where('id', $other)->get('products')->row()->comment);
    }

    public function testSetBackorderFilledOnlyClearsAProductThatWasOnBackorder(): void
    {
        $on = Factory::product(['backorder' => 1]);
        $off = Factory::product(['backorder' => 0]);
        $otherOn = Factory::product(['backorder' => 1]);

        $this->products->set_backorder_filled($on);
        $this->products->set_backorder_filled($off);

        $backorder = fn (int $id) => (int) $this->ci->db->where('id', $id)->get('products')->row()->backorder;
        $this->assertSame(0, $backorder($on));
        $this->assertSame(0, $backorder($off));
        $this->assertSame(1, $backorder($otherOn)); // positive control: untouched
    }

    // ---------------------------------------------------------------- product_monthly_use / usage_detail

    /** @return array{0:int,1:int} product id and the pet id */
    private function usageFixture(): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $pid = Factory::product();

        return [$pid, $pet];
    }

    private function line(int $pid, int $pet, string $when, float $volume, array $event = [], array $line = []): int
    {
        $eventId = Factory::event($pet, 1, 0, ['created_at' => $when, 'updated_at' => $when] + $event);

        return Factory::eventProduct($eventId, $pid, $volume, ['created_at' => $when, 'updated_at' => $when] + $line);
    }

    public function testProductMonthlyUseSumsVolumePerMonthWithinTheWindow(): void
    {
        [$pid, $pet] = $this->usageFixture();
        $thisMonth = date('Y-m-15 12:00:00');
        $twoAgo = date('Y-m-15 12:00:00', strtotime('first day of -2 months'));
        $fortyAgo = date('Y-m-15 12:00:00', strtotime('first day of -40 months'));
        $this->line($pid, $pet, $thisMonth, 2.0);
        $this->line($pid, $pet, $thisMonth, 1.5);
        $this->line($pid, $pet, $twoAgo, 4.0);
        $this->line($pid, $pet, $fortyAgo, 8.0);
        // another product in the same month never counts
        [$other] = $this->usageFixture();
        $this->line($other, $pet, $thisMonth, 100.0);

        $rows = $this->products->product_monthly_use($pid);

        // default window 36 months: 40 months back is out. Newest month first: 2 + 1.5 = 3.5, then 4
        $this->assertCount(2, $rows);
        $this->assertSame((int) date('Y'), (int) $rows[0]['y']);
        $this->assertSame((int) date('n'), (int) $rows[0]['m']);
        $this->assertEqualsWithDelta(3.5, (float) $rows[0]['p'], 0.001);
        $this->assertEqualsWithDelta(4.0, (float) $rows[1]['p'], 0.001);
        $this->assertSame((int) date('n', strtotime($twoAgo)), (int) $rows[1]['m']);

        // a window of 48 months brings the old line in
        $wide = $this->products->product_monthly_use($pid, 'none', 48);
        $this->assertCount(3, $wide);
        $this->assertEqualsWithDelta(8.0, (float) $wide[2]['p'], 0.001);
        // a window of 1 month: only this month (two months ago is out)
        $this->assertCount(1, $this->products->product_monthly_use($pid, 'none', 1));
    }

    public function testProductMonthlyUsePerVetAndPerLocationSplitTheMonth(): void
    {
        [$pid, $pet] = $this->usageFixture();
        $now = date('Y-m-15 12:00:00');
        $this->line($pid, $pet, $now, 2.0, ['vet' => Fixtures::USER_VET, 'location' => 1]);
        $this->line($pid, $pet, $now, 3.0, ['vet' => Fixtures::USER_VET, 'location' => 2]);
        $this->line($pid, $pet, $now, 5.0, ['vet' => Fixtures::USER_VET2, 'location' => 1]);

        // per vet: Victor 2 + 3 = 5, Veerle 5
        $perVet = $this->products->product_monthly_use($pid, 'per_vet');
        $byName = array_column($perVet, 'p', 'first_name');
        $this->assertCount(2, $perVet);
        $this->assertEqualsWithDelta(5.0, (float) $byName['Victor'], 0.001);
        $this->assertEqualsWithDelta(5.0, (float) $byName['Veerle'], 0.001);

        // per location: Praktijk 2 + 5 = 7, Wagen 3
        $perLoc = $this->products->product_monthly_use($pid, 'per_location');
        $byLoc = array_column($perLoc, 'p', 'stockname');
        $this->assertCount(2, $perLoc);
        $this->assertEqualsWithDelta(7.0, (float) $byLoc['Praktijk'], 0.001);
        $this->assertEqualsWithDelta(3.0, (float) $byLoc['Wagen'], 0.001);

        // plain: all three together
        $plain = $this->products->product_monthly_use($pid);
        $this->assertCount(1, $plain);
        $this->assertEqualsWithDelta(10.0, (float) $plain[0]['p'], 0.001);
        // an unknown type falls back to the plain query
        $this->assertCount(1, $this->products->product_monthly_use($pid, 'bogus'));
    }

    public function testUsageDetailListsTheLinesInTheRangeWithEventPetOwnerAndStockInfo(): void
    {
        $owner = Factory::owner(['last_name' => 'Usagetest']);
        $pet = Factory::pet($owner, ['name' => 'Fluffy']);
        $pid = Factory::product();
        $stock = Factory::stock($pid, ['lotnr' => 'UD-LOT', 'in_price' => 3.25, 'location' => '2', 'eol' => '2031-01-01']);
        $from = date('Y-m-d', strtotime('-10 days'));
        $to = date('Y-m-d', strtotime('-2 days'));
        $inside = $this->line($pid, $pet, date('Y-m-d 10:00:00', strtotime('-5 days')), 2.0, ['vet' => Fixtures::USER_VET, 'location' => 1], ['stock_id' => $stock]);
        $later = $this->line($pid, $pet, date('Y-m-d 11:00:00', strtotime('-4 days')), 1.0, ['vet' => Fixtures::USER_VET2, 'location' => 2]);
        $this->line($pid, $pet, date('Y-m-d 10:00:00', strtotime('-20 days')), 9.0);   // before
        $this->line($pid, $pet, date('Y-m-d 10:00:00', strtotime('-1 day')), 9.0);     // after
        [$other] = $this->usageFixture();
        $this->line($other, $pet, date('Y-m-d 10:00:00', strtotime('-5 days')), 9.0);  // other product

        $rows = $this->products->usage_detail($pid, $from, $to);

        // newest first
        $this->assertCount(2, $rows);
        $this->assertEqualsWithDelta(1.0, (float) $rows[0]['volume'], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $rows[1]['volume'], 0.001);
        $detail = $rows[1];
        $this->assertSame('Victor', $detail['first_name']);
        $this->assertSame('UD-LOT', $detail['lotnr']);
        $this->assertEqualsWithDelta(3.25, (float) $detail['in_price'], 0.001);
        $this->assertSame('Fluffy', $detail['petname']);
        $this->assertSame($pet, (int) $detail['pet_id']);
        $this->assertSame($owner, (int) $detail['id']);
        $this->assertSame('Usagetest', $detail['last_name']);
        $this->assertSame('Praktijk', $detail['event_location_name']);
        $this->assertSame('Wagen', $detail['stock_location_name']);
        // the line without a stock row still appears, with empty stock columns
        $this->assertNull($rows[0]['lotnr']);
        $this->assertSame('Wagen', $rows[0]['event_location_name']);
    }

    public function testUsageDetailRangeBoundariesAreStrict(): void
    {
        // PIN: events.created_at > "<from> 00:00" and < "<to> 23:59" (minutes, not seconds)
        [$pid, $pet] = $this->usageFixture();
        $day = date('Y-m-d', strtotime('-3 days'));
        $midnight = $this->line($pid, $pet, $day . ' 00:00:00', 1.0);
        $firstSecond = $this->line($pid, $pet, $day . ' 00:00:01', 2.0);
        $lateButInside = $this->line($pid, $pet, $day . ' 23:58:59', 4.0);
        $lastSeconds = $this->line($pid, $pet, $day . ' 23:59:30', 8.0);

        $volumes = array_map(static fn ($r) => (float) $r['volume'], $this->products->usage_detail($pid, $day, $day));

        sort($volumes);
        $this->assertSame([2.0, 4.0], $volumes); // 00:00:00 and 23:59:30 fall outside the one-day range
    }

    // ---------------------------------------------------------------- Product_type_model

    public function testNormalizeRootReturnsTheRootIdOnlyForAValidTopLevelParent(): void
    {
        $rootA = $this->type('RootA ' . Factory::unique());
        $rootB = $this->type('RootB ' . Factory::unique());
        $child = $this->type('Child ' . Factory::unique(), $rootA);

        // valid: an existing top-level type; for a new type (0) or a type without children
        $this->assertSame($rootA, $this->types->normalize_root($rootA));
        $this->assertSame($rootA, $this->types->normalize_root($rootA, $rootB));   // rootB has no children
        $this->assertSame($rootB, $this->types->normalize_root($rootB, $child));   // a child with no children of its own

        // null cases
        $this->assertNull($this->types->normalize_root(null));
        $this->assertNull($this->types->normalize_root(0));
        $this->assertNull($this->types->normalize_root(-5));
        $this->assertNull($this->types->normalize_root($rootA, $rootA));            // own parent
        $this->assertNull($this->types->normalize_root(250));                       // does not exist
        $this->assertNull($this->types->normalize_root($child));                    // parent is itself a child
        $this->assertNull($this->types->normalize_root($rootB, $rootA));            // rootA has children: cannot become a child
    }

    public function testGetTypeOptionsAddsDisplayNamesAndListsRootsBeforeChildren(): void
    {
        $key = Factory::unique();
        $rootB = $this->type('B-root ' . $key);
        $rootA = $this->type('A-root ' . $key);
        $childB = $this->type('Z-child ' . $key, $rootB);
        $childA = $this->type('M-child ' . $key, $rootA);

        $options = $this->types->get_type_options();
        $byId = array_column($options, null, 'id');

        $this->assertSame('A-root ' . $key, $byId[$rootA]['display_name']);
        $this->assertSame('M-child ' . $key . '', $byId[$childA]['name']);
        $this->assertSame('A-root ' . $key . ' / M-child ' . $key, $byId[$childA]['display_name']);
        $this->assertSame('B-root ' . $key . ' / Z-child ' . $key, $byId[$childB]['display_name']);

        // all roots come first (by name), then all children (by name)
        $order = $this->ids($options);
        $this->assertLessThan(array_search($rootB, $order, true), array_search($rootA, $order, true));
        $this->assertLessThan(array_search($childA, $order, true), array_search($rootB, $order, true));
        $this->assertLessThan(array_search($childB, $order, true), array_search($childA, $order, true));
        // seeded: Medicatie (root), Antibiotica (child of 1) shows as "Medicatie / Antibiotica"
        $this->assertSame('Medicatie / Antibiotica', $byId[3]['display_name']);
        $this->assertSame('Medicatie', $byId[1]['display_name']);
    }

    public function testGetDescendantIdsReturnsOnlyTheDirectChildren(): void
    {
        $root = $this->type('Root ' . Factory::unique());
        $c1 = $this->type('C1 ' . Factory::unique(), $root);
        $c2 = $this->type('C2 ' . Factory::unique(), $root);
        $grand = $this->type('Grand ' . Factory::unique(), $c1); // the database allows it, the UI does not

        $ids = $this->types->get_descendant_ids($root);
        $this->assertEqualsCanonicalizing([$c1, $c2], $ids);
        $this->assertNotContains($grand, $ids);   // PIN: one level only
        $this->assertSame([$grand], $this->types->get_descendant_ids($c1));
        $this->assertSame([], $this->types->get_descendant_ids($c2));
        foreach ($ids as $id) {
            $this->assertIsInt($id);
        }
    }

    // ---------------------------------------------------------------- Product_label_model

    private function labelsOf(int $pid): array
    {
        return array_map(static fn ($r) => (int) $r['id'], $this->labels->get_for_product($pid));
    }

    public function testReplaceProductLabelsDropsDuplicatesZerosAndUnknownIds(): void
    {
        $pid = Factory::product();
        $other = Factory::product();
        $l1 = Factory::insert('products_label', ['name' => 'lbl-a-' . Factory::unique(), 'created_at' => Factory::raw('NOW()')]);
        $l2 = Factory::insert('products_label', ['name' => 'lbl-b-' . Factory::unique(), 'created_at' => Factory::raw('NOW()')]);
        $l3 = Factory::insert('products_label', ['name' => 'lbl-c-' . Factory::unique(), 'created_at' => Factory::raw('NOW()')]);
        $this->assertTrue($this->labels->replace_product_labels($pid, [$l1]));
        $this->assertTrue($this->labels->replace_product_labels($other, [$l1, $l2]));

        // duplicates, a zero, a non-numeric string, an unknown id and numeric strings
        $this->assertTrue($this->labels->replace_product_labels($pid, [$l2, 0, (string) $l2, 'abc', 987654, (string) $l3, $l2]));

        $this->assertSame([$l2, $l3], $this->labelsOf($pid));  // old l1 is gone; ordered by label name
        $this->assertSame(2, $this->ci->db->where('product_id', $pid)->count_all_results('products_product_label'));
        $this->assertSame([$l1, $l2], $this->labelsOf($other)); // other product untouched
    }

    public function testReplaceProductLabelsWithNothingValidClearsTheLinks(): void
    {
        $pid = Factory::product();
        $l1 = Factory::insert('products_label', ['name' => 'lbl-x-' . Factory::unique(), 'created_at' => Factory::raw('NOW()')]);
        $this->labels->replace_product_labels($pid, [$l1]);
        $this->assertSame([$l1], $this->labelsOf($pid));

        // only invalid ids: everything is removed and nothing is inserted
        $this->assertTrue($this->labels->replace_product_labels($pid, [0, 987654]));
        $this->assertSame([], $this->labelsOf($pid));

        $this->labels->replace_product_labels($pid, [$l1]);
        $this->assertTrue($this->labels->replace_product_labels($pid, []));
        $this->assertSame([], $this->labelsOf($pid));
    }
}
