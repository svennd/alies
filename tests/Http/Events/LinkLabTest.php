<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T105 - Events::link_lab and unlink_lab (POST only). Linking needs a CLOSED/HISTORY event and a lab of
 * the same pet that is not yet linked; unlinking removes the link only, never the lab report.
 */
final class LinkLabTest extends HttpTestCase
{
    private function assertFlash($response, string $english, string $dutch): void
    {
        $page = $this->followRedirect($response);
        $text = $this->text($page);
        $this->assertTrue(
            str_contains($text, $english) || str_contains($text, $dutch),
            'flash message missing, expected "' . $english . '" / "' . $dutch . '"'
        );
    }

    public function testAGetIsRefusedWith405(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $lab = Factory::labReport($e['pet']);
        $this->loginAs('vet');

        $this->assertStatus($this->get('events/link_lab/' . $e['event']), 405);
        $this->assertStatus($this->get('events/unlink_lab/' . $e['event'] . '/' . $lab), 405);
        $this->assertRowCount(0, 'events_labs', ['event_id' => $e['event']]);
    }

    public function testAMissingEventIsAnswered404(): void
    {
        $this->loginAs('vet');

        $this->assertStatus($this->post('events/link_lab/99999999', ['lab_id' => 1]), 404);
        $this->assertStatus($this->post('events/unlink_lab/99999999/1'), 404);
    }

    public function testAnOpenEventCannotLinkAndStaysUnlinked(): void
    {
        $e = EventsHelper::openEvent();
        $lab = Factory::labReport($e['pet']);
        $this->loginAs('vet');

        $response = $this->post('events/link_lab/' . $e['event'], ['lab_id' => $lab]);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertRowCount(0, 'events_labs', ['event_id' => $e['event']]);
    }

    public function testALabOfTheSamePetIsLinkedOnAClosedEvent(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $lab = Factory::labReport($e['pet']);
        $this->loginAs('vet');
        $before = $this->logCount('event_lab_linked');

        $response = $this->post('events/link_lab/' . $e['event'], ['lab_id' => $lab]);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertRowExists('events_labs', ['event_id' => $e['event'], 'lab_id' => $lab]);
        $this->assertSame($before + 1, $this->logCount('event_lab_linked'));
        $this->assertFlash($response, 'The lab result was linked.', 'Het labresultaat werd gekoppeld.');
    }

    public function testInvalidLabsAreNotLinked(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $foreign = Factory::labReport(Factory::pet(Factory::owner()));
        $deleted = Factory::labReport($e['pet'], [], ['deleted_at' => Factory::raw('NOW()')]);
        $good = Factory::labReport($e['pet']);
        $this->loginAs('vet');

        foreach ([0, $foreign, $deleted, 99999999] as $labId) {
            $response = $this->post('events/link_lab/' . $e['event'], ['lab_id' => $labId]);
            $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        }
        $this->assertRowCount(0, 'events_labs', ['event_id' => $e['event']]);
        $this->assertFlash($response, 'This lab result cannot be linked to the event.', 'Dit labresultaat kan niet aan het event worden gekoppeld.');

        // positive control: the valid lab of the same pet goes through
        $this->post('events/link_lab/' . $e['event'], ['lab_id' => $good]);
        $this->assertRowCount(1, 'events_labs', ['event_id' => $e['event']]);
    }

    public function testLinkingTheSameLabTwiceKeepsOneRow(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $lab = Factory::labReport($e['pet']);
        $this->loginAs('vet');

        $this->post('events/link_lab/' . $e['event'], ['lab_id' => $lab]);
        $second = $this->post('events/link_lab/' . $e['event'], ['lab_id' => $lab]);

        $this->assertRedirectsTo($second, 'events/event/' . $e['event']);
        $this->assertRowCount(1, 'events_labs', ['event_id' => $e['event'], 'lab_id' => $lab]);
    }

    public function testUnlinkingRemovesTheLinkButKeepsTheLabReport(): void
    {
        $e = EventsHelper::openEvent([], [], 1);
        $lab = Factory::labReport($e['pet']);
        Factory::insert('events_labs', ['event_id' => $e['event'], 'lab_id' => $lab]);
        $this->loginAs('vet');
        $before = $this->logCount('event_lab_unlinked');

        $response = $this->post('events/unlink_lab/' . $e['event'] . '/' . $lab);

        $this->assertRedirectsTo($response, 'events/event/' . $e['event']);
        $this->assertRowMissing('events_labs', ['event_id' => $e['event'], 'lab_id' => $lab]);
        $this->assertRowExists('lab_report', ['id' => $lab]);
        $this->assertSame($before + 1, $this->logCount('event_lab_unlinked'));
        $this->assertFlash($response, 'The lab result was unlinked.', 'Het labresultaat werd ontkoppeld.');
    }

    public function testUnlinkingOnAnOpenEventOrANotLinkedLabChangesNothing(): void
    {
        $open = EventsHelper::openEvent();
        $labOpen = Factory::labReport($open['pet']);
        Factory::insert('events_labs', ['event_id' => $open['event'], 'lab_id' => $labOpen]);
        $closed = EventsHelper::openEvent([], [], 1);
        $labClosed = Factory::labReport($closed['pet']);
        $this->loginAs('vet');

        $this->post('events/unlink_lab/' . $open['event'] . '/' . $labOpen);
        $this->assertRowExists('events_labs', ['event_id' => $open['event'], 'lab_id' => $labOpen]);

        $response = $this->post('events/unlink_lab/' . $closed['event'] . '/' . $labClosed);
        $this->assertRedirectsTo($response, 'events/event/' . $closed['event']);
        $this->assertRowExists('lab_report', ['id' => $labClosed]);
    }
}
