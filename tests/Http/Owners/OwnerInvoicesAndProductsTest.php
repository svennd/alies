<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T054 - Owners::invoices (bills of a client) and Owners::products (products bought by a client).
 * Both default to the last 12 months; a POST with search_from / search_to chooses the range.
 * The range of both is applied to the creation date of the BILL.
 */
final class OwnerInvoicesAndProductsTest extends HttpTestCase
{
    private function daysAgo(int $days): RawSql
    {
        return Factory::raw('DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)');
    }

    // ---- invoices ---------------------------------------------------------------------------

    public function testTheLastTwelveMonthsAreListedByDefaultNewestFirst(): void
    {
        $owner = Factory::owner(['last_name' => 'Invoiceowner']);
        $old = Factory::bill($owner, 1, ['total_brut' => 11.11, 'created_at' => $this->daysAgo(400)]);
        $lastYear = Factory::bill($owner, 1, ['total_brut' => 22.22, 'created_at' => $this->daysAgo(300)]);
        $recent = Factory::bill($owner, 1, ['total_brut' => 33.33, 'created_at' => $this->daysAgo(5)]);
        $this->loginAs('vet');

        $page = $this->get('owners/invoices/' . $owner);
        $body = (string) $page->getBody();

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Invoiceowner');
        $this->assertStringContainsString('invoice/get_bill/' . $recent, $body);
        $this->assertStringContainsString('invoice/get_bill/' . $lastYear, $body);
        $this->assertStringNotContainsString('invoice/get_bill/' . $old, $body, 'older than 12 months');
        // ordered by id, newest first
        $this->assertLessThan(strpos($body, 'invoice/get_bill/' . $lastYear), strpos($body, 'invoice/get_bill/' . $recent));
        $this->assertSee($page, '33.33');
    }

    public function testTheDefaultRangeIsShownInTheForm(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');

        $body = (string) $this->get('owners/invoices/' . $owner)->getBody();

        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-12 months')) . '"', $body);
        $this->assertStringContainsString('value="' . date('Y-m-d') . '"', $body);
    }

    public function testAPostedRangeChoosesTheBills(): void
    {
        $owner = Factory::owner();
        $old = Factory::bill($owner, 1, ['created_at' => $this->daysAgo(400)]);
        $recent = Factory::bill($owner, 1, ['created_at' => $this->daysAgo(5)]);
        $this->loginAs('vet');

        $everything = (string) $this->post('owners/invoices/' . $owner, ['search_from' => '2000-01-01', 'search_to' => date('Y-m-d')])->getBody();
        $onlyOld = (string) $this->post('owners/invoices/' . $owner, [
            'search_from' => date('Y-m-d', strtotime('-500 days')),
            'search_to' => date('Y-m-d', strtotime('-300 days')),
        ])->getBody();

        $this->assertStringContainsString('invoice/get_bill/' . $old, $everything);
        $this->assertStringContainsString('invoice/get_bill/' . $recent, $everything);
        $this->assertStringContainsString('invoice/get_bill/' . $old, $onlyOld);
        $this->assertStringNotContainsString('invoice/get_bill/' . $recent, $onlyOld);
    }

    public function testASoftDeletedBillAndTheBillsOfOtherClientsAreNotListed(): void
    {
        $owner = Factory::owner();
        $deleted = Factory::bill($owner, 1, ['deleted_at' => Factory::raw('NOW()')]);
        $foreign = Factory::bill(Factory::owner(), 1);
        $mine = Factory::bill($owner, 1);
        $this->loginAs('vet');

        $body = (string) $this->get('owners/invoices/' . $owner)->getBody();

        $this->assertStringContainsString('invoice/get_bill/' . $mine, $body);
        $this->assertStringNotContainsString('invoice/get_bill/' . $deleted, $body);
        $this->assertStringNotContainsString('invoice/get_bill/' . $foreign, $body);
    }

