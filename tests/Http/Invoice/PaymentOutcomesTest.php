<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/InvoiceHelper.php';

/**
 * T141 - Invoice::bill_pay: the status a bill gets for each amount paid.
 *
 * Every case starts from a PENDING bill with total_brut 100.00 and one event (open) with one
 * product line of 2 from a lot of 50 (dead volume 0), so a stock reduction is visible as 50 -> 48.
 * Rule: paid == total (within 0.001): PAID, or ONHOLD when a transfer is part of it; anything
 * else (under or over) is INCOMPLETE. Only PENDING, UNPAID and INCOMPLETE bills are processed
 * and only with submit=1.
 */
final class PaymentOutcomesTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        InvoiceHelper::cleanUp();
        parent::tearDown();
    }

    /** @return array{owner: int, bill: int, event: int, lot: int} */
    private function pendingBill(int $status = 1): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $product = Factory::product();
        $lot = Factory::stock($product, ['volume' => 50.0]);
        $bill = Factory::bill($owner, $status, ['total_brut' => 100.00, 'total_net' => 100.00, 'BTW_0' => 100.00]);
        $event = Factory::event($pet, 0, $bill);
        Factory::eventProduct($event, $product, 2.0, ['unit_price' => 50.00, 'btw' => 0, 'stock_id' => $lot]);

        return compact('owner', 'bill', 'event', 'lot');
    }

    private function pay(int $bill, float $cash, float $card = 0.0, float $transfer = 0.0, array $extra = ['submit' => 1]): void
    {
        $this->post('invoice/bill_pay/' . $bill, $extra + [
            'cash_value' => (string) $cash,
            'card_value' => (string) $card,
            'transfer_value' => (string) $transfer,
        ]);
        $this->registerPdf($bill);
    }

    private function registerPdf(int $bill): void
    {
        $row = $this->find('bills', $bill);
        if ($row['invoice_id'] !== null) {
            InvoiceHelper::register(InvoiceHelper::pdfPath((string) $row['invoice_date'], 'bill_' . date('Y') . str_pad((string) $row['invoice_id'], 5, '0', STR_PAD_LEFT)));
        }
    }

    private function billStatus(int $bill): int
    {
        return (int) $this->find('bills', $bill)['status'];
    }

    private function lotVolume(int $lot): float
    {
        return (float) $this->find('stock', $lot)['volume'];
    }

    public function testExactCashPaysTheBill(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 100.0);

        $this->assertSame(4, $this->billStatus($s['bill']));
        $this->assertNotNull($this->find('bills', $s['bill'])['invoice_id']);
        $this->assertEqualsWithDelta(48.0, $this->lotVolume($s['lot']), 0.001);
    }

    public function testExactAmountSplitOverCashAndCardPaysTheBill(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 40.0, 60.0);

        $this->assertSame(4, $this->billStatus($s['bill']));
    }

    public function testExactAmountWithATransferPutsTheBillOnHold(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 0.0, 0.0, 100.0);

        $this->assertSame(6, $this->billStatus($s['bill']));
        $row = $this->find('bills', $s['bill']);
        $this->assertEqualsWithDelta(100.0, (float) $row['transfer'], 0.001);
        $this->assertSame('0', (string) $row['transfer_verified']);
        $this->assertNotNull($row['invoice_id'], 'on hold already has an invoice number');
        $this->assertEqualsWithDelta(48.0, $this->lotVolume($s['lot']), 0.001);
    }

    public function testAMixedPaymentWithATransferPartIsAlsoOnHold(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 50.0, 0.0, 50.0);

        $this->assertSame(6, $this->billStatus($s['bill']));
    }

    public function testUnderpaymentIsIncompleteButStockIsReducedAndAnInvoiceNumberIsAssigned(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 60.0);

        $row = $this->find('bills', $s['bill']);
        $this->assertSame('3', (string) $row['status']);
        $this->assertEqualsWithDelta(60.0, (float) $row['cash'], 0.001);
        // KNOWN BUG: the invoice number (a legal document) and the stock reduction already happen at
        // a partial payment - decide in CI4 whether to keep
        $this->assertNotNull($row['invoice_id']);
        $this->assertEqualsWithDelta(48.0, $this->lotVolume($s['lot']), 0.001);
        $this->assertSame('1', (string) $this->find('events', $s['event'])['status'], 'events are closed');
        $this->assertSame(1, $this->countWhere('payment', ['bill_id' => $s['bill']]));
    }

    #[Group('known-bug')]
    public function testAnInvoiceNumberAtPartialPaymentFollowsTheYearCounter(): void
    {
        // KNOWN BUG: invoice_id is assigned at a partial payment - decide in CI4 whether to keep
        $s = $this->pendingBill();
        $this->loginAs('vet');
        $expected = (int) $this->scalar('SELECT COALESCE(MAX(invoice_id), 0) FROM bills WHERE YEAR(created_at) = ?', [date('Y')]) + 1;

        $this->pay($s['bill'], 10.0);

        $this->assertSame((string) $expected, (string) $this->find('bills', $s['bill'])['invoice_id']);
    }

    #[Group('known-bug')]
    public function testOverpaymentByOneCentIsIncomplete(): void
    {
        // KNOWN BUG: paying too much is treated like paying too little - decide in CI4 whether to keep
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $this->pay($s['bill'], 100.01);

        $this->assertSame(3, $this->billStatus($s['bill']));
    }

    public function testAHalfCentDifferenceCountsAsExact(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        // the amounts are rounded to cents first: 99.996 -> 100.00
        $this->pay($s['bill'], 99.996);

        $this->assertSame(4, $this->billStatus($s['bill']));
    }

    #[Group('known-bug')]
    public function testPayingAnIncompleteBillAgainPaysItWithoutReducingStockTwice(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');
        $this->pay($s['bill'], 60.0);
        $invoice = $this->find('bills', $s['bill'])['invoice_id'];
        $this->assertSame('3', (string) $this->find('bills', $s['bill'])['status']);
        $logBefore = $this->countWhere('log', ['event' => 'bill_pay_incomplete']);

        // the payment must cover the whole bill again: paying only the remaining 40 stays INCOMPLETE
        $this->pay($s['bill'], 100.0);

        $row = $this->find('bills', $s['bill']);
        $this->assertSame('4', (string) $row['status']);
        $this->assertEqualsWithDelta(48.0, $this->lotVolume($s['lot']), 0.001, 'stock reduced only once');
        $this->assertSame($invoice, $row['invoice_id'], 'the invoice number stays');
        $this->assertSame($logBefore + 1, $this->countWhere('log', ['event' => 'bill_pay_incomplete']));
        $this->assertSame(2, $this->countWhere('payment', ['bill_id' => $s['bill']]), 'both payments are kept');
        // KNOWN BUG: the bill columns hold the LAST post, not the sum (60 + 100) - decide in CI4 whether to keep
        $this->assertEqualsWithDelta(100.0, (float) $row['cash'], 0.001);
        $this->assertEqualsWithDelta(60.0, (float) $this->scalar('SELECT MIN(cash) FROM payment WHERE bill_id = ?', [$s['bill']]), 0.001);
    }

    #[Group('known-bug')]
    public function testPayingOnlyTheRemainderOfAnIncompleteBillKeepsItIncomplete(): void
    {
        // KNOWN BUG: the remainder is compared with the whole bill, not with what is still due
        $s = $this->pendingBill();
        $this->loginAs('vet');
        $this->pay($s['bill'], 60.0);

        $this->pay($s['bill'], 40.0);

        $this->assertSame(3, $this->billStatus($s['bill']));
        $this->assertEqualsWithDelta(40.0, (float) $this->find('bills', $s['bill'])['cash'], 0.001);
    }

    public function testAPaidBillIsNotProcessedAgain(): void
    {
        $s = $this->pendingBill(4);
        $this->loginAs('vet');
        $before = $this->find('bills', $s['bill']);

        $response = $this->post('invoice/bill_pay/' . $s['bill'], ['submit' => 1, 'cash_value' => '100', 'card_value' => '0', 'transfer_value' => '0']);

        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $s['bill']);
        $after = $this->find('bills', $s['bill']);
        $this->assertSame($before['cash'], $after['cash']);
        $this->assertSame('4', (string) $after['status']);
        $this->assertNull($after['invoice_id']);
        $this->assertSame(0, $this->countWhere('payment', ['bill_id' => $s['bill']]));
        $this->assertSame('0', (string) $this->find('events', $s['event'])['status']);
        $this->assertEqualsWithDelta(50.0, $this->lotVolume($s['lot']), 0.001);
    }

    public function testADraftBillCannotBePaid(): void
    {
        $s = $this->pendingBill(0);
        $this->loginAs('vet');

        $response = $this->post('invoice/bill_pay/' . $s['bill'], ['submit' => 1, 'cash_value' => '100', 'card_value' => '0', 'transfer_value' => '0']);

        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $s['bill']);
        $this->assertSame(0, $this->billStatus($s['bill']));
        $this->assertSame(0, $this->countWhere('payment', ['bill_id' => $s['bill']]));
    }

    public function testWithoutSubmitNothingChangesButTheRedirectStillHappens(): void
    {
        $s = $this->pendingBill();
        $this->loginAs('vet');

        $response = $this->post('invoice/bill_pay/' . $s['bill'], ['cash_value' => '100', 'card_value' => '0', 'transfer_value' => '0']);

        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $s['bill']);
        $this->assertSame(1, $this->billStatus($s['bill']));
        $this->assertSame(0, $this->countWhere('payment', ['bill_id' => $s['bill']]));
        $this->assertSame('0', (string) $this->find('events', $s['event'])['status']);
        $this->assertEqualsWithDelta(50.0, $this->lotVolume($s['lot']), 0.001);
        // positive control: the same post with submit=1 does pay
        $this->pay($s['bill'], 100.0);
        $this->assertSame(4, $this->billStatus($s['bill']));
    }

    public function testAnUnpaidStatusBillCanBePaid(): void
    {
        $s = $this->pendingBill(2);
        $this->loginAs('vet');

        $this->pay($s['bill'], 100.0);

        $this->assertSame(4, $this->billStatus($s['bill']));
    }
}
