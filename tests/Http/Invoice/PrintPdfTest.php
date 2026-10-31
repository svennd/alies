<?php

declare(strict_types=1);

require_once __DIR__ . '/InvoiceHelper.php';

/**
 * T146 - the invoice pdf. Source: Invoice::get_bill / generate_pdf, Pdf library.
 *
 * Rules (checked in the code, the spec was partly wrong):
 *  - get_bill/{id}/1 (INVOICE_PRINT) streams a freshly made pdf (inline) and does NOT store a file;
 *  - the plain bill page stores the pdf (data/stored/.invoices/{Y}/{W}/) when the bill is PAID or
 *    has an invoice number: "bill_{invoice number as shown}" (Y/W from invoice_date), or
 *    "check_{bill id, 6 digits}" (Y/W from created_at) when a PAID bill has no invoice number;
 *  - an unpaid bill without invoice number stores nothing.
 * Only files created here are removed; names use invoice numbers far away from real ones.
 */
final class PrintPdfTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        InvoiceHelper::cleanUp();
    }

    protected function tearDown(): void
    {
        InvoiceHelper::cleanUp();
        parent::tearDown();
    }

    /** @return array{owner: int, bill: int, number: int, date: string} a paid bill with one line */
    private function paidBill(array $overrides = []): array
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 4, $overrides + [
            'total_brut' => 10.60, 'total_net' => 10.00, 'BTW_6' => 10.00, 'cash' => 10.60,
            'invoice_id' => random_int(70000, 79999), 'invoice_date' => Factory::raw('NOW()'),
        ]);
        $event = Factory::event(Factory::pet($owner), 1, $bill);
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 10.00, 'btw' => 6]);
        $row = $this->find('bills', $bill);

        return ['owner' => $owner, 'bill' => $bill, 'number' => (int) $row['invoice_id'], 'date' => (string) $row['invoice_date']];
    }

    private function invoiceName(int $number): string
    {
        return 'bill_' . date('Y') . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    public function testThePrintModeStreamsAPdfInline(): void
    {
        $p = $this->paidBill();
        $this->loginAs('vet');

        $response = $this->get('invoice/get_bill/' . $p['bill'] . '/1');

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
        $this->assertStringContainsString('inline', strtolower($response->getHeaderLine('Content-Disposition')));
        $this->assertStringContainsString($this->invoiceName($p['number']), $response->getHeaderLine('Content-Disposition'));
    }

    public function testThePrintModeDoesNotStoreAFile(): void
    {
        $p = $this->paidBill();
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath($p['date'], $this->invoiceName($p['number'])));
        $this->loginAs('vet');

        $this->get('invoice/get_bill/' . $p['bill'] . '/1');

        $this->assertFileDoesNotExist($path);
    }

    public function testThePlainBillPageStoresTheInvoicePdfUnderYearAndIsoWeekOfTheInvoiceDate(): void
    {
        // a date in an earlier week to prove the folder comes from invoice_date and not from today
        $date = date('Y-m-d H:i:s', strtotime('-20 days'));
        $p = $this->paidBill(['invoice_date' => $date, 'created_at' => $date]);
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath($date, $this->invoiceName($p['number'])));
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $p['bill']);

        $this->assertStatus($page, 200);
        $this->assertFileExists($path);
        $this->assertStringContainsString('/' . date('Y', strtotime($date)) . '/' . date('W', strtotime($date)) . '/', $path);
        $this->assertStringStartsWith('%PDF', (string) file_get_contents($path));
    }

    public function testAnExistingStoredPdfIsNotRemadeByThePlainPage(): void
    {
        $p = $this->paidBill();
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath($p['date'], $this->invoiceName($p['number'])));
        $this->loginAs('vet');
        $this->get('invoice/get_bill/' . $p['bill']);
        $this->assertFileExists($path);
        file_put_contents($path, '%PDF-marker');

        $this->get('invoice/get_bill/' . $p['bill']);

        $this->assertSame('%PDF-marker', file_get_contents($path), 'the stored file is reused');
        // while the print mode always makes a fresh one
        $response = $this->get('invoice/get_bill/' . $p['bill'] . '/1');
        $this->assertStringNotContainsString('marker', (string) $response->getBody());
    }

    public function testAPaidBillWithoutInvoiceNumberIsStoredAsCheckWithTheBillNumber(): void
    {
        $p = $this->paidBill(['invoice_id' => null, 'invoice_date' => null]);
        $name = 'check_' . str_pad((string) $p['bill'], 6, '0', STR_PAD_LEFT);
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath(date('Y-m-d H:i:s'), $name));
        $this->loginAs('vet');

        $this->get('invoice/get_bill/' . $p['bill']);

        $this->assertFileExists($path);
    }

    public function testAnUnpaidBillWithoutInvoiceNumberStoresNoFile(): void
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner), 0, 0);
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 10.00, 'btw' => 6]);
        $bill = Factory::bill($owner, 1);
        $check = InvoiceHelper::register(InvoiceHelper::pdfPath(date('Y-m-d H:i:s'), 'check_' . str_pad((string) $bill, 6, '0', STR_PAD_LEFT)));
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $bill);

        $this->assertStatus($page, 200);
        $this->assertFileDoesNotExist($check);
        // the print mode of an unpaid bill is still a pdf, named "check"
        $response = $this->get('invoice/get_bill/' . $bill . '/1');
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
        $this->assertStringContainsString('check_' . str_pad((string) $bill, 6, '0', STR_PAD_LEFT), $response->getHeaderLine('Content-Disposition'));
        $this->assertFileDoesNotExist($check);
    }

    public function testABillWithAnInvoiceNumberButNotPaidIsStoredToo(): void
    {
        $p = $this->paidBill(['status' => 3]);
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath($p['date'], $this->invoiceName($p['number'])));
        $this->loginAs('vet');

        $this->get('invoice/get_bill/' . $p['bill']);

        $this->assertFileExists($path);
    }

    public function testThePdfOfAnOpenBillHasTheStructuredMessageAndTheIban(): void
    {
        // the payment block (IBAN, message, QR) is only printed while the bill is not fully paid
        $p = $this->paidBill(['status' => 3, 'cash' => 0]);
        $this->loginAs('vet');
        $pdf = (string) $this->get('invoice/get_bill/' . $p['bill'] . '/1')->getBody();
        $text = InvoiceHelper::pdfText($pdf);

        // setting struct_config 0 = client + bill: client id, zeros up to 10 digits minus the bill digits, bill id
        $bill = (string) $p['bill'];
        $owner = (string) $p['owner'];
        $message = (strlen($owner . $bill) > 10) ? str_pad($owner, 10, '0') : $owner . str_repeat('0', 10 - strlen($owner) - strlen($bill)) . $bill;
        $this->assertMatchesRegularExpression('#\+\+\+' . substr($message, 0, 3) . '/' . substr($message, 3, 4) . '/' . substr($message, 7, 3) . '\d\d\+\+\+#', $text);
        $this->assertStringContainsString('BE68 5390 0754 7034', $text);
        $this->assertStringContainsString(date('Y') . str_pad((string) $p['number'], 5, '0', STR_PAD_LEFT), $text);
        $this->assertStringContainsString('10.60', $text);
    }
}
