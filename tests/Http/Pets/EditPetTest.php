<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/PetFormHelper.php';

/**
 * T061 - Pets::edit. Source: application/controllers/Pets.php.
 *
 * An empty breed keeps current_breed; breed2 = -1 clears the second breed; gender_custom overrides gender when
 * posted; a weight that differs from the newest history row adds a row.
 */
final class EditPetTest extends HttpTestCase
{
    private function newPet(array $overrides = []): array
    {
        $owner = Factory::owner();
        $id = Factory::pet($owner, $overrides + ['last_weight' => 10.00]);

        return [$id, $owner];
    }

    private function edit(int $id, array $overrides = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->post('pets/edit/' . $id, PetFormHelper::edit($this->find('pets', $id), $overrides));
    }

    public function testTheFormShowsThePetAndTheOwner(): void
    {
        [$id, $owner] = $this->newPet(['name' => 'Formpet', 'color' => 'paarsgroen']);
        $this->loginAs('vet');

        $page = $this->get('pets/edit/' . $id);

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="Formpet"', (string) $page->getBody());
        $this->assertStringContainsString('paarsgroen', (string) $page->getBody());
        $this->assertSee($page, (string) $this->find('owners', $owner)['last_name']);
    }

    public function testSavingStoresTheFieldsAndGoesToTheFiche(): void
    {
        [$id] = $this->newPet(['name' => 'Oud', 'type' => 0, 'chip' => null]);
        $this->loginAs('vet');

        $response = $this->edit($id, [
            'name' => 'Nieuw', 'type' => '1', 'gender' => '3', 'birth' => '2018-04-05', 'color' => 'wit',
            'hairtype' => 'lang', 'chip' => '981-111-222', 'vacbook' => 'VB-9', 'msg' => 'let op',
            'nutritional_advice' => 'brokken', 'medication' => 'pil', 'lost' => '1',
        ]);

        $this->assertRedirectsTo($response, 'pets/fiche/' . $id);
        $pet = $this->find('pets', $id);
        $this->assertSame('Nieuw', $pet['name']);
        $this->assertSame(1, (int) $pet['type']);
        $this->assertSame(3, (int) $pet['gender']);
        $this->assertSame('2018-04-05', $pet['birth']);
        $this->assertSame('wit', $pet['color']);
        $this->assertSame('lang', $pet['hairtype']);
        $this->assertSame('981111222', $pet['chip'], 'dashes are removed');
        $this->assertSame('VB-9', $pet['nr_vac_book']);
        $this->assertSame('let op', $pet['note']);
        $this->assertSame('brokken', $pet['nutritional_advice']);
        $this->assertSame('pil', $pet['medication']);
        $this->assertSame(1, (int) $pet['lost']);
    }

    public function testLostIsClearedWhenTheBoxIsNotSent(): void
    {
        [$id] = $this->newPet(['lost' => 1]);
        $this->loginAs('vet');

        $this->edit($id, []);

        $this->assertSame(0, (int) $this->find('pets', $id)['lost']);
    }

    public function testAnEmptyBreedKeepsTheCurrentBreedAndAGivenBreedReplacesIt(): void
    {
        [$id] = $this->newPet(['breed' => Fixtures::BREED_LABRADOR]);
        $this->loginAs('vet');

        $this->edit($id, ['breed' => '', 'current_breed' => (string) Fixtures::BREED_LABRADOR]);
        $this->assertSame(Fixtures::BREED_LABRADOR, (int) $this->find('pets', $id)['breed']);

        $this->edit($id, ['breed' => (string) Fixtures::BREED_CAT_SHORTHAIR, 'current_breed' => (string) Fixtures::BREED_LABRADOR]);
        $this->assertSame(Fixtures::BREED_CAT_SHORTHAIR, (int) $this->find('pets', $id)['breed']);
    }

