<?php

declare(strict_types=1);

require_once __DIR__ . '/InvoiceHelper.php';

/**
 * T140 - the whole payment flow: invoice/bill -> invoice/get_bill -> invoice/bill_pay.
 *
 * Source: Invoice::bill / get_bill / bill_pay, Bills_model::calculate_bill / set_invoice_id,
 * Events_model::set_open_events_to_bills, Stock_model::reduce / fallback_reduce.
 *
 * Rules: opening a bill creates a DRAFT; get_bill attaches all open events of the owner and
 * calculates the totals (BILL_PENDING); bill_pay stores the payment, sets the bill PAID,
 * assigns the invoice number, closes the events and reduces the stock (lines with a lot: the
 * lot loses volume + dead volume; lines without a lot: a new stock row state 3 / NO_STOCK).
 *
 * Example: product A 2 x 10.00 at 6 % (lot of 50, dead volume 0.5), product B 1 x 100.00 at
 * 21 %, procedure 25.00 at 21 %:
 *   net = 20 + 100 + 25 = 145.00; BTW_6 base 20 -> 1.20; BTW_21 base 125 -> 26.25
 *   gross = 145 + 1.20 + 26.25 = 172.45
 */
final class FullPaymentFlowTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        InvoiceHelper::cleanUp();
        parent::tearDown();
    }

    public function testTheCompletePaymentFlowFromOpeningTheBillToTheInvoice(): void
    {
        $s = InvoiceHelper::standardOwner();
        $this->loginAs('vet');

        // 1. open the bill
        $response = $this->get('invoice/bill/' . $s['owner']);
        $bill = (int) $this->scalar('SELECT MAX(id) FROM bills WHERE owner_id = ?', [$s['owner']]);
        $this->assertGreaterThan(0, $bill);
        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $bill);
        $row = $this->find('bills', $bill);
        $this->assertSame('0', (string) $row['status'], 'a new bill is a DRAFT');
        $this->assertSame((string) Fixtures::USER_VET, (string) $row['vet']);
        $this->assertSame((string) Fixtures::LOCATION_PRACTICE, (string) $row['location']);
        $this->assertSame(date('Y-m-d'), $this->find('owners', $s['owner'])['last_bill']);
        $this->assertSame('0', (string) $this->find('events', $s['event'])['payment'], 'not attached yet');

        // 2. show the bill: attaches the events and calculates
        $page = $this->get('invoice/get_bill/' . $bill);
        $this->assertStatus($page, 200);
        $this->assertSame((string) $bill, (string) $this->find('events', $s['event'])['payment']);
        $row = $this->find('bills', $bill);
        $this->assertSame('1', (string) $row['status'], 'PENDING after the calculation');
        $this->assertEqualsWithDelta(145.00, (float) $row['total_net'], 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $row['BTW_6'], 0.001);
        $this->assertEqualsWithDelta(125.00, (float) $row['BTW_21'], 0.001);
        $this->assertEqualsWithDelta(0.00, (float) $row['BTW_0'], 0.001);
        $this->assertEqualsWithDelta(172.45, (float) $row['total_brut'], 0.001);
        $this->assertNull($row['invoice_id']);
        $this->assertSame('0', (string) $this->find('events', $s['event'])['status'], 'still open before payment');
        $this->assertSame('50.00', $this->find('stock', $s['lot'])['volume'], 'stock untouched before payment');
        $this->assertSee($page, '172.45');

        // 3. pay
        $expectedInvoice = (int) $this->scalar('SELECT COALESCE(MAX(invoice_id), 0) FROM bills WHERE YEAR(created_at) = ?', [date('Y')]) + 1;
        $stockRowsBefore = $this->countWhere('stock', ['product_id' => $s['productB']]);
        $response = $this->post('invoice/bill_pay/' . $bill, [
            'submit' => 1,
            'cash_value' => '100',
            'card_value' => '72.45',
            'transfer_value' => '0',
            'msg' => 'intern bericht',
            'msg_invoice' => 'factuur bericht',
        ]);
        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $bill);

        $this->assertSame('1', (string) $this->find('events', $s['event'])['status'], 'the event is closed');
        $payment = $this->row('SELECT * FROM payment WHERE bill_id = ?', [$bill]);
        $this->assertNotNull($payment, 'a payment row');
        $this->assertEqualsWithDelta(100.00, (float) $payment['cash'], 0.001);
        $this->assertEqualsWithDelta(72.45, (float) $payment['card'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $payment['transfer'], 0.001);
        $this->assertSame((string) Fixtures::USER_VET, (string) $payment['vet']);
        $this->assertSame((string) Fixtures::LOCATION_PRACTICE, (string) $payment['location']);

        $row = $this->find('bills', $bill);
        $this->assertSame('4', (string) $row['status'], 'PAID');
        $this->assertEqualsWithDelta(100.00, (float) $row['cash'], 0.001);
        $this->assertEqualsWithDelta(72.45, (float) $row['card'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $row['transfer'], 0.001);
        $this->assertSame('intern bericht', $row['msg']);
        $this->assertSame('factuur bericht', $row['msg_invoice']);
        $this->assertSame((string) $expectedInvoice, (string) $row['invoice_id']);
        $this->assertSame(date('Y-m-d'), substr((string) $row['invoice_date'], 0, 10));
        $this->assertSame('0', (string) $row['modified']);

        // stock: lot A loses 2 + dead volume 0.5
        $this->assertEqualsWithDelta(47.50, (float) $this->find('stock', $s['lot'])['volume'], 0.001);
        $this->assertSame('1', (string) $this->find('stock', $s['lot'])['state'], 'lot is still in use');
        // product B has no lot: one error row
        $this->assertSame($stockRowsBefore + 1, $this->countWhere('stock', ['product_id' => $s['productB']]));
        $noStock = $this->row('SELECT * FROM stock WHERE product_id = ?', [$s['productB']]);
        $this->assertSame('3', (string) $noStock['state']);
        $this->assertSame('NO_STOCK', $noStock['info']);
        $this->assertEqualsWithDelta(-1.0, (float) $noStock['volume'], 0.001);
        $this->assertSame((string) Fixtures::LOCATION_PRACTICE, (string) $noStock['location']);

        // 4. the bill page shows the invoice number: year + 5 digits (prefix setting is on)
        $page = $this->get('invoice/get_bill/' . $bill);
        $this->assertStatus($page, 200);
        $this->assertSee($page, date('Y') . str_pad((string) $expectedInvoice, 5, '0', STR_PAD_LEFT));
        $this->assertSee($page, '172.45');
        $this->assertSame('4', (string) $this->find('bills', $bill)['status'], 'viewing a paid bill changes nothing');
        $this->assertEqualsWithDelta(47.50, (float) $this->find('stock', $s['lot'])['volume'], 0.001);

        // the invoice pdf was stored while viewing the paid bill
        $pdf = InvoiceHelper::pdfPath((string) $row['invoice_date'], 'bill_' . date('Y') . str_pad((string) $expectedInvoice, 5, '0', STR_PAD_LEFT));
        InvoiceHelper::register($pdf);
    }

    public function testThePaymentIsTracedInTheLog(): void
    {
        $s = InvoiceHelper::standardOwner();
        $this->loginAs('vet');
        $this->get('invoice/bill/' . $s['owner']);
        $bill = (int) $this->scalar('SELECT MAX(id) FROM bills WHERE owner_id = ?', [$s['owner']]);
        $this->get('invoice/get_bill/' . $bill);
        $before = $this->countWhere('log', ['event' => 'bill_pay_complete']);

        $this->post('invoice/bill_pay/' . $bill, ['submit' => 1, 'cash_value' => '172.45', 'card_value' => '0', 'transfer_value' => '0']);

        $this->assertSame($before + 1, $this->countWhere('log', ['event' => 'bill_pay_complete']));
        $this->assertSame(1, $this->countWhere('log', ['event' => 'drop_from_stock', 'msg' => 'bill_id: ' . $bill . ' products : 2']));
        $this->assertSame(1, $this->countWhere('log', ['event' => 'generate_bill', 'msg' => 'bill_id: ' . $bill]));
        InvoiceHelper::register(InvoiceHelper::pdfPath((string) $this->find('bills', $bill)['invoice_date'], 'bill_' . date('Y') . str_pad((string) $this->find('bills', $bill)['invoice_id'], 5, '0', STR_PAD_LEFT)));
    }
}
