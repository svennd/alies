<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T119 - Events::proc_edit is a placeholder. */
final class ProcEditTest extends HttpTestCase
{
    public function testAnOpenEventAnswersNotImplemented(): void
    {
        $e = EventsHelper::openEvent();
        $this->loginAs('vet');

        $this->assertSee($this->get('events/proc_edit/' . $e['event']), 'not implemented yet');
    }

    public function testAClosedEventAnswersCannotChange(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $this->loginAs('vet');

        $response = $this->get('events/proc_edit/' . $e['event']);

        $this->assertSee($response, 'cannot change due to status');
        $this->assertDontSee($response, 'not implemented yet');
    }
}
