<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T120 - Events::edit_vaccin: POST redo=<date> sets the reminder date and re-enables it; POST disable=1 switches it off. */
final class EditVaccinTest extends HttpTestCase
{
    public function testTheReminderDateCanBeChangedAndReEnabled(): void
    {
        $e = EventsHelper::openEvent();
        $vaccine = Factory::vaccine($e['pet'], ['event_id' => $e['event'], 'no_rappel' => 1]);
        $other = Factory::vaccine($e['pet'], ['event_id' => $e['event']]);
        $otherRedo = $this->find('vaccine_pet', $other)['redo'];
        $this->loginAs('vet');
        $date = date('Y-m-d', strtotime('+400 days'));

        $response = $this->post('events/edit_vaccin/' . $e['event'] . '/' . $vaccine, ['redo' => $date]);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $row = $this->find('vaccine_pet', $vaccine);
        $this->assertSame($date, $row['redo']);
        $this->assertSame(0, (int) $row['no_rappel']);
        $this->assertSame($otherRedo, $this->find('vaccine_pet', $other)['redo'], 'other vaccine untouched');
    }

    public function testDisableSwitchesTheReminderOffAndKeepsTheDate(): void
    {
        $e = EventsHelper::openEvent();
        $vaccine = Factory::vaccine($e['pet'], ['event_id' => $e['event']]);
        $redo = $this->find('vaccine_pet', $vaccine)['redo'];
        $this->loginAs('vet');

        $response = $this->post('events/edit_vaccin/' . $e['event'] . '/' . $vaccine, ['disable' => 1, 'redo' => '2030-01-01']);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $row = $this->find('vaccine_pet', $vaccine);
        $this->assertSame(1, (int) $row['no_rappel']);
        $this->assertSame($redo, $row['redo']);
    }
}
