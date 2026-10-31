<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T060 - Pets::add (new pet for a client). Source: application/controllers/Pets.php.
 *
 * The owner is taken from the posted field "owner", not from the number in the url.
 * type, gender and name are required; everything else is optional.
 */
final class AddPetTest extends HttpTestCase
{
    private function petByName(string $name): ?array
    {
        return $this->row('SELECT * FROM pets WHERE name = ?', [$name]);
    }

    public function testTheFormNamesTheClientAndOffersTheChoices(): void
    {
        $this->loginAs('vet');

        $page = $this->get('pets/add/' . Fixtures::OWNER_JANSSENS);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Janssens');
        $body = (string) $page->getBody();
        foreach (['type', 'name', 'gender', 'birth', 'breed', 'breed2', 'color', 'hairtype', 'chip', 'weight', 'vacbook', 'msg', 'owner'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $body, $field);
        }
        $this->assertMatchesRegularExpression('#name="owner"[^>]*value="1001"|value="1001"[^>]*name="owner"#', $body, 'the owner travels in a hidden field');
    }

    public function testTheCompanionFieldIsOnlyOfferedForClientsWithAVatNumber(): void
    {
        $this->loginAs('vet');

        $private = $this->get('pets/add/' . Fixtures::OWNER_JANSSENS);
        $company = $this->get('pets/add/' . Fixtures::OWNER_COMPANY);

        $this->assertStringNotContainsString('name="companion"', (string) $private->getBody());
        $this->assertStringContainsString('name="companion"', (string) $company->getBody());
    }

