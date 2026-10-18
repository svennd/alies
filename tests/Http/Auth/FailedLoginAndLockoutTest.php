<?php

declare(strict_types=1);

/**
 * T032 - failed login and lockout.
 * Source: Auth::login, Ion_auth_model::login / increase_login_attempts / is_max_login_attempts_exceeded.
 *
 * Settings (application/config/ion_auth.php): lockout after 3 failed attempts within 600 seconds,
 * counted per identity AND ip address. While locked out even the right password is refused.
 */
final class FailedLoginAndLockoutTest extends HttpTestCase
{
    private const IDENTITY = 'vet@test.local';

    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('DELETE FROM login_attempts');
        $this->db()->exec('DELETE FROM log');
    }

    private function failLogin(string $identity = self::IDENTITY): void
    {
        $this->assertRedirectsTo($this->attemptLogin($identity, 'wrong-password-123'), 'auth/login');
    }

    private function attempts(string $identity = self::IDENTITY): int
    {
        return $this->countWhere('login_attempts', ['login' => $identity]);
    }

    private function assertLoggedOut(): void
    {
        $this->assertRedirectsTo($this->get('/'), 'auth/login');
    }

    public function testAWrongPasswordGoesBackToTheLoginPageAndIsLogged(): void
    {
        $response = $this->attemptLogin(self::IDENTITY, 'wrong-password-123');

        $this->assertRedirectsTo($response, 'auth/login');
        $log = $this->row("SELECT * FROM log WHERE event = 'login_failed'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level'], 'INFO level');
        $this->assertSame('user : vet@test.local', $log['msg']);
        $this->assertRowMissing('log', ['event' => 'login_success']);
        $this->assertLoggedOut();
    }

    public function testEveryFailureIsRememberedWithIdentityAndAddress(): void
    {
        $this->failLogin();

        $attempt = $this->row('SELECT * FROM login_attempts');
        $this->assertSame(self::IDENTITY, $attempt['login']);
        $this->assertSame('127.0.0.1', $attempt['ip_address']);
        $this->assertEqualsWithDelta(time(), (int) $attempt['time'], 30);
        $this->assertSame(1, $this->attempts());
    }

    public function testAnUnknownIdentityFailsInTheSameWayAndIsCountedToo(): void
    {
        $this->failLogin('nobody@test.local');

        $this->assertSame(1, $this->attempts('nobody@test.local'));
        $this->assertRowExists('log', ['event' => 'login_failed', 'msg' => 'user : nobody@test.local']);
    }

    public function testThreeFailuresLockTheAccountEvenForTheRightPassword(): void
    {
        $this->failLogin();
        $this->failLogin();
        $this->failLogin();
        $this->assertSame(3, $this->attempts());

        $response = $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertLoggedOut();
        $this->assertRowMissing('log', ['event' => 'login_success']);
    }

    public function testTwoFailuresDoNotLockTheAccount(): void
    {
        $this->failLogin();
        $this->failLogin();

        $this->assertRedirectsTo($this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD), '');
        $this->assertStatus($this->get('/'), 200);
    }

    public function testFurtherAttemptsWhileLockedOutDoNotExtendTheLockout(): void
    {
        $this->failLogin();
        $this->failLogin();
        $this->failLogin();
        $this->failLogin();
        $this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD);

        // the lockout branch returns before another attempt is written
        $this->assertSame(3, $this->attempts());
    }

    public function testTheLockoutEndsTenMinutesAfterTheAttempts(): void
    {
        $this->failLogin();
        $this->failLogin();
        $this->failLogin();
        // pretend the three attempts happened 11 minutes ago
        $this->db()->exec('UPDATE login_attempts SET time = ' . (time() - 660));

        $this->assertRedirectsTo($this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD), '');
    }

    public function testASuccessfulLoginForgetsEarlierFailures(): void
    {
        $this->failLogin();
        $this->failLogin();
        $this->assertSame(2, $this->attempts());

        $this->assertRedirectsTo($this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD), '');

        $this->assertSame(0, $this->attempts());
    }

    public function testTheLockoutOfOneIdentityDoesNotBlockAnother(): void
    {
        $this->failLogin();
        $this->failLogin();
        $this->failLogin();

        $this->assertRedirectsTo($this->attemptLogin('admin@test.local', Fixtures::PASSWORD), '');
    }

    public function testFailuresFromAnotherAddressDoNotCount(): void
    {
        // attempts are tracked per identity and ip address: three failures from elsewhere
        $this->db()->exec("INSERT INTO login_attempts (ip_address, login, time) VALUES
            ('10.9.8.7', 'vet@test.local', " . time() . "), ('10.9.8.7', 'vet@test.local', " . time() . "), ('10.9.8.7', 'vet@test.local', " . time() . ')');

        $this->assertRedirectsTo($this->attemptLogin(self::IDENTITY, Fixtures::PASSWORD), '');
    }

    public function testAWrongPasswordIsNotRevealedAsWrongRatherThanUnknown(): void
    {
        // both go to the same page without any message that tells them apart
        $wrong = $this->attemptLogin(self::IDENTITY, 'wrong-password-123');
        $unknown = $this->attemptLogin('nobody@test.local', 'wrong-password-123');

        $this->assertSame($this->redirectTarget($wrong), $this->redirectTarget($unknown));
        $this->assertSame($wrong->getStatusCode(), $unknown->getStatusCode());
        $this->assertSame((string) $wrong->getBody(), (string) $unknown->getBody());
    }

    public function testNoMessageExplainsAFailedLogin(): void
    {
        // quirk: the controller sets no message, the user just sees the same empty form again
        $before = (string) $this->get('auth/login')->getBody();
        $this->failLogin();

        $after = (string) $this->get('auth/login')->getBody();

        $this->assertSame($before, $after);
    }
}
