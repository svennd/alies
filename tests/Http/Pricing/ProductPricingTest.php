<?php

declare(strict_types=1);

/**
 * T190 - Pricing::prod, rm_prod_price, man (price tiers of a product).
 *
 * Pricing is an Accounting_Controller: admin and accounting only (a vet gets a 500, see AccountingRoleTest).
 * `pricing/prod` without id lists the sellable products. A POST with `submit` to `pricing/prod/{pid}`
 * deletes ALL tiers of the product and inserts the pairs volume[i]/price[i], skipping pairs where either is
 * 0 or empty. The tiers that were saved are written to price_log as a JSON object {volume: price}, the
 * values as the strings that were posted. With accept_wholesale the linked wholesale row gets
 * last_bruto = bruto and last_bruto_date = today.
 */
final class ProductPricingTest extends HttpTestCase
{
    private function tiers(int $pid): array
    {
        $out = [];
        foreach ($this->rows('SELECT volume, price FROM products_price WHERE product_id = ? ORDER BY volume', [$pid]) as $r) {
            $out[(string) (float) $r['volume']] = (float) $r['price'];
        }

        return $out;
    }

    public function testTheListShowsSellableProductsOnly(): void
    {
        $this->loginAs('acc');
        $sellable = Factory::product(['name' => Factory::unique('PrcSell')]);
        $hidden = Factory::product(['name' => Factory::unique('PrcHide'), 'sellable' => 0]);
        $deleted = Factory::product(['name' => Factory::unique('PrcGone'), 'deleted_at' => Factory::raw('NOW()')]);

        $page = $this->get('pricing/prod');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->find('products', $sellable)['name']);
        $this->assertSee($page, 'pricing/prod/' . $sellable);
        $this->assertDontSee($page, $this->find('products', $hidden)['name']);
        $this->assertDontSee($page, $this->find('products', $deleted)['name']);
        $this->assertSee($page, 'Amoxi Tabs');
    }

    public function testTheDetailPageShowsTheTiers(): void
    {
        $this->loginAs('admin');

        $page = $this->get('pricing/prod/' . Fixtures::PRODUCT_TIERED);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Amoxi Tabs');
        $this->assertSee($page, '10.00');
        $this->assertSee($page, '8.00');
        $this->assertSee($page, '6.00');
    }

    public function testSavingReplacesTheTiersAndSkipsZeroPairs(): void
    {
        $pid = Factory::product(['price' => null]);
        Factory::productPrice($pid, 1.0, 99.0);
        Factory::productPrice($pid, 3.0, 98.0);
        $this->loginAs('acc');

        $page = $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1', '5', '0'], 'price' => ['10', '8', '7']]);

        $this->assertStatus($page, 200);
        $this->assertSame(['1' => 10.0, '5' => 8.0], $this->tiers($pid), 'old tiers gone, the 0 volume pair skipped');
    }

    public function testAPairWithAnEmptyOrZeroPriceIsSkippedToo(): void
    {
        $pid = Factory::product(['price' => null]);
        $this->loginAs('admin');

        $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1', '2', '3', ''], 'price' => ['4.5', '0', '', '9']]);

        $this->assertSame(['1' => 4.5], $this->tiers($pid));
    }

    public function testSavingWritesThePriceLog(): void
    {
        $pid = Factory::product(['price' => null]);
        $this->loginAs('acc');

        $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1', '5', '0'], 'price' => ['10', '8', '7']]);

        $logs = $this->rows('SELECT * FROM price_log WHERE product_id = ?', [$pid]);
        $this->assertCount(1, $logs);
        // the keys and values are the posted strings, in posted order
        $this->assertSame('{"1":"10","5":"8"}', $logs[0]['log']);
        $this->assertSame(['1' => '10', '5' => '8'], json_decode($logs[0]['log'], true));
    }

    public function testTheLastPriceLogsAreShownOnThePage(): void
    {
        $pid = Factory::product(['price' => null]);
        $this->loginAs('acc');

        $page = $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1'], 'price' => ['13.37']]);

        $this->assertSee($page, '13.37');
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testSavingWithoutAnyValidPairRemovesTheTiersAndLogsNull(): void
    {
        // KNOWN BUG: $log is only defined once a pair was saved; with none the log row contains "null"
        // (and PHP raises an undefined variable notice). Decide in CI4 whether to keep.
        $pid = Factory::product(['price' => 5]);
        $this->loginAs('admin');

        $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['0'], 'price' => ['0']]);

        $this->assertSame([], $this->tiers($pid));
        $this->assertSame('null', $this->scalar('SELECT log FROM price_log WHERE product_id = ?', [$pid]));
    }

    public function testAcceptingTheWholesalePriceCopiesBrutoToLastBruto(): void
    {
        $pid = Factory::product(['price' => null]);
        $wh = Factory::wholesale(['bruto' => 12.00, 'last_bruto' => 10.00, 'last_bruto_date' => '2020-01-01']);
        $other = Factory::wholesale(['bruto' => 7.00, 'last_bruto' => 5.00]);
        $this->loginAs('acc');

        $this->post('pricing/prod/' . $pid, [
            'submit' => 1, 'volume' => ['1'], 'price' => ['20'], 'accept_wholesale' => '1', 'wholesale' => (string) $wh,
        ]);

        $w = $this->find('wholesale', $wh);
        $this->assertEqualsWithDelta(12.0, (float) $w['last_bruto'], 0.001);
        $this->assertSame(date('Y-m-d'), $w['last_bruto_date']);
        $this->assertEqualsWithDelta(5.0, (float) $this->find('wholesale', $other)['last_bruto'], 0.001, 'other rows are untouched');
    }

    public function testWithoutAcceptTheWholesaleRowIsLeftAlone(): void
    {
        $pid = Factory::product(['price' => null]);
        $wh = Factory::wholesale(['bruto' => 12.00, 'last_bruto' => 10.00]);
        $this->loginAs('acc');

        $this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1'], 'price' => ['20'], 'wholesale' => (string) $wh]);

        $this->assertEqualsWithDelta(10.0, (float) $this->find('wholesale', $wh)['last_bruto'], 0.001);
    }

    public function testRemovingATierRedirectsToTheProduct(): void
    {
        $pid = Factory::product(['price' => null]);
        $keep = Factory::productPrice($pid, 1.0, 5.0);
        $drop = Factory::productPrice($pid, 5.0, 4.0);
        $this->loginAs('acc');
        $this->db()->exec('DELETE FROM log');

        $response = $this->get('pricing/rm_prod_price/' . $drop);

        $this->assertRedirectsTo($response, 'pricing/prod/' . $pid);
        $this->assertNull($this->find('products_price', $drop));
        $this->assertNotNull($this->find('products_price', $keep));
        $this->assertSame(1, $this->logCount('rm_price'));
    }

    public function testRemovingAnUnknownTierRedirectsToTheList(): void
    {
        $this->loginAs('acc');
        $this->db()->exec('DELETE FROM log');

        $response = $this->get('pricing/rm_prod_price/99999999');

        $this->assertRedirectsTo($response, 'pricing/prod/0');
        $this->assertSame(1, $this->logCount('failed_to_rm_price'));
    }

    public function testTheManualBuyPriceIsStored(): void
    {
        $pid = Factory::product(['buy_price' => 1.00]);
        $other = Factory::product(['buy_price' => 2.00]);
        $this->loginAs('acc');

        $response = $this->post('pricing/man/' . $pid, ['buy_price' => '17.25', 'buy_price_date' => '2025-02-03']);

        $this->assertRedirectsTo($response, 'pricing/prod/' . $pid);
        $p = $this->find('products', $pid);
        $this->assertEqualsWithDelta(17.25, (float) $p['buy_price'], 0.001);
        $this->assertSame('2025-02-03', $p['buy_price_date']);
        $this->assertEqualsWithDelta(2.0, (float) $this->find('products', $other)['buy_price'], 0.001);
    }

    public function testAVetCannotChangeTiers(): void
    {
        $pid = Factory::product(['price' => 10]);
        $this->loginAs('vet');

        $this->assertStatus($this->post('pricing/prod/' . $pid, ['submit' => 1, 'volume' => ['1'], 'price' => ['99']]), 500);
        $this->assertStatus($this->post('pricing/man/' . $pid, ['buy_price' => '99', 'buy_price_date' => '2025-01-01']), 500);

        $this->assertSame(['1' => 10.0], $this->tiers($pid));
        $this->assertEqualsWithDelta(1.0, (float) $this->find('products', $pid)['buy_price'], 0.001);
    }
}
