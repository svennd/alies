<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T127 - Events::edit_event_price($event, $pct) (GET). Every product and procedure line is reduced.
 * First reduction: unit = unit * (100-pct)/100 and price_ori_net := price_net.
 * Later reductions start from price_ori_net/volume (rounded to 2 decimals), so they do not compound.
 */
#[Group('known-bug')]
final class EditEventPriceReductionTest extends HttpTestCase
{
    /** @return array{0:int,1:int,2:int} event, product line, procedure line (unit 10, vol 2, 21%) */
    private function event(): array
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $prod = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0, ['unit_price' => 10.0, 'btw' => 21, 'price_ori_net' => 0]);
        $proc = Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 2.0, ['unit_price' => 10.0, 'btw' => 21, 'price_ori_net' => 0]);

        return [$event, $prod, $proc];
    }

    private function assertLine(string $table, int $id, float $unit, float $net, float $brut, float $ori): void
    {
        $row = $this->find($table, $id);
        $this->assertEqualsWithDelta($unit, (float) $row['unit_price'], 0.001, 'unit');
        $this->assertEqualsWithDelta($net, (float) $row['price_net'], 0.001, 'net');
        $this->assertEqualsWithDelta($brut, (float) $row['price_brut'], 0.001, 'brut');
        $this->assertEqualsWithDelta($ori, (float) $row['price_ori_net'], 0.001, 'ori');
        $this->assertSame('AUTO_REDUCTION', $row['reduction_reason']);
    }

    public function testATenPercentReductionChangesProductsAndProcedures(): void
    {
        [$event, $prod, $proc] = $this->event();
        $this->loginAs('vet');

        // KNOWN BUG: a GET request mutates data — decide in CI4 whether to keep
        $response = $this->get('events/edit_event_price/' . $event . '/10');

        $this->assertRedirectsTo($response, 'events/edit_price/' . $event);
        // unit 10 * 0.9 = 9 ; net 18 ; gross 18 * 1.21 = 21.78 ; original net 20
        $this->assertLine('events_products', $prod, 9.0, 18.0, 21.78, 20.0);
        $this->assertLine('events_procedures', $proc, 9.0, 18.0, 21.78, 20.0);
    }

    public function testASecondReductionStartsFromTheOriginalPrice(): void
    {
        [$event, $prod, $proc] = $this->event();
        $this->loginAs('vet');

        $this->get('events/edit_event_price/' . $event . '/10');
        $this->get('events/edit_event_price/' . $event . '/20');

        // original net 20 / volume 2 = 10 ; 10 * 0.8 = 8 ; net 16 ; gross 16 * 1.21 = 19.36 (not 9 * 0.8 = 7.2)
        $this->assertLine('events_products', $prod, 8.0, 16.0, 19.36, 20.0);
        $this->assertLine('events_procedures', $proc, 8.0, 16.0, 19.36, 20.0);
    }

    public function testZeroPercentRestoresTheOriginalPrice(): void
    {
        [$event, $prod, $proc] = $this->event();
        $this->loginAs('vet');

        $this->get('events/edit_event_price/' . $event . '/15');
        $this->get('events/edit_event_price/' . $event . '/0');

        // back to unit 10, net 20, gross 24.20
        $this->assertLine('events_products', $prod, 10.0, 20.0, 24.20, 20.0);
        $this->assertLine('events_procedures', $proc, 10.0, 20.0, 24.20, 20.0);
    }

    public function testOtherEventsAreNotTouched(): void
    {
        [$event] = $this->event();
        [, $otherProd] = $this->event();
        $this->loginAs('vet');

        $this->get('events/edit_event_price/' . $event . '/10');

        $row = $this->find('events_products', $otherProd);
        $this->assertEqualsWithDelta(10.0, (float) $row['unit_price'], 0.001);
        $this->assertNull($row['reduction_reason']);
    }
}
