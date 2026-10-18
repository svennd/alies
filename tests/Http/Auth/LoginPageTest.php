<?php

declare(strict_types=1);

/**
 * T030 - Auth::login (GET): the login page and the location suggestion cookie.
 * Source: application/controllers/Auth.php (login, set_login_suggestion).
 */
final class LoginPageTest extends HttpTestCase
{
    public function testLoginPageShowsTheIdentityAndPasswordFields(): void
    {
        $response = $this->get('auth/login');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('name="identity"', $body);
        $this->assertStringContainsString('name="password"', $body);
        $this->assertStringContainsString('type="password"', $body);
        $this->assertStringContainsString('<form', $body);
    }

    public function testLoginPageHasItsOwnLayoutWithoutTheApplicationHeaderAndFooter(): void
    {
        $response = $this->get('auth/login');

        // markers of the normal header/footer (menu entries, logout dialog)
        $this->assertDontSee($response, 'Plakbriefjes');
        $this->assertDontSee($response, 'Uitloggen');
        $this->assertDontSee($response, 'Ready to Leave?');
    }

    public function testALocationInTheUrlSetsASuggestionCookieForFiveMinutes(): void
    {
        $this->get('auth/login/2');

        $cookie = $this->cookies->getCookieByName('alies_location');
        $this->assertNotNull($cookie, 'cookie alies_location was not set');
        $this->assertSame('2', $cookie->getValue());
        $this->assertGreaterThan(time() + 240, $cookie->getExpires());
        $this->assertLessThanOrEqual(time() + 310, $cookie->getExpires());
    }

    public function testWithoutALocationNoSuggestionCookieIsSet(): void
    {
        $this->get('auth/login');
        $this->get('auth/login/-1');

        $this->assertNull($this->cookies->getCookieByName('alies_location'));
    }

    public function testTheLastSuggestionWins(): void
    {
        $this->get('auth/login/1');
        $this->get('auth/login/2');

        $this->assertSame('2', $this->cookies->getCookieByName('alies_location')->getValue());
    }

    public function testASuggestionThatIsNotANumberIsAServerError(): void
    {
        // the method takes an int; the string "abc" is a TypeError
        $this->assertStatus($this->get('auth/login/abc'), 500);
    }

    public function testTheLoginPageIsStillShownToSomeoneWhoIsLoggedIn(): void
    {
        // there is no "already logged in" redirect
        $this->loginAs('vet');

        $response = $this->get('auth/login');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="identity"', (string) $response->getBody());
    }

    public function testSubmittingNothingShowsThePageAgainWithoutLoggingAnything(): void
    {
        $before = $this->countWhere('log', ['event' => 'login_failed']);

        $response = $this->post('auth/login', ['identity' => '', 'password' => '']);

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="identity"', (string) $response->getBody());
        $this->assertSame($before, $this->countWhere('log', ['event' => 'login_failed']), 'validation stops before the login attempt');
    }
}
