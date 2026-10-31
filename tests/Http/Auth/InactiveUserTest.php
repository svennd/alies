<?php

declare(strict_types=1);

/**
 * T034 - a user with active = 0 cannot log in (seed user 7, inactive@test.local).
 * Source: Ion_auth_model::login.
 */
final class InactiveUserTest extends HttpTestCase
{
    private const IDENTITY = 'inactive@test.local';

    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('DELETE FROM login_attempts');
        $this->db()->exec('DELETE FROM log');
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . Fixtures::USER_INACTIVE);
    }

    public function testTheRightPasswordOfAnInactiveUserIsRefused(): void
    {
        $response = $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertRedirectsTo($this->get('/'), 'auth/login', 'still not logged in');
        $this->assertRowMissing('log', ['event' => 'login_success']);
        $this->assertRowExists('log', ['event' => 'login_failed', 'msg' => 'user : inactive@test.local']);
        $this->assertNull($this->find('users', Fixtures::USER_INACTIVE)['last_login']);
    }

    public function testAnInactiveUserIsNotLockedOutByTryingTheRightPassword(): void
    {
        // the "not active" refusal does not count as a failed attempt, so it can be repeated forever
        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);
        }

        $this->assertRowCount(0, 'login_attempts', ['login' => self::IDENTITY]);
    }

    public function testAWrongPasswordOfAnInactiveUserCountsAsAnAttemptLikeAnyOther(): void
    {
        $this->attemptLogin(self::IDENTITY, 'wrong-password-123');

        $this->assertRowCount(1, 'login_attempts', ['login' => self::IDENTITY]);
    }

    public function testOnceActivatedTheSameUserCanLogIn(): void
    {
        $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);
        $this->db()->exec('UPDATE users SET active = 1 WHERE id = ' . Fixtures::USER_INACTIVE);

        $response = $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);

        $this->assertRedirectsTo($response, '');
        $this->assertStatus($this->get('/'), 200);
    }

    public function testDeactivatingALoggedInUserDoesNotEndTheirSession(): void
    {
        // quirk: the active flag is only checked at login, not on every request
        $this->loginAs('vet2');

        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . Fixtures::USER_VET2);

        $this->assertStatus($this->get('/'), 200);
    }
}
