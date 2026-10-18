<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T132 - Events_report::disable_history / enable_history / set_type. All are GET requests that change the event
 * and redirect to events/event/{id}.
 * The "hidden from the pet history" cross-check (T068) is not possible here: the only readers of no_history
 * are the Cli cron queries, the pet history page (Pets::fiche) does not filter on it.
 */
#[Group('known-bug')]
final class EventsReportFlagsTest extends HttpTestCase
{
    public function testDisableAndEnableHistoryToggleTheFlag(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $other = Factory::event(Factory::pet(Factory::owner()));
        $this->loginAs('vet');

        // KNOWN BUG: state change through GET — decide in CI4 whether to keep
        $off = $this->get('events_report/disable_history/' . $event);
        $this->assertRedirectsTo($off, 'events/event/' . $event);
        $this->assertSame(1, (int) $this->find('events', $event)['no_history']);
        $this->assertSame(0, (int) $this->find('events', $other)['no_history']);

        $on = $this->get('events_report/enable_history/' . $event);
        $this->assertRedirectsTo($on, 'events/event/' . $event);
        $this->assertSame(0, (int) $this->find('events', $event)['no_history']);
    }

    public function testSetTypeStoresTheType(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $this->loginAs('vet');

        $response = $this->get('events_report/set_type/' . $event . '/3');

        $this->assertRedirectsTo($response, 'events/event/' . $event);
        $this->assertSame(3, (int) $this->find('events', $event)['type']);
    }
}
