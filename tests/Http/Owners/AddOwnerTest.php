<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T050 - Owners::add (new client). Source: application/controllers/Owners.php.
 *
 * The browser form always sends every field (empty ones as ""), so tests post the complete form
 * (Forms::owner). Checkboxes are only sent when ticked.
 */
final class AddOwnerTest extends HttpTestCase
{
    private function ownerByName(string $lastName): ?array
    {
        return $this->row('SELECT * FROM owners WHERE last_name = ?', [$lastName]);
    }

    public function testTheFormShowsTheFieldsOfAClient(): void
    {
        $this->loginAs('vet');

        $page = $this->get('owners/add');

        $this->assertStatus($page, 200);
        foreach (['last_name', 'first_name', 'street', 'nr', 'zip', 'city', 'main_city', 'province', 'phone', 'mobile', 'phone2', 'phone3', 'mail', 'msg', 'btw_nr', 'invoice_addr', 'invoice_contact', 'invoice_tel', 'debts', 'low_budget', 'contact'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', (string) $page->getBody(), $field);
        }
    }

    public function testAClientIsStoredWithEveryField(): void
    {
        $this->loginAs('vet');

        $response = $this->post('owners/add', Forms::owner([
            'first_name' => 'Greta', 'last_name' => 'Addtest',
            'street' => 'Veldstraat', 'nr' => '14B', 'zip' => '9000', 'city' => 'Gent', 'main_city' => 'Gent', 'province' => 'Oost-Vlaanderen',
            'phone' => '091234567', 'mobile' => '0471234567', 'phone2' => '0472000000', 'phone3' => '0473000000',
            'mail' => 'greta@example.test', 'msg' => 'Bijt niet', 'btw_nr' => 'BE0999999999',
            'invoice_addr' => 'Postbus 1, 9000 Gent', 'invoice_contact' => 'Boekhouding', 'invoice_tel' => '0470999999',
        ]));

        $owner = $this->ownerByName('Addtest');
        $this->assertNotNull($owner, 'no owner was created');
        $this->assertRedirectsTo($response, 'owners/detail/' . $owner['id']);
        $this->assertSame('Greta', $owner['first_name']);
        $this->assertSame('Veldstraat', $owner['street']);
        $this->assertSame('14B', $owner['nr']);
        $this->assertSame('9000', $owner['zip']);
        $this->assertSame('Gent', $owner['city']);
        $this->assertSame('Gent', $owner['main_city']);
        $this->assertSame('Oost-Vlaanderen', $owner['province']);
        // the form field "phone" is the column "telephone"
        $this->assertSame('091234567', $owner['telephone']);
        $this->assertSame('0471234567', $owner['mobile']);
        $this->assertSame('0472000000', $owner['phone2']);
        $this->assertSame('0473000000', $owner['phone3']);
        $this->assertSame('greta@example.test', $owner['mail']);
        $this->assertSame('Bijt niet', $owner['msg']);
        $this->assertSame('BE0999999999', $owner['btw_nr']);
        $this->assertSame('Postbus 1, 9000 Gent', $owner['invoice_addr']);
        $this->assertSame('Boekhouding', $owner['invoice_contact']);
        $this->assertSame('0470999999', $owner['invoice_tel']);
    }

    public function testTheServerFillsInWhoAddedTheClientWhenAndWhere(): void
    {
        $this->loginAs('vet2', Fixtures::LOCATION_CAR);

        $this->post('owners/add', Forms::owner(['last_name' => 'Servertest']));

        $owner = $this->ownerByName('Servertest');
        $this->assertSame(date('Y-m-d'), $owner['last_bill']);
        $this->assertSame(Fixtures::USER_VET2, (int) $owner['initial_vet']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $owner['initial_loc']);
        $this->assertSame('0', (string) $owner['disabled']);
        $this->assertNull($owner['deleted_at']);
        $this->assertEqualsWithDelta(time(), strtotime($owner['created_at']), 60);
    }

    public function testCheckboxesAreOneWhenTickedAndZeroWhenLeftOut(): void
    {
        $this->loginAs('vet');

        $this->post('owners/add', Forms::owner(['last_name' => 'Unticked']));
        $this->post('owners/add', Forms::owner(['last_name' => 'Ticked', 'low_budget' => '1', 'debts' => '1', 'contact' => '1']));

        $unticked = $this->ownerByName('Unticked');
        $this->assertSame([0, 0, 0], [(int) $unticked['low_budget'], (int) $unticked['debts'], (int) $unticked['contact']]);
        $ticked = $this->ownerByName('Ticked');
        $this->assertSame([1, 1, 1], [(int) $ticked['low_budget'], (int) $ticked['debts'], (int) $ticked['contact']]);
    }

    public function testAddingAClientIsLogged(): void
    {
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $this->post('owners/add', Forms::owner(['last_name' => 'Logtest']));

        $owner = $this->ownerByName('Logtest');
        $log = $this->row("SELECT * FROM log WHERE event = 'add_client'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level'], 'INFO level');
        $this->assertSame('Added client Logtest (' . $owner['id'] . ')', $log['msg']);
    }

    public function testTheNewClientCanBeOpenedRightAway(): void
    {
        $this->loginAs('vet');

        $response = $this->post('owners/add', Forms::owner(['last_name' => 'Landingpage']));

        $page = $this->followRedirect($response);
        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Landingpage');
    }

    public function testPhoneNumbersAreStoredExactlyAsTyped(): void
    {
        // quirk: no normalisation on the server. A number with spaces is stored with spaces, so a
        // search for "0470123456" (spaces removed by format_phone) will not find it
        $this->loginAs('vet');

        $this->post('owners/add', Forms::owner(['last_name' => 'Spaces', 'mobile' => '0470 12 34 56']));

        $this->assertSame('0470 12 34 56', $this->ownerByName('Spaces')['mobile']);
    }

    public function testWithoutALastNameTheFormIsShownAgainAndNothingIsStored(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('owners');

        $response = $this->post('owners/add', Forms::owner(['last_name' => '']));

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="last_name"', (string) $response->getBody());
        $this->assertNotRedirect($response);
        $this->assertSame($before, $this->countWhere('owners'));
    }

    public function testWithoutThePressedSubmitButtonNothingIsStored(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('owners');

        $form = Forms::owner(['last_name' => 'Nosubmit']);
        unset($form['submit']);
        $response = $this->post('owners/add', $form);

        $this->assertStatus($response, 200);
        $this->assertSame($before, $this->countWhere('owners'));
    }

    #[Group('known-bug')]
    public function testAFormThatLeavesOutFieldsIsADatabaseError(): void
    {
        // KNOWN BUG: fields that are not posted are inserted as NULL and first_name, city and others are
        // NOT NULL. A real browser always sends them, but anything else (an API client, a half form)
        // gets a 500 and nothing is stored.
        $this->loginAs('vet');
        $before = $this->countWhere('owners');

        $response = $this->post('owners/add', ['submit' => 1, 'last_name' => 'Minimal']);

        $this->assertStatus($response, 500);
        $this->assertSee($response, "Column 'first_name' cannot be null");
        $this->assertSame($before, $this->countWhere('owners'));
    }

    #[Group('known-bug')]
    public function testANameThatIsOnlySpacesCountsAsFilledIn(): void
    {
        // KNOWN BUG: the check is empty(), which does not trim
        $this->loginAs('vet');
        $before = $this->countWhere('owners');

        $this->post('owners/add', Forms::owner(['last_name' => '   ']));

        $this->assertSame($before + 1, $this->countWhere('owners'));
    }

    #[Group('known-bug')]
    public function testTextIsStoredAndShownWithoutEscaping(): void
    {
        // KNOWN BUG (stored XSS): the detail page prints the last name as it was typed
        $this->loginAs('vet');
        $payload = '<b id="xss-probe">bold</b>';

        $response = $this->post('owners/add', Forms::owner(['last_name' => $payload]));

        $this->assertSame($payload, $this->ownerByName($payload)['last_name']);
        $this->assertStringContainsString($payload, (string) $this->followRedirect($response)->getBody(), 'printed as HTML, not as text');
    }

    public function testUsersWithoutStaffRightsCannotAddClients(): void
    {
        $this->loginAs('member');
        $before = $this->countWhere('owners');

        $response = $this->post('owners/add', Forms::owner(['last_name' => 'Memberadd']));

        $this->assertStatus($response, 500);
        $this->assertSame($before, $this->countWhere('owners'));
    }

    public function testAccountingUsersCanAddClients(): void
    {
        $this->loginAs('acc');

        $this->post('owners/add', Forms::owner(['last_name' => 'Accountingadd']));

        $this->assertNotNull($this->ownerByName('Accountingadd'));
    }
}
