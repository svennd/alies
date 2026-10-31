<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T264 - export/kluwer/{from}/{to} (Export::kluwer, admin only): Expert/M import file.
 *
 * Rules: root ImportExpMPlus, encoded Windows-1252, with <Customers> (owners created or updated in the
 * window) and <Sales> (bills with invoice_id and invoice_date strictly inside the window). Customer:
 * VATStatus 1 only when the owner has a btw number, VATNumber is the btw number with the characters
 * "B" and "E" trimmed off. Sale: DocNumber = invoice year + 5 digit invoice id (prefix setting on),
 * DocDate d/m/Y, amounts with a decimal comma, VATAmount = total_brut - total_net. Details in order:
 * 400000 (gross, DebCre 1, Ventil 0), 451000 (VAT, DebCre -1, Ventil 11), then one line per booking code
 * of the products and one per booking code of the procedures (net, DebCre -1, Ventil 0%->1 6%->2 12%->3 21%->4).
 *
 * Golden file tests/Fixtures/golden/golden_export_kluwer.xml: written by hand from the rules above (the
 * amounts are worked out in setUpBeforeClass) and checked by eye; {OWNER_*}/{BILL_*} stand for generated ids.
 */
final class KluwerXmlTest extends HttpTestCase
{
    private const FROM = '2016-09-01';
    private const TO = '2016-09-30';

    /** @var array<string, int> */
    private static array $ids = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $inWindow = ['created_at' => '2016-09-02 10:00:00', 'updated_at' => '2016-09-02 10:00:00'];
        self::$ids['company'] = Factory::owner($inWindow + [
            'first_name' => 'Kees', 'last_name' => 'Kluwer Company', 'street' => 'Bedrijfslaan', 'nr' => '7', 'zip' => '2000',
            'city' => 'Antwerpen', 'btw_nr' => 'BE0123456789', 'telephone' => '031234567', 'mobile' => '0470123456',
            'mail' => 'kluwer@company.test', 'msg' => 'factureren per mail',
        ]);
        self::$ids['private'] = Factory::owner($inWindow + [
            'first_name' => 'Piet', 'last_name' => 'Kluwer Private', 'street' => 'Privaatweg', 'nr' => '1', 'zip' => '2018',
            'city' => 'Antwerpen', 'btw_nr' => null, 'telephone' => null, 'mobile' => null, 'mail' => null, 'msg' => '',
        ]);

