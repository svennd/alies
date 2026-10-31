<?php

declare(strict_types=1);

/**
 * T130 - Events_report::update_report($event). POST submit=report|finished_report, title, anamnese, sup_vet[],
 * finished, pet_id. report = 2 (done) for finished_report or finished=1, otherwise 1 (open).
 * Refused with an echo for an event with status HISTORY (2); no redirect in those cases.
 */
final class UpdateReportTest extends HttpTestCase
{
    private function event(int $status = 0): array
    {
        $pet = Factory::pet(Factory::owner());

        return [Factory::event($pet, $status, 0, ['title' => 'old title', 'anamnese' => 'old text']), $pet];
    }

    public function testSavingTheReportUpdatesTheRowAndReturnsToTheReport(): void
    {
        [$event] = $this->event();
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, [
            'submit' => 'report', 'title' => 'new title', 'anamnese' => 'new text',
            'sup_vet' => [(string) Fixtures::USER_VET2],
        ]);

        $this->assertRedirectsTo($response, 'events/event/' . $event . '/report');
        $row = $this->find('events', $event);
        $this->assertSame('new title', $row['title']);
        $this->assertSame('new text', $row['anamnese']);
        $this->assertSame(1, (int) $row['report']);
        $this->assertSame(Fixtures::USER_VET2, (int) $row['vet_support_1']);
        $this->assertSame(0, (int) $row['vet_support_2']);
    }

    public function testTwoSupportingVetsAreStored(): void
    {
        [$event] = $this->event();
        $this->loginAs('vet');

        $this->post('events_report/update_report/' . $event, [
            'submit' => 'report', 'title' => 't', 'anamnese' => 'a',
            'sup_vet' => [(string) Fixtures::USER_VET2, (string) Fixtures::USER_ADMIN],
        ]);

        $row = $this->find('events', $event);
        $this->assertSame(Fixtures::USER_VET2, (int) $row['vet_support_1']);
        $this->assertSame(Fixtures::USER_ADMIN, (int) $row['vet_support_2']);
    }

    public function testFinishedReportMarksItDoneAndGoesToThePetPage(): void
    {
        [$event, $pet] = $this->event();
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, [
            'submit' => 'finished_report', 'title' => 'x', 'anamnese' => 'y', 'pet_id' => (string) $pet,
        ]);

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $this->assertSame(2, (int) $this->find('events', $event)['report']);
    }

    public function testTheFinishedCheckboxAlsoMarksItDone(): void
    {
        [$event] = $this->event();
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, ['submit' => 'report', 'title' => 'x', 'anamnese' => 'y', 'finished' => '1']);

        $this->assertRedirectsTo($response, 'events/event/' . $event . '/report');
        $this->assertSame(2, (int) $this->find('events', $event)['report']);
    }

    public function testWithoutSubmitNothingIsSavedAndNoPostDataIsEchoed(): void
    {
        [$event] = $this->event();
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, ['title' => 'new title', 'anamnese' => 'new text']);

        $this->assertNotRedirect($response);
        $this->assertSame('no post data', trim($this->text($response)));
        $row = $this->find('events', $event);
        $this->assertSame('old title', $row['title']);
        $this->assertSame('old text', $row['anamnese']);
    }

    public function testAnEventInHistoryCannotBeChanged(): void
    {
        [$event] = $this->event(2);
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, ['submit' => 'report', 'title' => 'new title', 'anamnese' => 'new text']);

        $this->assertNotRedirect($response);
        $this->assertSame('cannot change due to status : status_history', trim($this->text($response)));
        $this->assertSame('old title', $this->find('events', $event)['title']);
    }

    public function testAClosedEventCanStillBeChanged(): void
    {
        [$event] = $this->event(1);
        $this->loginAs('vet');

        $response = $this->post('events_report/update_report/' . $event, ['submit' => 'report', 'title' => 'closed ok', 'anamnese' => 'a']);

        $this->assertRedirectsTo($response, 'events/event/' . $event . '/report');
        $this->assertSame('closed ok', $this->find('events', $event)['title']);
    }
}
