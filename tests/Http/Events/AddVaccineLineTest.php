<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T114 - adding a product with vaccin=1 creates a vaccine_pet reminder redo = today + vaccin_freq days. */
final class AddVaccineLineTest extends HttpTestCase
{
    public function testAVaccineLineCreatesAReminder(): void
    {
        $e = EventsHelper::openEvent();
        $this->loginAs('vet', Fixtures::LOCATION_CAR);
        $from = strtotime('+365 days');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => Fixtures::PRODUCT_VACCINE, 'title' => 'Posted vaccine name', 'volume' => 1, 'btw' => 6,
            'vaccin' => 1, 'vaccin_freq' => 365, 'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));
        $to = strtotime('+365 days');

        $this->assertTrue($json['vaccin']);
        $row = $this->row('SELECT * FROM vaccine_pet WHERE event_id = ?', [$e['event']]);
        $this->assertNotNull($row);
        $this->assertSame((int) $json['return'], (int) $row['event_line']);
        $this->assertSame($e['pet'], (int) $row['pet']);
        $this->assertSame(Fixtures::PRODUCT_VACCINE, (int) $row['product_id']);
        $this->assertContains($row['redo'], [date('Y-m-d', $from), date('Y-m-d', $to)]);
        $this->assertSame(0, (int) $row['no_rappel']);
        $this->assertSame('Posted vaccine name', $row['product']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $row['location']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['vet']);
    }

    public function testWithoutTheVaccinFlagNoReminderIsCreated(): void
    {
        $e = EventsHelper::openEvent();
        $this->loginAs('vet');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => Fixtures::PRODUCT_VACCINE, 'title' => 'v', 'volume' => 1, 'btw' => 6, 'vaccin_freq' => 365,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));

        $this->assertFalse($json['vaccin']);
        $this->assertRowCount(0, 'vaccine_pet', ['event_id' => $e['event']]);
    }
}
