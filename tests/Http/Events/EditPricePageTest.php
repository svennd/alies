<?php

declare(strict_types=1);

/**
 * T125 - Events::edit_price. The page lists every product and procedure line of the event
 * with its unit price in an input field, and posts to events/edit_unit_price/{line}/{event}.
 */
final class EditPricePageTest extends HttpTestCase
{
    public function testItListsEveryProductAndProcedureLineWithItsUnitPrice(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet);
        $prod = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0, ['unit_price' => 12.34]);
        $proc = Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['unit_price' => 45.67]);
        $this->loginAs('vet');

        $page = $this->get('events/edit_price/' . $event);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringContainsString('name="unit_price" value="12.34"', $body);
        $this->assertStringContainsString('name="unit_price" value="45.67"', $body);
        $this->assertStringContainsString('events/edit_unit_price/' . $prod . '/' . $event, $body);
        $this->assertStringContainsString('events/edit_unit_price/' . $proc . '/' . $event, $body);
        // exactly the two lines of this event
        $this->assertSame(2, substr_count($body, 'name="unit_price"'));
        // totals: net 24.68 + 45.67 = 70.35
        $this->assertSee($page, '70.35');
    }

    public function testAnEventWithoutLinesStillRenders(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $this->loginAs('vet');

        $page = $this->get('events/edit_price/' . $event);

        $this->assertStatus($page, 200);
        $this->assertSame(0, substr_count((string) $page->getBody(), 'name="unit_price"'));
    }
}
