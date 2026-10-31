<?php

declare(strict_types=1);

/**
 * T171, T172 - write-off of stock (Stock::write_off_full, Stock::write_off, Stock_model::write_off).
 *
 * A write-off inserts a `liquidate` row and reduces the stock by exactly the written-off volume
 * (the product's dead volume is NOT added). State: remaining < 0 -> 3 (ERROR), = 0 -> 2 (HISTORY), > 0 -> 1.
 */
final class WriteOffTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function expiredRow(int $pid, float $volume, string $lot): int
    {
        return Factory::stock($pid, [
            'volume' => $volume, 'lotnr' => $lot, 'eol' => date('Y-m-d', strtotime('-10 days')),
            'location' => '2',
        ]);
    }

    // ---- T171 --------------------------------------------------------------------------------

    public function testAnExpiredRowIsWrittenOffCompletely(): void
    {
        $pid = Factory::product(['name' => 'Full WO Product', 'dead_volume' => 0.5]);
        $row = $this->expiredRow($pid, 6, 'WO-FULL');
        $eol = date('Y-m-d', strtotime('-10 days'));

        $response = $this->get('stock/write_off_full/' . $row);

        $this->assertRedirectsTo($response, 'stock/expired_stock');
        $stock = $this->find('stock', $row);
        $this->assertEqualsWithDelta(0.0, (float) $stock['volume'], 0.001);
        $this->assertSame(2, (int) $stock['state']);

        $liq = $this->row('SELECT * FROM liquidate WHERE stock_id = ?', [$row]);
        $this->assertNotNull($liq);
        $this->assertSame('EXPIRED', $liq['reason']);
        $this->assertEqualsWithDelta(6.0, (float) $liq['volume'], 0.001);
        $this->assertSame($eol, $liq['eol']);
        $this->assertSame('WO-FULL', $liq['lotnr']);
        $this->assertSame($pid, (int) $liq['product_id']);
        $this->assertSame('Full WO Product', $liq['product_name']);
        $this->assertSame(Fixtures::USER_VET, (int) $liq['user']);
        $this->assertSame(2, (int) $liq['location']);

        $log = $this->row("SELECT * FROM log_stock WHERE event = 'write_off' AND product = ?", [$pid]);
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(6.0, (float) $log['volume'], 0.001);
        $this->assertSame(2, (int) $log['location']);
        $this->assertSame(4, (int) $log['level']);
        $this->assertRowExists('log', ['event' => 'write_off']);
    }

    public function testWritingOffOneRowLeavesOthersAlone(): void
    {
        $pid = Factory::product();
        $a = $this->expiredRow($pid, 6, 'WO-A');
        $b = $this->expiredRow($pid, 8, 'WO-B');

        $this->get('stock/write_off_full/' . $a);

        $this->assertSame(2, (int) $this->find('stock', $a)['state']);
        $this->assertSame(1, (int) $this->find('stock', $b)['state']);
        $this->assertEqualsWithDelta(8.0, (float) $this->find('stock', $b)['volume'], 0.001);
        $this->assertSame(0, $this->countWhere('liquidate', ['stock_id' => $b]));
    }

    // ---- T172 --------------------------------------------------------------------------------

    public function testTheWriteOffFormShowsTheRow(): void
    {
        $pid = Factory::product(['name' => 'Partial WO Form']);
        $row = Factory::stock($pid, ['lotnr' => 'WO-FORM', 'volume' => 12]);

        $page = $this->get('stock/write_off/' . $row);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Partial WO Form');
        $this->assertSee($page, 'WO-FORM');
        $this->assertEqualsWithDelta(12.0, (float) $this->find('stock', $row)['volume'], 0.001, 'GET changes nothing');
    }

    public function testAPartialWriteOffReducesByExactlyTheVolume(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);
        $row = Factory::stock($pid, ['lotnr' => 'WO-PART', 'volume' => 10]);

        $response = $this->post('stock/write_off/' . $row, ['submit' => 1, 'volume' => 2, 'reason' => 'BROKEN']);

        $this->assertRedirectsTo($response, 'products/profile/' . $pid);
        // 10 - 2 = 8 (dead volume 0.5 is not added)
        $stock = $this->find('stock', $row);
        $this->assertEqualsWithDelta(8.0, (float) $stock['volume'], 0.001);
        $this->assertSame(1, (int) $stock['state']);
        $liq = $this->row('SELECT * FROM liquidate WHERE stock_id = ?', [$row]);
        $this->assertSame('BROKEN', $liq['reason']);
        $this->assertEqualsWithDelta(2.0, (float) $liq['volume'], 0.001);
        $this->assertSame('WO-PART', $liq['lotnr']);
        $log = $this->row("SELECT * FROM log_stock WHERE event = 'write_off' AND product = ?", [$pid]);
        $this->assertEqualsWithDelta(2.0, (float) $log['volume'], 0.001);
    }

    public function testWritingOffTheExactRemainderMovesTheRowToHistory(): void
    {
        $pid = Factory::product();
        $row = Factory::stock($pid, ['volume' => 3.5]);

        $this->post('stock/write_off/' . $row, ['submit' => 1, 'volume' => 3.5, 'reason' => 'LOST']);

        // 3.5 - 3.5 = 0 -> state 2
        $this->assertSame(2, (int) $this->find('stock', $row)['state']);
        $this->assertEqualsWithDelta(0.0, (float) $this->find('stock', $row)['volume'], 0.001);
    }

    public function testWritingOffMoreThanIsLeftPutsTheRowInError(): void
    {
        $pid = Factory::product();
        $row = Factory::stock($pid, ['volume' => 3]);

        $this->post('stock/write_off/' . $row, ['submit' => 1, 'volume' => 5, 'reason' => 'LOST']);

        // 3 - 5 = -2 -> state 3
        $stock = $this->find('stock', $row);
        $this->assertEqualsWithDelta(-2.0, (float) $stock['volume'], 0.001);
        $this->assertSame(3, (int) $stock['state']);
        $this->assertSame(1, $this->countWhere('liquidate', ['stock_id' => $row]));
    }

    public function testWithoutSubmitTheFormIsShownAndNothingIsWrittenOff(): void
    {
        $pid = Factory::product();
        $row = Factory::stock($pid, ['volume' => 3]);

        $this->post('stock/write_off/' . $row, ['volume' => 1, 'reason' => 'LOST']);

        $this->assertEqualsWithDelta(3.0, (float) $this->find('stock', $row)['volume'], 0.001);
        $this->assertSame(0, $this->countWhere('liquidate', ['stock_id' => $row]));
    }
}
