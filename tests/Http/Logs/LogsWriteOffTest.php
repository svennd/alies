<?php

declare(strict_types=1);

/**
 * T215 - Logs::write_off and Logs::write_off_invoice (Liquidate_model, Bills_model::set_invoice_id). Admin only.
 *
 * Rules: write_off lists the liquidate rows and the number of unbilled ones (bill_id IS NULL).
 * write_off_invoice takes all unbilled rows, creates one bill for the write-off client (owner 1837)
 * with status BILL_PAID (4), every amount 0 and the fixed invoice message, gives it the next
 * invoice number of the year, links the rows (liquidate.bill_id) and redirects to
 * invoice/get_bill/{id}. Without unbilled rows it redirects back to logs/write_off and creates
 * nothing. Without owner 1837 it stops with a 500 "Write-off invoice client #1837 was not found.".
 */
final class LogsWriteOffTest extends HttpTestCase
{
    private const MESSAGE = 'Stock write-off invoice. All listed products were written off from stock.';

    protected function setUp(): void
    {
        parent::setUp();
        // other tests of this class may have left unbilled rows
        $this->db()->exec('DELETE FROM liquidate WHERE bill_id IS NULL');
        $this->loginAs('admin');
    }

    private function liquidate(array $overrides = []): int
    {
        return Factory::insert('liquidate', array_merge([
            'product_id' => Fixtures::PRODUCT_TIERED,
            'product_name' => 'Amoxi Tabs',
            'volume' => 2.50,
            'eol' => date('Y-m-d', strtotime('-10 days')),
            'lotnr' => Factory::unique('LIQ'),
            'reason' => 'expired',
            'user' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'stock_id' => 1,
            'bill_id' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));
    }

    private function billCount(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM bills WHERE owner_id = ?', [Fixtures::OWNER_WRITE_OFF]);
    }

    public function testNonAdminsAreSentHome(): void
    {
        $row = $this->liquidate();
        $this->cookies->clear();
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('logs/write_off'), '');
        $this->assertRedirectsTo($this->post('logs/write_off_invoice'), '');
        $this->assertNull($this->find('liquidate', $row)['bill_id']);
        $this->assertSame(0, $this->billCount());

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('logs/write_off'), 200);
    }

    // ---- the log ----------------------------------------------------------------------------

    public function testTheLogListsTheRowsWithProductVetLocationAndReason(): void
    {
        $this->liquidate(['lotnr' => 'WO-LOT-1', 'reason' => 'broken bottle', 'volume' => 3.25]);

        $response = $this->get('logs/write_off');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'WO-LOT-1');
        $this->assertSee($response, 'broken bottle');
        $this->assertSee($response, 'Amoxi Tabs');
        $this->assertSee($response, '3.25 tab');
        $this->assertSee($response, 'Victor');
        $this->assertSee($response, 'Praktijk');
        $this->assertSee($response, date('d-m-Y', strtotime('-10 days')), 'the expiry date in the user format');
    }

    public function testTheProductNameFallsBackToTheStoredNameWhenTheProductIsGone(): void
    {
        $this->liquidate(['product_id' => 99999999, 'product_name' => 'Vanished Item', 'lotnr' => 'WO-GONE']);

        $response = $this->get('logs/write_off');

        $this->assertSee($response, 'WO-GONE');
        $this->assertSee($response, 'Vanished Item');
    }

    public function testTheButtonShowsTheUnbilledCountAndIsDisabledWithoutRows(): void
    {
        $empty = $this->get('logs/write_off');
        $this->assertMatchesRegularExpression('#<button type="submit" class="btn btn-outline-success btn-sm" disabled>#', (string) $empty->getBody());
        $this->assertStringNotContainsString('badge badge-light ml-1', (string) $empty->getBody());

        $this->liquidate();
        $this->liquidate();
        $billed = $this->liquidate();
        $this->db()->exec('UPDATE liquidate SET bill_id = ' . Factory::bill(Fixtures::OWNER_WRITE_OFF, 4) . ' WHERE id = ' . $billed);

        $response = $this->get('logs/write_off');

        $this->assertDoesNotMatchRegularExpression('#btn-sm" disabled>#', (string) $response->getBody());
        $this->assertMatchesRegularExpression('#<span class="badge badge-light ml-1">2</span>#', (string) $response->getBody());
    }

    public function testBilledRowsLinkToTheirInvoice(): void
    {
        $bill = Factory::bill(Fixtures::OWNER_WRITE_OFF, 4);
        $this->liquidate(['bill_id' => $bill, 'lotnr' => 'WO-BILLED']);

        $response = $this->get('logs/write_off');

        $this->assertSee($response, 'WO-BILLED');
        $this->assertStringContainsString('invoice/get_bill/' . $bill, (string) $response->getBody());
    }

    // ---- the invoice ------------------------------------------------------------------------

    public function testTheInvoiceBillsEveryUnbilledRowToTheWriteOffClient(): void
    {
        $a = $this->liquidate();
        $b = $this->liquidate();
        $c = $this->liquidate();
        $alreadyBilled = Factory::bill(Fixtures::OWNER_WRITE_OFF, 4);
        $old = $this->liquidate(['bill_id' => $alreadyBilled]);
        $before = $this->billCount();
        $expectedInvoiceId = 1 + (int) $this->scalar('SELECT COALESCE(MAX(invoice_id), 0) FROM bills WHERE YEAR(created_at) = ?', [date('Y')]);

        $response = $this->post('logs/write_off_invoice');

        $this->assertSame($before + 1, $this->billCount(), 'exactly one bill');
        $billId = (int) $this->scalar('SELECT MAX(id) FROM bills WHERE owner_id = ?', [Fixtures::OWNER_WRITE_OFF]);
        $this->assertGreaterThan($alreadyBilled, $billId);
        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $billId);

        $bill = $this->find('bills', $billId);
        $this->assertSame(Fixtures::OWNER_WRITE_OFF, (int) $bill['owner_id']);
        $this->assertSame(4, (int) $bill['status']);
        foreach (['cash', 'card', 'transfer', 'total_brut', 'total_net', 'BTW_0', 'BTW_6', 'BTW_21'] as $column) {
            $this->assertEqualsWithDelta(0.0, (float) $bill[$column], 0.001, $column);
        }
        $this->assertSame(self::MESSAGE, $bill['msg_invoice']);
        $this->assertSame(Fixtures::USER_ADMIN, (int) $bill['vet']);
        $this->assertSame((int) $this->find('users', Fixtures::USER_ADMIN)['current_location'], (int) $bill['location']);
        $this->assertSame($expectedInvoiceId, (int) $bill['invoice_id']);
        $this->assertNotNull($bill['invoice_date']);
        $this->assertNull($bill['deleted_at']);

        foreach ([$a, $b, $c] as $id) {
            $this->assertSame($billId, (int) $this->find('liquidate', $id)['bill_id']);
        }
        $this->assertSame($alreadyBilled, (int) $this->find('liquidate', $old)['bill_id'], 'earlier bills are not touched');
    }

    public function testTheActionIsLogged(): void
    {
        $this->liquidate();
        $this->liquidate();
        $this->db()->exec("DELETE FROM log WHERE event = 'write_off_invoice'");

        $this->post('logs/write_off_invoice');

        $billId = (int) $this->scalar('SELECT MAX(id) FROM bills WHERE owner_id = ?', [Fixtures::OWNER_WRITE_OFF]);
        $log = $this->row("SELECT * FROM log WHERE event = 'write_off_invoice'");
        $this->assertNotNull($log);
        $this->assertSame('bill_id:' . $billId . ' items:2', $log['msg']);
        $this->assertSame(4, (int) $log['level']);
    }

    public function testAfterTheInvoiceNothingIsLeftAndTheNextCallDoesNothing(): void
    {
        $this->liquidate();
        $this->post('logs/write_off_invoice');
        $count = $this->billCount();
        $this->assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM liquidate WHERE bill_id IS NULL'));

        $response = $this->post('logs/write_off_invoice');

        $this->assertRedirectsTo($response, 'logs/write_off');
        $this->assertSame($count, $this->billCount(), 'no second bill');
    }

    public function testWithoutUnbilledRowsItRedirectsToTheLogAndCreatesNothing(): void
    {
        $before = (int) $this->scalar('SELECT COUNT(*) FROM bills');

        $response = $this->post('logs/write_off_invoice');

        $this->assertRedirectsTo($response, 'logs/write_off');
        $this->assertSame($before, (int) $this->scalar('SELECT COUNT(*) FROM bills'));
    }

    public function testTwoInvoicesGetConsecutiveInvoiceNumbers(): void
    {
        $this->liquidate();
        $this->post('logs/write_off_invoice');
        $first = (int) $this->scalar('SELECT invoice_id FROM bills WHERE owner_id = ? ORDER BY id DESC LIMIT 1', [Fixtures::OWNER_WRITE_OFF]);

        $this->liquidate();
        $this->post('logs/write_off_invoice');
        $second = (int) $this->scalar('SELECT invoice_id FROM bills WHERE owner_id = ? ORDER BY id DESC LIMIT 1', [Fixtures::OWNER_WRITE_OFF]);

        $this->assertGreaterThan(0, $first);
        $this->assertSame($first + 1, $second);
    }

    public function testWithoutTheWriteOffClientItStopsWithAnError(): void
    {
        $row = $this->liquidate();
        $before = (int) $this->scalar('SELECT COUNT(*) FROM bills');
        $this->db()->exec('UPDATE owners SET deleted_at = NOW() WHERE id = ' . Fixtures::OWNER_WRITE_OFF);

        try {
            $response = $this->post('logs/write_off_invoice');
        } finally {
            $this->db()->exec('UPDATE owners SET deleted_at = NULL WHERE id = ' . Fixtures::OWNER_WRITE_OFF);
        }

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Write-off invoice client #1837 was not found.');
        $this->assertSame($before, (int) $this->scalar('SELECT COUNT(*) FROM bills'));
        $this->assertNull($this->find('liquidate', $row)['bill_id']);

        // positive control: with the client back it works
        $this->post('logs/write_off_invoice');
        $this->assertNotNull($this->find('liquidate', $row)['bill_id']);
    }
}
