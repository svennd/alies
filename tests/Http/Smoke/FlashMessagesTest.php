<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T347 - flash messages survive the redirect and are shown exactly once.
 *
 * Events::link_lab / unlink_lab and Pets::save_avatar / remove_avatar store a message with
 * session->set_flashdata; the event page / pet fiche prints it (and only that next request).
 * The application language is Dutch; the English text is accepted too.
 */
final class FlashMessagesTest extends HttpTestCase
{
    private function occurrences($response, string $dutch, string $english): int
    {
        // only the alert boxes count: the fiche also carries the same text in a data attribute for its script
        preg_match_all('/<div class="alert alert-[a-z]+[^"]*"[^>]*>(.*?)<\/div>/s', (string) $response->getBody(), $m);
        $count = 0;
        foreach ($m[1] as $box) {
            $box = html_entity_decode(strip_tags($box), ENT_QUOTES | ENT_HTML5);
            $count += (str_contains($box, $dutch) || str_contains($box, $english)) ? 1 : 0;
        }

        return $count;
    }

    private function assertShownOnceThenGone($redirect, string $path, string $dutch, string $english): void
    {
        $this->assertRedirectsTo($redirect, $path);

        $first = $this->followRedirect($redirect);
        $this->assertSame(200, $first->getStatusCode());
        $this->assertSame(1, $this->occurrences($first, $dutch, $english), 'the message is shown once after the redirect');

        $second = $this->get($path);
        $this->assertSame(200, $second->getStatusCode());
        $this->assertSame(0, $this->occurrences($second, $dutch, $english), 'the message is gone on the next page');
    }

    public function testALinkLabFailureIsShownOnceAfterTheRedirect(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 1);
        $this->loginAs('vet');

        // lab_id 0 is invalid
        $response = $this->post('events/link_lab/' . $event, ['lab_id' => 0]);

        $this->assertShownOnceThenGone(
            $response,
            'events/event/' . $event,
            'Dit labresultaat kan niet aan het event worden gekoppeld.',
            'This lab result cannot be linked to the event.'
        );
        $this->assertRowCount(0, 'events_labs', ['event_id' => $event]);
    }

    public function testALinkLabSuccessIsShownOnceAfterTheRedirect(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 1);
        $lab = Factory::labReport($pet);
        $this->loginAs('vet');

        $response = $this->post('events/link_lab/' . $event, ['lab_id' => $lab]);

        $this->assertRowCount(1, 'events_labs', ['event_id' => $event, 'lab_id' => $lab]);
        $this->assertShownOnceThenGone(
            $response,
            'events/event/' . $event,
            'Het labresultaat werd gekoppeld.',
            'The lab result was linked.'
        );
    }

    public function testAFlashMessageLivesForOneRequestOnly(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 1);
        $this->loginAs('vet');
        $this->post('events/link_lab/' . $event, ['lab_id' => 0]);

        // CodeIgniter ages flash data with the very next request, whichever page it is: the pet page does not print
        // it, and the event page after that does not have it any more
        $other = $this->get('pets/fiche/' . $pet);
        $this->assertSame(0, $this->occurrences($other, 'Dit labresultaat kan niet', 'This lab result cannot be linked'));
        $this->assertSame(
            0,
            $this->occurrences($this->get('events/event/' . $event), 'Dit labresultaat kan niet', 'This lab result cannot be linked')
        );
    }

    public function testAnAvatarFailureIsShownOnceAfterTheRedirect(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');

        // no source file: rejected as invalid image
        $response = $this->postMultipart('pets/save_avatar/' . $pet, ['pet_avatar_crop' => 'x']);

        $this->assertShownOnceThenGone(
            $response,
            'pets/fiche/' . $pet,
            'Het geselecteerde bestand is geen geldige afbeelding.',
            'The selected file is not a valid image.'
        );
        $this->assertNull($this->find('pets', $pet)['avatar']);
    }

    public function testTheMessageIsKeptPerUserSession(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');
        $this->postMultipart('pets/save_avatar/' . $pet, ['pet_avatar_crop' => 'x']);

        // another browser does not see it
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertSame(
            0,
            $this->occurrences($this->get('pets/fiche/' . $pet), 'geen geldige afbeelding', 'not a valid image')
        );
    }

    #[Group('known-bug')]
    public function testTheMessageOfAnOpenEventIsNeverShown(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 0);
        $lab = Factory::labReport($pet);
        $this->loginAs('vet');

        $response = $this->post('events/link_lab/' . $event, ['lab_id' => $lab]);

        // KNOWN BUG: the open-event page has no place for the flash message, so the refusal is silent - decide in CI4 whether to keep
        $this->assertRedirectsTo($response, 'events/event/' . $event);
        $page = $this->followRedirect($response);
        $this->assertSame(200, $page->getStatusCode());
        $this->assertSame(0, $this->occurrences($page, 'Dit labresultaat kan niet', 'This lab result cannot be linked'));
        $this->assertRowCount(0, 'events_labs', ['event_id' => $event]);
    }
}
