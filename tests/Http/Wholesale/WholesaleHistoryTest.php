<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T212 - Wholesale::get_history, ajax_get_articles, ajax_get_history. Admin only.
 *
 * Rules: the history page shows one wholesale article with its automatic deliveries (table
 * delivery), earlier prices (wholesale_price, matched on vendor_id = art_nr) and the manual
 * register_in rows of the internal product that is linked to it. ajax_get_articles returns
 * {"results":[{id,text,distr,bruto,vhb,cnk}]} (25 rows without a term) and an EMPTY body when
 * nothing matches. ajax_get_history returns the article with its deliveries as JSON, but only
 * for AJAX requests; otherwise the body is empty.
 */
final class WholesaleHistoryTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function delivery(int $wholesaleId, string $vendorId, array $overrides = []): int
    {
        return Factory::insert('delivery', array_merge([
            'order_date' => date('Y-m-d', strtotime('-9 days')),
            'order_nr' => 1,
            'my_ref' => 'REF',
            'wholesale_artnr' => $vendorId,
            'wholesale_id' => $wholesaleId,
            'CNK' => 1234500,
            'delivery_date' => date('Y-m-d', strtotime('-7 days')),
            'delivery_nr' => 777,
            'bruto_price' => 10.00,
            'netto_price' => 8.50,
            'amount' => 5,
            'lotnr' => Factory::unique('LOT'),
            'due_date' => date('Y-m-d', strtotime('+1 year')),
            'btw' => 6,
            'billing' => 'Test',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));
    }

    public function testNonAdminsAreSentHome(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('wholesale/get_history/5001'), '');
        $this->assertRedirectsTo($this->get('wholesale/ajax_get_articles'), '');
        $this->assertRedirectsTo($this->ajaxGet('wholesale/ajax_get_history/5001'), '');

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('wholesale/get_history/5001'), 200);
    }

    public function testHistoryShowsTheArticleItsPriceHistoryAndItsDeliveries(): void
    {
        $vendor = Factory::unique('HV');
        $wh = Factory::wholesale([
            'vendor_id' => $vendor, 'description' => 'History article ' . $vendor, 'bruto' => 12.00,
            'last_bruto' => 10.00, 'sell_price' => 16.00, 'btw' => 21, 'distributor' => 'DistriHist', 'CNK' => '5550001',
        ]);
        Factory::insert('wholesale_price', ['art_nr' => $vendor, 'bruto' => 9.25, 'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))]);
        Factory::insert('wholesale_price', ['art_nr' => 'OTHER-' . $vendor, 'bruto' => 77.77, 'created_at' => date('Y-m-d H:i:s')]);
        $this->delivery($wh, $vendor, ['delivery_nr' => 424242, 'lotnr' => 'AUTO-LOT-1', 'netto_price' => 8.75]);
        $this->delivery(5001, 'W-1001', ['delivery_nr' => 313131, 'lotnr' => 'FOREIGN-LOT']);

        $response = $this->get('wholesale/get_history/' . $wh);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'History article ' . $vendor);
        $this->assertSee($response, '(#' . $vendor . ')', 'unlinked: the header shows the vendor id');
        $this->assertSee($response, 'DistriHist');
        $this->assertSee($response, '5550001');
        $this->assertSee($response, '21 %');
        $this->assertSee($response, '16.00');
        // price change 10.00 --> 12.00, 12 / 10 * 100 - 100 = 20 %
        $this->assertSee($response, '10.00 --> 12.00');
        $this->assertSee($response, '20%');
        // earlier price of this article only
        $this->assertSee($response, '9.25');
        $this->assertDontSee($response, '77.77');
        // deliveries of this article only
        $this->assertSee($response, '424242');
        $this->assertSee($response, 'AUTO-LOT-1');
        $this->assertSee($response, '8.75');
        $this->assertDontSee($response, '313131');
        $this->assertDontSee($response, 'FOREIGN-LOT');
    }

    public function testAnUnchangedPriceShowsNoArrowAndNoPercentage(): void
    {
        $wh = Factory::wholesale(['bruto' => 10.00, 'last_bruto' => 10.00]);

        $response = $this->get('wholesale/get_history/' . $wh);

        $this->assertDontSee($response, '10.00 --> 10.00');
        $this->assertSee($response, 'Deliveries (automatic)');
    }

    public function testADecreaseIsShownWithANegativePercentage(): void
    {
        // 8 / 10 * 100 - 100 = -20
        $wh = Factory::wholesale(['bruto' => 8.00, 'last_bruto' => 10.00]);

        $response = $this->get('wholesale/get_history/' . $wh);

        $this->assertSee($response, '10.00 --> 8.00');
        $this->assertSee($response, '-20%');
    }

    public function testALinkedArticleShowsTheProductAndItsManualDeliveries(): void
    {
        $vendor = Factory::unique('HM');
        $wh = Factory::wholesale(['vendor_id' => $vendor, 'description' => 'Linked wholesale text']);
        $name = Factory::unique('LinkedProd');
        $pid = Factory::product(['name' => $name, 'wholesale' => $wh]);
        $otherPid = Factory::product(['name' => Factory::unique('OtherProd')]);
        $this->insertRegisterIn($pid, 'MAN-LOT-1', 'Supplier One', 3.21);
        $this->insertRegisterIn($otherPid, 'OTHER-MAN-LOT', 'Supplier Two', 9.99);

        $response = $this->get('wholesale/get_history/' . $wh);

        $this->assertSee($response, $name);
        $this->assertStringContainsString('products/profile/' . $pid, (string) $response->getBody());
        $this->assertSee($response, 'Linked wholesale text');
        $this->assertSee($response, 'Deliveries (manual)');
        $this->assertSee($response, 'MAN-LOT-1');
        $this->assertSee($response, 'Supplier One');
        $this->assertSee($response, '3.21');
        $this->assertDontSee($response, 'OTHER-MAN-LOT');
        // the manual delivery shows the slip's date (seed delivery slip 1: 7 days ago)
        $this->assertSee($response, date('Y-m-d', strtotime('-7 days')));
    }

    public function testAnUnlinkedArticleHasNoManualDeliveries(): void
    {
        $wh = Factory::wholesale();
        $this->assertGreaterThan(0, $this->scalar('SELECT COUNT(*) FROM register_in'), 'seed has manual rows');

        $response = $this->get('wholesale/get_history/' . $wh);

        $this->assertSee($response, 'Deliveries (manual)');
        $this->assertDontSee($response, 'AMX-001');
        $this->assertDontSee($response, 'VAC-001');
    }

    #[Group('known-bug')]
    public function testTheVhbCodeIsNeverShown(): void
    {
        // KNOWN BUG: views/wholesale/history.php tests !empty($date['VHB']) (typo for $data), so the VHB row is never printed.
        $this->assertSame('VHB-0001', $this->find('wholesale', 5001)['VHB']);

        $this->assertDontSee($this->get('wholesale/get_history/5001'), 'VHB-0001');
    }

    private function insertRegisterIn(int $product, string $lot, string $supplier, float $price): int
    {
        return Factory::insert('register_in', [
            'product' => $product, 'volume' => 2.00, 'eol' => date('Y-m-d', strtotime('+1 year')),
            'in_price' => $price, 'cat_price' => $price, 'lotnr' => $lot, 'supplier' => $supplier, 'delivery_slip' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ---- ajax_get_articles ------------------------------------------------------------------

    public function testArticlesAreReturnedWithTheDocumentedKeys(): void
    {
        $token = Factory::unique('Art');
        $wh = Factory::wholesale([
            'description' => 'Findme ' . $token, 'distributor' => 'DistriAjax', 'bruto' => 12.34, 'VHB' => 'VHB-AJ', 'CNK' => '7770001',
        ]);

        $response = $this->get('wholesale/ajax_get_articles', ['term' => $token]);

        $this->assertStatus($response, 200);
        $data = $this->json($response);
        $this->assertCount(1, $data['results']);
        $row = $data['results'][0];
        $this->assertSame(['id', 'text', 'distr', 'bruto', 'vhb', 'cnk'], array_keys($row));
        $this->assertSame($wh, (int) $row['id']);
        $this->assertSame('Findme ' . $token, $row['text']);
        $this->assertSame('DistriAjax', $row['distr']);
        $this->assertEqualsWithDelta(12.34, (float) $row['bruto'], 0.001);
        $this->assertSame('VHB-AJ', $row['vhb']);
        $this->assertSame('7770001', (string) $row['cnk']);
    }

    public function testTheTermMatchesAnywhereInTheDescription(): void
    {
        $token = Factory::unique('Mid');
        Factory::wholesale(['description' => 'Before ' . $token . ' after']);

        $data = $this->json($this->get('wholesale/ajax_get_articles', ['term' => $token]));

        $this->assertCount(1, $data['results']);
    }

    public function testWithoutATermAtMostTwentyFiveArticlesAreReturned(): void
    {
        for ($i = 0; $i < 26; $i++) {
            Factory::wholesale();
        }
        $this->assertGreaterThan(25, (int) $this->scalar('SELECT COUNT(*) FROM wholesale'));

        $data = $this->json($this->get('wholesale/ajax_get_articles'));

        $this->assertCount(25, $data['results']);
    }

    public function testNoMatchGivesAnEmptyBodyNotJson(): void
    {
        $response = $this->get('wholesale/ajax_get_articles', ['term' => Factory::unique('NoSuchArticle')]);

        $this->assertStatus($response, 200);
        $this->assertSame('', trim((string) $response->getBody()));
    }

    // ---- ajax_get_history -------------------------------------------------------------------

    public function testAjaxHistoryReturnsTheArticleWithItsDeliveriesOnly(): void
    {
        $vendor = Factory::unique('AH');
        $wh = Factory::wholesale(['vendor_id' => $vendor, 'description' => 'Ajax history']);
        $this->delivery($wh, $vendor, ['lotnr' => 'AJ-LOT-1', 'amount' => 4, 'bruto_price' => 11.00, 'netto_price' => 9.00]);
        $this->delivery($wh, $vendor, ['lotnr' => 'AJ-LOT-2']);
        $this->delivery(5001, 'W-1001', ['lotnr' => 'AJ-FOREIGN']);

        $response = $this->ajaxGet('wholesale/ajax_get_history/' . $wh);

        $data = $this->json($response);
        $this->assertSame($wh, (int) $data['id']);
        $this->assertSame('Ajax history', $data['description']);
        $this->assertCount(2, $data['deliveries']);
        $lots = array_column($data['deliveries'], 'lotnr');
        sort($lots);
        $this->assertSame(['AJ-LOT-1', 'AJ-LOT-2'], $lots);
        $this->assertNotContains('AJ-FOREIGN', $lots);
        // only the selected fields
        $first = $data['deliveries'][0];
        foreach (['delivery_date', 'bruto_price', 'netto_price', 'amount', 'lotnr', 'due_date'] as $field) {
            $this->assertArrayHasKey($field, $first);
        }
        $this->assertArrayNotHasKey('delivery_nr', $first);
        $this->assertArrayNotHasKey('billing', $first);
    }

    public function testAjaxHistoryWithoutTheAjaxHeaderIsEmpty(): void
    {
        $response = $this->get('wholesale/ajax_get_history/5001');

        $this->assertStatus($response, 200);
        $this->assertSame('', trim((string) $response->getBody()));
        // positive control: same URL with the header answers
        $this->assertSame(5001, (int) $this->json($this->ajaxGet('wholesale/ajax_get_history/5001'))['id']);
    }

    public function testAjaxHistoryOfAnUnknownArticleIsTheJsonValueFalse(): void
    {
        $response = $this->ajaxGet('wholesale/ajax_get_history/99999999');

        $this->assertSame('false', trim((string) $response->getBody()));
    }
}
