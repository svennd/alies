<?php

declare(strict_types=1);

/**
 * T143 - Invoice::bill: an owner with a PENDING bill gets that bill again; with nothing or only
 * a DRAFT (or any other status) a NEW DRAFT is created. Only the PENDING status is reused.
 */
final class BillReuseTest extends HttpTestCase
{
    private function billCount(int $owner): int
    {
        return $this->countWhere('bills', ['owner_id' => $owner]);
    }

    public function testAPendingBillIsReusedAndNoRowIsCreated(): void
    {
        $owner = Factory::owner();
        $pending = Factory::bill($owner, 1);
        $this->loginAs('vet');

        $response = $this->get('invoice/bill/' . $owner);

        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $pending);
        $this->assertSame(1, $this->billCount($owner));
    }

    public function testAnOwnerWithoutBillsGetsANewDraft(): void
    {
        $owner = Factory::owner(['last_bill' => null]);
        $this->loginAs('vet', Fixtures::LOCATION_CAR);

        $response = $this->get('invoice/bill/' . $owner);

        $this->assertSame(1, $this->billCount($owner));
        $bill = $this->row('SELECT * FROM bills WHERE owner_id = ?', [$owner]);
        $this->assertRedirectsTo($response, 'invoice/get_bill/' . $bill['id']);
        $this->assertSame('0', (string) $bill['status']);
        $this->assertSame((string) Fixtures::LOCATION_CAR, (string) $bill['location'], 'the location of the session');
        $this->assertSame(date('Y-m-d'), $this->find('owners', $owner)['last_bill']);
    }

    public function testADraftIsNotReusedSoEveryVisitMakesAnotherDraft(): void
    {
        $owner = Factory::owner();
        $draft = Factory::bill($owner, 0);
        $this->loginAs('vet');

        $first = $this->get('invoice/bill/' . $owner);
        $second = $this->get('invoice/bill/' . $owner);

        $this->assertSame(3, $this->billCount($owner), 'the old draft plus two new ones');
        $this->assertStringNotContainsString('get_bill/' . $draft, (string) $first->getHeaderLine('Refresh'));
        $this->assertNotSame($first->getHeaderLine('Refresh'), $second->getHeaderLine('Refresh'));
    }

    public function testOtherStatusesAreNotReusedEither(): void
    {
        $owner = Factory::owner();
        foreach ([2, 3, 4, 6] as $status) {
            Factory::bill($owner, $status);
        }
        $this->loginAs('vet');

        $this->get('invoice/bill/' . $owner);

        $this->assertSame(5, $this->billCount($owner));
        $this->assertSame(1, $this->countWhere('bills', ['owner_id' => $owner, 'status' => 0]));
    }

    public function testAPendingBillOfAnotherOwnerIsNotReused(): void
    {
        $owner = Factory::owner();
        $foreign = Factory::bill(Factory::owner(), 1);
        $this->loginAs('vet');

        $this->get('invoice/bill/' . $owner);

        $this->assertSame(1, $this->billCount($owner));
        $this->assertSame('1', (string) $this->find('bills', $foreign)['status']);
    }

    public function testASoftDeletedPendingBillIsNotReused(): void
    {
        $owner = Factory::owner();
        Factory::bill($owner, 1, ['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $this->get('invoice/bill/' . $owner);

        $this->assertSame(1, $this->countWhere('bills', ['owner_id' => $owner, 'status' => 0]));
    }
}
