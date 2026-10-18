<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T063 - Pets::delete (admin only). Source: application/controllers/Pets.php.
 * Soft delete (deleted_at); a vet is redirected to / and nothing changes.
 */
final class DeletePetTest extends HttpTestCase
{
    public function testAnAdminSoftDeletesThePetAndGoesToTheOwner(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['name' => 'Doomedpet']);
        $this->loginAs('admin');
        $logs = $this->logCount('delete_pet');

        $response = $this->get('pets/delete/' . $pet);

        $this->assertRedirectsTo($response, 'owners/detail/' . $owner);
        $row = $this->find('pets', $pet);
        $this->assertNotNull($row, 'the row stays');
        $this->assertNotNull($row['deleted_at']);
        $this->assertSame($logs + 1, $this->logCount('delete_pet'));
    }

    public function testTheDeletedPetDisappearsFromTheOwnerPageAndTheFiche(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['name' => 'Vanishingpet']);
        $stays = Factory::pet($owner, ['name' => 'Stayingpet']);
        $this->loginAs('admin');
        $this->assertSee($this->get('owners/detail/' . $owner), 'Vanishingpet');

        $this->get('pets/delete/' . $pet);

        $page = $this->get('owners/detail/' . $owner);
        $this->assertDontSee($page, 'Vanishingpet');
        $this->assertSee($page, 'Stayingpet');
        $this->assertStatus($this->get('pets/fiche/' . $pet), 404);
        $this->assertStatus($this->get('pets/fiche/' . $stays), 200);
    }

    #[Group('known-bug')]
    public function testADeletedPetIsStillFoundByTheHomeSearch(): void
    {
        // KNOWN BUG: Pets_model::search_by_name is raw SQL without a deleted_at filter,
        // so a deleted pet keeps showing in search - decide in CI4 whether to keep
        $owner = Factory::owner();
        $name = 'Zqxsearch' . substr(uniqid(), -5);
        $pet = Factory::pet($owner, ['name' => $name]);
        $this->loginAs('admin');
        $this->assertSee($this->get('search', ['search_query' => $name]), $name);

        $this->get('pets/delete/' . $pet);

        $this->assertSee($this->get('search', ['search_query' => $name]), $name);
    }

    public function testAVetIsSentHomeAndNothingChanges(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');
        $logs = $this->logCount('delete_pet');

        $response = $this->get('pets/delete/' . $pet);

        $this->assertRedirectsTo($response, '');
        $this->assertNull($this->find('pets', $pet)['deleted_at']);
        $this->assertSame($logs, $this->logCount('delete_pet'));
    }
}
