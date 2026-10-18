<?php

declare(strict_types=1);

/**
 * T145 - Invoice::get_bill with an unknown, negative (BILL_INVALID) or soft-deleted bill renders
 * the "invalid bill" view (a link that goes back) instead of a bill, with status 200.
 * The text comes from the language file (english: "Invalid bill or invoice ...").
 */
final class InvalidBillTest extends HttpTestCase
{
    private function assertInvalid($page): void
    {
        $this->assertStatus($page, 200);
        $this->assertMatchesRegularExpression('/Invalid bill or invoice|Ongeldige rekening of factuur/', $this->text($page));
        $this->assertStringContainsString('goBack()', (string) $page->getBody());
        $this->assertDontSee($page, 'Rekening #');
    }

    public function testMinusOneRendersTheInvalidView(): void
    {
        $this->loginAs('vet');
        $this->assertInvalid($this->get('invoice/get_bill/-1'));
    }

    public function testAnUnknownBillRendersTheInvalidView(): void
    {
        $this->loginAs('vet');
        $this->assertInvalid($this->get('invoice/get_bill/999999'));
    }

    public function testASoftDeletedBillRendersTheInvalidViewAndIsNotTouched(): void
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner), 0, 0);
        $deleted = Factory::bill($owner, 0, ['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $this->assertInvalid($this->get('invoice/get_bill/' . $deleted));

        $this->assertSame('0', (string) $this->find('events', $event)['payment'], 'no events are attached to it');
        $this->assertSame('0', (string) $this->find('bills', $deleted)['status']);
    }

    public function testPositiveControlAValidBillIsNotInvalid(): void
    {
        $owner = Factory::owner();
        Factory::event(Factory::pet($owner), 0, 0);
        $bill = Factory::bill($owner, 1);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $bill);

        $this->assertStatus($page, 200);
        $this->assertDontSee($page, 'Invalid bill or invoice');
        $this->assertDontSee($page, 'Ongeldige rekening of factuur');
    }
}
