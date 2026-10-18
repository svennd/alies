<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T115 - adding a product with is_antibiotic=1 stages a Vamreg declaration (vamreg_out_buffer) that is
 * DRAFT for volume > 0 and INVALID for volume 0. Nothing is sent to Vamreg here.
 */
final class AddAntibioticLineTest extends HttpTestCase
{
    private function add(int $event, int $product, $volume, int $antibiotic = 1): array
    {
        return $this->json($this->ajaxPost('events/add_line/' . $event . '/1', [
            'line' => $product, 'title' => 'ab', 'volume' => $volume, 'btw' => 6, 'is_antibiotic' => $antibiotic,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));
    }

    public function testADraftIsStagedForAPackProduct(): void
    {
        // product 4003: CNK 1234567, default indication RESP, ab_unit PACKS, ab_unit_volume 1
        $e = EventsHelper::openEvent(['type' => 1]); // a cat
        $this->loginAs('vet');

        $json = $this->add($e['event'], Fixtures::PRODUCT_ANTIBIOTIC, 2);

        $this->assertTrue($json['antibiotic']);
        $row = $this->row('SELECT * FROM vamreg_out_buffer WHERE event = ?', [$e['event']]);
        $this->assertNotNull($row);
        $this->assertSame((int) $json['return'], (int) $row['event_line']);
        $this->assertSame('BE', $row['product_type']);
        $this->assertSame('DRAFT', $row['status']);
        $this->assertSame('CAT', $row['target_species']);
        $this->assertSame('RESP', $row['indication']);
        $this->assertSame('1234567', $row['cnk']);
        $this->assertSame(date('Y-m-d'), $row['out_date']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['vet']);
        // PACKS: pack count = ab_unit_volume (1) * volume (2) = 2, no unit fields
        $this->assertSame('PACKS', $row['out_quantity_type']);
        $this->assertEqualsWithDelta(2.0, (float) $row['out_quantity_pack_count'], 0.001);
        $this->assertNull($row['out_quantity_unit_count']);
        $this->assertNull($row['out_quantity_unit']);
    }

    public function testUnitProductsUseUnitCountAndUnit(): void
    {
        $product = Factory::product(['is_antibiotic' => 1, 'cnk' => '7654321', 'default_indication' => 'EYE', 'ab_unit' => 'ML', 'ab_unit_volume' => 5]);
        $e = EventsHelper::openEvent(['type' => 0]); // a dog
        $this->loginAs('vet');

        $this->add($e['event'], $product, 3);

        $row = $this->row('SELECT * FROM vamreg_out_buffer WHERE event = ?', [$e['event']]);
        $this->assertSame('DOG', $row['target_species']);
        $this->assertSame('EYE', $row['indication']);
        // UNITS: unit count = 5 ml * 3 = 15
        $this->assertSame('UNITS', $row['out_quantity_type']);
        $this->assertEqualsWithDelta(15.0, (float) $row['out_quantity_unit_count'], 0.001);
        $this->assertSame('ML', $row['out_quantity_unit']);
        $this->assertNull($row['out_quantity_pack_count']);
    }

    public function testOtherSpeciesMapToOtherNonFood(): void
    {
        $e = EventsHelper::openEvent(['type' => 3]);
        $horse = EventsHelper::openEvent(['type' => 2]);
        $this->loginAs('vet');

        $this->add($e['event'], Fixtures::PRODUCT_ANTIBIOTIC, 1);
        $this->add($horse['event'], Fixtures::PRODUCT_ANTIBIOTIC, 1);

        $this->assertSame('OTHER_NON_FOOD', $this->row('SELECT target_species FROM vamreg_out_buffer WHERE event = ?', [$e['event']])['target_species']);
        $this->assertSame('HORSE', $this->row('SELECT target_species FROM vamreg_out_buffer WHERE event = ?', [$horse['event']])['target_species']);
    }

    public function testVolumeZeroGivesAnInvalidDraft(): void
    {
        $e = EventsHelper::openEvent();
        $this->loginAs('vet');

        $this->add($e['event'], Fixtures::PRODUCT_ANTIBIOTIC, 0);

        $this->assertSame('INVALID', $this->row('SELECT status FROM vamreg_out_buffer WHERE event = ?', [$e['event']])['status']);
    }

    public function testWithoutTheFlagNothingIsStaged(): void
    {
        $e = EventsHelper::openEvent();
        $this->loginAs('vet');

        $json = $this->add($e['event'], Fixtures::PRODUCT_ANTIBIOTIC, 1, 0);

        $this->assertFalse($json['antibiotic']);
        $this->assertRowCount(0, 'vamreg_out_buffer', ['event' => $e['event']]);
    }
}
