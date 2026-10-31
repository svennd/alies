<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/EventsHelper.php';

/** T117 - Events::prod_remove and proc_remove (GET, open events only). */
final class RemoveLineTest extends HttpTestCase
{
    private function vamreg(int $event, int $line): int
    {
        return Factory::insert('vamreg_out_buffer', [
            'event' => $event, 'event_line' => $line, 'out_quantity_type' => 'PACKS', 'out_quantity_pack_count' => 1,
            'out_date' => date('Y-m-d'), 'product_type' => 'BE', 'target_species' => 'DOG',
            'vet' => Fixtures::USER_VET, 'status' => 'DRAFT', 'created_at' => Factory::raw('NOW()'),
        ]);
    }

    public function testRemovingAProductAlsoRemovesItsVaccineAndVamregRows(): void
    {
        $e = EventsHelper::openEvent([], ['updated_at' => Factory::raw('DATE_SUB(NOW(), INTERVAL 2 DAY)')]);
        $line = Factory::eventProduct($e['event'], Fixtures::PRODUCT_VACCINE);
        $other = Factory::eventProduct($e['event'], Fixtures::PRODUCT_VACCINE);
        $vaccine = Factory::vaccine($e['pet'], ['event_id' => $e['event'], 'event_line' => $line]);
        $otherVaccine = Factory::vaccine($e['pet'], ['event_id' => $e['event'], 'event_line' => $other]);
        $this->vamreg($e['event'], $line);
        $this->vamreg($e['event'], $other);
        $this->loginAs('vet');

        $response = $this->get('events/prod_remove/' . $e['event'] . '/' . $line);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertRowMissing('events_products', ['id' => $line]);
        $this->assertRowMissing('vaccine_pet', ['id' => $vaccine]);
        $this->assertRowMissing('vamreg_out_buffer', ['event' => $e['event'], 'event_line' => $line]);
        // the other line and its rows stay
        $this->assertRowExists('events_products', ['id' => $other]);
        $this->assertRowExists('vaccine_pet', ['id' => $otherVaccine]);
        $this->assertRowExists('vamreg_out_buffer', ['event' => $e['event'], 'event_line' => $other]);
        $this->assertGreaterThan(strtotime('-1 day'), strtotime($this->find('events', $e['event'])['updated_at']), 'event updated_at bumped');
    }

    public function testRemovingAProcedure(): void
    {
        $e = EventsHelper::openEvent();
        $line = Factory::eventProcedure($e['event']);
        $keep = Factory::eventProcedure($e['event']);
        $this->loginAs('vet');

        $response = $this->get('events/proc_remove/' . $e['event'] . '/' . $line);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertRowMissing('events_procedures', ['id' => $line]);
        $this->assertRowExists('events_procedures', ['id' => $keep]);
    }

    public function testNothingIsRemovedFromAClosedEvent(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $product = Factory::eventProduct($e['event'], Fixtures::PRODUCT_TIERED);
        $procedure = Factory::eventProcedure($e['event']);
        $this->loginAs('vet');

        $a = $this->get('events/prod_remove/' . $e['event'] . '/' . $product);
        $b = $this->get('events/proc_remove/' . $e['event'] . '/' . $procedure);

        $this->assertSee($a, 'cannot change due to status');
        $this->assertSee($b, 'cannot change due to status');
        $this->assertRowExists('events_products', ['id' => $product]);
        $this->assertRowExists('events_procedures', ['id' => $procedure]);
    }

    #[Group('known-bug')]
    public function testTheLineIdIsNotCheckedAgainstTheEvent(): void
    {
        // KNOWN BUG: prod_remove/proc_remove delete by line id only; a line of another event is removed
        $a = EventsHelper::openEvent();
        $b = EventsHelper::openEvent();
        $lineOfB = Factory::eventProduct($b['event'], Fixtures::PRODUCT_TIERED);
        $procOfB = Factory::eventProcedure($b['event']);
        $this->loginAs('vet');

        $this->get('events/prod_remove/' . $a['event'] . '/' . $lineOfB);
        $this->get('events/proc_remove/' . $a['event'] . '/' . $procOfB);

        $this->assertRowMissing('events_products', ['id' => $lineOfB]);
        $this->assertRowMissing('events_procedures', ['id' => $procOfB]);
    }
}
