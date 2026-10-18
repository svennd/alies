<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T121 - Events::set_vamreg_indication stores a valid indication, anything else becomes NULL. Redirects to the event. */
final class SetVamregIndicationTest extends HttpTestCase
{
    private function buffer(array $e, int $line, ?string $indication = 'RESP'): void
    {
        Factory::insert('vamreg_out_buffer', [
            'event' => $e['event'], 'event_line' => $line, 'out_quantity_type' => 'PACKS', 'out_quantity_pack_count' => 1,
            'out_date' => date('Y-m-d'), 'product_type' => 'BE', 'target_species' => 'DOG', 'indication' => $indication,
            'vet' => Fixtures::USER_VET, 'status' => 'DRAFT', 'created_at' => Factory::raw('NOW()'),
        ]);
    }

    private function indication(int $line): ?string
    {
        return $this->row('SELECT indication FROM vamreg_out_buffer WHERE event_line = ?', [$line])['indication'];
    }

    public function testAValidIndicationIsStoredOnTheMatchingLineOnly(): void
    {
        $e = EventsHelper::openEvent();
        $line = Factory::eventProduct($e['event'], Fixtures::PRODUCT_ANTIBIOTIC);
        $other = Factory::eventProduct($e['event'], Fixtures::PRODUCT_ANTIBIOTIC);
        $this->buffer($e, $line);
        $this->buffer($e, $other);
        $this->loginAs('vet');

        $response = $this->post('events/set_vamreg_indication/' . $e['event'] . '/' . $line, ['indication' => 'EYE']);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertSame('EYE', $this->indication($line));
        $this->assertSame('RESP', $this->indication($other));
    }

    public function testAnInvalidOrMissingIndicationBecomesNull(): void
    {
        $e = EventsHelper::openEvent();
        $line = Factory::eventProduct($e['event'], Fixtures::PRODUCT_ANTIBIOTIC);
        $this->buffer($e, $line);
        $this->loginAs('vet');

        $this->post('events/set_vamreg_indication/' . $e['event'] . '/' . $line, ['indication' => 'NOT_AN_INDICATION']);
        $this->assertNull($this->indication($line));

        // positive control: valid again, then no value at all
        $this->post('events/set_vamreg_indication/' . $e['event'] . '/' . $line, ['indication' => 'DERMA']);
        $this->assertSame('DERMA', $this->indication($line));
        $this->post('events/set_vamreg_indication/' . $e['event'] . '/' . $line, []);
        $this->assertNull($this->indication($line));
    }

    public function testEveryAllowedIndicationIsAccepted(): void
    {
        $e = EventsHelper::openEvent();
        $line = Factory::eventProduct($e['event'], Fixtures::PRODUCT_ANTIBIOTIC);
        $this->buffer($e, $line, null);
        $this->loginAs('vet');

        foreach (['DIGEST', 'EYE', 'LOCO', 'MAST', 'NERVE', 'PERI_OP', 'RESP', 'DERMA', 'SYST', 'URO_GEN'] as $value) {
            $this->post('events/set_vamreg_indication/' . $e['event'] . '/' . $line, ['indication' => $value]);
            $this->assertSame($value, $this->indication($line));
        }
    }
}
