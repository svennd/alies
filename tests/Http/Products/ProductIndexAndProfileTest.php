<?php

declare(strict_types=1);

/**
 * T180 - Products::index and Products::search_catalog.
 * T181 - Products::profile.
 *
 * Rules (from the code): the index lists the IN_USE stock rows of the user's location (or, for
 * `products/index/all`, one row per product summed over all locations). The catalogue search needs
 * at least 2 characters, matches products (name, wholesale_name, short_name) and procedures (name)
 * with LIKE %q%, and sorts: sellable products and procedures first, then non-sellable products;
 * inside a group "procedure" < "product" (strcmp) and then by name. The profile page saves a
 * non-empty POST `message` in products.comment.
 */
final class ProductIndexAndProfileTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    // ---- T180 index ------------------------------------------------------------------------

    public function testTheIndexListsTheStockOfTheUsersLocationOnly(): void
    {
        $here = Factory::product(['name' => Factory::unique('IdxHere')]);
        $there = Factory::product(['name' => Factory::unique('IdxThere')]);
        Factory::stock($here, ['location' => '1', 'volume' => 4]);
        Factory::stock($there, ['location' => '2', 'volume' => 4]);

        $page = $this->get('products');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->find('products', $here)['name']);
        $this->assertDontSee($page, $this->find('products', $there)['name']);
    }

    public function testTheAllViewListsEveryLocation(): void
    {
        $here = Factory::product(['name' => Factory::unique('AllHere')]);
        $there = Factory::product(['name' => Factory::unique('AllThere')]);
        Factory::stock($here, ['location' => '1']);
        Factory::stock($there, ['location' => '2']);

        $page = $this->get('products/index/all');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->find('products', $here)['name']);
        $this->assertSee($page, $this->find('products', $there)['name']);
    }

    public function testAnotherLocationCanBePassedInTheUrl(): void
    {
        $there = Factory::product(['name' => Factory::unique('UrlThere')]);
        Factory::stock($there, ['location' => '2']);

        $page = $this->get('products/index/2');

        $this->assertSee($page, $this->find('products', $there)['name']);
    }

    public function testStockInHistoryStateIsNotListed(): void
    {
        $p = Factory::product(['name' => Factory::unique('IdxHist')]);
        Factory::stock($p, ['location' => '1', 'state' => Fixtures::STATE_HISTORY, 'volume' => 0]);

        $this->assertDontSee($this->get('products'), $this->find('products', $p)['name']);
    }

    public function testTheIndexPageShowsTheSearchResultsForTheQueryString(): void
    {
        $name = Factory::unique('QsFind');
        Factory::product(['name' => $name]);

        $page = $this->get('products', ['search_query' => $name]);

        $this->assertSee($page, $name);
    }

    // ---- T180 search_catalog ---------------------------------------------------------------

    public function testTheCatalogueSearchReturnsJsonWithQueryCountAndHtml(): void
    {
        $name = Factory::unique('CatA');
        $pid = Factory::product(['name' => $name]);
        Factory::stock($pid, ['location' => '1', 'volume' => 3]);

        $response = $this->get('products/search_catalog', ['q' => $name]);

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/json', $response->getHeaderLine('Content-Type'));
        $json = $this->json($response);
        $this->assertSame($name, $json['query']);
        $this->assertSame(1, $json['count']);
        $this->assertStringContainsString($name, $json['html']);
        $this->assertStringContainsString('products/profile/' . $pid, $json['html']);
    }

    public function testCountCoversProductsAndProcedures(): void
    {
        $key = Factory::unique('Both');
        Factory::product(['name' => $key . ' product']);
        Factory::procedure(['name' => $key . ' procedure']);

        $json = $this->json($this->get('products/search_catalog', ['q' => $key]));

        $this->assertSame(2, $json['count']);
        $this->assertStringContainsString($key . ' product', $json['html']);
        $this->assertStringContainsString($key . ' procedure', $json['html']);
    }

    public function testAShortQueryReturnsNothing(): void
    {
        // positive control: the 2 character prefix of a seeded product name works
        $short = $this->json($this->get('products/search_catalog', ['q' => 'A']));
        $this->assertSame(0, $short['count']);
        $this->assertSame('A', $short['query']);

        $empty = $this->json($this->get('products/search_catalog'));
        $this->assertSame(0, $empty['count']);

        $two = $this->json($this->get('products/search_catalog', ['q' => 'Am']));
        $this->assertGreaterThanOrEqual(1, $two['count']);
        $this->assertStringContainsString('Amoxi Tabs', $two['html']);
    }

    public function testTheQueryIsTrimmed(): void
    {
        $name = Factory::unique('Trim');
        Factory::product(['name' => $name]);

        $json = $this->json($this->get('products/search_catalog', ['q' => '  ' . $name . ' ']));

        $this->assertSame($name, $json['query']);
        $this->assertSame(1, $json['count']);
    }

    public function testDeletedProductsAreNotFoundButNonSellableOnesAre(): void
    {
        $key = Factory::unique('Vis');
        Factory::product(['name' => $key . ' gone', 'deleted_at' => Factory::raw('NOW()')]);
        Factory::product(['name' => $key . ' hidden', 'sellable' => 0]);

        $json = $this->json($this->get('products/search_catalog', ['q' => $key]));

        $this->assertSame(1, $json['count']);
        $this->assertStringContainsString($key . ' hidden', $json['html']);
        $this->assertStringNotContainsString($key . ' gone', $json['html']);
    }

    public function testProductsAreAlsoFoundByShortNameAndWholesaleName(): void
    {
        $short = Factory::unique('shrt');
        $wh = Factory::unique('whn');
        Factory::product(['name' => Factory::unique('ByShort'), 'short_name' => $short]);
        Factory::product(['name' => Factory::unique('ByWh'), 'wholesale_name' => $wh]);

        $this->assertSame(1, $this->json($this->get('products/search_catalog', ['q' => $short]))['count']);
        $this->assertSame(1, $this->json($this->get('products/search_catalog', ['q' => $wh]))['count']);
    }

    public function testNonSellableProductsAreSortedLast(): void
    {
        $key = Factory::unique('Srt');
        // names chosen so that plain alphabetical order would put the non-sellable product first
        Factory::product(['name' => $key . ' A not sellable', 'sellable' => 0]);
        Factory::product(['name' => $key . ' Z sellable']);
        Factory::procedure(['name' => $key . ' M procedure']);

        $html = $this->json($this->get('products/search_catalog', ['q' => $key]))['html'];

        $procedure = strpos($html, $key . ' M procedure');
        $sellable = strpos($html, $key . ' Z sellable');
        $notSellable = strpos($html, $key . ' A not sellable');
        $this->assertNotFalse($procedure);
        $this->assertNotFalse($sellable);
        $this->assertNotFalse($notSellable);
        // type order inside the sellable group: "procedure" < "product"
        $this->assertLessThan($sellable, $procedure);
        $this->assertLessThan($notSellable, $sellable);
    }

    public function testTheResultsAreCappedAtThirtyProducts(): void
    {
        $key = Factory::unique('Cap');
        for ($i = 0; $i < 32; $i++) {
            Factory::product(['name' => sprintf('%s item %02d', $key, $i), 'price' => null]);
        }

        $json = $this->json($this->get('products/search_catalog', ['q' => $key]));

        $this->assertSame(30, $json['count']);
        $this->assertStringContainsString($key . ' item 00', $json['html'], 'ordered by name, so the first ones stay');
        $this->assertStringNotContainsString($key . ' item 31', $json['html']);
    }

    public function testTheSearchShowsLocalAndGlobalStock(): void
    {
        $name = Factory::unique('Stk');
        $pid = Factory::product(['name' => $name, 'unit_sell' => 'tabx']);
        Factory::stock($pid, ['location' => '1', 'volume' => 3]);
        Factory::stock($pid, ['location' => '2', 'volume' => 4.5]);
        // not in use: does not count
        Factory::stock($pid, ['location' => '1', 'volume' => 100, 'state' => Fixtures::STATE_HISTORY]);

        $html = $this->json($this->get('products/search_catalog', ['q' => $name]))['html'];

        // local 3 (location 1), global 3 + 4.5 = 7.5
        $this->assertMatchesRegularExpression('~3\s*/\s*7\.5\s*tabx~', preg_replace('/\s+/', ' ', $html));
    }

    public function testOnlyAdminsGetEditLinksInTheSearchResults(): void
    {
        $name = Factory::unique('Edl');
        $pid = Factory::product(['name' => $name]);

        $vet = $this->json($this->get('products/search_catalog', ['q' => $name]))['html'];
        $this->assertStringNotContainsString('products/product/' . $pid, $vet);

        $this->cookies->clear();
        $this->loginAs('admin');
        $admin = $this->json($this->get('products/search_catalog', ['q' => $name]))['html'];
        $this->assertStringContainsString('products/product/' . $pid, $admin);
    }

    // ---- T181 profile ----------------------------------------------------------------------

    public function testTheProfileShowsPricesAndStock(): void
    {
        $page = $this->get('products/profile/' . Fixtures::PRODUCT_TIERED);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Amoxi Tabs');
        // seeded tiers 1: 10.00, 5: 8.00, 10: 6.00
        $this->assertSee($page, '10.00');
        $this->assertSee($page, '8.00');
        $this->assertSee($page, '6.00');
        // IN_USE rows: AMX-001 (100 + 20) and AMX-OLD (5) show up; the ERROR row does not
        $this->assertSee($page, 'AMX-001');
        $this->assertSee($page, 'AMX-OLD');
        $this->assertDontSee($page, 'AMX-ERR');
    }

    public function testTheProfileCalculatesLocalAndGlobalStock(): void
    {
        $pid = Factory::product(['name' => Factory::unique('ProfStock'), 'unit_sell' => 'zzu']);
        Factory::stock($pid, ['location' => '1', 'volume' => 6]);
        Factory::stock($pid, ['location' => '2', 'volume' => 9]);
        Factory::stock($pid, ['location' => '1', 'volume' => 50, 'state' => Fixtures::STATE_ERROR]);

        $text = preg_replace('/\s+/', ' ', $this->text($this->get('products/profile/' . $pid)));

        // global 6 + 9 = 15 (the ERROR row does not count); a table row per IN_USE stock row
        $this->assertStringContainsString('15 zzu', $text);
        $this->assertStringContainsString('6.00 zzu', $text);
        $this->assertStringContainsString('9.00 zzu', $text);
        $this->assertStringNotContainsString('50.00 zzu', $text);
    }

    public function testTheProfileShowsTheUsageOfThirtyOneHundredEightyAndThreeSixtyFiveDays(): void
    {
        $pid = Factory::product(['name' => Factory::unique('ProfUse')]);
        $event = Factory::event(Fixtures::PET_REX, 1, 1);
        Factory::eventProduct($event, $pid, 2, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 10 DAY)')]);
        Factory::eventProduct($event, $pid, 30, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 100 DAY)')]);
        Factory::eventProduct($event, $pid, 400, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 300 DAY)')]);
        Factory::eventProduct($event, $pid, 5000, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 500 DAY)')]);

        $text = preg_replace('/\s+/', ' ', $this->text($this->get('products/profile/' . $pid)));

        // 30d: 2, 180d: 32, 365d: 432; the 500 days old line (5000) is in none of them
        $this->assertMatchesRegularExpression('/(?<![\d.])2 stuk/', $text);
        $this->assertMatchesRegularExpression('/(?<![\d.])32 stuk/', $text);
        $this->assertMatchesRegularExpression('/(?<![\d.])432 stuk/', $text);
        $this->assertStringNotContainsString('5000', $text);
    }

    public function testAMessageIsStoredAsTheProductComment(): void
    {
        $pid = Factory::product(['comment' => '']);
        $note = Factory::unique('note');

        $page = $this->post('products/profile/' . $pid, ['message' => $note]);

        $this->assertStatus($page, 200);
        $this->assertSame($note, $this->find('products', $pid)['comment']);
        $this->assertSee($page, $note);
    }

    public function testAnEmptyMessageLeavesTheCommentAlone(): void
    {
        $pid = Factory::product(['comment' => 'keep me']);

        $this->post('products/profile/' . $pid, ['message' => '']);
        $this->get('products/profile/' . $pid);

        $this->assertSame('keep me', $this->find('products', $pid)['comment']);
    }
}
