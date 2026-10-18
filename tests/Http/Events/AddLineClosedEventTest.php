<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T116 - add_line on an event that is not OPEN answers with an empty body and inserts nothing. */
final class AddLineClosedEventTest extends HttpTestCase
{
    public function testNothingIsAddedToAClosedEvent(): void
    {
        $closed = EventsHelper::openEvent([], [], 1);
        $history = EventsHelper::openEvent([], [], 2);
        $open = EventsHelper::openEvent();
        $procedure = Factory::procedure();
        $product = Factory::product();
        $this->loginAs('vet');

        foreach ([$closed['event'], $history['event']] as $event) {
            foreach ([[0, $procedure], [1, $product]] as [$type, $line]) {
                $response = $this->ajaxPost('events/add_line/' . $event . '/' . $type, [
                    'line' => $line, 'title' => 'x', 'volume' => 1, 'btw' => 6,
                    'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
                ]);
                $this->assertStatus($response, 200);
                $this->assertSame('', trim((string) $response->getBody()));
            }
            $this->assertRowCount(0, 'events_procedures', ['event_id' => $event]);
            $this->assertRowCount(0, 'events_products', ['event_id' => $event]);
        }

        // positive control: the same request on an open event works
        $json = $this->json($this->ajaxPost('events/add_line/' . $open['event'] . '/1', [
            'line' => $product, 'title' => 'x', 'volume' => 1, 'btw' => 6,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));
        $this->assertRowCount(1, 'events_products', ['event_id' => $open['event']]);
        $this->assertGreaterThan(0, (int) $json['return']);
    }
}
