<?php

declare(strict_types=1);

/**
 * T085 - Search::index, the route `search?search_query=...`.
 *
 * Rules: an empty query goes home; owners are found by last name or first name (prefix), street
 * (anywhere in it) or phone (digits only, prefix, over 4 columns); pets by name (prefix, alive and
 * not transferred unless the query starts with "!"), by chip (10+ digits, "-" allowed) or by id
 * (shorter numbers, which also look up the owner with that id); breeds by name (4+ characters).
 * The result row of a pet or breed links to the OWNER.
 * Every test builds its rows with unique names, so the seed is never relied on.
 */
final class GlobalSearchTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function search(string $query)
    {
        return $this->get('search', ['search_query' => $query]);
    }

    private function assertLinksToOwner($page, int $owner, string $message = ''): void
    {
        $this->assertMatchesRegularExpression(
            '#owners/detail/' . $owner . '"#',
            (string) $page->getBody(),
            $message ?: 'the result list should link to owner ' . $owner
        );
    }

    private function assertNoLinkToOwner($page, int $owner): void
    {
        $this->assertDoesNotMatchRegularExpression('#owners/detail/' . $owner . '"#', (string) $page->getBody());
    }

    public function testAnEmptyQueryGoesBackToTheHomePage(): void
    {
        $this->assertRedirectsTo($this->get('search', ['search_query' => '']), '');
        $this->assertRedirectsTo($this->get('search'), '');
        // positive control: a real query shows the result page
        $page = $this->search('Janssens');
        $this->assertNotRedirect($page);
        $this->assertStatus($page, 200);
    }

    public function testALastNamePrefixFindsTheOwner(): void
    {
        $owner = Factory::owner(['last_name' => 'Zeldenrust' . random_int(100, 999)]);
        $other = Factory::owner();

        $page = $this->search('Zeldenr');

        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Zeldenrust');
        $this->assertNoLinkToOwner($page, $other);
    }

    public function testAFirstNamePrefixFindsTheOwner(): void
    {
        $owner = Factory::owner(['first_name' => 'Xaverius' . random_int(100, 999)]);
        $other = Factory::owner();

        $page = $this->search('xaveri');

        $this->assertLinksToOwner($page, $owner);
        $this->assertNoLinkToOwner($page, $other);
    }

    public function testAPartOfTheStreetNameFindsTheOwner(): void
    {
        $owner = Factory::owner(['street' => 'Grootzandstraat']);

        $page = $this->search('zandstr');

        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Grootzandstraat');
    }

    public function testAPhoneNumberWithSpacesFindsTheOwnerOnAnyOfItsNumbers(): void
    {
        $suffix = random_int(100000, 999999);
        $mobile = Factory::owner(['mobile' => '0488' . $suffix]);
        $second = Factory::owner(['phone2' => '0487' . $suffix]);
        $other = Factory::owner(['mobile' => '0466' . $suffix]);

        $page = $this->search('0488 ' . substr((string) $suffix, 0, 2) . ' ' . substr((string) $suffix, 2, 2) . ' ' . substr((string) $suffix, 4, 2));
        $this->assertLinksToOwner($page, $mobile);
        $this->assertNoLinkToOwner($page, $other);

        $page = $this->search('+0487' . $suffix);
        $this->assertLinksToOwner($page, $second);
        $this->assertNoLinkToOwner($page, $mobile);
    }

    public function testAPetNameFindsTheOwnerButNotForADeadPet(): void
    {
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Alivepet' . random_int(100, 999)]);
        $deadOwner = Factory::owner();
        Factory::pet($deadOwner, ['name' => 'Deadpet' . random_int(100, 999), 'death' => 1]);

        $page = $this->search('Alivepet');
        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Alivepet');

        $page = $this->search('Deadpet');
        $this->assertNoLinkToOwner($page, $deadOwner);
    }

    public function testAnExclamationMarkAlsoFindsDeadPets(): void
    {
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Ghostpet' . random_int(100, 999), 'death' => 1]);

        $this->assertNoLinkToOwner($this->search('Ghostpet'), $owner);

        $page = $this->search('!Ghostpet');

        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Ghostpet');
        // the "!" is not part of the query that is echoed back
        $this->assertStringContainsString('value="Ghostpet"', (string) $page->getBody());
    }

    public function testATransferredPetIsNotFoundByName(): void
    {
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Movedpet' . random_int(100, 999), 'transfered' => 1]);

        $this->assertNoLinkToOwner($this->search('!Movedpet'), $owner);
    }

    public function testAChipOfTenOrMoreDigitsFindsThePetAndDashesAreIgnored(): void
    {
        $chip = '9810' . random_int(10000000, 99999999);
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Chippet', 'chip' => $chip]);
        $other = Factory::owner();
        Factory::pet($other, ['chip' => '9811' . random_int(10000000, 99999999)]);

        $page = $this->search($chip);
        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Chippet');
        $this->assertNoLinkToOwner($page, $other);

        $dashed = substr($chip, 0, 3) . '-' . substr($chip, 3, 4) . '-' . substr($chip, 7);
        $this->assertLinksToOwner($this->search($dashed), $owner);
    }

    public function testAShortNumberFindsThePetWithThatIdAndTheOwnerWithThatId(): void
    {
        // an owner and a pet that share an id, plus an unrelated owner and pet
        $owner = Factory::owner(['id' => 91001]);
        $petOwner = Factory::owner();
        Factory::pet($petOwner, ['id' => 91001, 'name' => 'Idpet']);

        $page = $this->search('91001');

        $this->assertLinksToOwner($page, $owner, 'the owner with that id');
        $this->assertLinksToOwner($page, $petOwner, 'the owner of the pet with that id');
        $this->assertSee($page, 'Idpet');
    }

    public function testANumberStartingWithZeroIsNotLookedUpAsAnOwnerId(): void
    {
        $owner = Factory::owner(['id' => 91005]);

        $this->assertNoLinkToOwner($this->search('091005'), $owner);
        // positive control: without the leading zero the same owner is found
        $this->assertLinksToOwner($this->search('91005'), $owner);
    }

    public function testABreedNameOfFourOrMoreCharactersFindsItsPets(): void
    {
        $breed = Factory::breed(['name' => 'Wolfspitzx' . random_int(100, 999)]);
        $owner = Factory::owner();
        Factory::pet($owner, ['breed' => $breed, 'name' => 'Spitzpet']);
        $deadOwner = Factory::owner();
        Factory::pet($deadOwner, ['breed' => $breed, 'death' => 1]);

        $page = $this->search('Wolfspitzx');

        $this->assertLinksToOwner($page, $owner);
        $this->assertSee($page, 'Spitzpet');
        $this->assertNoLinkToOwner($page, $deadOwner);
    }

    public function testABreedQueryOfThreeCharactersIsIgnored(): void
    {
        // seed breed "Pers" (id 6): a three-character query is not used for the breed search
        $owner = Factory::owner();
        Factory::pet($owner, ['breed' => 6, 'name' => 'Qwertypet']);

        $this->assertNoLinkToOwner($this->search('Per'), $owner);
        $this->assertLinksToOwner($this->search('Pers'), $owner, 'positive control: four characters match the breed');
    }

    public function testMarkupInTheQueryIsEscaped(): void
    {
        $page = $this->search('<script>alert(1)</script>');

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $body);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $body);
    }
}
