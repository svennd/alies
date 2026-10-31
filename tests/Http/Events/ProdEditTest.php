<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T118 - Events::prod_edit changes the volume of a product line and re-prices it from the tiers.
 * brut uses the POSTED btw (not rounded, not the stored one); vaccine and Vamreg rows are not touched.
 */
final class ProdEditTest extends HttpTestCase
{
    private function line(array $e, int $product): int
    {
        return Factory::eventProduct($e['event'], $product, 1.0, ['unit_price' => 10.0, 'btw' => 6]);
    }

    public function testTheTierIsRecalculatedForTheNewVolume(): void
    {
        $e = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $line = $this->line($e, $product);
        $this->loginAs('vet');

        $response = $this->post('events/prod_edit/' . $e['event'], [
            'pid' => $product, 'volume' => 6, 'btw' => 6, 'event_product_id' => $line,
        ]);

        // tier 5 -> unit 8, net 6 * 8 = 48, brut 48 * 1.06 = 50.88
        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $row = $this->find('events_products', $line);
        $this->assertEqualsWithDelta(6.0, (float) $row['volume'], 0.001);
        $this->assertEqualsWithDelta(8.0, (float) $row['unit_price'], 0.001);
        $this->assertEqualsWithDelta(48.0, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta(50.88, (float) $row['price_brut'], 0.001);
    }

    public function testTheVatComesFromThePostAndIsNotRounded(): void
    {
        $e = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $line = $this->line($e, $product);
        $this->loginAs('vet');

        $this->post('events/prod_edit/' . $e['event'], [
            'pid' => $product, 'volume' => 6, 'btw' => 5.5, 'event_product_id' => $line,
        ]);

        // 48 * 1.055 = 50.64 (add_line would round the factor to 1.06 -> 50.88); stored btw stays 6
        $row = $this->find('events_products', $line);
        $this->assertEqualsWithDelta(50.64, (float) $row['price_brut'], 0.001);
        $this->assertSame(6, (int) $row['btw']);
    }

    public function testVaccineAndVamregRowsAreNotUpdated(): void
    {
        $e = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $line = $this->line($e, $product);
        $vaccine = Factory::vaccine($e['pet'], ['event_id' => $e['event'], 'event_line' => $line]);
        $redo = $this->find('vaccine_pet', $vaccine)['redo'];
        Factory::insert('vamreg_out_buffer', [
            'event' => $e['event'], 'event_line' => $line, 'out_quantity_type' => 'UNITS', 'out_quantity_unit_count' => 1,
            'out_quantity_unit' => 'ML', 'out_date' => date('Y-m-d'), 'product_type' => 'BE', 'target_species' => 'DOG',
            'vet' => Fixtures::USER_VET, 'status' => 'DRAFT', 'created_at' => Factory::raw('NOW()'),
        ]);
        $this->loginAs('vet');

        $this->post('events/prod_edit/' . $e['event'], ['pid' => $product, 'volume' => 6, 'btw' => 6, 'event_product_id' => $line]);

        $this->assertSame($redo, $this->find('vaccine_pet', $vaccine)['redo']);
        $buffer = $this->row('SELECT * FROM vamreg_out_buffer WHERE event_line = ?', [$line]);
        $this->assertEqualsWithDelta(1.0, (float) $buffer['out_quantity_unit_count'], 0.001, 'Vamreg quantity not recalculated');
        $this->assertSame('DRAFT', $buffer['status']);
    }

    public function testANonNumericVolumeChangesNothing(): void
    {
        $e = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $line = $this->line($e, $product);
        $this->loginAs('vet');

        $response = $this->post('events/prod_edit/' . $e['event'], ['pid' => $product, 'volume' => 'abc', 'btw' => 6, 'event_product_id' => $line]);

        $this->assertSee($response, 'You entered a non-numeric value!');
        $row = $this->find('events_products', $line);
        $this->assertEqualsWithDelta(1.0, (float) $row['volume'], 0.001);
        $this->assertEqualsWithDelta(10.0, (float) $row['price_net'], 0.001);
    }

    public function testAClosedEventCannotBeEdited(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $product = EventsHelper::tieredProduct();
        $line = $this->line($e, $product);
        $this->loginAs('vet');

        $response = $this->post('events/prod_edit/' . $e['event'], ['pid' => $product, 'volume' => 6, 'btw' => 6, 'event_product_id' => $line]);

        $this->assertSee($response, 'cannot change due to status');
        $this->assertEqualsWithDelta(1.0, (float) $this->find('events_products', $line)['volume'], 0.001);
    }

    public function testALineOfAnotherEventIsNotChanged(): void
    {
        $a = EventsHelper::openEvent();
        $b = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $lineOfB = $this->line($b, $product);
        $this->loginAs('vet');

        $this->post('events/prod_edit/' . $a['event'], ['pid' => $product, 'volume' => 6, 'btw' => 6, 'event_product_id' => $lineOfB]);

        $this->assertEqualsWithDelta(1.0, (float) $this->find('events_products', $lineOfB)['volume'], 0.001, 'the update is scoped to the event');
    }
}
