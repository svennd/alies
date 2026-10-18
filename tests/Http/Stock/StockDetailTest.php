<?php

declare(strict_types=1);

/**
 * T175 - Stock::stock_detail (IN_USE rows, or all states with the extra argument) and
 * Stock::get_product_stock (autocomplete for the move screen, JSON).
 */
final class StockDetailTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function productWithRows(string $name): int
    {
        $pid = Factory::product(['name' => $name]);
        Factory::stock($pid, ['lotnr' => 'DT-USE', 'state' => 1]);
        Factory::stock($pid, ['lotnr' => 'DT-CHECK', 'state' => 0]);
        Factory::stock($pid, ['lotnr' => 'DT-HIST', 'state' => 2, 'volume' => 0]);
        Factory::stock($pid, ['lotnr' => 'DT-ERR', 'state' => 3, 'volume' => -1]);

        return $pid;
    }

    public function testTheDetailPageShowsOnlyInUseRows(): void
    {
        $pid = $this->productWithRows('Detail Product');

        $page = $this->get('stock/stock_detail/' . $pid);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Detail Product');
        $this->assertSee($page, 'DT-USE');
        foreach (['DT-CHECK', 'DT-HIST', 'DT-ERR'] as $lot) {
            $this->assertDontSee($page, $lot);
        }
    }

    public function testTheShowAllArgumentListsEveryState(): void
    {
        $pid = $this->productWithRows('Detail All Product');

        $page = $this->get('stock/stock_detail/' . $pid . '/1');

        foreach (['DT-USE', 'DT-CHECK', 'DT-HIST', 'DT-ERR'] as $lot) {
            $this->assertSee($page, $lot);
        }
    }

    public function testAProductWithoutStockSaysSo(): void
    {
        $pid = Factory::product(['name' => 'Empty Detail']);

        $page = $this->get('stock/stock_detail/' . $pid);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'no stock found');
    }

    public function testTheAutocompleteReturnsTheInUseRowsOfTheLocation(): void
    {
        $prefix = 'Acomp' . substr(md5(uniqid()), 0, 6);
        $pid = Factory::product(['name' => $prefix . ' Drops', 'unit_sell' => 'ml']);
        $a = Factory::stock($pid, ['location' => '1', 'volume' => 5, 'lotnr' => 'AC-A', 'eol' => '2031-01-02']);
        $b = Factory::stock($pid, ['location' => '1', 'volume' => 8, 'lotnr' => 'AC-B', 'eol' => '2031-02-03']);
        Factory::stock($pid, ['location' => '2', 'volume' => 99, 'lotnr' => 'AC-OTHERLOC']);
        Factory::stock($pid, ['location' => '1', 'volume' => 77, 'lotnr' => 'AC-CHECK', 'state' => 0]);

        $response = $this->get('stock/get_product_stock/1', ['query' => $prefix]);

        $this->assertStatus($response, 200);
        $json = $this->json($response);
        $this->assertSame($prefix, $json['query']);
        $this->assertCount(1, $json['suggestions']);
        $s = $json['suggestions'][0];
        $this->assertSame($prefix . ' Drops', $s['value']);
        $this->assertSame($prefix . ' Drops', $s['data']['prod']);
        $this->assertSame('ml', $s['data']['unit']);
        $this->assertEqualsCanonicalizing([(string) $a, (string) $b], $s['data']['id']);
        $this->assertEqualsCanonicalizing(['AC-A', 'AC-B'], $s['data']['lotnr']);
        $this->assertEqualsCanonicalizing(['2031-01-02', '2031-02-03'], $s['data']['eol']);
        $this->assertEqualsWithDelta(13.0, array_sum(array_map('floatval', $s['data']['volume'])), 0.001);
        $this->assertCount(2, $s['data']['volume']);
    }

    public function testTheAutocompleteOtherLocationAndNoMatch(): void
    {
        $prefix = 'Loc' . substr(md5(uniqid()), 0, 6);
        $pid = Factory::product(['name' => $prefix . ' Gel']);
        Factory::stock($pid, ['location' => '2', 'lotnr' => 'AC-CAR']);

        $car = $this->json($this->get('stock/get_product_stock/2', ['query' => $prefix]));
        $this->assertCount(1, $car['suggestions']);
        $this->assertSame(['AC-CAR'], $car['suggestions'][0]['data']['lotnr']);

        $practice = $this->json($this->get('stock/get_product_stock/1', ['query' => $prefix]));
        $this->assertSame([], $practice['suggestions']);

        $none = $this->json($this->get('stock/get_product_stock/1', ['query' => 'zzzz-no-such-product']));
        $this->assertSame([], $none['suggestions']);
    }

    public function testAMissingQueryIsAServerError(): void
    {
        // pinned: the null query is passed to a string-typed model method (TypeError)
        $this->assertStatus($this->get('stock/get_product_stock/1'), 500);
    }
}
