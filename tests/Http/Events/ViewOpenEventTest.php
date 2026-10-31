<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T101 - Events::event for an OPEN event shows the working screen (lines, lot number, lock button);
 * a CLOSED event shows the report screen (lab link form) instead.
 */
final class ViewOpenEventTest extends HttpTestCase
{
    public function testTheOpenEventShowsPetOwnerLinesAndLot(): void
    {
        $owner = Factory::owner(['last_name' => 'Openowner']);
        $pet = Factory::pet($owner, ['name' => 'Openpet']);
        $event = Factory::event($pet);
        $product = Factory::product(['name' => 'Openproduct']);
        $stock = Factory::stock($product, ['lotnr' => 'LOT-OPEN-77']);
        Factory::eventProduct($event, $product, 1.0, ['stock_id' => $stock]);
        $procedure = Factory::procedure(['name' => 'Openprocedure']);
        Factory::eventProcedure($event, $procedure);
        $this->loginAs('vet');

        $page = $this->get('events/event/' . $event);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Openpet');
        $this->assertSee($page, 'Openowner');
        $this->assertSee($page, 'Openproduct');
        $this->assertSee($page, 'Openprocedure');
        $this->assertSee($page, 'LOT-OPEN-77');
        $body = (string) $page->getBody();
        $this->assertStringContainsString('events/lock/' . $event, $body, 'open view has the lock button');
        $this->assertStringNotContainsString('event-lab-link-form', $body);
    }

    public function testAClosedEventWithAPaidBillShowsTheReportView(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['name' => 'Closedpet']);
        $bill = Factory::bill($owner, 4);
        $event = Factory::event($pet, 1, $bill);
        $this->loginAs('vet');

        $page = $this->get('events/event/' . $event);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringContainsString('event-lab-link-form', $body, 'report view');
        $this->assertStringContainsString('events_report/update_report/' . $event, $body);
        $this->assertStringNotContainsString('events/lock/' . $event, $body, 'no lock button in the report view');
        $this->assertSee($page, 'Closedpet');
    }
}
