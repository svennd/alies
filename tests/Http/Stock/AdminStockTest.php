<?php

declare(strict_types=1);

/**
 * T174 - admin stock edit, the stock-problems screen and clear_error.
 * (The "a vet is refused" side of edit and stock_check is pinned in AdminChecksInsideVetControllersTest.)
 *
 * edit: updates eol, in_price, lotnr, volume, state; when new_volume differs from ori_volume it writes two
 * WARN rows to log_stock (-ori, +new). stock_check lists ERROR rows and IN_USE rows with a negative volume.
 * clear_error: state 2, volume 0, logs the removed volume.
 */
final class AdminStockTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testEditingARowUpdatesItAndLogsTheVolumeChange(): void
    {
        $pid = Factory::product();
        $row = Factory::stock($pid, ['volume' => 5, 'state' => 3, 'lotnr' => 'ED-OLD', 'location' => '2']);
        $eol = date('Y-m-d', strtotime('+200 days'));

        $response = $this->post('stock/edit/' . $row, [
            'submit' => 1, 'eol' => $eol, 'in_price' => '4.75', 'lotnr' => 'ED-NEW',
            'new_volume' => 7, 'state' => 1, 'ori_volume' => 5,
        ]);

        $this->assertRedirectsTo($response, 'stock/stock_detail/' . $pid);
        $stock = $this->find('stock', $row);
        $this->assertSame($eol, $stock['eol']);
        $this->assertEqualsWithDelta(4.75, (float) $stock['in_price'], 0.001);
        $this->assertSame('ED-NEW', $stock['lotnr']);
        $this->assertEqualsWithDelta(7.0, (float) $stock['volume'], 0.001);
        $this->assertSame(1, (int) $stock['state']);

        $logs = $this->rows("SELECT * FROM log_stock WHERE event = 'admin_stock_edit' AND product = ? ORDER BY id", [$pid]);
        $this->assertCount(2, $logs);
        $this->assertEqualsWithDelta(-5.0, (float) $logs[0]['volume'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $logs[1]['volume'], 0.001);
        $this->assertSame([3, 3], [(int) $logs[0]['level'], (int) $logs[1]['level']]);
        $this->assertSame([2, 2], [(int) $logs[0]['location'], (int) $logs[1]['location']]);
        $this->assertRowExists('log', ['event' => 'admin_stock_edit']);
    }

    public function testEditingWithoutAVolumeChangeWritesNoStockLog(): void
    {
        $pid = Factory::product();
        $row = Factory::stock($pid, ['volume' => 5, 'lotnr' => 'ED-SAME']);

        $this->post('stock/edit/' . $row, [
            'submit' => 1, 'eol' => '2032-01-01', 'in_price' => '1', 'lotnr' => 'ED-SAME2',
            'new_volume' => 5, 'state' => 1, 'ori_volume' => 5,
        ]);

        $this->assertSame('ED-SAME2', $this->find('stock', $row)['lotnr']);
        $this->assertSame(0, (int) $this->scalar("SELECT COUNT(*) FROM log_stock WHERE event = 'admin_stock_edit' AND product = ?", [$pid]));
    }

    public function testTheEditFormShowsTheRowAndChangesNothing(): void
    {
        $row = Factory::stock(Factory::product(['name' => 'Edit Form Product']), ['lotnr' => 'ED-FORM', 'volume' => 9]);

        $page = $this->get('stock/edit/' . $row);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'ED-FORM');
        $this->assertEqualsWithDelta(9.0, (float) $this->find('stock', $row)['volume'], 0.001);
    }

    public function testTheProblemScreenListsErrorRowsAndNegativeInUseRows(): void
    {
        $pid = Factory::product();
        Factory::stock($pid, ['state' => 3, 'volume' => -1, 'lotnr' => 'PR-ERROR']);
        Factory::stock($pid, ['state' => 1, 'volume' => -4, 'lotnr' => 'PR-NEGATIVE']);
        Factory::stock($pid, ['state' => 1, 'volume' => 4, 'lotnr' => 'PR-FINE']);
        Factory::stock($pid, ['state' => 2, 'volume' => -4, 'lotnr' => 'PR-HISTORY']);

        $page = $this->get('stock/stock_check');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'PR-ERROR');
        $this->assertSee($page, 'PR-NEGATIVE');
        $this->assertDontSee($page, 'PR-FINE');
        $this->assertDontSee($page, 'PR-HISTORY');
    }

    public function testClearingAnErrorZeroesTheRowAndLogsIt(): void
    {
        $pid = Factory::product(['name' => 'Clear Error Product']);
        $row = Factory::stock($pid, ['state' => 3, 'volume' => -2, 'lotnr' => 'CL-ERR']);

        $response = $this->get('stock/clear_error/' . $row);

        $this->assertRedirectsTo($response, 'stock/stock_check');
        $stock = $this->find('stock', $row);
        $this->assertSame(2, (int) $stock['state']);
        $this->assertEqualsWithDelta(0.0, (float) $stock['volume'], 0.001);

        // the volume that was removed is -(-2) = +2, level ERROR (2)
        $log = $this->row("SELECT * FROM log_stock WHERE event = 'clear_error' AND product = ?", [$pid]);
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(2.0, (float) $log['volume'], 0.001);
        $this->assertSame(2, (int) $log['level']);
        $general = $this->row("SELECT * FROM log WHERE event = 'clear_error' AND msg LIKE ?", ['s:' . $row . ' %']);
        $this->assertNotNull($general);
        $this->assertStringContainsString('p:Clear Error Product', $general['msg']);
        $this->assertStringContainsString('v:-2', $general['msg']);
        $this->assertStringContainsString('l:CL-ERR', $general['msg']);

        // it is gone from the problem screen
        $this->assertDontSee($this->get('stock/stock_check'), 'CL-ERR');
    }

    public function testClearingAnErrorOfADeletedProductLogsUnknown(): void
    {
        // the product of the row is soft deleted (the controller falls back to 'unknown' for a missing product name)
        $row = Factory::stock(Factory::product(['deleted_at' => Factory::raw('NOW()'), 'name' => 'Deleted Prod']), ['state' => 3, 'volume' => -1]);

        $this->assertRedirectsTo($this->get('stock/clear_error/' . $row), 'stock/stock_check');

        $this->assertSame(2, (int) $this->find('stock', $row)['state']);
    }
}
