<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/**
 * T102 - the report view of a CLOSED event lists the labs linked to the event (link to lab/detail) and
 * offers the other labs of the SAME pet in a select. Labs of another pet never show up.
 */
final class ViewClosedEventLabsTest extends HttpTestCase
{
    public function testLinkedAndLinkableLabsAreSeparatedAndOtherPetsAreHidden(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $otherPet = Factory::pet($owner);
        $event = Factory::event($pet, 1);
        $linked = Factory::labReport($pet);
        $unlinked = Factory::labReport($pet);
        $foreign = Factory::labReport($otherPet);
        Factory::insert('events_labs', ['event_id' => $event, 'lab_id' => $linked]);
        $this->loginAs('vet');

        $page = $this->get('events/event/' . $event);
        $body = (string) $page->getBody();

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('lab/detail/' . $linked, $body, 'linked lab has an open link');
        $this->assertStringContainsString('event-lab-unlink-' . $linked, $body, 'and an unlink button');
        $this->assertStringContainsString('<option value="' . $unlinked . '">', $body, 'unlinked lab can be picked');
        $this->assertStringNotContainsString('<option value="' . $linked . '">', $body, 'linked lab is not offered again');
        $this->assertStringNotContainsString('lab/detail/' . $unlinked, $body);
        $this->assertStringNotContainsString('lab/detail/' . $foreign, $body);
        $this->assertStringNotContainsString('<option value="' . $foreign . '">', $body);
    }

    public function testASoftDeletedLabIsNotShown(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 1);
        $gone = Factory::labReport($pet, [], ['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $body = (string) $this->get('events/event/' . $event)->getBody();

        $this->assertStringNotContainsString('<option value="' . $gone . '">', $body);
    }
}
