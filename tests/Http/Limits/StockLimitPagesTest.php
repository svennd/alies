<?php

declare(strict_types=1);

/**
 * T195 - Limits::global, local and order (vet screens).
 *
 * global: products with limit_stock > 0, not deleted, not on backorder, whose total IN_USE volume (all
 * locations) is below the limit; products without any stock row count as 0.
 * local/{location}: the stock_limit rows of that location whose IN_USE volume there is below the limit
 * (location 0 or none = the user's location). order: products with backorder = 1.
 * The SQL itself is tested in tests/Models/StockLimitTest.php.
 */
final class StockLimitPagesTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function limit(int $pid, int $location, int $volume): int
    {
        return Factory::insert('stock_limit', [
            'stock' => $location, 'product_id' => $pid, 'volume' => $volume,
            'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    private function pname(int $pid): string
    {
        return $this->find('products', $pid)['name'];
    }

    // ---- global ----------------------------------------------------------------------------

    public function testTheGlobalPageListsProductsBelowTheirLimit(): void
    {
        $short = Factory::product(['name' => Factory::unique('GlobShort'), 'limit_stock' => 10]);
        Factory::stock($short, ['location' => '1', 'volume' => 3]);
        Factory::stock($short, ['location' => '2', 'volume' => 4]);
        $enough = Factory::product(['name' => Factory::unique('GlobEnough'), 'limit_stock' => 10]);
        Factory::stock($enough, ['location' => '1', 'volume' => 4]);
        Factory::stock($enough, ['location' => '2', 'volume' => 6]);
        $nolimit = Factory::product(['name' => Factory::unique('GlobNoLimit'), 'limit_stock' => 0]);

        $page = $this->get('limits/global');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->pname($short));
        $this->assertDontSee($page, $this->pname($enough), '3 + 4 + ... equals the limit exactly: not a shortage');
        $this->assertDontSee($page, $this->pname($nolimit));
        $this->assertSee($page, 'Zonder voorraad', 'seeded product 4005: limit 5, no stock at all');
    }

    public function testOnlyInUseStockCountsTowardsTheGlobalStock(): void
    {
        $pid = Factory::product(['name' => Factory::unique('GlobStates'), 'limit_stock' => 10]);
        Factory::stock($pid, ['volume' => 6]);
        Factory::stock($pid, ['volume' => 50, 'state' => Fixtures::STATE_HISTORY]);
        Factory::stock($pid, ['volume' => 50, 'state' => Fixtures::STATE_ERROR]);
        Factory::stock($pid, ['volume' => 50, 'state' => Fixtures::STATE_CHECK]);

        $this->assertSee($this->get('limits/global'), $this->pname($pid));
    }

    public function testBackorderedAndDeletedProductsAreNotOnTheGlobalPage(): void
    {
        $ordered = Factory::product(['name' => Factory::unique('GlobOrdered'), 'limit_stock' => 10, 'backorder' => 1]);
        $deleted = Factory::product(['name' => Factory::unique('GlobDeleted'), 'limit_stock' => 10, 'deleted_at' => Factory::raw('NOW()')]);
        $nullOrder = Factory::product(['name' => Factory::unique('GlobNull'), 'limit_stock' => 10, 'backorder' => null]);

        $page = $this->get('limits/global');

        $this->assertDontSee($page, $this->pname($ordered));
        $this->assertDontSee($page, $this->pname($deleted));
        $this->assertSee($page, $this->pname($nullOrder), 'backorder NULL counts as not on backorder');
    }

    // ---- local -----------------------------------------------------------------------------

    public function testTheLocalPageListsLimitsAboveTheLocalVolume(): void
    {
        $short = Factory::product(['name' => Factory::unique('LocShort')]);
        Factory::stock($short, ['location' => '1', 'volume' => 5]);
        $this->limit($short, 1, 20);
        $enough = Factory::product(['name' => Factory::unique('LocEnough')]);
        Factory::stock($enough, ['location' => '1', 'volume' => 9]);
        $this->limit($enough, 1, 5);
        // enough elsewhere does not help locally, and a shortage elsewhere does not show here
        $car = Factory::product(['name' => Factory::unique('LocCar')]);
        Factory::stock($car, ['location' => '1', 'volume' => 100]);
        Factory::stock($car, ['location' => '2', 'volume' => 1]);
        $this->limit($car, 2, 10);

        $one = $this->get('limits/local/1');
        $two = $this->get('limits/local/2');

        $this->assertStatus($one, 200);
        $this->assertSee($one, $this->pname($short));
        $this->assertMatchesRegularExpression('~>5\s*</span>\s*/\s*20 ~', $this->text($one), 'local volume / limit of the product');
        $this->assertDontSee($one, $this->pname($enough));
        $this->assertDontSee($one, $this->pname($car));
        $this->assertSee($two, $this->pname($car));
        $this->assertDontSee($two, $this->pname($short));
    }

    public function testWithoutALocationTheUsersLocationIsUsed(): void
    {
        $pid = Factory::product(['name' => Factory::unique('LocDefault')]);
        Factory::stock($pid, ['location' => '1', 'volume' => 1]);
        $this->limit($pid, 1, 8);

        $this->assertSee($this->get('limits/local'), $this->pname($pid));
        $this->assertSee($this->get('limits/local/0'), $this->pname($pid));
    }

    public function testDeletedProductsAreNotOnTheLocalPage(): void
    {
        $pid = Factory::product(['name' => Factory::unique('LocDeleted'), 'deleted_at' => Factory::raw('NOW()')]);
        Factory::stock($pid, ['location' => '1', 'volume' => 1]);
        $this->limit($pid, 1, 8);

        $this->assertDontSee($this->get('limits/local/1'), $this->pname($pid));
    }

    // ---- order -----------------------------------------------------------------------------

    public function testTheOrderPageListsProductsOnBackorder(): void
    {
        $on = Factory::product(['name' => Factory::unique('OrdOn'), 'backorder' => 1]);
        $off = Factory::product(['name' => Factory::unique('OrdOff'), 'backorder' => 0]);
        $wsId = Factory::wholesale(['vendor_id' => 'WS-ORD-1', 'description' => 'Backordered article']);
        $linked = Factory::product(['name' => Factory::unique('OrdLinked'), 'backorder' => 1, 'wholesale' => $wsId]);

        $page = $this->get('limits/order');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->pname($on));
        $this->assertDontSee($page, 'products/unset_backorder/' . $on, 'the switch is for admins');
        $this->assertDontSee($page, $this->pname($off));
        $this->assertSee($page, $this->pname($linked));
        $this->assertSee($page, 'WS-ORD-1');
        $this->assertSee($page, 'Backordered article');

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertSee($this->get('limits/order'), 'products/unset_backorder/' . $on);
    }

    public function testAProductMovesFromTheShortageListToTheOrderList(): void
    {
        $pid = Factory::product(['name' => Factory::unique('Flow'), 'limit_stock' => 10]);
        $this->assertSee($this->get('limits/global'), $this->pname($pid));
        $this->assertDontSee($this->get('limits/order'), $this->pname($pid));

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->get('products/set_backorder/' . $pid);

        $this->assertDontSee($this->get('limits/global'), $this->pname($pid));
        $this->assertSee($this->get('limits/order'), $this->pname($pid));
    }

    public function testMembersWithoutAStaffGroupAreRefused(): void
    {
        $this->cookies->clear();
        $this->loginAs('member');

        $this->assertStatus($this->get('limits/global'), 500);
        $this->assertStatus($this->get('limits/order'), 500);
    }
}
