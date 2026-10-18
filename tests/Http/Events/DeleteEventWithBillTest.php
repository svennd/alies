<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T134 - Events::del($event, $owner) for an event that is attached to a bill.
 * Status != 2 (not final): a PENDING bill without invoice_id is soft deleted, the other events of that bill are
 * released (payment 0), the event is deleted and "remove_event_with_bill" is logged.
 * Status 2 (history): nothing happens at all.
 */
final class DeleteEventWithBillTest extends HttpTestCase
{
    private const PENDING = 1;
    private const PAID = 4;

    public function testAPendingBillIsRemovedAndTheOtherEventIsReleased(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, self::PENDING);
        $e1 = Factory::event(Factory::pet($owner), 0, $bill);
        $e2 = Factory::event(Factory::pet($owner), 0, $bill);
        $logs = $this->logCount('remove_event_with_bill');
        $this->loginAs('vet');

        $response = $this->get('events/del/' . $e1 . '/' . $owner);

        $this->assertRedirectsTo($response, 'owners/detail/' . $owner);
        $this->assertNotNull($this->find('bills', $bill)['deleted_at']);
        $this->assertRowMissing('events', ['id' => $e1]);
        $this->assertSame(0, (int) $this->find('events', $e2)['payment']);
        $this->assertSame($logs + 1, $this->logCount('remove_event_with_bill'));
        $this->assertSame(
            'event_id: ' . $e1 . ' | bill_id: ' . $bill . ' | affected :1 | released: 1',
            $this->scalar("SELECT msg FROM log WHERE event='remove_event_with_bill' ORDER BY id DESC LIMIT 1")
        );
    }

    #[Group('known-bug')]
    public function testAPaidBillWithInvoiceKeepsTheBillButTheEventIsStillDeleted(): void
    {
        // KNOWN BUG: the event of a paid, invoiced bill is deleted anyway, leaving the bill without its event — decide in CI4 whether to keep
        $owner = Factory::owner();
        $bill = Factory::bill($owner, self::PAID, ['invoice_id' => 987654, 'invoice_date' => Factory::raw('NOW()')]);
        $e1 = Factory::event(Factory::pet($owner), 0, $bill);
        $e2 = Factory::event(Factory::pet($owner), 0, $bill);
        $this->loginAs('vet');

        $this->get('events/del/' . $e1 . '/' . $owner);

        $this->assertNull($this->find('bills', $bill)['deleted_at']);
        $this->assertRowMissing('events', ['id' => $e1]);
        $this->assertSame($bill, (int) $this->find('events', $e2)['payment']);
    }

    public function testAnEventInHistoryWithABillIsKept(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, self::PENDING);
        $e1 = Factory::event(Factory::pet($owner), 2, $bill);
        $e2 = Factory::event(Factory::pet($owner), 0, $bill);
        $logs = $this->logCount('remove_event_with_bill');
        $this->loginAs('vet');

        $response = $this->get('events/del/' . $e1 . '/' . $owner);

        $this->assertRedirectsTo($response, 'owners/detail/' . $owner);
        $this->assertRowExists('events', ['id' => $e1]);
        $this->assertNull($this->find('bills', $bill)['deleted_at']);
        $this->assertSame($bill, (int) $this->find('events', $e2)['payment']);
        $this->assertSame($logs, $this->logCount('remove_event_with_bill'));
    }
}
