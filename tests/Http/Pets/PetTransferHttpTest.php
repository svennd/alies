<?php

declare(strict_types=1);

/**
 * T066 - pet transfer over HTTP. Source: Pets::change_owner / change_owner_complete, Pets_model::transfer_pet.
 * (The model internals are covered by tests/Integration/PetTransfer*.)
 *
 * A transfer hides the source pet (transfered = 1, chip removed, note [transfer:send:N]), creates a copy for the
 * new owner (note ends with [transfer:owner:N]), moves vaccines/weights/rx/lab, and copies the events
 * that are not hidden as finished history events (status 2) on the successor.
 */
final class PetTransferHttpTest extends HttpTestCase
{
    public function testSearchingByNameListsTheMatchingOwners(): void
    {
        $pet = Factory::pet(Factory::owner());
        $prefix = 'Zqtrans' . substr(uniqid(), -4);
        $target = Factory::owner(['last_name' => $prefix . 'Target']);
        Factory::owner(['last_name' => 'Unrelated' . $prefix]);
        $this->loginAs('vet');

        $page = $this->post('pets/change_owner/' . $pet, ['submit' => 'name', 'name' => $prefix]);

        $this->assertStatus($page, 200);
        $this->assertSee($page, $prefix . 'Target');
        $this->assertDontSee($page, 'Unrelated' . $prefix);
        $this->assertStringContainsString('pets/change_owner/' . $pet . '/' . $target, (string) $page->getBody());
    }

    public function testSearchingByClientIdListsThatOwner(): void
    {
        $pet = Factory::pet(Factory::owner());
        $target = Factory::owner(['last_name' => 'Zqclient' . substr(uniqid(), -4)]);
        $this->loginAs('vet');

        $page = $this->post('pets/change_owner/' . $pet, ['submit' => 'client', 'client' => (string) $target]);

        $this->assertStatus($page, 200);
        $this->assertSee($page, (string) $this->find('owners', $target)['last_name']);
    }

    public function testSelectingAnOwnerShowsTheConfirmationWithTheCompleteLink(): void
    {
        $oldOwner = Factory::owner(['last_name' => 'Zqold']);
        $pet = Factory::pet($oldOwner, ['name' => 'Movingpet']);
        $target = Factory::owner(['last_name' => 'Zqnew']);
        $this->loginAs('vet');

        $page = $this->get('pets/change_owner/' . $pet . '/' . $target);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Zqold');
        $this->assertSee($page, 'Zqnew');
        $this->assertStringContainsString('pets/change_owner_complete/' . $pet . '/' . $target, (string) $page->getBody());
    }

    public function testCompletingTheTransferMovesThePetAndItsHistory(): void
    {
        $oldOwner = Factory::owner();
        $newOwner = Factory::owner();
        $chip = '9813' . random_int(10000000000, 99999999999);
        $pet = Factory::pet($oldOwner, ['name' => 'Travelpet', 'chip' => $chip, 'type' => 1, 'last_weight' => 4.5]);
        $visible = Factory::event($pet, 1, 4, ['title' => 'Visible visit', 'anamnese' => 'Kerngezond', 'report' => 2]);
        $product = Factory::product(['name' => 'Transferproduct']);
        Factory::eventProduct($visible, $product, 2.0);
        $hidden = Factory::event($pet, 1, 4, ['title' => 'Hidden visit', 'no_history' => 1]);
        $vaccine = Factory::vaccine($pet);
        $weight = Factory::petWeight($pet, 4.5);
        $this->loginAs('vet');
        $logs = $this->logCount('transfer_pet');

        $response = $this->get('pets/change_owner_complete/' . $pet . '/' . $newOwner);

        $this->assertRedirectsTo($response, 'owners/detail/' . $newOwner);
        $source = $this->find('pets', $pet);
        $this->assertSame(1, (int) $source['transfered']);
        $this->assertNull($source['chip']);
        $this->assertSame('[transfer:send:' . $newOwner . ']', $source['note']);
        $this->assertSame($oldOwner, (int) $source['owner']);

        $successor = $this->row('SELECT * FROM pets WHERE owner = ? AND id != ?', [$newOwner, $pet]);
        $this->assertNotNull($successor, 'a successor pet exists for the new owner');
        $this->assertSame('Travelpet', $successor['name']);
        $this->assertSame(1, (int) $successor['type']);
        $this->assertSame(0, (int) $successor['transfered']);
        $this->assertStringContainsString('[transfer:owner:' . $newOwner . ']', $successor['note']);
        $this->assertEqualsWithDelta(4.5, (float) $successor['last_weight'], 0.001);

        // vaccines and weights follow the pet
        $this->assertSame((int) $successor['id'], (int) $this->find('vaccine_pet', $vaccine)['pet']);
        $this->assertSame((int) $successor['id'], (int) $this->find('pets_weight', $weight)['pets']);

        // visible events are copied as history, with the treatment lines in the report; the hidden one is not
        $copies = $this->rows('SELECT * FROM events WHERE pet = ?', [$successor['id']]);
        $this->assertCount(1, $copies);
        $this->assertSame('Visible visit', $copies[0]['title']);
        $this->assertSame(2, (int) $copies[0]['status']);
        $this->assertStringContainsString('Kerngezond', $copies[0]['anamnese']);
        $this->assertStringContainsString('Transferproduct', $copies[0]['anamnese']);
        // the originals stay on the source pet
        $this->assertSame($pet, (int) $this->find('events', $visible)['pet']);
        $this->assertSame($pet, (int) $this->find('events', $hidden)['pet']);

        $this->assertSame($logs + 1, $this->logCount('transfer_pet'));
    }

