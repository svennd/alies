<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T051 - Owners::edit. Source: application/controllers/Owners.php.
 * Editing keeps last_bill, initial_vet, initial_loc and created_at untouched.
 */
final class EditOwnerTest extends HttpTestCase
{
    private function ownerWithEverything(): int
    {
        return Factory::owner([
            'first_name' => 'Edit', 'last_name' => 'Candidate', 'street' => 'Oudstraat', 'nr' => '3', 'zip' => '2000',
            'city' => 'Antwerpen', 'telephone' => '031111111', 'mobile' => '0471111111', 'mail' => 'old@example.test',
            'msg' => 'old note', 'low_budget' => 1, 'debts' => 1, 'contact' => 1, 'disabled' => 0,
            'last_bill' => '2024-02-02', 'initial_vet' => Fixtures::USER_VET2, 'initial_loc' => Fixtures::LOCATION_CAR,
        ]);
    }

    public function testTheFormShowsTheCurrentValues(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        $page = $this->get('owners/edit/' . $id);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        foreach (['value="Candidate"', 'value="Edit"', 'value="Oudstraat"', 'value="031111111"', 'value="0471111111"', 'value="old@example.test"', '>old note</textarea>'] as $expected) {
            $this->assertStringContainsString($expected, $body, $expected);
        }
        // ticked boxes are marked
        $this->assertMatchesRegularExpression('#name="low_budget"[^>]*checked#', $body);
        $this->assertDoesNotMatchRegularExpression('#name="disabled"[^>]*checked#', $body);
    }

    public function testSavingUpdatesTheFieldsAndGoesToTheDetailPage(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        $response = $this->post('owners/edit/' . $id, Forms::owner([
            'first_name' => 'Edited', 'last_name' => 'Updated', 'street' => 'Nieuwstraat', 'nr' => '9',
            'zip' => '9000', 'city' => 'Gent', 'main_city' => 'Gent', 'province' => 'Oost-Vlaanderen',
            'phone' => '092222222', 'mobile' => '0472222222', 'mail' => 'new@example.test', 'msg' => 'new note',
            'btw_nr' => 'BE0888888888', 'invoice_addr' => 'Factuurstraat 1', 'invoice_contact' => 'Jan', 'invoice_tel' => '0473333333',
        ]));

        $this->assertRedirectsTo($response, 'owners/detail/' . $id . '/true');
        $owner = $this->find('owners', $id);
        $this->assertSame('Edited', $owner['first_name']);
        $this->assertSame('Updated', $owner['last_name']);
        $this->assertSame('Nieuwstraat', $owner['street']);
        $this->assertSame('9000', $owner['zip']);
        $this->assertSame('092222222', $owner['telephone']);
        $this->assertSame('0472222222', $owner['mobile']);
        $this->assertSame('new@example.test', $owner['mail']);
        $this->assertSame('new note', $owner['msg']);
        $this->assertSame('BE0888888888', $owner['btw_nr']);
        $this->assertSame('Factuurstraat 1', $owner['invoice_addr']);
    }

    public function testFieldsThatAreNotInTheEditFormStayAsTheyWere(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Updated']));

        $owner = $this->find('owners', $id);
        $this->assertSame('2024-02-02', $owner['last_bill']);
        $this->assertSame(Fixtures::USER_VET2, (int) $owner['initial_vet']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $owner['initial_loc']);
        $this->assertNull($owner['deleted_at']);
    }

    public function testCheckboxesFollowTheFormAndDisabledCanBeSet(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        // everything unticked: all four flags off
        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Flags']));
        $owner = $this->find('owners', $id);
        $this->assertSame([0, 0, 0, 0], [(int) $owner['low_budget'], (int) $owner['debts'], (int) $owner['contact'], (int) $owner['disabled']]);

        // everything ticked: all four flags on
        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Flags', 'low_budget' => '1', 'debts' => '1', 'contact' => '1', 'disabled' => '1']));
        $owner = $this->find('owners', $id);
        $this->assertSame([1, 1, 1, 1], [(int) $owner['low_budget'], (int) $owner['debts'], (int) $owner['contact'], (int) $owner['disabled']]);
    }

