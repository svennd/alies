<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T126 - Events::edit_unit_price($line, $event). POST unit_price, volume, btw, type (1 product, 0 procedure),
 * reason, price_ori_net. The unit price is rounded to 2 decimals, net = unit * volume,
 * gross = net * (1 + btw/100) with the factor NOT rounded. The columns hold 2 decimals, so gross is stored rounded.
 */
final class EditUnitPriceTest extends HttpTestCase
{
    private function form(int $type, array $over = []): array
    {
        return $over + ['unit_price' => '7.456', 'volume' => '2', 'btw' => '21', 'type' => (string) $type, 'reason' => 'MANUAL', 'price_ori_net' => '20'];
    }

    public function testAProductLineGetsTheNewPriceAndTheVatIsNotRoundedBeforeMultiplying(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $line = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0, ['unit_price' => 10.0, 'btw' => 21]);
        $this->loginAs('vet');

        $response = $this->post('events/edit_unit_price/' . $line . '/' . $event, $this->form(1));

        $this->assertRedirectsTo($response, 'events/edit_price/' . $event);
        $row = $this->find('events_products', $line);
        // unit 7.456 -> 7.46 ; net 7.46 * 2 = 14.92 ; gross 14.92 * 1.21 = 18.0532 -> column keeps 18.05
        $this->assertEqualsWithDelta(7.46, (float) $row['unit_price'], 0.001);
        $this->assertEqualsWithDelta(14.92, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(18.05, (float) $row['price_brut'], 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $row['price_ori_net'], 0.001);
        $this->assertSame('MANUAL', $row['reduction_reason']);
    }

    public function testAProcedureLineIsChangedInTheProcedureTableOnly(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $proc = Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 2.0, ['unit_price' => 25.0, 'btw' => 21]);
        // a product line with the same id must stay untouched
        $prod = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['unit_price' => 10.0, 'btw' => 6]);
        $this->loginAs('vet');

        $this->post('events/edit_unit_price/' . $proc . '/' . $event, $this->form(0, ['unit_price' => '20']));

        $row = $this->find('events_procedures', $proc);
        // net 20 * 2 = 40 ; gross 40 * 1.21 = 48.40
        $this->assertEqualsWithDelta(20.00, (float) $row['unit_price'], 0.001);
        $this->assertEqualsWithDelta(40.00, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(48.40, (float) $row['price_brut'], 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $row['price_ori_net'], 0.001);
        $this->assertSame('MANUAL', $row['reduction_reason']);
        $other = $this->find('events_products', $prod);
        $this->assertEqualsWithDelta(10.00, (float) $other['unit_price'], 0.001);
        $this->assertNull($other['reduction_reason']);
    }

    public function testTheVatRateOfTheForm6PercentGivesTheMatchingGross(): void
    {
        $event = Factory::event(Factory::pet(Factory::owner()));
        $line = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 3.0, ['unit_price' => 10.0, 'btw' => 6]);
        $this->loginAs('vet');

        $this->post('events/edit_unit_price/' . $line . '/' . $event, $this->form(1, ['unit_price' => '5', 'volume' => '3', 'btw' => '6']));

        // net 15 ; gross 15 * 1.06 = 15.90
        $row = $this->find('events_products', $line);
        $this->assertEqualsWithDelta(15.00, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(15.90, (float) $row['price_brut'], 0.001);
    }

    #[Group('known-bug')]
    public function testItAlsoWorksOnAClosedEventWithAPaidBill(): void
    {
        // KNOWN BUG: no status check, the price of a closed/paid event can still be changed — decide in CI4 whether to keep
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 4); // BILL_PAID
        $event = Factory::event(Factory::pet($owner), 1, $bill);
        $line = Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0, ['unit_price' => 10.0, 'btw' => 21]);
        $this->loginAs('vet');

        $response = $this->post('events/edit_unit_price/' . $line . '/' . $event, $this->form(1));

        $this->assertRedirectsTo($response, 'events/edit_price/' . $event);
        $this->assertEqualsWithDelta(7.46, (float) $this->find('events_products', $line)['unit_price'], 0.001);
        $this->assertEqualsWithDelta(14.92, (float) $this->find('events_products', $line)['price_net'], 0.001);
    }
}
