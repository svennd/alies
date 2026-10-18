<?php

declare(strict_types=1);

/**
 * T210 - Wholesale::index, Wholesale::unlinked (Wholesale_model::search_index, get_index_stats,
 * get_recent_index_items, get_unlinked_items). Admin only.
 *
 * Rules: without a search term the page shows the counters and the 5 most recently updated
 * wholesale rows; with ?search= it lists matches on description, vendor id, CNK, VHB, distributor
 * and the name of the linked internal product. "Unlinked" means no product points to the row.
 */
final class WholesaleIndexTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testNonAdminsAreSentHome(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('wholesale/index'), '');
        $this->assertRedirectsTo($this->get('wholesale/unlinked'), '');

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('wholesale/index'), 200);
    }

    public function testTheCountersMatchTheDatabase(): void
    {
        Factory::wholesale(['description' => Factory::unique('CountMe')]);
        $total = (int) $this->scalar('SELECT COUNT(*) FROM wholesale');
        $unlinked = (int) $this->scalar('SELECT COUNT(*) FROM wholesale w WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.wholesale = w.id)');

        $response = $this->get('wholesale/index');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertMatchesRegularExpression('#Wholesale records</div>\s*<div class="h2 mb-0">' . $total . '</div>#', $body);
        $this->assertMatchesRegularExpression('#Not linked</div>\s*<div class="font-weight-bold mb-2">\s*<a [^>]*>' . $unlinked . '</a>#', $body);
    }

    public function testWithoutASearchTheFiveMostRecentlyUpdatedRowsAreListed(): void
    {
        $names = [];
        for ($i = 1; $i <= 6; $i++) {
            $names[$i] = Factory::unique('Recent');
            Factory::wholesale([
                'description' => $names[$i],
                'updated_at' => Factory::raw('DATE_ADD(NOW(), INTERVAL ' . (10 + $i) . ' HOUR)'),
            ]);
        }

        $response = $this->get('wholesale/index');

        $this->assertSee($response, 'Last 5 products');
        for ($i = 2; $i <= 6; $i++) {
            $this->assertSee($response, $names[$i]);
        }
        $this->assertDontSee($response, $names[1], 'the sixth most recent is cut off');
        $this->assertDontSee($response, 'Results for');
    }

    public function testSearchFindsByDescription(): void
    {
        $token = Factory::unique('Desc');
        Factory::wholesale(['description' => 'Needle ' . $token]);
        Factory::wholesale(['description' => 'Haystack ' . Factory::unique('Other')]);

        $response = $this->get('wholesale/index', ['search' => $token]);

        $this->assertSee($response, 'Results for "' . $token . '"');
        $this->assertSee($response, '1 result(s)');
        $this->assertSee($response, 'Needle ' . $token);
        $this->assertDontSee($response, 'Haystack');
        $this->assertDontSee($response, 'Last 5 products');
    }

    public function testSearchFindsByVendorIdCnkVhbAndDistributor(): void
    {
        $vendor = Factory::unique('VND');
        $byVendor = Factory::wholesale(['vendor_id' => $vendor, 'description' => 'ByVendor ' . Factory::unique('d')]);
        $byCnk = Factory::wholesale(['CNK' => '9876501', 'description' => 'ByCnk ' . Factory::unique('d')]);
        $byVhb = Factory::wholesale(['VHB' => 'VHB-ZZ-77', 'description' => 'ByVhb ' . Factory::unique('d')]);
        $byDist = Factory::wholesale(['distributor' => 'Zebra Pharma', 'description' => 'ByDist ' . Factory::unique('d')]);

        $this->assertSee($this->get('wholesale/index', ['search' => $vendor]), 'ByVendor');
        $this->assertSee($this->get('wholesale/index', ['search' => '9876501']), 'ByCnk');
        $this->assertSee($this->get('wholesale/index', ['search' => 'VHB-ZZ-77']), 'ByVhb');
        $this->assertSee($this->get('wholesale/index', ['search' => 'Zebra Pharma']), 'ByDist');

        $r = $this->get('wholesale/index', ['search' => '9876501']);
        $this->assertDontSee($r, 'ByVendor');
        $this->assertDontSee($r, 'ByVhb');
        $this->assertSame(1, (int) $this->scalar('SELECT COUNT(*) FROM wholesale WHERE CNK = ?', ['9876501']));
        $this->assertGreaterThan(0, $byVendor + $byCnk + $byVhb + $byDist);
    }

    public function testSearchFindsByTheNameOfTheLinkedProductAndShowsTheLink(): void
    {
        $wh = Factory::wholesale(['description' => 'Plain wholesale text ' . Factory::unique('d')]);
        $productName = Factory::unique('InternalName');
        $pid = Factory::product(['name' => $productName, 'wholesale' => $wh]);

        $response = $this->get('wholesale/index', ['search' => $productName]);

        $this->assertSee($response, 'Plain wholesale text');
        $this->assertSee($response, $productName);
        $this->assertStringContainsString('products/profile/' . $pid, (string) $response->getBody());
        $this->assertStringContainsString('wholesale/get_history/' . $wh, (string) $response->getBody());
    }

    public function testSearchWithoutMatchesSaysSo(): void
    {
        $term = Factory::unique('NothingLikeThis');
        $response = $this->get('wholesale/index', ['search' => $term]);

        $this->assertSee($response, 'No wholesale records found for "' . $term . '".');
        $this->assertSee($response, '0 result(s)');
    }

    public function testASearchTermIsEscapedInThePage(): void
    {
        $response = $this->get('wholesale/index', ['search' => '<b>xx</b>']);

        $this->assertStatus($response, 200);
        $this->assertStringNotContainsString('<b>xx</b>', (string) $response->getBody());
        $this->assertStringContainsString('&lt;b&gt;xx&lt;/b&gt;', (string) $response->getBody());
    }

    public function testBlankSearchIsTreatedAsNoSearch(): void
    {
        $response = $this->get('wholesale/index', ['search' => '   ']);

        $this->assertSee($response, 'Last 5 products');
        $this->assertDontSee($response, 'Results for');
    }

    public function testUnlinkedListsOnlyRowsWithoutAProduct(): void
    {
        $free = Factory::wholesale(['description' => 'FreeRow ' . Factory::unique('d')]);
        $linked = Factory::wholesale(['description' => 'LinkedRow ' . Factory::unique('d')]);
        Factory::product(['wholesale' => $linked]);

        $response = $this->get('wholesale/unlinked');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'FreeRow');
        $this->assertDontSee($response, 'LinkedRow');
        $this->assertStringContainsString('wholesale/get_history/' . $free, (string) $response->getBody());
        // seeded: 5001 is linked to product 4001, 5002 and 5003 are not
        $this->assertDontSee($response, 'Amoxi Tabs 500 mg 100 tabletten');
        $this->assertSee($response, 'Ontsmettingsmiddel 250 ml');
        $this->assertSee($response, 'Shampoo 500 ml');
    }

    public function testUnlinkedHeaderCountsTheRows(): void
    {
        $expected = (int) $this->scalar('SELECT COUNT(*) FROM wholesale w WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.wholesale = w.id)');

        $response = $this->get('wholesale/unlinked');

        $this->assertSee($response, 'Not linked (' . $expected . ')');
    }

    public function testASoftDeletedProductStillCountsAsALink(): void
    {
        // The join does not look at products.deleted_at: a row linked only to a deleted product is "linked".
        $wh = Factory::wholesale(['description' => 'GhostLink ' . Factory::unique('d')]);
        Factory::product(['wholesale' => $wh, 'deleted_at' => Factory::raw('NOW()')]);

        $this->assertDontSee($this->get('wholesale/unlinked'), 'GhostLink');
    }
}
