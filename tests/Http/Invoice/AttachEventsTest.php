<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T144 - Invoice::get_bill on a DRAFT/PENDING/UNPAID bill attaches ALL events of the owner's pets
 * that have no bill yet (payment = 0), whatever their status or location. Events of other
 * owners, events that already belong to a bill, and bills with another status stay untouched.
 */
final class AttachEventsTest extends HttpTestCase
{
    public function testAllEventsWithoutABillOfTheOwnersPetsAreAttached(): void
    {
        $owner = Factory::owner();
        $petA = Factory::pet($owner);
        $petB = Factory::pet($owner);
        $open = Factory::event($petA, 0, 0, ['location' => Fixtures::LOCATION_PRACTICE]);
        $closed = Factory::event($petB, 1, 0, ['location' => Fixtures::LOCATION_CAR]);
        $foreign = Factory::event(Factory::pet(Factory::owner()), 0, 0);
        $otherBill = Factory::bill($owner, 4);
        $alreadyBilled = Factory::event($petA, 1, $otherBill);
        $bill = Factory::bill($owner, 0);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $bill);

        $this->assertStatus($page, 200);
        $this->assertSame((string) $bill, (string) $this->find('events', $open)['payment']);
        $this->assertSame((string) $bill, (string) $this->find('events', $closed)['payment']);
        $this->assertSame('0', (string) $this->find('events', $foreign)['payment'], 'another owner');
        $this->assertSame((string) $otherBill, (string) $this->find('events', $alreadyBilled)['payment']);
        $this->assertSame('0', (string) $this->find('events', $open)['status'], 'attaching does not close');
        $this->assertSame('1', (string) $this->find('bills', $bill)['status'], 'DRAFT becomes PENDING');
    }

    public function testAHistoryEventIsAttachedToo(): void
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner), 2, 0);
        $bill = Factory::bill($owner, 1);
        $this->loginAs('vet');

        $this->get('invoice/get_bill/' . $bill);

        $this->assertSame((string) $bill, (string) $this->find('events', $event)['payment']);
    }

    public function testAnEventOfALateAddedPetIsPickedUpOnTheNextVisit(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 1);
        $this->loginAs('vet');
        $this->get('invoice/get_bill/' . $bill);
        $late = Factory::event(Factory::pet($owner), 0, 0);
        Factory::eventProduct($late, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 10.00, 'btw' => 6]);

        $this->get('invoice/get_bill/' . $bill);

        $this->assertSame((string) $bill, (string) $this->find('events', $late)['payment']);
        $this->assertEqualsWithDelta(10.60, (float) $this->find('bills', $bill)['total_brut'], 0.001);
    }

    public function testAPaidBillDoesNotAttachAnything(): void
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner), 0, 0);
        $paid = Factory::bill($owner, 4, ['total_brut' => 5.00]);
        $this->loginAs('vet');

        $this->get('invoice/get_bill/' . $paid);

        $this->assertSame('0', (string) $this->find('events', $event)['payment']);
        $this->assertEqualsWithDelta(5.00, (float) $this->find('bills', $paid)['total_brut'], 0.001, 'stored totals are shown, not recalculated');
        // positive control: an unpaid bill of the same owner does attach
        $pending = Factory::bill($owner, 1);
        $this->get('invoice/get_bill/' . $pending);
        $this->assertSame((string) $pending, (string) $this->find('events', $event)['payment']);
    }

    #[Group('known-bug')]
    public function testABillOfAnOwnerWithoutEventsGivesAServerError(): void
    {
        // KNOWN BUG: calculate_bill builds "event_id IN ()" for an owner without events: database error
        // (500) and the bill stays a DRAFT - decide in CI4 whether to keep
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 0);
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $bill);

        $this->assertStatus($page, 500);
        $this->assertSame('0', (string) $this->find('bills', $bill)['status']);
        // positive control: with one event the same bill works
        Factory::event(Factory::pet($owner), 0, 0);
        $this->assertStatus($this->get('invoice/get_bill/' . $bill), 200);
        $this->assertSame('1', (string) $this->find('bills', $bill)['status']);
    }
}
