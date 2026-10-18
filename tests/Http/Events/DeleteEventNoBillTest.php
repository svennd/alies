<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T133 - Events::del($event, $owner) for an event without bill (payment 0): the row is deleted,
 * log event "remove_event" is written and the browser goes to owners/detail/{owner}.
 */
final class DeleteEventNoBillTest extends HttpTestCase
{
    public function testTheEventIsDeletedAndLogged(): void
    {
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner));
        $keep = Factory::event(Factory::pet($owner));
        $logs = $this->logCount('remove_event');
        $this->loginAs('vet');

        $response = $this->get('events/del/' . $event . '/' . $owner);

        $this->assertRedirectsTo($response, 'owners/detail/' . $owner);
        $this->assertRowMissing('events', ['id' => $event]);
        $this->assertRowExists('events', ['id' => $keep]);
        $this->assertSame($logs + 1, $this->logCount('remove_event'));
        $this->assertSame('event_id: ' . $event, $this->scalar("SELECT msg FROM log WHERE event='remove_event' ORDER BY id DESC LIMIT 1"));
    }

    #[Group('known-bug')]
    public function testTheLinesOfTheEventAreLeftBehindAsOrphans(): void
    {
        // KNOWN BUG: events_products and events_procedures rows are not deleted with the event — decide in CI4 whether to keep
        $owner = Factory::owner();
        $event = Factory::event(Factory::pet($owner));
        $prod = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED);
        $proc = Factory::eventProcedure($event);
        $this->loginAs('vet');

        $this->get('events/del/' . $event . '/' . $owner);

        $this->assertRowMissing('events', ['id' => $event]);
        $this->assertRowExists('events_products', ['id' => $prod]);
        $this->assertRowExists('events_procedures', ['id' => $proc]);
    }
}
