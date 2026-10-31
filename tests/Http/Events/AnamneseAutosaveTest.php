<?php

declare(strict_types=1);

/**
 * T131 - Events_report::anamnese($event). Autosave: only a non-empty anamnese is written (together with the title),
 * and only while report != 2. The response body is always empty.
 */
final class AnamneseAutosaveTest extends HttpTestCase
{
    private function event(int $report): int
    {
        return Factory::event(Factory::pet(Factory::owner()), 0, 0, ['title' => 'old title', 'anamnese' => 'old text', 'report' => $report]);
    }

    public function testANonEmptyAnamneseIsSavedWhileTheReportIsOpen(): void
    {
        $this->loginAs('vet');
        foreach ([0, 1] as $report) {
            $event = $this->event($report);

            $response = $this->post('events_report/anamnese/' . $event, ['anamnese' => 'autosaved', 'title' => 'auto title']);

            $row = $this->find('events', $event);
            $this->assertSame('autosaved', $row['anamnese'], 'report ' . $report);
            $this->assertSame('auto title', $row['title']);
            $this->assertSame('', trim($this->text($response)));
        }
    }

    public function testAFinishedReportIsNotOverwritten(): void
    {
        $this->loginAs('vet');
        $event = $this->event(2);

        $response = $this->post('events_report/anamnese/' . $event, ['anamnese' => 'autosaved', 'title' => 'auto title']);

        $row = $this->find('events', $event);
        $this->assertSame('old text', $row['anamnese']);
        $this->assertSame('old title', $row['title']);
        $this->assertSame('', trim($this->text($response)));
    }

    public function testAnEmptyAnamneseChangesNothing(): void
    {
        $this->loginAs('vet');
        $event = $this->event(1);

        $response = $this->post('events_report/anamnese/' . $event, ['anamnese' => '', 'title' => 'auto title']);

        $row = $this->find('events', $event);
        $this->assertSame('old text', $row['anamnese']);
        $this->assertSame('old title', $row['title']);
        $this->assertSame('', trim($this->text($response)));
    }
}
