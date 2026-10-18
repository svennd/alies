<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T151 - Admin_invoice::edit_bill (admin only).
 * GET shows the form; a POST with submit updates vet, location, cash, card, transfer, status and
 * created_at (the form field "created") through a hand written SQL query and writes a WARN log row
 * "edit_bill". The amount field is accepted by the controller but the query ignores it, so
 * total_brut never changes. Staff that are not admin are sent to the home page.
 */
final class AdminEditBillTest extends HttpTestCase
{
    private function post_data(array $overrides = []): array
    {
        return $overrides + [
            'submit' => 1,
            'vet' => Fixtures::USER_VET2,
            'location' => Fixtures::LOCATION_CAR,
            'amount' => '999.99',
            'cash' => '10.50',
            'card' => '20.25',
            'transfer' => '5.00',
            'status' => '4',
            'created' => '2024-02-03 04:05:06',
        ];
    }

    private function bill(): int
    {
        return Factory::bill(Factory::owner(['last_name' => 'Editbillowner']), 1, ['total_brut' => 35.75, 'cash' => 1, 'card' => 2, 'transfer' => 3]);
    }

    public function testAnAdminSeesTheFormWithTheBillData(): void
    {
        $bill = $this->bill();
        $this->loginAs('admin');

        $page = $this->get('admin_invoice/edit_bill/' . $bill);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Editbillowner');
        $this->assertSee($page, '#' . str_pad((string) $bill, 6, '0', STR_PAD_LEFT));
        $this->assertStringContainsString('value="35.75"', (string) $page->getBody());
    }

    public function testASubmitUpdatesTheColumnsAndLogsIt(): void
    {
        $bill = $this->bill();
        $other = $this->bill();
        $this->loginAs('admin');
        $before = $this->logCount('edit_bill');

        $page = $this->post('admin_invoice/edit_bill/' . $bill, $this->post_data());

        $this->assertStatus($page, 200);
        $row = $this->find('bills', $bill);
        $this->assertSame((string) Fixtures::USER_VET2, (string) $row['vet']);
        $this->assertSame((string) Fixtures::LOCATION_CAR, (string) $row['location']);
        $this->assertEqualsWithDelta(10.50, (float) $row['cash'], 0.001);
        $this->assertEqualsWithDelta(20.25, (float) $row['card'], 0.001);
        $this->assertEqualsWithDelta(5.00, (float) $row['transfer'], 0.001);
        $this->assertSame('4', (string) $row['status']);
        $this->assertSame('2024-02-03 04:05:06', $row['created_at']);
        $this->assertSame($before + 1, $this->logCount('edit_bill'));
        $this->assertSame('1', (string) $this->find('bills', $other)['status'], 'other bills are not touched');
        $this->assertEqualsWithDelta(1.0, (float) $this->find('bills', $other)['cash'], 0.001);
        // the page shows the new state
        $this->assertSee($page, '20.25');
    }

    #[Group('known-bug')]
    public function testTheAmountIsIgnored(): void
    {
        // KNOWN BUG: the form field "amount" is passed on but never written (and the form shows it
        // as disabled, so a browser does not even send it) - decide in CI4 whether to keep
        $bill = $this->bill();
        $this->loginAs('admin');

        $this->post('admin_invoice/edit_bill/' . $bill, $this->post_data(['amount' => '999.99']));

        $this->assertEqualsWithDelta(35.75, (float) $this->find('bills', $bill)['total_brut'], 0.001);
    }

    public function testWithoutSubmitNothingChangesAndNothingIsLogged(): void
    {
        $bill = $this->bill();
        $this->loginAs('admin');
        $before = $this->logCount('edit_bill');

        $page = $this->post('admin_invoice/edit_bill/' . $bill, ['cash' => '77', 'status' => '4']);

        $this->assertStatus($page, 200);
        $this->assertEqualsWithDelta(1.0, (float) $this->find('bills', $bill)['cash'], 0.001);
        $this->assertSame($before, $this->logCount('edit_bill'));
    }

    public function testAVetIsSentToTheHomePageAndChangesNothing(): void
    {
        $bill = $this->bill();
        $this->loginAs('vet');

        $response = $this->post('admin_invoice/edit_bill/' . $bill, $this->post_data());

        $this->assertRedirectsTo($response, '');
        $this->assertSame('1', (string) $this->find('bills', $bill)['status']);
        $this->assertEqualsWithDelta(1.0, (float) $this->find('bills', $bill)['cash'], 0.001);
        $this->assertRedirectsTo($this->get('admin_invoice/edit_bill/' . $bill), '');
        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('admin_invoice/edit_bill/' . $bill, $this->post_data());
        $this->assertSame('4', (string) $this->find('bills', $bill)['status']);
    }
}
