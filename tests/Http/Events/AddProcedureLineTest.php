<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T110 - Events::add_line with type 0 (procedure). unit price = procedures.price (never the posted one),
 * net = unit * volume, brut = net * round(1 + btw/100, 2).
 */
final class AddProcedureLineTest extends HttpTestCase
{
    public function testAProcedureLineIsPricedFromTheCatalogue(): void
    {
        $e = EventsHelper::openEvent();
        $procedure = Factory::procedure(['name' => 'Priced proc', 'price' => 25.00]);
        $this->loginAs('vet');

        $response = $this->ajaxPost('events/add_line/' . $e['event'] . '/0', [
            'line' => $procedure, 'title' => 'Priced proc', 'volume' => 2, 'btw' => 21,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_SERVICES,
        ]);

        // net 25 * 2 = 50.00, brut 50 * 1.21 = 60.50
        $json = $this->json($response);
        $this->assertSame(0, $json['type']);
        $this->assertEqualsWithDelta(50.0, (float) $json['net_price'], 0.001);
        $this->assertSame('60.50', $json['brut_price']);
        $this->assertSame(21, (int) $json['btw']);
        $this->assertSame($e['event'], (int) $json['event_id']);

        $row = $this->find('events_procedures', (int) $json['return']);
        $this->assertSame($e['event'], (int) $row['event_id']);
        $this->assertSame($procedure, (int) $row['procedures_id']);
        $this->assertEqualsWithDelta(25.0, (float) $row['unit_price'], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $row['volume'], 0.001);
        $this->assertEqualsWithDelta(50.0, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(60.5, (float) $row['price_brut'], 0.001);
        $this->assertSame(21, (int) $row['btw']);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $row['booking']);
    }

    public function testAFractionalVolumeAndSixPercent(): void
    {
        $e = EventsHelper::openEvent();
        $procedure = Factory::procedure(['price' => 10.00]);
        $this->loginAs('vet');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/0', [
            'line' => $procedure, 'title' => 'x', 'volume' => 1.5, 'btw' => 6,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));

        // net 10 * 1.5 = 15.00, brut 15 * 1.06 = 15.90
        $row = $this->find('events_procedures', (int) $json['return']);
        $this->assertEqualsWithDelta(15.0, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(15.9, (float) $row['price_brut'], 0.001);
        $this->assertSame('15.90', $json['brut_price']);
        $this->assertRowCount(1, 'events_procedures', ['event_id' => $e['event']]);
        $this->assertRowCount(0, 'events_products', ['event_id' => $e['event']]);
    }
}