    public function testTheChangeIsLogged(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Logged']));

        $log = $this->row("SELECT * FROM log WHERE event = 'update_client'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level']);
        $this->assertSame('client Logged (' . $id . ')', $log['msg']);
    }

    public function testTheDetailPageConfirmsTheUpdate(): void
    {
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');
        $response = $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Confirmed']));

        $page = $this->followRedirect($response);

        $this->assertSee($page, 'Klantinformatie aangepast!');
        $this->assertDontSee($this->get('owners/detail/' . $id), 'Klantinformatie aangepast!');
    }

    public function testOnlyThatOwnerIsChanged(): void
    {
        $id = $this->ownerWithEverything();
        $other = Factory::owner(['last_name' => 'Bystander']);
        $this->loginAs('vet');

        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => 'Changed']));

        $this->assertSame('Bystander', $this->find('owners', $other)['last_name']);
    }

    #[Group('known-bug')]
    public function testASoftDeletedOwnerCanStillBeEdited(): void
    {
        // KNOWN BUG: the update goes by id and ignores deleted_at, so a client that was removed can
        // be changed by url (it cannot be found or opened, but the edit form works)
        $this->loginAs('vet');

        $this->post('owners/edit/' . Fixtures::OWNER_DELETED, Forms::owner(['last_name' => 'Revived']));

        $this->assertSame('Revived', $this->find('owners', Fixtures::OWNER_DELETED)['last_name']);
        $this->assertNotNull($this->find('owners', Fixtures::OWNER_DELETED)['deleted_at'], 'still deleted');
    }

    #[Group('known-bug')]
    public function testEditingAnOwnerThatDoesNotExistStillRedirectsAndLogs(): void
    {
        // KNOWN BUG: no existence check: nothing is updated, but the user is sent to a detail page of nobody
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('owners/edit/999999', Forms::owner(['last_name' => 'Ghost']));

        $this->assertRedirectsTo($response, 'owners/detail/999999/true');
        $this->assertRowExists('log', ['event' => 'update_client']);
        $this->assertRowMissing('owners', ['last_name' => 'Ghost']);
    }

    #[Group('known-bug')]
    public function testTheLastNameCanBeEmptiedBecauseThereIsNoValidation(): void
    {
        // KNOWN BUG: unlike "add", editing does not require a last name
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        $this->post('owners/edit/' . $id, Forms::owner(['last_name' => '']));

        $this->assertSame('', $this->find('owners', $id)['last_name']);
    }

    #[Group('known-bug')]
    public function testAFormThatLeavesOutFieldsBlanksThoseFieldsInsteadOfFailing(): void
    {
        // KNOWN BUG (data loss): a field that is not posted is updated to NULL; for NOT NULL columns
        // the database turns that into an empty value, and unposted checkboxes switch off. Unlike
        // "add" this does not fail, so a partial post silently wipes the client.
        $id = $this->ownerWithEverything();
        $this->loginAs('vet');

        $response = $this->post('owners/edit/' . $id, ['submit' => 1, 'last_name' => 'Half']);

        $this->assertRedirectsTo($response, 'owners/detail/' . $id . '/true');
        $owner = $this->find('owners', $id);
        $this->assertSame('Half', $owner['last_name']);
        foreach (['first_name', 'street', 'nr', 'city', 'msg'] as $blanked) {
            $this->assertSame('', $owner[$blanked], $blanked);
        }
        foreach (['telephone', 'mobile', 'mail'] as $nulled) {
            $this->assertNull($owner[$nulled], $nulled);
        }
        $this->assertSame([0, 0, 0], [(int) $owner['low_budget'], (int) $owner['debts'], (int) $owner['contact']]);
    }

    public function testWithoutLoginTheFormIsNotAvailable(): void
    {
        $this->assertRedirectsTo($this->get('owners/edit/' . Fixtures::OWNER_JANSSENS), 'auth/login');
    }
}