    public function testTheSecondBreedIsKeptSetOrClearedWithMinusOne(): void
    {
        [$id] = $this->newPet(['breed2' => Fixtures::BREED_LABRADOR]);
        $this->loginAs('vet');

        // nothing chosen: current_breed2 stays
        $this->edit($id, ['breed2' => '', 'current_breed2' => (string) Fixtures::BREED_LABRADOR]);
        $this->assertSame(Fixtures::BREED_LABRADOR, (int) $this->find('pets', $id)['breed2']);

        // another one chosen
        $this->edit($id, ['breed2' => (string) Fixtures::BREED_CAT_SHORTHAIR]);
        $this->assertSame(Fixtures::BREED_CAT_SHORTHAIR, (int) $this->find('pets', $id)['breed2']);

        // -1 means none
        $this->edit($id, ['breed2' => '-1', 'current_breed2' => (string) Fixtures::BREED_CAT_SHORTHAIR]);
        $this->assertNull($this->find('pets', $id)['breed2']);
    }

    public function testGenderCustomOverridesGenderWhenPosted(): void
    {
        // the edit form has no gender_custom field, but the controller honours it when it is sent
        [$id] = $this->newPet(['gender' => 0]);
        $this->loginAs('vet');

        $this->edit($id, ['gender' => '1', 'gender_custom' => '4']);

        $this->assertSame(4, (int) $this->find('pets', $id)['gender'], 'gender_custom overrides gender');

        $this->edit($id, ['gender' => '2']);
        $this->assertSame(2, (int) $this->find('pets', $id)['gender'], 'without gender_custom, gender is used');
    }

    public function testANewWeightAddsAHistoryRowButTheSameWeightDoesNot(): void
    {
        [$id] = $this->newPet(['last_weight' => 10.00]);
        Factory::petWeight($id, 10.00, date('Y-m-d H:i:s', strtotime('-2 days')));
        $this->loginAs('vet');

        $this->edit($id, ['weight' => '10']);
        $this->assertRowCount(1, 'pets_weight', ['pets' => $id], 'same weight as the newest row');

        $this->edit($id, ['weight' => '11.5']);
        $this->assertRowCount(2, 'pets_weight', ['pets' => $id]);
        $this->assertSame('11.50', $this->find('pets', $id)['last_weight']);
        $this->assertEqualsWithDelta(11.5, (float) $this->scalar('SELECT weight FROM pets_weight WHERE pets = ? ORDER BY id DESC LIMIT 1', [$id]), 0.001);
    }

    public function testAZeroWeightAddsNoHistoryRow(): void
    {
        [$id] = $this->newPet();
        $this->loginAs('vet');

        $this->edit($id, ['weight' => '0']);

        $this->assertRowCount(0, 'pets_weight', ['pets' => $id]);
    }

    public function testTickingDeadSetsDeathAndTodayAsDeathDate(): void
    {
        [$id] = $this->newPet();
        $this->loginAs('vet');

        $this->edit($id, ['dead' => '1']);

        $pet = $this->find('pets', $id);
        $this->assertSame(1, (int) $pet['death']);
        $this->assertSame(date('Y-m-d'), $pet['death_date']);
    }

    #[Group('known-bug')]
    public function testEditingADeadPetAgainResetsTheDeathDateToToday(): void
    {
        // KNOWN BUG: death_date is overwritten with today on every save of a dead pet — decide in CI4 whether to keep
        [$id] = $this->newPet();
        $this->loginAs('vet');
        $this->edit($id, ['dead' => '1']);
        $this->db()->prepare('UPDATE pets SET death_date = ? WHERE id = ?')->execute(['2020-02-02', $id]);

        $this->edit($id, ['dead' => '1', 'name' => 'Renamed']);

        $this->assertSame(date('Y-m-d'), $this->find('pets', $id)['death_date']);
    }

    public function testNotTickingDeadClearsDeathAndDate(): void
    {
        [$id] = $this->newPet(['death' => 1, 'death_date' => '2020-02-02']);
        $this->loginAs('vet');

        $this->edit($id, []);

        $pet = $this->find('pets', $id);
        $this->assertSame(0, (int) $pet['death']);
        $this->assertNull($pet['death_date']);
    }
}
