<?php

declare(strict_types=1);

/**
 * T164 (delete a CHECK row) and T165 (verify stock with a delivery slip).
 *
 * verify_stock moves EVERY CHECK row of EVERY location to IN_USE, so setUp clears all CHECK rows
 * first; the class then creates its own.
 */
final class VerifyStockTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('DELETE FROM stock WHERE state = 0');
        $this->loginAs('vet');
    }

    private function check(int $pid, string $location, float $volume, string $lot): int
    {
        return Factory::stock($pid, [
            'state' => 0, 'location' => $location, 'volume' => $volume, 'lotnr' => $lot,
            'in_price' => 1.5, 'cat_price' => 2.25, 'supplier' => 'Sup-' . $lot,
        ]);
    }

    // ---- T164 --------------------------------------------------------------------------------

    public function testACheckRowCanBeDeleted(): void
    {
        $pid = Factory::product();
        $row = $this->check($pid, '1', 5, 'DEL-1');
        $this->db()->exec("DELETE FROM log_stock WHERE event = 'delete_stock'");

        $response = $this->get('stock/delete_stock/' . $row);

        $this->assertRedirectsTo($response, 'stock/add_stock');
        $this->assertRowMissing('stock', ['id' => $row]);
        // logging level is DEBUG in the app, so the removal is logged with a negative volume
        $log = $this->row("SELECT * FROM log_stock WHERE event = 'delete_stock' AND product = ?", [$pid]);
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(-5.0, (float) $log['volume'], 0.001);
    }

    #[\PHPUnit\Framework\Attributes\Group('known-bug')]
    public function testAnInUseRowIsNotDeletedButTheRequestCrashes(): void
    {
        // KNOWN BUG: the debug log looks the row up with state CHECK, gets false and passes null
        // as product id (TypeError) -> status 500 instead of the redirect. The row survives anyway.
        $pid = Factory::product();
        $inUse = Factory::stock($pid, ['state' => 1, 'volume' => 3]);
        $check = $this->check($pid, '1', 2, 'DEL-2');

        $this->assertStatus($this->get('stock/delete_stock/' . $inUse), 500);
        $this->assertRowExists('stock', ['id' => $inUse]);

        // positive control: the CHECK row of the same product does go
        $this->assertRedirectsTo($this->get('stock/delete_stock/' . $check), 'stock/add_stock');
        $this->assertRowMissing('stock', ['id' => $check]);
    }

    // ---- T165 --------------------------------------------------------------------------------

    public function testTheGetPageListsThePendingRows(): void
    {
        $pid = Factory::product(['name' => 'Verify Listed Product']);
        $this->check($pid, '1', 5, 'VER-LIST');
        $done = Factory::product(['name' => 'Verify Not Listed']);
        Factory::stock($done, ['state' => 1]);

        $page = $this->get('stock/verify_stock');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Verify Listed Product');
        $this->assertDontSee($page, 'Verify Not Listed');
    }

    public function testVerifyingMovesAllCheckRowsAndWritesTheDeliverySlipAndRegister(): void
    {
        $p1 = Factory::product();
        $p2 = Factory::product();
        $a = $this->check($p1, '1', 4, 'VER-A');
        $b = $this->check($p1, '1', 6, 'VER-B');
        $c = $this->check($p2, '2', 9, 'VER-C');     // other location: moved as well
        $slips = $this->countWhere('delivery_slip');
        $registered = $this->countWhere('register_in');
        $regdate = date('Y-m-d', strtotime('-1 day'));

        $response = $this->post('stock/verify_stock', ['submit' => 1, 'delivery_slip' => 'note-' . $a, 'regdate' => $regdate]);

        $this->assertRedirectsTo($response, 'stock/add_stock');

        // one slip, from the vet at location 1
        $this->assertSame($slips + 1, $this->countWhere('delivery_slip'));
        $slip = $this->row('SELECT * FROM delivery_slip WHERE note = ?', ['note-' . $a]);
        $this->assertNotNull($slip);
        $this->assertSame($regdate, $slip['regdate']);
        $this->assertSame(1, (int) $slip['location']);
        $this->assertSame(Fixtures::USER_VET, (int) $slip['vet']);

        // 3 register_in rows, with the data of the stock rows
        $this->assertSame($registered + 3, $this->countWhere('register_in'));
        $regs = $this->rows('SELECT * FROM register_in WHERE delivery_slip = ? ORDER BY lotnr', [$slip['id']]);
        $this->assertCount(3, $regs);
        $this->assertSame(['VER-A', 'VER-B', 'VER-C'], array_column($regs, 'lotnr'));
        $this->assertSame([$p1, $p1, $p2], array_map('intval', array_column($regs, 'product')));
        $this->assertEqualsWithDelta(4.0, (float) $regs[0]['volume'], 0.001);
        $this->assertEqualsWithDelta(6.0, (float) $regs[1]['volume'], 0.001);
        $this->assertEqualsWithDelta(9.0, (float) $regs[2]['volume'], 0.001);
        $this->assertEqualsWithDelta(1.5, (float) $regs[0]['in_price'], 0.001);
        $this->assertEqualsWithDelta(2.25, (float) $regs[0]['cat_price'], 0.001);
        $this->assertSame('Sup-VER-C', $regs[2]['supplier']);
        $this->assertSame(date('Y-m-d', strtotime('+1 year')), $regs[0]['eol']);

        // all three rows are in use now, volumes untouched
        foreach ([$a => 4.0, $b => 6.0, $c => 9.0] as $id => $volume) {
            $row = $this->find('stock', $id);
            $this->assertSame(1, (int) $row['state'], 'row ' . $id);
            $this->assertEqualsWithDelta($volume, (float) $row['volume'], 0.001);
        }
        $this->assertSame(0, $this->countWhere('stock', ['state' => 0]));

        $log = $this->row("SELECT * FROM log WHERE event = 'stock_verify' ORDER BY id DESC LIMIT 1");
        $this->assertSame('products verified: 3', $log['msg']);
    }

    public function testVerifyingWithoutPendingRowsStillWritesASlipAndZeroInTheLog(): void
    {
        $slips = $this->countWhere('delivery_slip');

        $this->post('stock/verify_stock', ['submit' => 1, 'delivery_slip' => 'empty-slip', 'regdate' => date('Y-m-d')]);

        $this->assertSame($slips + 1, $this->countWhere('delivery_slip'));
        $this->assertSame(0, $this->countWhere('register_in', ['delivery_slip' => $this->scalar("SELECT id FROM delivery_slip WHERE note = 'empty-slip'")]));
    }

    public function testAGetDoesNotVerifyAnything(): void
    {
        $row = $this->check(Factory::product(), '1', 2, 'VER-GET');
        $slips = $this->countWhere('delivery_slip');

        $this->get('stock/verify_stock');

        $this->assertSame(0, (int) $this->find('stock', $row)['state']);
        $this->assertSame($slips, $this->countWhere('delivery_slip'));
    }
}
