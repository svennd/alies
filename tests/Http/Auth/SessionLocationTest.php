<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T044 - the location chosen at login is used for what the user creates afterwards.
 * Source: Welcome::change_location, Ion_auth::set_user_location, Events::new_event.
 */
final class SessionLocationTest extends HttpTestCase
{
    public function testANewEventGetsTheLocationThatWasChosen(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet', Fixtures::LOCATION_CAR);

        $this->get('events/new_event/' . $pet);

        $event = $this->row('SELECT * FROM events WHERE pet = ?', [$pet]);
        $this->assertNotNull($event, 'no event was created');
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $event['location']);
        $this->assertSame(Fixtures::USER_VET, (int) $event['vet']);
    }

    public function testAfterSwitchingLocationNewRecordsUseTheNewOne(): void
    {
        $firstPet = Factory::pet(Factory::owner());
        $secondPet = Factory::pet(Factory::owner());
        $this->loginAs('vet', Fixtures::LOCATION_PRACTICE);
        $this->get('events/new_event/' . $firstPet);

        $this->get('welcome/change_location/' . Fixtures::LOCATION_CAR);
        $this->get('events/new_event/' . $secondPet);

        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $this->row('SELECT location FROM events WHERE pet = ?', [$firstPet])['location']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $this->row('SELECT location FROM events WHERE pet = ?', [$secondPet])['location']);
    }

    public function testANewOwnerRemembersTheLocationAndTheVetWhoAddedThem(): void
    {
        $this->loginAs('vet2', Fixtures::LOCATION_CAR);

        $this->post('owners/add', Forms::owner(['last_name' => 'Locationtest']));

        $owner = $this->row("SELECT * FROM owners WHERE last_name = 'Locationtest'");
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $owner['initial_loc']);
        $this->assertSame(Fixtures::USER_VET2, (int) $owner['initial_vet']);
    }

    public function testTheChosenLocationIsStoredOnTheUserAndInTheSession(): void
    {
        $this->loginAs('vet', Fixtures::LOCATION_CAR);

        $this->assertSame(Fixtures::LOCATION_CAR, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
        $this->assertRowExists('log', ['event' => 'set_user_location']);
    }

    public function testTwoUsersKeepTheirOwnLocation(): void
    {
        $this->loginAs('vet', Fixtures::LOCATION_PRACTICE);
        $other = new \GuzzleHttp\Client([
            'base_uri' => self::baseUrl(),
            \GuzzleHttp\RequestOptions::COOKIES => new \GuzzleHttp\Cookie\CookieJar(),
            \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
        ]);
        $other->post('auth/login', ['form_params' => ['identity' => 'vet2@test.local', 'password' => Fixtures::PASSWORD]]);
        $other->get('welcome/change_location/' . Fixtures::LOCATION_CAR);

        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $this->find('users', Fixtures::USER_VET2)['current_location']);
    }

    public function testOnlyStaffCanChangeLocation(): void
    {
        $this->loginAs('member');

        $response = $this->get('welcome/change_location/1');

        $this->assertStatus($response, 500);
        $this->assertSame(0, (int) $this->find('users', Fixtures::USER_MEMBER)['current_location']);
    }

    public function testAnonymousVisitorsCannotChangeLocation(): void
    {
        $this->assertRedirectsTo($this->get('welcome/change_location/1'), 'auth/login');
    }

    #[Group('known-bug')]
    public function testALocationThatDoesNotExistIsAccepted(): void
    {
        // KNOWN BUG: the id is not validated; the user is now "at" a location that does not exist.
        // (users.current_location is a TINYINT UNSIGNED: a value above 255 is silently stored as 255)
        $this->loginAs('vet');

        $response = $this->get('welcome/change_location/200');

        $this->assertRedirectsTo($response, '');
        $this->assertSame(200, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
        $this->assertRowMissing('stock_location', ['id' => 200]);
    }

    #[Group('known-bug')]
    public function testTheDeletedLocationCanBeChosenToo(): void
    {
        // KNOWN BUG: the soft deleted location (id 3) is not offered in the menu but can still be chosen by url
        $this->loginAs('vet');

        $this->get('welcome/change_location/' . Fixtures::LOCATION_DELETED);

        $this->assertSame(Fixtures::LOCATION_DELETED, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
    }
}