    public function testTheTransferredPetIsOnTheNewOwnersPageOnly(): void
    {
        $oldOwner = Factory::owner();
        $newOwner = Factory::owner();
        $pet = Factory::pet($oldOwner, ['name' => 'Zqwanderer']);
        $this->loginAs('vet');

        $this->get('pets/change_owner_complete/' . $pet . '/' . $newOwner);

        $this->assertSee($this->get('owners/detail/' . $newOwner), 'Zqwanderer');
        $this->assertDontSee($this->get('owners/detail/' . $oldOwner), 'Zqwanderer');
    }

    public function testTransferringToTheCurrentOwnerFailsWithAMessageAndChangesNothing(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['chip' => '9814' . random_int(10000000000, 99999999999)]);
        $this->loginAs('vet');
        $before = $this->countWhere('pets');
        $logs = $this->logCount('transfer_pet');

        $response = $this->get('pets/change_owner_complete/' . $pet . '/' . $owner);

        $this->assertRedirectsTo($response, 'pets/change_owner/' . $pet . '/' . $owner);
        // the flash text is Dutch for the test users (English: "The pet transfer failed")
        $this->assertMatchesRegularExpression('/mislukt|transfer failed/i', $this->text($this->followRedirect($response)));
        $row = $this->find('pets', $pet);
        $this->assertSame(0, (int) $row['transfered']);
        $this->assertNotNull($row['chip']);
        $this->assertSame($before, $this->countWhere('pets'));
        $this->assertSame($logs, $this->logCount('transfer_pet'));
    }

    public function testTransferringToADisabledOwnerFailsAndChangesNothing(): void
    {
        $pet = Factory::pet(Factory::owner());
        $disabled = Factory::owner(['disabled' => 1]);
        $healthy = Factory::owner();
        $this->loginAs('vet');
        $before = $this->countWhere('pets');

        $response = $this->get('pets/change_owner_complete/' . $pet . '/' . $disabled);

        $this->assertRedirectsTo($response, 'pets/change_owner/' . $pet . '/' . $disabled);
        $this->assertSame(0, (int) $this->find('pets', $pet)['transfered']);
        $this->assertSame($before, $this->countWhere('pets'));

        // positive control: an enabled owner works for the same pet
        $ok = $this->get('pets/change_owner_complete/' . $pet . '/' . $healthy);
        $this->assertRedirectsTo($ok, 'owners/detail/' . $healthy);
        $this->assertSame(1, (int) $this->find('pets', $pet)['transfered']);
    }

    public function testTransferringToAMissingOrDeletedOwnerFails(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');

        $missing = $this->get('pets/change_owner_complete/' . $pet . '/987654');
        $deleted = $this->get('pets/change_owner_complete/' . $pet . '/' . Fixtures::OWNER_DELETED);

        $this->assertRedirectsTo($missing, 'pets/change_owner/' . $pet . '/987654');
        $this->assertRedirectsTo($deleted, 'pets/change_owner/' . $pet . '/' . Fixtures::OWNER_DELETED);
        $this->assertSame(0, (int) $this->find('pets', $pet)['transfered']);
    }

    public function testAPetThatWasAlreadyTransferredCannotBeTransferredAgain(): void
    {
        $pet = Factory::pet(Factory::owner());
        $first = Factory::owner();
        $second = Factory::owner();
        $this->loginAs('vet');
        $this->get('pets/change_owner_complete/' . $pet . '/' . $first);
        $before = $this->countWhere('pets');

        $again = $this->get('pets/change_owner_complete/' . $pet . '/' . $second);

        $this->assertRedirectsTo($again, 'pets/change_owner/' . $pet . '/' . $second);
        $this->assertSame($before, $this->countWhere('pets'));
    }
}
