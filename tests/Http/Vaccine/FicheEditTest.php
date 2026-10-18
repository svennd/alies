<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T223 - Vaccine record pages and edits.
 * Source: application/controllers/Vaccine.php (fiche, detail, update, remove), views/vaccine/pet_fiche.php, detail.php
 *
 * fiche lists the pet's vaccines, detail shows one record. update stores `redo` and the INVERTED checkbox:
 * `no_rappel` present (checked = "remind me") stores no_rappel 0, missing stores 1. Both redirect to
 * vaccine/fiche/{pet}. remove hard deletes the row (through a GET link).
 */
final class FicheEditTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function pet(): array
    {
        $petName = Factory::unique('FichePet');
        $owner = Factory::owner(['last_name' => Factory::unique('FicheOwner')]);

        return [Factory::pet($owner, ['name' => $petName]), $petName];
    }

    public function testTheFicheListsTheVaccinesOfThePet(): void
    {
        [$pet, $name] = $this->pet();
        $product = Factory::product(['name' => Factory::unique('FicheProd'), 'vaccin' => 1]);
        Factory::vaccine($pet, ['product_id' => $product]);
        Factory::vaccine($pet, ['product_id' => 0, 'product' => 'Fiche Martian X']);
        [$otherPet] = $this->pet();
        $otherProduct = Factory::product(['name' => Factory::unique('OtherProd'), 'vaccin' => 1]);
        Factory::vaccine($otherPet, ['product_id' => $otherProduct]);

        $page = $this->get('vaccine/fiche/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertSee($page, $name);
        $this->assertSee($page, $this->find('products', $product)['name']);
        $this->assertSee($page, 'Fiche Martian X');
        $this->assertDontSee($page, $this->find('products', $otherProduct)['name']);
    }

    public function testTheDetailPageShowsOneRecordWithItsDate(): void
    {
        [$pet, $name] = $this->pet();
        $redo = date('Y-m-d', strtotime('+40 days'));
        $product = Factory::product(['name' => Factory::unique('DetailProd'), 'vaccin' => 1]);
        $vaccine = Factory::vaccine($pet, ['redo' => $redo, 'product_id' => $product]);

        $page = $this->get('vaccine/detail/' . $vaccine);

        $this->assertStatus($page, 200);
        $this->assertSee($page, $name);
        $this->assertSee($page, $this->find('products', $product)['name']);
        $this->assertStringContainsString('value="' . $redo . '"', (string) $page->getBody());
        $this->assertStringContainsString('vaccine/update/' . $vaccine . '/' . $pet, (string) $page->getBody());
    }

    #[Group('known-bug')]
    public function testTheDetailPageOfAnExternalVaccineShowsNoProductName(): void
    {
        [$pet] = $this->pet();
        $vaccine = Factory::vaccine($pet, ['product_id' => 0, 'product' => 'Detail Vaccin Q']);

        $page = $this->get('vaccine/detail/' . $vaccine);

        // KNOWN BUG: with_product() replaces the free-text `product` column by an empty relation, so the name is lost -- decide in CI4 whether to keep
        $this->assertStatus($page, 200);
        $this->assertDontSee($page, 'Detail Vaccin Q');
    }

    public function testTickedCheckboxStoresNoRappelZero(): void
    {
        [$pet] = $this->pet();
        $vaccine = Factory::vaccine($pet, ['no_rappel' => 1]);
        $redo = date('Y-m-d', strtotime('+200 days'));

        $response = $this->post('vaccine/update/' . $vaccine . '/' . $pet, ['submit' => 1, 'date_redo' => $redo, 'no_rappel' => 'on']);

        $this->assertRedirectsTo($response, 'vaccine/fiche/' . $pet);
        $row = $this->find('vaccine_pet', $vaccine);
        $this->assertSame(0, (int) $row['no_rappel']);
        $this->assertSame($redo, $row['redo']);
    }

    public function testMissingCheckboxStoresNoRappelOne(): void
    {
        [$pet] = $this->pet();
        $vaccine = Factory::vaccine($pet, ['no_rappel' => 0]);
        $redo = date('Y-m-d', strtotime('+300 days'));

        $response = $this->post('vaccine/update/' . $vaccine . '/' . $pet, ['submit' => 1, 'date_redo' => $redo]);

        $this->assertRedirectsTo($response, 'vaccine/fiche/' . $pet);
        $row = $this->find('vaccine_pet', $vaccine);
        $this->assertSame(1, (int) $row['no_rappel']);
        $this->assertSame($redo, $row['redo']);
    }

    public function testUpdateChangesOnlyThatVaccine(): void
    {
        [$pet] = $this->pet();
        $a = Factory::vaccine($pet, ['no_rappel' => 0, 'redo' => '2031-05-05']);
        $b = Factory::vaccine($pet, ['no_rappel' => 0, 'redo' => '2031-06-06']);

        $this->post('vaccine/update/' . $a . '/' . $pet, ['date_redo' => '2032-01-01']);

        $this->assertSame('2031-06-06', $this->find('vaccine_pet', $b)['redo']);
        $this->assertSame(0, (int) $this->find('vaccine_pet', $b)['no_rappel']);
    }

    public function testRemoveDeletesTheRowAndRedirects(): void
    {
        [$pet] = $this->pet();
        $vaccine = Factory::vaccine($pet);
        $keep = Factory::vaccine($pet);

        $response = $this->get('vaccine/remove/' . $vaccine . '/' . $pet);

        $this->assertRedirectsTo($response, 'vaccine/fiche/' . $pet);
        $this->assertRowMissing('vaccine_pet', ['id' => $vaccine]);
        $this->assertRowExists('vaccine_pet', ['id' => $keep]);
        $this->assertSame(1, $this->logCount('remove_vaccine'));
    }
}
