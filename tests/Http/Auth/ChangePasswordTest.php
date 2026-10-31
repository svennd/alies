<?php

declare(strict_types=1);

/**
 * T040 - Vet::change_password. Uses the second vet (user 6) so the shared vet keeps working; the
 * password hash is put back after every test.
 *
 * Rules: old required; new 12-40 characters, must match new_confirm. After a successful change the
 * user is logged out. Source: application/controllers/Vet.php, Ion_auth_model::change_password.
 */
final class ChangePasswordTest extends HttpTestCase
{
    private const EMAIL = 'vet2@test.local';
    private const NEW_PASSWORD = 'AnotherPassword456!';

    private string $originalHash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalHash = (string) $this->find('users', Fixtures::USER_VET2)['password'];
        $this->db()->exec('DELETE FROM login_attempts');
        $this->loginAs('vet2');
    }

    protected function tearDown(): void
    {
        $this->db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$this->originalHash, Fixtures::USER_VET2]);
        parent::tearDown();
    }

    private function storedHash(): string
    {
        return (string) $this->find('users', Fixtures::USER_VET2)['password'];
    }

    private function assertStillLoggedIn(): void
    {
        $this->assertStatus($this->get('/'), 200);
    }

    public function testTheFormShowsThreeFieldsAndTheMinimumLength(): void
    {
        $page = $this->get('vet/change_password');

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        foreach (['old', 'new', 'new_confirm'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $body, $field);
        }
        $this->assertStringContainsString('type="password"', $body);
        $this->assertStringContainsString('12', $body, 'the minimum length is mentioned');
    }

    public function testAnonymousVisitorsAreSentToTheLogin(): void
    {
        $this->cookies->clear();

        $this->assertRedirectsTo($this->get('vet/change_password'), 'auth/login');
        $this->assertRedirectsTo($this->post('vet/change_password', ['old' => 'x', 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD]), 'auth/login');
    }

    /** @return array<string, array{array<string, string>}> */
    public static function invalidForms(): array
    {
        $ok = 'AnotherPassword456!';

        return [
            'confirmation does not match' => [['old' => Fixtures::PASSWORD, 'new' => $ok, 'new_confirm' => $ok . 'x']],
            'new is one character short' => [['old' => Fixtures::PASSWORD, 'new' => str_repeat('a', 11), 'new_confirm' => str_repeat('a', 11)]],
            'new is one character too long' => [['old' => Fixtures::PASSWORD, 'new' => str_repeat('a', 41), 'new_confirm' => str_repeat('a', 41)]],
            'old is missing' => [['old' => '', 'new' => $ok, 'new_confirm' => $ok]],
            'new is missing' => [['old' => Fixtures::PASSWORD, 'new' => '', 'new_confirm' => '']],
            'confirmation is missing' => [['old' => Fixtures::PASSWORD, 'new' => $ok, 'new_confirm' => '']],
            'nothing at all' => [[]],
        ];
    }

    /** @param array<string, string> $form */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidForms')]
    public function testAnInvalidFormShowsThePageAgainAndChangesNothing(array $form): void
    {
        $response = $this->post('vet/change_password', $form);

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="new_confirm"', (string) $response->getBody());
        $this->assertSame($this->originalHash, $this->storedHash());
        $this->assertStillLoggedIn();
    }

    public function testTheLimitsThemselvesAreAllowed(): void
    {
        foreach ([12, 40] as $length) {
            $password = str_repeat('p', $length);
            $this->assertRedirectsTo(
                $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => $password, 'new_confirm' => $password]),
                'auth/login',
                "length $length"
            );
            $this->db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$this->originalHash, Fixtures::USER_VET2]);
            $this->cookies->clear();
            $this->loginAs('vet2');
        }
    }

    public function testAWrongOldPasswordGoesBackToTheFormWithoutChangingAnything(): void
    {
        $response = $this->post('vet/change_password', ['old' => 'not-my-password-1', 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD]);

        $this->assertRedirectsTo($response, 'vet/change_password');
        $this->assertSame($this->originalHash, $this->storedHash());
        $this->assertStillLoggedIn();
    }

    public function testAValidChangeLogsTheUserOutAndTheNewPasswordWorks(): void
    {
        $response = $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD]);

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertNotSame($this->originalHash, $this->storedHash());
        $this->assertRedirectsTo($this->get('/'), 'auth/login', 'the session ended');

        // the old password no longer works, the new one does
        $this->assertRedirectsTo($this->attemptLogin(self::EMAIL, Fixtures::PASSWORD), 'auth/login');
        $this->assertRedirectsTo($this->attemptLogin(self::EMAIL, self::NEW_PASSWORD), '');
    }

    public function testThePasswordIsStoredAsABcryptHashAndNeverInPlainText(): void
    {
        $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD]);

        $hash = $this->storedHash();
        $this->assertStringNotContainsString(self::NEW_PASSWORD, $hash);
        $info = password_get_info($hash);
        $this->assertSame('bcrypt', $info['algoName']);
        $this->assertSame(10, $info['options']['cost'], 'cost for a normal user (admins get 12)');
        $this->assertTrue(password_verify(self::NEW_PASSWORD, $hash));
    }

    public function testChangingToTheSamePasswordIsAllowed(): void
    {
        $response = $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => Fixtures::PASSWORD, 'new_confirm' => Fixtures::PASSWORD]);

        $this->assertRedirectsTo($response, 'auth/login');
    }

    public function testOnlyTheOwnPasswordIsChanged(): void
    {
        $other = $this->find('users', Fixtures::USER_VET)['password'];

        $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD, 'user_id' => Fixtures::USER_VET]);

        $this->assertSame($other, $this->find('users', Fixtures::USER_VET)['password'], 'a posted user_id is ignored');
    }

    public function testAnAdminChangeUsesTheHigherCostForAdmins(): void
    {
        $adminHash = (string) $this->find('users', Fixtures::USER_ADMIN)['password'];
        $this->cookies->clear();
        $this->loginAs('admin');
        try {
            $this->post('vet/change_password', ['old' => Fixtures::PASSWORD, 'new' => self::NEW_PASSWORD, 'new_confirm' => self::NEW_PASSWORD]);

            $info = password_get_info((string) $this->find('users', Fixtures::USER_ADMIN)['password']);
            $this->assertSame(12, $info['options']['cost']);
        } finally {
            $this->db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$adminHash, Fixtures::USER_ADMIN]);
        }
    }
}
