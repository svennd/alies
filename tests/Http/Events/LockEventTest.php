<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/EventsHelper.php';

/** T104 - Events::lock sets status CLOSED on a GET, without any state check. */
final class LockEventTest extends HttpTestCase
{
    #[Group('known-bug')]
    public function testLockingAnOpenEventClosesIt(): void
    {
        // KNOWN BUG: a GET request changes the status - decide in CI4 whether to keep
        $e = EventsHelper::openEvent();
        $this->loginAs('vet');

        $response = $this->get('events/lock/' . $e['event']);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertSame(1, (int) $this->find('events', $e['event'])['status']);
    }

    public function testLockingAnAlreadyClosedEventChangesNothing(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $this->loginAs('vet');

        $response = $this->get('events/lock/' . $e['event']);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertSame(1, (int) $this->find('events', $e['event'])['status']);
    }

    public function testLockingAHistoryEventReopensItToClosed(): void
    {
        // no state check: even a HISTORY (2) event is set back to CLOSED (1)
        $e = EventsHelper::openEvent([], [], 2);
        $this->loginAs('vet');

        $this->get('events/lock/' . $e['event']);

        $this->assertSame(1, (int) $this->find('events', $e['event'])['status']);
    }
}
