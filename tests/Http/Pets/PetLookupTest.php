<?php

declare(strict_types=1);

/**
 * T065 - Pets::get_pet_name (select2 autocomplete) and Pets::check_chip. Source: application/controllers/Pets.php.
 *
 * term empty -> {"results":[]}; numeric term -> that pet id (any state); text -> prefix search on name,
 * max 5, alive and not transferred pets only. check_chip tells whether ANOTHER pet has the chip.
 */
final class PetLookupTest extends HttpTestCase
{
    public function testWithoutATermTheResultIsEmpty(): void
    {
        $this->loginAs('vet');

        $this->assertSame(['results' => []], $this->json($this->get('pets/get_pet_name')));
        $this->assertSame(['results' => []], $this->json($this->get('pets/get_pet_name', ['term' => ''])));
    }

    public function testANumericTermFindsThePetById(): void
    {
        $owner = Factory::owner(['last_name' => 'Lookupowner']);
        $pet = Factory::pet($owner, ['name' => 'Lookuppet']);
        $this->loginAs('vet');

        $json = $this->json($this->get('pets/get_pet_name', ['term' => (string) $pet]));

        $this->assertSame(['results' => [['id' => (string) $pet, 'text' => 'Lookuppet(#' . $pet . ') - Lookupowner']]], $json);
    }

    public function testATextTermIsAPrefixSearchWithoutDeadOrTransferredPets(): void
    {
        $owner = Factory::owner(['last_name' => 'Prefixowner']);
        $prefix = 'Qzp' . substr(uniqid(), -4);
        $alive = Factory::pet($owner, ['name' => $prefix . 'alive']);
        Factory::pet($owner, ['name' => $prefix . 'dead', 'death' => 1]);
        Factory::pet($owner, ['name' => $prefix . 'moved', 'transfered' => 1]);
        Factory::pet($owner, ['name' => 'x' . $prefix . 'infix']);
        $this->loginAs('vet');

        // the controller lower-cases the term; MySQL compares case-insensitively
        $json = $this->json($this->get('pets/get_pet_name', ['term' => strtoupper($prefix)]));

        $this->assertCount(1, $json['results']);
        $this->assertSame((string) $alive, (string) $json['results'][0]['id']);
        $this->assertSame($prefix . 'alive(#' . $alive . ') - Prefixowner', $json['results'][0]['text']);
    }

    public function testATextTermReturnsAtMostFiveResults(): void
    {
        $owner = Factory::owner();
        $prefix = 'Qzm' . substr(uniqid(), -4);
        for ($i = 1; $i <= 7; $i++) {
            Factory::pet($owner, ['name' => $prefix . $i]);
        }
        $this->loginAs('vet');

        $json = $this->json($this->get('pets/get_pet_name', ['term' => $prefix]));

        $this->assertCount(5, $json['results']);
    }

    public function testCheckChipFindsAnotherPetWithThatChip(): void
    {
        $owner = Factory::owner(['last_name' => 'Chipowner']);
        $chip = '9811' . random_int(10000000000, 99999999999);
        $pet = Factory::pet($owner, ['name' => 'Chippet', 'chip' => $chip]);
        $this->loginAs('vet');

        $json = $this->json($this->get('pets/check_chip/0/' . $chip));

        $this->assertSame('200', $json['status']);
        $this->assertSame($pet, (int) $json['pet_id']);
        $this->assertSame('Chippet', $json['pet_name']);
        $this->assertSame($owner, (int) $json['owner_id']);
        $this->assertSame('Chipowner', $json['owner_name']);
    }

    public function testCheckChipIgnoresThePetItselfAndUnknownChips(): void
    {
        $chip = '9812' . random_int(10000000000, 99999999999);
        $pet = Factory::pet(Factory::owner(), ['chip' => $chip]);
        $this->loginAs('vet');

        $this->assertSame('200', $this->json($this->get('pets/check_chip/0/' . $chip))['status'], 'positive control');
        $this->assertSame(['status' => '404'], $this->json($this->get('pets/check_chip/' . $pet . '/' . $chip)));
        $this->assertSame(['status' => '404'], $this->json($this->get('pets/check_chip/0/981000999000111')));
    }
}
