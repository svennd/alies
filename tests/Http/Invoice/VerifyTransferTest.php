<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T142 - Invoice::verify: the transfer of an ONHOLD bill has arrived. The bill becomes PAID with
 * transfer_verified = 1 and the request redirects to the bill overview. Done with a GET request.
 */
final class VerifyTransferTest extends HttpTestCase
{
    public function testVerifyingAnOnHoldBillMakesItPaid(): void
    {
        $bill = Factory::bill(Factory::owner(), 6, ['transfer' => 50.0, 'total_brut' => 50.0]);
        $this->loginAs('vet');
        $this->assertSame('0', (string) $this->find('bills', $bill)['transfer_verified']);
        $before = $this->logCount('verify_transfer');

        $response = $this->get('invoice/verify/' . $bill);

        $this->assertRedirectsTo($response, 'invoice');
        $row = $this->find('bills', $bill);
        $this->assertSame('4', (string) $row['status']);
        $this->assertSame('1', (string) $row['transfer_verified']);
        $this->assertSame($before + 1, $this->logCount('verify_transfer'));
    }

    public function testOnlyTheGivenBillChanges(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 6);
        $other = Factory::bill($owner, 6);
        $this->loginAs('vet');

        $this->get('invoice/verify/' . $bill);

        $this->assertSame('6', (string) $this->find('bills', $other)['status']);
        $this->assertSame('0', (string) $this->find('bills', $other)['transfer_verified']);
    }

    public function testAnonymousVisitorsCannotVerify(): void
    {
        $bill = Factory::bill(Factory::owner(), 6);

        $this->get('invoice/verify/' . $bill);

        $this->assertSame('6', (string) $this->find('bills', $bill)['status'], 'unchanged without login');
        // positive control
        $this->loginAs('vet');
        $this->get('invoice/verify/' . $bill);
        $this->assertSame('4', (string) $this->find('bills', $bill)['status']);
    }

    #[Group('known-bug')]
    public function testItAlsoWorksFromAnyOtherStatusAndChangesDataWithAGet(): void
    {
        // KNOWN BUG: no status check (a DRAFT becomes PAID without any payment) and a GET mutates data
        // - decide in CI4 whether to keep
        $draft = Factory::bill(Factory::owner(), 0);
        $this->loginAs('vet');

        $this->get('invoice/verify/' . $draft);

        $this->assertSame('4', (string) $this->find('bills', $draft)['status']);
        $this->assertSame('1', (string) $this->find('bills', $draft)['transfer_verified']);
        $this->assertNull($this->find('bills', $draft)['invoice_id'], 'and no invoice number');
    }
}
