<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;

/**
 * T033 - Auth::logout.
 */
final class LogoutTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('DELETE FROM log');
        $this->db()->exec('DELETE FROM login_attempts');
    }

    public function testLogoutGoesToTheLoginPageAndResetsTheLocation(): void
    {
        $this->loginAs('vet', Fixtures::LOCATION_CAR);
        $this->assertSame(2, (int) $this->find('users', Fixtures::USER_VET)['current_location']);

        $response = $this->get('auth/logout');

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertSame(0, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
    }

    public function testAfterLogoutProtectedPagesAskForALoginAgain(): void
    {
        $this->loginAs('vet');
        $this->assertStatus($this->get('/'), 200);

        $this->get('auth/logout');

        $this->assertRedirectsTo($this->get('/'), 'auth/login');
        $this->assertRedirectsTo($this->get('owners/add'), 'auth/login');
    }

    public function testLogoutIsLogged(): void
    {
        $this->loginAs('vet');

        $this->get('auth/logout');

        $logout = $this->row("SELECT * FROM log WHERE event = 'logout'");
        $this->assertNotNull($logout, 'no logout log row');
        $this->assertSame(5, (int) $logout['level'], 'DEBUG level');
        $this->assertSame('user : vet@test.local', $logout['msg']);
        $this->assertGreaterThan(0, $this->countWhere('log', ['event' => 'unset_location']));
    }

    public function testTheOldSessionCannotBeUsedAfterLogout(): void
    {
        $this->loginAs('vet');
        $sessionCookie = $this->cookies->getCookieByName('alies_demo_root');
        $this->assertNotNull($sessionCookie);
        $stolen = $sessionCookie->toArray();

        // a second browser that has a copy of the session cookie
        $jar = new CookieJar();
        $jar->setCookie(new SetCookie($stolen));
        $other = new Client([
            'base_uri' => self::baseUrl(),
            RequestOptions::COOKIES => $jar,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS => false,
        ]);
        $this->assertStatus($other->get('/'), 200, 'the copied session works before the logout');

        $this->get('auth/logout');

        $this->assertRedirectsTo($other->get('/'), 'auth/login');
    }

    public function testLoggingOutWhenNotLoggedInStillRedirectsAndLogsAnError(): void
    {
        $response = $this->get('auth/logout');

        $this->assertRedirectsTo($response, 'auth/login');
        $error = $this->row("SELECT * FROM log WHERE event = 'unset_location failed'");
        $this->assertNotNull($error);
        $this->assertSame(2, (int) $error['level'], 'ERROR level');
    }

    public function testLogoutOnlyResetsTheLocationOfTheUserWhoLogsOut(): void
    {
        $this->db()->exec('UPDATE users SET current_location = 2 WHERE id = ' . Fixtures::USER_VET2);
        $this->loginAs('vet');

        $this->get('auth/logout');

        $this->assertSame(2, (int) $this->find('users', Fixtures::USER_VET2)['current_location']);
    }

    public function testLogoutChangesStateOnAPlainGetRequest(): void
    {
        // no confirmation and no token: any page can log a user out with an image tag
        $this->loginAs('vet');

        $this->assertStatus($this->get('auth/logout'), 200);
        $this->assertRedirectsTo($this->get('/'), 'auth/login');
    }

    public function testTheUserCanLogInAgainAfterLoggingOut(): void
    {
        $this->loginAs('vet');
        $this->get('auth/logout');

        $this->loginAs('vet');

        $this->assertStatus($this->get('/'), 200);
    }
}