        // bill 1: product 2 x 10.00 at 6% (net 20.00, brut 21.20), procedure 1 x 25.00 at 21% (net 25.00, brut 30.25)
        //         total_net 45.00, total_brut 51.45, vat 6.45
        self::$ids['bill1'] = Factory::bill(self::$ids['company'], 4, [
            'invoice_id' => 21, 'invoice_date' => '2016-09-10 11:00:00', 'created_at' => '2016-09-10 11:00:00', 'total_net' => 45.00, 'total_brut' => 51.45,
        ]);
        $pet = Factory::pet(self::$ids['company']);
        $event = Factory::event($pet, 1, self::$ids['bill1']);
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0, ['unit_price' => 10.00, 'btw' => 6]);
        Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['unit_price' => 25.00, 'btw' => 21]);

        // bill 2: product 1 x 5.00 at 6% (net 5.00, brut 5.30), procedure 1 x 30.00 at 21% (net 30.00, brut 36.30)
        //         total_net 35.00, total_brut 41.60, vat 6.60
        self::$ids['bill2'] = Factory::bill(self::$ids['private'], 4, [
            'invoice_id' => 22, 'invoice_date' => '2016-09-12 14:00:00', 'created_at' => '2016-09-12 14:00:00', 'total_net' => 35.00, 'total_brut' => 41.60,
        ]);
        $pet2 = Factory::pet(self::$ids['private']);
        $event2 = Factory::event($pet2, 1, self::$ids['bill2']);
        Factory::eventProduct($event2, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 5.00, 'btw' => 6]);
        Factory::eventProcedure($event2, Fixtures::PROCEDURE_CONSULT, 1.0, ['unit_price' => 30.00, 'btw' => 21]);

        // on the window edges (no events: they must be skipped before the booking lookup)
        self::$ids['opening'] = Factory::bill(self::$ids['private'], 4, ['invoice_id' => 23, 'invoice_date' => '2016-09-01 00:00:00', 'created_at' => '2016-09-01 00:00:00']);
        self::$ids['closing'] = Factory::bill(self::$ids['private'], 4, ['invoice_id' => 24, 'invoice_date' => '2016-09-30 23:59:30', 'created_at' => '2016-09-30 23:59:30']);
        self::$ids['uninvoiced'] = Factory::bill(self::$ids['private'], 1, ['invoice_date' => '2016-09-15 10:00:00', 'created_at' => '2016-09-15 10:00:00']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function body(string $from = self::FROM, string $to = self::TO): string
    {
        $response = $this->get('export/kluwer/' . $from . '/' . $to);
        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('text/xml', $response->getHeaderLine('Content-Type'));

        return (string) $response->getBody();
    }

    private function xml(): SimpleXMLElement
    {
        $xml = simplexml_load_string($this->body());
        $this->assertNotFalse($xml, 'well formed XML');

        return $xml;
    }

    private function sale(SimpleXMLElement $xml, int $bill): SimpleXMLElement
    {
        foreach ($xml->Sales->Sale as $sale) {
            if ((int) $sale->OurRef === $bill) {
                return $sale;
            }
        }
        $this->fail('bill ' . $bill . ' not in the export');
    }

    public function testMatchesTheGoldenFile(): void
    {
        $expected = trim(file_get_contents(__DIR__ . '/../../Fixtures/golden/golden_export_kluwer.xml'));
        $expected = str_replace(
            ['{OWNER_COMPANY}', '{OWNER_PRIVATE}', '{BILL_1}', '{BILL_2}'],
            [(string) self::$ids['company'], (string) self::$ids['private'], (string) self::$ids['bill1'], (string) self::$ids['bill2']],
            $expected
        );

        $this->assertSame($expected, trim($this->body()));
    }

    public function testTheDocumentIsWindows1252WithTheExpertMRoot(): void
    {
        $body = $this->body();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="Windows-1252"?>', $body);
        $this->assertSame('ImportExpMPlus', $this->xml()->getName());
    }

    public function testVatStatusAndNumberOfTheCustomers(): void
    {
        $xml = $this->xml();

        $customers = [];
        foreach ($xml->Customers->Customer as $customer) {
            $customers[(int) $customer->Prime] = $customer;
        }
        $this->assertSame([self::$ids['company'], self::$ids['private']], array_keys($customers));
        $this->assertSame('1', (string) $customers[self::$ids['company']]->VATStatus);
        $this->assertSame('0123456789', (string) $customers[self::$ids['company']]->VATNumber, '"BE" trimmed');
        $this->assertSame('0', (string) $customers[self::$ids['private']]->VATStatus, '"0" is kept');
        $this->assertCount(0, $customers[self::$ids['private']]->VATNumber);
    }

    public function testSaleHeaderUsesTheInvoiceNumberDateAndDecimalComma(): void
    {
        $sale = $this->sale($this->xml(), self::$ids['bill1']);

        $this->assertSame((string) self::$ids['company'], (string) $sale->Customer_Prime);
        $this->assertSame('10', (string) $sale->DocType);
        $this->assertSame('201600021', (string) $sale->DocNumber);
        $this->assertSame('10/09/2016', (string) $sale->DocDate);
        $this->assertSame('51,45', (string) $sale->Amount);
        $this->assertSame('6,45', (string) $sale->VATAmount);
        $this->assertSame('0', (string) $sale->Status);
    }

    public function testDetailLinesAreInTheDocumentedOrder(): void
    {
        $details = [];
        foreach ($this->sale($this->xml(), self::$ids['bill2'])->Details->Detail as $detail) {
            $details[] = [(string) $detail->Account, (string) $detail->Amount, (string) $detail->DebCre, (string) $detail->Ventil];
        }

        $this->assertSame([
            ['400000', '41,60', '1', '0'],
            ['451000', '6,60', '-1', '11'],
            ['700000', '5,00', '-1', '2'],   // products, booking code 1 (6%)
            ['701000', '30,00', '-1', '4'],  // procedures, booking code 2 (21%)
        ], $details);
    }

    public function testOnlyInvoicedBillsStrictlyInsideTheWindowAreSales(): void
    {
        $refs = [];
        foreach ($this->xml()->Sales->Sale as $sale) {
            $refs[] = (int) $sale->OurRef;
        }

        $this->assertSame([self::$ids['bill1'], self::$ids['bill2']], $refs, 'oldest first, edges and the bill without invoice number left out');
    }

    public function testAnEmptyWindowGivesEmptyCustomersAndSales(): void
    {
        $body = $this->body('2010-05-01', '2010-05-30');

        $this->assertStringContainsString('<Customers/><Sales/>', $body);
    }

    #[Group('known-bug')]
    public function testABillWithoutEventsBreaksTheWholeExport(): void
    {
        // KNOWN BUG: generate_booking_xml runs "IN ()" for a bill without events and the export dies with a 500 — decide in CI4 whether to keep
        $owner = Factory::owner(['created_at' => '2016-10-02 10:00:00', 'updated_at' => '2016-10-02 10:00:00']);
        Factory::bill($owner, 4, ['invoice_id' => 31, 'invoice_date' => '2016-10-10 10:00:00', 'created_at' => '2016-10-10 10:00:00']);

        $broken = $this->get('export/kluwer/2016-10-01/2016-10-31');
        $this->assertStatus($broken, 500);
        // positive control: the same window without that bill is fine
        $this->assertStatus($this->get('export/kluwer/2016-11-01/2016-11-30'), 200);
    }

    public function testOnlyAdminsMayExport(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export/kluwer/' . self::FROM . '/' . self::TO);

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('ImportExpMPlus', (string) $response->getBody());
    }
}
