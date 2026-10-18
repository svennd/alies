<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T113 - add_line check_booking: a posted booking code that differs from booking_default (and is not 0)
 * wins, and its VAT replaces the posted VAT. With booking 0 (or equal to the default) the default code and
 * the posted VAT are used.
 */
final class AddLineBookingTest extends HttpTestCase
{
    private function add(int $event, int $procedure, array $post): array
    {
        return $this->json($this->ajaxPost('events/add_line/' . $event . '/0', $post + [
            'line' => $procedure, 'title' => 'x', 'volume' => 1,
        ]));
    }

    public function testAnotherBookingCodeOverridesThePostedVat(): void
    {
        $e = EventsHelper::openEvent();
        $procedure = Factory::procedure(['price' => 10.0]);
        $this->loginAs('vet');

        // booking 2 = services, 21 %; default is 1; the posted 6 % is ignored. brut = 10 * 1.21 = 12.10
        $json = $this->add($e['event'], $procedure, ['btw' => 6, 'booking' => Fixtures::BOOKING_SERVICES, 'booking_default' => Fixtures::BOOKING_PRODUCTS]);

        $row = $this->find('events_procedures', (int) $json['return']);
        $this->assertSame(21, (int) $row['btw']);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $row['booking']);
        $this->assertEqualsWithDelta(12.1, (float) $row['price_brut'], 0.001);
        $this->assertSame(21, (int) $json['btw']);
    }

    public function testBookingZeroUsesTheDefaultCodeAndThePostedVat(): void
    {
        $e = EventsHelper::openEvent();
        $procedure = Factory::procedure(['price' => 10.0]);
        $this->loginAs('vet');

        // the posted VAT (21) is kept even though code 1 is a 6 % code; brut = 12.10
        $json = $this->add($e['event'], $procedure, ['btw' => 21, 'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS]);

        $row = $this->find('events_procedures', (int) $json['return']);
        $this->assertSame(21, (int) $row['btw']);
        $this->assertSame(Fixtures::BOOKING_PRODUCTS, (int) $row['booking']);
        $this->assertEqualsWithDelta(12.1, (float) $row['price_brut'], 0.001);
    }

    public function testABookingEqualToTheDefaultKeepsThePostedVat(): void
    {
        $e = EventsHelper::openEvent();
        $procedure = Factory::procedure(['price' => 10.0]);
        $this->loginAs('vet');

        $json = $this->add($e['event'], $procedure, ['btw' => 0, 'booking' => Fixtures::BOOKING_SERVICES, 'booking_default' => Fixtures::BOOKING_SERVICES]);

        $row = $this->find('events_procedures', (int) $json['return']);
        $this->assertSame(0, (int) $row['btw'], 'posted VAT kept');
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $row['booking']);
        $this->assertEqualsWithDelta(10.0, (float) $row['price_brut'], 0.001);
    }

    public function testTheSameOverrideWorksForProducts(): void
    {
        $e = EventsHelper::openEvent();
        $product = Factory::product(['price' => 10.0]);
        $this->loginAs('vet');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => $product, 'title' => 'x', 'volume' => 2, 'btw' => 6,
            'booking' => Fixtures::BOOKING_SERVICES, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));

        // net 20, brut 20 * 1.21 = 24.20
        $row = $this->find('events_products', (int) $json['return']);
        $this->assertSame(21, (int) $row['btw']);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $row['booking']);
        $this->assertEqualsWithDelta(24.2, (float) $row['price_brut'], 0.001);
    }
}
