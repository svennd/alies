<?php

declare(strict_types=1);

require_once __DIR__ . '/InvoiceHelper.php';

/**
 * T150 - a bill that has `liquidate` rows (a stock write-off) is rendered as a write-off bill.
 * Source: Invoice::get_bill (write-off branch), get_write_off_bill_data, Liquidate_model::get_bill_rows.
 *
 * Rules: totals are 0 and no events are attached (the branch skips attaching and calculating);
 * one pseudo "pet" block with the write-off title lists every row as
 * "name (lot X | EOL dd-mm-YYYY)" with its volume and price 0; the location label (printed in the
 * pdf) is the one location when all rows share it, "Multiple" when they differ.
 * The language is dutch or english depending on the user; the test accepts both labels.
 */
final class WriteOffBillTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        InvoiceHelper::cleanUp();
        parent::tearDown();
    }

    private function liquidate(int $bill, array $overrides = []): int
    {
        return Factory::insert('liquidate', $overrides + [
            'product_id' => Fixtures::PRODUCT_TIERED,
            'product_name' => 'Writeoffproduct',
            'volume' => 3.0,
            'eol' => '2027-03-05',
            'lotnr' => 'LOTX1',
            'reason' => 'expired',
            'user' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'stock_id' => 0,
            'bill_id' => $bill,
            'created_at' => Factory::raw('NOW()'),
            'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    /** @return array{owner: int, bill: int, event: int} */
    private function writeOffBill(array $billOverrides = []): array
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner), 0, 0);
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 10.00, 'btw' => 6]);
        $bill = Factory::bill($owner, 1, $billOverrides);

        return compact('owner', 'bill', 'event');
    }

    public function testTheTotalsAreZeroAndNoEventIsAttached(): void
    {
        $s = $this->writeOffBill(['total_brut' => 0, 'total_net' => 0]);
        $this->liquidate($s['bill']);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $s['bill']);

        $this->assertStatus($page, 200);
        $this->assertSame('0', (string) $this->find('events', $s['event'])['payment'], 'the open event of the owner stays free');
        $row = $this->find('bills', $s['bill']);
        $this->assertSame('1', (string) $row['status'], 'no recalculation: status unchanged');
        $this->assertEqualsWithDelta(0.0, (float) $row['total_brut'], 0.001);
        $this->assertSee($page, '0.00');
        $this->assertDontSee($page, '10.60');
        // positive control: the same owner with a bill without write-off rows does attach the event
        $normal = Factory::bill($s['owner'], 1);
        $this->get('invoice/get_bill/' . $normal);
        $this->assertSame((string) $normal, (string) $this->find('events', $s['event'])['payment']);
    }

    public function testEachRowShowsNameLotAndEndOfLife(): void
    {
        $s = $this->writeOffBill();
        $this->liquidate($s['bill'], ['product_name' => 'Alphaproduct', 'lotnr' => 'LOT-A', 'eol' => '2027-03-05']);
        $this->liquidate($s['bill'], ['product_name' => 'Betaproduct', 'lotnr' => '', 'eol' => '2028-12-31']);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $s['bill']);

        $this->assertSee($page, 'Alphaproduct (lot LOT-A | EOL 05-03-2027)');
        $this->assertSee($page, 'Betaproduct (EOL 31-12-2028)');
        $this->assertMatchesRegularExpression('/Stock write-off|Stock afschrijving/', $this->text($page));
    }

    public function testARowWithoutNameFallsBackToTheWordProduct(): void
    {
        $s = $this->writeOffBill();
        $this->liquidate($s['bill'], ['product_name' => null, 'lotnr' => 'LOT-N', 'eol' => '2027-01-02']);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $s['bill']);

        $this->assertMatchesRegularExpression('/(Product|Produkt) \(lot LOT-N \| EOL 02-01-2027\)/', $this->text($page));
    }

    public function testTheRowsOfOtherBillsAreNotShown(): void
    {
        $s = $this->writeOffBill();
        $other = $this->writeOffBill();
        $this->liquidate($s['bill'], ['product_name' => 'Mineproduct']);
        $this->liquidate($other['bill'], ['product_name' => 'Foreignproduct']);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $s['bill']);

        $this->assertSee($page, 'Mineproduct');
        $this->assertDontSee($page, 'Foreignproduct');
    }

    public function testThePdfNamesTheLocationWhenAllRowsShareOne(): void
    {
        $s = $this->writeOffBill();
        $this->liquidate($s['bill'], ['location' => Fixtures::LOCATION_CAR]);
        $this->liquidate($s['bill'], ['location' => Fixtures::LOCATION_CAR, 'product_name' => 'Second']);
        $this->loginAs('vet');

        $text = InvoiceHelper::pdfText((string) $this->get('invoice/get_bill/' . $s['bill'] . '/1')->getBody());

        $this->assertStringContainsString('Wagen', $text);
        $this->assertStringNotContainsString('Praktijk : Praktijk', $text);
        $this->assertDoesNotMatchRegularExpression('/(: |^)(Meerdere|Multiple)/m', $text);
    }

    public function testThePdfSaysMultipleWhenTheRowsComeFromDifferentLocations(): void
    {
        $s = $this->writeOffBill();
        $this->liquidate($s['bill'], ['location' => Fixtures::LOCATION_CAR]);
        $this->liquidate($s['bill'], ['location' => Fixtures::LOCATION_PRACTICE, 'product_name' => 'Second']);
        $this->loginAs('vet');

        $text = InvoiceHelper::pdfText((string) $this->get('invoice/get_bill/' . $s['bill'] . '/1')->getBody());

        $this->assertMatchesRegularExpression('/(Meerdere|Multiple)/', $text);
        $this->assertStringNotContainsString('Wagen', $text);
    }
}