    public function testAPetIsStoredWithItsFieldsAndTheServerSideValues(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet2', Fixtures::LOCATION_CAR);

        $response = $this->post('pets/add/' . $owner, Forms::pet($owner, [
            'type' => '1', 'name' => 'Poekie', 'gender' => '3', 'birth' => '2019-06-30',
            'breed' => (string) Fixtures::BREED_CAT_SHORTHAIR, 'color' => 'grijs', 'hairtype' => 'lang',
            'vacbook' => 'VB-12345', 'msg' => 'Is bang van de dierenarts',
        ]));

        $pet = $this->petByName('Poekie');
        $this->assertNotNull($pet, 'no pet was created');
        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet['id']);
        $this->assertSame(1, (int) $pet['type']);
        $this->assertSame(3, (int) $pet['gender']);
        $this->assertSame('2019-06-30', $pet['birth']);
        $this->assertSame(Fixtures::BREED_CAT_SHORTHAIR, (int) $pet['breed']);
        $this->assertSame('grijs', $pet['color']);
        $this->assertSame('lang', $pet['hairtype']);
        // the form field vacbook is the column nr_vac_book, msg is the note
        $this->assertSame('VB-12345', $pet['nr_vac_book']);
        $this->assertSame('Is bang van de dierenarts', $pet['note']);
        $this->assertSame($owner, (int) $pet['owner']);
        // set by the server: the location the vet is working at, and the vet
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $pet['location']);
        $this->assertSame(Fixtures::USER_VET2, (int) $pet['init_vet']);
        $this->assertSame(0, (int) $pet['death']);
        $this->assertSame(0, (int) $pet['lost']);
        $this->assertSame(0, (int) $pet['transfered']);
        $this->assertNull($pet['deleted_at']);
    }

    public function testDashesAreRemovedFromTheChipNumber(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Chipped', 'chip' => '981-000-000-000-777']));

        $this->assertSame('981000000000777', $this->petByName('Chipped')['chip']);
    }

    public function testAnEmptyCompanionIsStoredAsNullAndAGivenOneIsKept(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Alone', 'companion' => '']));
        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Together', 'companion' => (string) $owner]));

        $this->assertNull($this->petByName('Alone')['companion']);
        $this->assertSame($owner, (int) $this->petByName('Together')['companion']);
    }

    public function testAWeightIsStoredAsLastWeightAndAsTheFirstEntryOfTheWeightHistory(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Heavy', 'weight' => '12.5']));

        $pet = $this->petByName('Heavy');
        $this->assertSame('12.50', $pet['last_weight']);
        $weights = $this->rows('SELECT * FROM pets_weight WHERE pets = ?', [$pet['id']]);
        $this->assertCount(1, $weights);
        $this->assertSame('12.50', $weights[0]['weight']);
    }

    public function testWithoutAWeightThereIsNoWeightHistory(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Unweighed', 'weight' => '']));
        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Zeroweight', 'weight' => '0']));

        $this->assertRowMissing('pets_weight', ['pets' => $this->petByName('Unweighed')['id']]);
        $this->assertRowMissing('pets_weight', ['pets' => $this->petByName('Zeroweight')['id']], 'a weight of 0 is not recorded');
    }

    public function testAddingAPetIsLogged(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Logged']));

        $pet = $this->petByName('Logged');
        $log = $this->row("SELECT * FROM log WHERE event = 'add_pet'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level']);
        $this->assertSame('Added pet Logged (' . $pet['id'] . ')', $log['msg']);
    }

    public function testTheNewPetIsOnTheClientsPageAndHasAFiche(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');
        $response = $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Newcomer']));

        $fiche = $this->followRedirect($response);

        $this->assertStatus($fiche, 200);
        $this->assertSee($fiche, 'Newcomer');
        $this->assertSee($this->get('owners/detail/' . $owner), 'Newcomer');
    }

    /** @return array<string, array{array<string, string>}> */
    public static function missingRequired(): array
    {
        return [
            'no name' => [['name' => '']],
            'no type' => [['type' => null]],
            'no gender' => [['gender' => null]],
        ];
    }

    /** @param array<string, ?string> $change */
    #[\PHPUnit\Framework\Attributes\DataProvider('missingRequired')]
    public function testWithoutNameTypeOrGenderThePetIsNotStored(array $change): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');
        $form = array_filter(array_merge(Forms::pet($owner, ['name' => 'Required']), $change), static fn ($v): bool => $v !== null);
        $before = $this->countWhere('pets');

        $response = $this->post('pets/add/' . $owner, $form);

        $this->assertRedirectsTo($response, 'pets/add/' . $owner);
        $this->assertSame($before, $this->countWhere('pets'));
    }

    public function testGenderZeroAndTypeZeroAreValidChoices(): void
    {
        // "male" and "dog" are the value 0; only a missing value is refused, not a falsy one
        $owner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Zeroes', 'type' => '0', 'gender' => '0']));

        $pet = $this->petByName('Zeroes');
        $this->assertSame([0, 0], [(int) $pet['type'], (int) $pet['gender']]);
    }

    public function testWithoutThePressedSubmitButtonTheFormIsShownAgain(): void
    {
        $owner = Factory::owner();
        $this->loginAs('vet');
        $before = $this->countWhere('pets');
        $form = Forms::pet($owner, ['name' => 'Nosubmit']);
        unset($form['submit']);

        $response = $this->post('pets/add/' . $owner, $form);

        $this->assertStatus($response, 200);
        $this->assertSame($before, $this->countWhere('pets'));
    }

    #[Group('known-bug')]
    public function testTheOwnerComesFromTheFormNotFromTheUrl(): void
    {
        // KNOWN BUG: the url says client A, the (hidden) form field says client B, and B gets the pet
        $urlOwner = Factory::owner();
        $formOwner = Factory::owner();
        $this->loginAs('vet');

        $this->post('pets/add/' . $urlOwner, Forms::pet($formOwner, ['name' => 'Crossed']));

        $this->assertSame($formOwner, (int) $this->petByName('Crossed')['owner']);
    }

    #[Group('known-bug')]
    public function testAPetCanBeAddedToAClientThatDoesNotExist(): void
    {
        // KNOWN BUG: nothing checks the owner id, the pet ends up without a client
        $this->loginAs('vet');

        $this->post('pets/add/999999', Forms::pet(999999, ['name' => 'Orphan']));

        $this->assertSame(999999, (int) $this->petByName('Orphan')['owner']);
        $this->assertRowMissing('owners', ['id' => 999999]);
    }

    public function testNoPetCanBeAddedWithoutALogin(): void
    {
        $owner = Factory::owner();
        $before = $this->countWhere('pets');

        $response = $this->post('pets/add/' . $owner, Forms::pet($owner, ['name' => 'Anonymous']));

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertSame($before, $this->countWhere('pets'));
    }
}
