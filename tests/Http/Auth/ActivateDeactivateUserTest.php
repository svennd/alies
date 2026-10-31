<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T042 - Auth::deactivate (confirmation form with a one-time token) and Auth::activate.
 * Uses the second vet (user 6) as the user to deactivate; its state is restored after every test.
 * Both actions redirect to "auth", which does not exist (see GroupAdminTest).
 */
final class ActivateDeactivateUserTest extends HttpTestCase
{
    private const TARGET = Fixtures::USER_VET2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('UPDATE users SET active = 1 WHERE id = ' . self::TARGET);
    }

    private function isActive(): bool
    {
        return (int) $this->find('users', self::TARGET)['active'] === 1;
    }

    /** Opens the confirmation form and returns what a browser would post: id and the token pair. */
    private function openConfirmation(int $userId = self::TARGET): array
    {
        $page = $this->get('auth/deactivate/' . $userId);
        $this->assertStatus($page, 200);

        return $this->hiddenFields($page);
    }

    public function testTheConfirmationFormAsksYesOrNoAndCarriesAToken(): void
    {
        $this->loginAs('admin');

        $page = $this->get('auth/deactivate/' . self::TARGET);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Veerle Vet', 'the user to deactivate is named');
        $this->assertStringContainsString('name="confirm" value="yes"', (string) $page->getBody());
        $this->assertStringContainsString('name="confirm" value="no"', (string) $page->getBody());
        $fields = $this->hiddenFields($page);
        $this->assertSame((string) self::TARGET, $fields['id']);
        $this->assertCount(2, $fields, 'the id and one token field');
    }

    public function testConfirmingWithTheTokenDeactivatesTheUser(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation();

        $response = $this->post('auth/deactivate/' . self::TARGET, $fields + ['confirm' => 'yes']);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertFalse($this->isActive());
    }

    public function testAnsweringNoChangesNothing(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation();

        $response = $this->post('auth/deactivate/' . self::TARGET, $fields + ['confirm' => 'no']);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertTrue($this->isActive());
    }

    public function testWithoutTheTokenTheRequestIsRefused(): void
    {
        $this->loginAs('admin');
        $this->openConfirmation();

        $response = $this->post('auth/deactivate/' . self::TARGET, ['confirm' => 'yes', 'id' => self::TARGET]);

        $this->assertStatus($response, 500);
        $this->assertTrue($this->isActive());
    }

    public function testAWrongTokenValueIsRefused(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation();
        $key = array_key_first(array_diff_key($fields, ['id' => 1]));

        $response = $this->post('auth/deactivate/' . self::TARGET, ['confirm' => 'yes', 'id' => self::TARGET, $key => 'forged']);

        $this->assertStatus($response, 500);
        $this->assertTrue($this->isActive());
    }

    public function testTheTokenCannotBeUsedTwice(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation();
        $this->post('auth/deactivate/' . self::TARGET, $fields + ['confirm' => 'yes']);
        $this->db()->exec('UPDATE users SET active = 1 WHERE id = ' . self::TARGET);

        $replay = $this->post('auth/deactivate/' . self::TARGET, $fields + ['confirm' => 'yes']);

        $this->assertStatus($replay, 500);
        $this->assertTrue($this->isActive());
    }

    public function testTheIdInTheFormMustBeTheIdInTheUrl(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation();

        $response = $this->post('auth/deactivate/' . self::TARGET, ['id' => Fixtures::USER_VET, 'confirm' => 'yes'] + $fields);

        $this->assertStatus($response, 500);
        $this->assertTrue($this->isActive());
        $this->assertSame(1, (int) $this->find('users', Fixtures::USER_VET)['active']);
    }

    public function testAnAdminCannotDeactivateTheirOwnAccount(): void
    {
        $this->loginAs('admin');
        $fields = $this->openConfirmation(Fixtures::USER_ADMIN);

        $this->post('auth/deactivate/' . Fixtures::USER_ADMIN, $fields + ['confirm' => 'yes']);

        $this->assertSame(1, (int) $this->find('users', Fixtures::USER_ADMIN)['active']);
    }

    public function testAVetCanOpenTheFormButCannotDeactivateAnyone(): void
    {
        $this->loginAs('vet');
        $fields = $this->openConfirmation();

        $response = $this->post('auth/deactivate/' . self::TARGET, $fields + ['confirm' => 'yes']);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertTrue($this->isActive());
    }

    public function testAnAdminCanActivateAUserAgain(): void
    {
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . self::TARGET);
        $this->loginAs('admin');

        $response = $this->get('auth/activate/' . self::TARGET);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertTrue($this->isActive());
    }

    #[Group('known-bug')]
    public function testAVetTryingToActivateIsSentToAPageThatDoesNotExist(): void
    {
        // KNOWN BUG: without an activation code and without being admin the controller has no result
        // (undefined variable) and sends the user to auth/forgot_password, which does not exist
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . self::TARGET);
        $this->loginAs('vet');

        $response = $this->get('auth/activate/' . self::TARGET);

        $this->assertRedirectsTo($response, 'auth/forgot_password');
        $this->assertStatus($this->followRedirect($response), 404);
        $this->assertFalse($this->isActive());
    }

    #[Group('known-bug')]
    public function testBothRedirectsPointToAnAuthPageThatDoesNotExist(): void
    {
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . self::TARGET);
        $this->loginAs('admin');

        $response = $this->get('auth/activate/' . self::TARGET);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertStatus($this->followRedirect($response), 404);
    }

    public function testActivatingAnActiveUserIsTreatedAsAFailure(): void
    {
        // nothing to change, so the library reports failure and the user lands on the forgot-password route
        $this->loginAs('admin');

        $this->assertRedirectsTo($this->get('auth/activate/' . self::TARGET), 'auth/forgot_password');
        $this->assertTrue($this->isActive());
    }

    public function testActivatingChangesStateOnAPlainGetRequest(): void
    {
        // no token needed to activate: any link or image can switch an account on
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . self::TARGET);
        $this->loginAs('admin');

        $this->assertStatus($this->get('auth/activate/' . self::TARGET), 200);

        $this->assertTrue($this->isActive());
    }
}
