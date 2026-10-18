<?php

declare(strict_types=1);

/**
 * T062 - Pets::fiche. Source: application/controllers/Pets.php, views/pets/fiche*.
 *
 * Shows name, owner, breed, last weight, vaccines with expiry, other pets (only when the owner has MORE
 * than one other pet: the view tests count > 1), Rx / lab links when the pet has rows; unknown pet = 404.
 */
final class PetFicheTest extends HttpTestCase
{
    public function testTheFicheShowsNameOwnerBreedAndWeight(): void
    {
        $owner = Factory::owner(['last_name' => 'Ficheowner']);
        $pet = Factory::pet($owner, ['name' => 'Fichepet', 'breed' => Fixtures::BREED_LABRADOR, 'last_weight' => 12.75, 'chip' => '981555000111222', 'nr_vac_book' => 'VB-77']);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Fichepet');
        $this->assertSee($page, 'Ficheowner');
        $this->assertSee($page, '#' . $pet);
        $this->assertSee($page, '12.75kg');
        $this->assertSee($page, '981-555-000-111-222', 'chip in groups of three');
        $this->assertSee($page, 'VB-77');
        $breed = (string) $this->scalar('SELECT name FROM breeds WHERE id = ?', [Fixtures::BREED_LABRADOR]);
        $this->assertSee($page, $breed);
    }

    public function testAPetWithoutWeightShowsDashes(): void
    {
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 0]);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertDontSee($page, '0kg');
        $this->assertSee($page, '---');
    }

    public function testVaccinesAreListedWithTheirExpiryDate(): void
    {
        $pet = Factory::pet(Factory::owner());
        $redo = date('Y-m-d', strtotime('+200 days'));
        Factory::vaccine($pet, ['redo' => $redo]);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $this->assertStatus($page, 200);
        $productName = (string) $this->scalar('SELECT name FROM products WHERE id = ?', [Fixtures::PRODUCT_VACCINE]);
        $this->assertSee($page, $productName);
        // the date format is a per-user setting; the year and the day of month are stable in every format
        $this->assertSee($page, date('Y', strtotime($redo)));
        $this->assertSee($page, date('d', strtotime($redo)));
    }

    public function testWithoutVaccinesTheNoVaccinesTextAppears(): void
    {
        $withVaccine = Factory::pet(Factory::owner());
        Factory::vaccine($withVaccine);
        $without = Factory::pet(Factory::owner());
        $this->loginAs('vet');

        $a = $this->get('pets/fiche/' . $withVaccine);
        $b = $this->get('pets/fiche/' . $without);

        $this->assertStringContainsString('vaccine/fiche/' . $withVaccine, (string) $a->getBody());
        // the heading link to the vaccine list is only there when there are vaccines (the action button is always there)
        $this->assertSame(1, substr_count((string) $a->getBody(), 'vaccine/fiche/' . $withVaccine) - substr_count((string) $b->getBody(), 'vaccine/fiche/' . $without));
    }

    public function testOtherPetsAreOnlyShownForTwoOrMoreAliveAndFoundOnes(): void
    {
        $owner = Factory::owner();
        $main = Factory::pet($owner, ['name' => 'Mainpet']);
        $one = Factory::pet($owner, ['name' => 'Siblingone']);
        $this->loginAs('vet');

        // KNOWN QUIRK: the block needs count > 1, so a single other pet is not listed
        $page = $this->get('pets/fiche/' . $main);
        $this->assertDontSee($page, 'Siblingone');

        $two = Factory::pet($owner, ['name' => 'Siblingtwo']);
        Factory::pet($owner, ['name' => 'Deadsibling', 'death' => 1]);
        Factory::pet($owner, ['name' => 'Lostsibling', 'lost' => 1]);
        Factory::pet($owner, ['name' => 'Movedsibling', 'transfered' => 1]);
        $page = $this->get('pets/fiche/' . $main);

        $this->assertSee($page, 'Siblingone');
        $this->assertSee($page, 'Siblingtwo');
        $this->assertDontSee($page, 'Deadsibling');
        $this->assertDontSee($page, 'Lostsibling');
        $this->assertDontSee($page, 'Movedsibling');
        $this->assertStringContainsString('pets/fiche/' . $two, (string) $page->getBody());
    }

    public function testOtherPetsAreLimitedToFive(): void
    {
        $owner = Factory::owner();
        $main = Factory::pet($owner, ['name' => 'Mainpet']);
        for ($i = 1; $i <= 7; $i++) {
            Factory::pet($owner, ['name' => 'Cousin' . $i]);
        }
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $main);

        $this->assertSame(5, preg_match_all('/Cousin\d/', $this->text($page)));
    }

    public function testRxAndLabLinksAppearOnlyWhenThePetHasRows(): void
    {
        $plain = Factory::pet(Factory::owner());
        $rich = Factory::pet(Factory::owner());
        Factory::labReport($rich);
        Factory::insert('rx', [
            'path' => Factory::unique('rx/') . '.jpg', 'pet_id' => $rich, 'studydate' => date('Y-m-d'),
            'description' => 'x', 'bodypart' => 'THORAX', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->loginAs('vet');

        $plainBody = (string) $this->get('pets/fiche/' . $plain)->getBody();
        $richBody = (string) $this->get('pets/fiche/' . $rich)->getBody();

        $this->assertStringNotContainsString('rx/list/' . $plain, $plainBody);
        $this->assertStringNotContainsString('lab/list_lab/' . $plain, $plainBody);
        $this->assertStringContainsString('rx/list/' . $rich, $richBody);
        $this->assertStringContainsString('lab/list_lab/' . $rich, $richBody);
    }

    public function testAMissingPetGives404(): void
    {
        $this->loginAs('vet');

        $this->assertStatus($this->get('pets/fiche/987654'), 404);
    }

    public function testASoftDeletedPetGives404(): void
    {
        $this->loginAs('vet');

        $this->assertStatus($this->get('pets/fiche/' . Fixtures::PET_DELETED), 404);
        $this->assertStatus($this->get('pets/fiche/' . Fixtures::PET_REX), 200, 'positive control');
    }
}
