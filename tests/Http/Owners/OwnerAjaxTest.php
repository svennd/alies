<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T053 - the small endpoints the owner screens call from JavaScript:
 * set_email, phone (duplicate check), addr (duplicate check) and get_zip.
 * Some answer only when the X-Requested-With header is present; otherwise the body is empty.
 */
final class OwnerAjaxTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    // ---- set_email --------------------------------------------------------------------------

    public function testTheEmailAddressIsStoredAndTheResponseIsEmpty(): void
    {
        $owner = Factory::owner(['mail' => null]);

        $response = $this->ajaxPost('owners/set_email/' . $owner, ['email' => 'new@example.test']);

        $this->assertStatus($response, 200);
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame('new@example.test', $this->find('owners', $owner)['mail']);
    }

    public function testSettingTheEmailIsLoggedWithoutTheAddressItself(): void
    {
        $owner = Factory::owner();
        $this->db()->exec('DELETE FROM log');

        $this->ajaxPost('owners/set_email/' . $owner, ['email' => 'secret@example.test']);

        $log = $this->row("SELECT * FROM log WHERE event = 'add_mail'");
        $this->assertNotNull($log);
        $this->assertSame('owner_id:' . $owner, $log['msg'], 'the address is personal data and stays out of the log');
    }

    public function testOnlyThatOwnerIsChanged(): void
    {
        $owner = Factory::owner();
        $other = Factory::owner(['mail' => 'keep@example.test']);

        $this->ajaxPost('owners/set_email/' . $owner, ['email' => 'new@example.test']);

        $this->assertSame('keep@example.test', $this->find('owners', $other)['mail']);
    }

    // ---- phone ------------------------------------------------------------------------------

    public function testAKnownPhoneNumberFindsItsOwner(): void
    {
        $response = $this->ajaxPost('owners/phone', ['phone' => '0470 12 34 56']);

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/json', $response->getHeaderLine('Content-Type'));
        $json = $this->json($response);
        $this->assertTrue($json['exists']);
        $this->assertSame(Fixtures::OWNER_JANSSENS, (int) $json['owner']['id']);
        $this->assertSame('Janssens', $json['owner']['last_name']);
    }

    public function testSpacesDotsAndSlashesInTheNumberDoNotMatter(): void
    {
        foreach (['0470/12.34.56', '0470 123 456', '0470123456'] as $phone) {
            $this->assertTrue($this->json($this->ajaxPost('owners/phone', ['phone' => $phone]))['exists'], $phone);
        }
    }

    public function testTheLandlineAndTheOtherNumberFieldsAreSearchedToo(): void
    {
        $this->assertTrue($this->json($this->ajaxPost('owners/phone', ['phone' => Fixtures::JANSSENS_LANDLINE]))['exists']);

        $owner = Factory::owner(['phone2' => '0499888777', 'phone3' => '0499666555']);
        $this->assertSame($owner, (int) $this->json($this->ajaxPost('owners/phone', ['phone' => '0499888777']))['owner']['id']);
        $this->assertSame($owner, (int) $this->json($this->ajaxPost('owners/phone', ['phone' => '0499666555']))['owner']['id']);
    }

    public function testAnUnknownNumberDoesNotExist(): void
    {
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/phone', ['phone' => '0499123123'])));
    }

    public function testAnEmptyNumberDoesNotExist(): void
    {
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/phone', ['phone' => ''])));
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/phone', [])));
    }

    public function testThePhoneCheckAlsoAnswersWithoutTheAjaxHeader(): void
    {
        // unlike the others, this endpoint has no is_ajax_request() check
        $this->assertTrue($this->json($this->post('owners/phone', ['phone' => '0470123456']))['exists']);
    }

    public function testANumberOfTwoClientsGivesOnlyTheFirstMatch(): void
    {
        $json = $this->json($this->ajaxPost('owners/phone', ['phone' => Fixtures::DUPLICATE_MOBILE]));

        $this->assertTrue($json['exists']);
        $this->assertContains((int) $json['owner']['id'], [Fixtures::OWNER_DUPLICATE_A, Fixtures::OWNER_DUPLICATE_B]);
        $this->assertArrayNotHasKey('owners', $json);
    }

    // ---- addr -------------------------------------------------------------------------------

    public function testAKnownAddressFindsTheClientsThatLiveThere(): void
    {
        $response = $this->ajaxPost('owners/addr', ['street' => 'Kerkstraat', 'nr' => '12', 'zip' => '2000']);

        $this->assertStringStartsWith('application/json', $response->getHeaderLine('Content-Type'));
        $json = $this->json($response);
        $this->assertTrue($json['exists']);
        $this->assertSame([Fixtures::OWNER_JANSSENS], array_map(static fn (array $o): int => (int) $o['id'], $json['owners']));
    }

    public function testAnotherNumberOrZipIsAnotherAddress(): void
    {
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/addr', ['street' => 'Kerkstraat', 'nr' => '13', 'zip' => '2000'])));
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/addr', ['street' => 'Kerkstraat', 'nr' => '12', 'zip' => '9000'])));
    }

    public function testTwoClientsAtTheSameAddressAreBothReturned(): void
    {
        $a = Factory::owner(['street' => 'Gedeelde Laan', 'nr' => '5', 'zip' => '3000']);
        $b = Factory::owner(['street' => 'Gedeelde Laan', 'nr' => '5', 'zip' => '3000']);

        $ids = array_map(static fn (array $o): int => (int) $o['id'], $this->json($this->ajaxPost('owners/addr', ['street' => 'Gedeelde Laan', 'nr' => '5', 'zip' => '3000']))['owners']);

        sort($ids);
        $this->assertSame([$a, $b], $ids);
    }

    public function testTheAddressCheckNeedsTheAjaxHeader(): void
    {
        $response = $this->post('owners/addr', ['street' => 'Kerkstraat', 'nr' => '12', 'zip' => '2000']);

        $this->assertStatus($response, 200);
        $this->assertSame('', (string) $response->getBody());
    }

    public function testASearchWithWildcardsDoesNotMatchEverything(): void
    {
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/addr', ['street' => '%', 'nr' => '%', 'zip' => '%'])));
        $this->assertSame(['exists' => false], $this->json($this->ajaxPost('owners/addr', ['street' => '_erkstraat', 'nr' => '12', 'zip' => '2000'])));
    }

    // ---- get_zip ----------------------------------------------------------------------------

    public function testAZipCodeGivesItsCityAndProvince(): void
    {
        $json = $this->json($this->ajaxGet('owners/get_zip/9000'));

        $this->assertSame('9000', $json['zip']);
        $this->assertSame('Gent', $json['city']);
        $this->assertSame('Gent', $json['main_city']);
        $this->assertSame('Oost-Vlaanderen', $json['province']);
    }

    public function testAnUnknownZipCodeGivesAnEmptyList(): void
    {
        $response = $this->ajaxGet('owners/get_zip/0000');

        $this->assertSame('[]', trim((string) $response->getBody()));
    }

    public function testTheZipLookupNeedsTheAjaxHeader(): void
    {
        $response = $this->get('owners/get_zip/9000');

        $this->assertStatus($response, 200);
        $this->assertSame('', (string) $response->getBody());
    }

    // ---- access -----------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testSettingAnEmailOfAnOwnerThatDoesNotExistIsNotAnError(): void
    {
        // KNOWN BUG: no existence check; the update touches no row and is still reported (and logged) as done
        $this->db()->exec('DELETE FROM log');

        $response = $this->ajaxPost('owners/set_email/999999', ['email' => 'ghost@example.test']);

        $this->assertStatus($response, 200);
        $this->assertRowExists('log', ['event' => 'add_mail', 'msg' => 'owner_id:999999']);
    }

    public function testAnonymousVisitorsCannotUseTheEndpoints(): void
    {
        $this->cookies->clear();

        foreach (['owners/phone', 'owners/addr', 'owners/set_email/' . Fixtures::OWNER_JANSSENS] as $route) {
            $this->assertRedirectsTo($this->ajaxPost($route, ['phone' => '0470123456']), 'auth/login', $route);
        }
        $this->assertRedirectsTo($this->ajaxGet('owners/get_zip/9000'), 'auth/login');
    }
}