    public function testABillWithoutInvoiceNumberShowsItsBillNumberAndAFinishedOneItsInvoiceNumber(): void
    {
        $owner = Factory::owner();
        $open = Factory::bill($owner, 1);
        $paid = Factory::bill($owner, 4, ['invoice_id' => 42, 'invoice_date' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $page = $this->get('owners/invoices/' . $owner);

        // bill number: 6 digits; invoice number: year + 5 digits (the seed turns the year prefix on)
        $this->assertSee($page, '#' . str_pad((string) $open, 6, '0', STR_PAD_LEFT));
        $this->assertSee($page, '#' . date('Y') . '00042');
        $this->assertDontSee($page, '#' . str_pad((string) $paid, 6, '0', STR_PAD_LEFT));
    }

    public function testEachBillShowsItsVetAndLocation(): void
    {
        $owner = Factory::owner();
        Factory::bill($owner, 1, ['vet' => Fixtures::USER_VET, 'location' => Fixtures::LOCATION_CAR]);
        $this->loginAs('vet');

        $page = $this->get('owners/invoices/' . $owner);

        $this->assertSee($page, 'Victor');
        $this->assertSee($page, 'Wagen');
    }

    public function testAClientWithoutBillsGivesAnEmptyTable(): void
    {
        $owner = Factory::owner(['last_name' => 'Nobills']);
        $this->loginAs('vet');

        $page = $this->get('owners/invoices/' . $owner);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Nobills');
        $this->assertStringNotContainsString('invoice/get_bill/', (string) $page->getBody());
    }

    #[Group('known-bug')]
    public function testTheDatesAreNotEscapedSoAQuoteBreaksTheQuery(): void
    {
        // KNOWN BUG (SQL injection): search_from / search_to are pasted into the SQL as they come
        $owner = Factory::owner();
        $this->loginAs('vet');

        $invoices = $this->post('owners/invoices/' . $owner, ['search_from' => '2020-01-01"', 'search_to' => date('Y-m-d')]);
        $products = $this->post('owners/products/' . $owner, ['search_from' => "2020-01-01'", 'search_to' => date('Y-m-d')]);

        $this->assertStatus($invoices, 500);
        $this->assertSee($invoices, 'Database');
        $this->assertStatus($products, 500);
    }

    // ---- products ---------------------------------------------------------------------------

    /** @return array{owner: int, pet: int, bill: int, event: int} */
    private function boughtProduct(string $petName, float $volume, int $billAgeInDays = 1, ?int $product = null): array
    {
        $owner = Factory::owner(['last_name' => 'Productowner']);
        $pet = Factory::pet($owner, ['name' => $petName]);
        $bill = Factory::bill($owner, 4, ['created_at' => $this->daysAgo($billAgeInDays)]);
        $event = Factory::event($pet, 1, $bill);
        Factory::eventProduct($event, $product ?? Fixtures::PRODUCT_TIERED, $volume);

        return ['owner' => $owner, 'pet' => $pet, 'bill' => $bill, 'event' => $event];
    }

    public function testTheProductsOfTheBilledConsultationsAreListedWithVolumeAndPet(): void
    {
        $data = $this->boughtProduct('Productpet', 2.5);
        $this->loginAs('vet');

        $page = $this->get('owners/products/' . $data['owner']);
        $body = (string) $page->getBody();

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Amoxi Tabs');
        $this->assertSee($page, '2.50 tab');
        $this->assertSee($page, 'Productpet');
        $this->assertStringContainsString('products/profile/' . Fixtures::PRODUCT_TIERED, $body);
        $this->assertStringContainsString('pets/fiche/' . $data['pet'], $body);
        $this->assertSee($page, date('d-m-Y'), 'the date of the consultation');
    }

    public function testOnlyBillsInTheRangeCount(): void
    {
        $data = $this->boughtProduct('Oldpet', 1.0, 400);
        $this->loginAs('vet');

        $this->assertDontSee($this->get('owners/products/' . $data['owner']), 'Oldpet');

        $page = $this->post('owners/products/' . $data['owner'], ['search_from' => '2000-01-01', 'search_to' => date('Y-m-d')]);
        $this->assertSee($page, 'Oldpet');
    }

    public function testProductsOfOtherClientsAndUnbilledConsultationsAreNotListed(): void
    {
        $mine = $this->boughtProduct('Mypet', 1.0);
        $other = $this->boughtProduct('Otherpet', 1.0);
        // a consultation of my pet that is not on any bill yet
        $openEvent = Factory::event($mine['pet'], 0, 0);
        Factory::eventProduct($openEvent, Fixtures::PRODUCT_VACCINE, 1.0);
        $this->loginAs('vet');

        $page = $this->get('owners/products/' . $mine['owner']);

        $this->assertSee($page, 'Mypet');
        $this->assertDontSee($page, 'Otherpet');
        $this->assertDontSee($page, 'Nobivac Vaccin', 'not billed yet');
    }

    public function testSeveralProductsOfOneConsultationAreAllListed(): void
    {
        $data = $this->boughtProduct('Multipet', 1.0);
        Factory::eventProduct($data['event'], Fixtures::PRODUCT_VACCINE, 1.0);
        $this->loginAs('vet');

        $page = $this->get('owners/products/' . $data['owner']);

        $this->assertSee($page, 'Amoxi Tabs');
        $this->assertSee($page, 'Nobivac Vaccin');
    }

    public function testSoftDeletedBillsStillShowTheirProducts(): void
    {
        // the products query selects the bills with raw SQL, which does not look at deleted_at
        $data = $this->boughtProduct('Deletedbillpet', 1.0);
        $this->db()->exec('UPDATE bills SET deleted_at = NOW() WHERE id = ' . $data['bill']);
        $this->loginAs('vet');

        $this->assertSee($this->get('owners/products/' . $data['owner']), 'Deletedbillpet');
    }

    public function testBothPagesNeedALogin(): void
    {
        $this->assertRedirectsTo($this->get('owners/invoices/' . Fixtures::OWNER_JANSSENS), 'auth/login');
        $this->assertRedirectsTo($this->get('owners/products/' . Fixtures::OWNER_JANSSENS), 'auth/login');
    }

    #[Group('known-bug')]
    public function testAnUnknownClientGivesTheSameKindOfPageAsAnUnknownDetailPage(): void
    {
        // KNOWN BUG: no existence check on any of the owner pages
        $this->loginAs('vet');

        $detail = $this->get('owners/detail/999999');

        $this->assertSame($detail->getStatusCode(), $this->get('owners/invoices/999999')->getStatusCode());
        $this->assertSame($detail->getStatusCode(), $this->get('owners/products/999999')->getStatusCode());
    }
}
