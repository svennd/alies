<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T092 - Vet::ajax_get_vets, the vet picker of the event screens.
 * Rules: with `term` it lists users whose username contains the term (any group, active or not);
 * without it the active members of group 3 (vet); the current user is always left out; `results`
 * holds id and text (the username). No user at all gives an empty body.
 * Seed usernames: Alice Admin (1), Victor Vet (2), Anna Account (3), Mark Member (4), Nora Nogroup (5),
 * Veerle Vet (6), Ines Inactive (7, group vet but inactive).
 */
final class VetAutocompleteTest extends HttpTestCase
{
    private function results($response): array
    {
        return $this->json($response)['results'];
    }

    public function testATermFindsUsersByPartOfTheUsernameAndLeavesOutTheCurrentUser(): void
    {
        $this->loginAs('vet');

        $results = $this->results($this->get('vet/ajax_get_vets', ['term' => 'adm']));

        $this->assertSame([['id' => '1', 'text' => 'Alice Admin']], $results);
    }

    public function testTheCurrentUserIsSkippedEvenWhenItMatches(): void
    {
        $this->loginAs('vet');

        // "vet" matches Victor Vet (the caller) and Veerle Vet
        $this->assertSame([['id' => '6', 'text' => 'Veerle Vet']], $this->results($this->get('vet/ajax_get_vets', ['term' => 'vet'])));
        // only the caller matches: a list without entries, not an empty body
        $only = $this->get('vet/ajax_get_vets', ['term' => 'victor']);
        $this->assertSame([], $this->results($only));
    }

    public function testATermSearchIgnoresGroupsAndTheActiveFlag(): void
    {
        $this->loginAs('vet');

        $results = $this->results($this->get('vet/ajax_get_vets', ['term' => 'ines']));

        $this->assertSame([['id' => '7', 'text' => 'Ines Inactive']], $results);
    }

    public function testWithoutATermTheActiveVetsAreListed(): void
    {
        $this->loginAs('vet');

        $results = $this->results($this->get('vet/ajax_get_vets'));

        // group 3 and active: Victor (the caller, skipped) and Veerle; Ines is inactive, admin/accounting/members are other groups
        $this->assertSame([['id' => '6', 'text' => 'Veerle Vet']], $results);

        $this->cookies->clear();
        $this->loginAs('vet2');
        $this->assertSame([['id' => '2', 'text' => 'Victor Vet']], $this->results($this->get('vet/ajax_get_vets')), 'positive control: for the other vet the list flips');
    }

    #[Group('known-bug')]
    public function testATermWithoutAMatchHasAnEmptyBody(): void
    {
        // KNOWN BUG: `return json_encode(array())` returns instead of echoing - decide in CI4 whether to keep
        $this->loginAs('vet');

        $response = $this->get('vet/ajax_get_vets', ['term' => 'zzzznomatch']);

        $this->assertStatus($response, 200);
        $this->assertSame('', (string) $response->getBody());
    }
}
