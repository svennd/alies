<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/EventsHelper.php';

/**
 * T100 - Events::new_event. A GET starts a consultation for a pet. An OPEN event of the same pet at the
 * same location that is at most 14 days old is reused (only its vet changes), otherwise a new row is made.
 */
final class NewEventTest extends HttpTestCase
{
    private function eventsOf(int $pet): array
    {
        return $this->rows('SELECT * FROM events WHERE pet = ? ORDER BY id', [$pet]);
    }

    #[Group('known-bug')]
    public function testAPetWithoutOpenEventGetsANewOne(): void
    {
        // KNOWN BUG: a GET request creates a row - decide in CI4 whether to keep
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet', Fixtures::LOCATION_CAR);
        $before = $this->logCount('new_event');

        $response = $this->get('events/new_event/' . $pet);

        $events = $this->eventsOf($pet);
        $this->assertCount(1, $events);
        $this->assertRedirectsTo($response, 'events/event/' . $events[0]['id']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $events[0]['location'], 'location of the logged in user');
        $this->assertSame(Fixtures::USER_VET, (int) $events[0]['vet']);
        $this->assertSame(0, (int) $events[0]['status']);
        $this->assertSame($before + 1, $this->logCount('new_event'));
    }

    public function testARecentOpenEventAtTheSameLocationIsReusedAndGetsTheNewVet(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 0, 0, [
            'vet' => Fixtures::USER_VET2,
            'created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 3 DAY)'),
        ]);
        $this->loginAs('vet');
        $before = $this->logCount('update_restart_event');
        $newBefore = $this->logCount('new_event');

        $response = $this->get('events/new_event/' . $pet);

        $this->assertRedirectsTo($response, 'events/event/' . $event);
        $this->assertCount(1, $this->eventsOf($pet), 'no new event');
        $this->assertSame(Fixtures::USER_VET, (int) $this->find('events', $event)['vet']);
        $this->assertSame($before + 1, $this->logCount('update_restart_event'));
        $this->assertSame($newBefore, $this->logCount('new_event'));
    }

    public function testAnOpenEventOlderThanFourteenDaysIsNotReused(): void
    {
        $pet = Factory::pet(Factory::owner());
        $old = Factory::event($pet, 0, 0, ['created_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 20 DAY)')]);
        $this->loginAs('vet');

        $response = $this->get('events/new_event/' . $pet);

        $events = $this->eventsOf($pet);
        $this->assertCount(2, $events);
        $newId = (int) $events[1]['id'];
        $this->assertNotSame($old, $newId);
        $this->assertRedirectsTo($response, 'events/event/' . $newId);
    }

    public function testAnOpenEventAtAnotherLocationIsNotReused(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::event($pet, 0, 0, ['location' => Fixtures::LOCATION_CAR]);
        $this->loginAs('vet', Fixtures::LOCATION_PRACTICE);

        $this->get('events/new_event/' . $pet);

        $events = $this->eventsOf($pet);
        $this->assertCount(2, $events);
        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $events[1]['location']);
    }

    public function testAClosedRecentEventIsNotReused(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::event($pet, 1);
        $this->loginAs('vet');

        $this->get('events/new_event/' . $pet);

        $this->assertCount(2, $this->eventsOf($pet));
    }
}
