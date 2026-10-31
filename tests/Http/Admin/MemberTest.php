<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T305 - Member::index, create_user, edit_user (admin only).
 *
 * Rules: create_user registers through Ion_auth (default group "members" always, then the posted
 * groups replace it); a user without posted groups stays in "members" only and cannot use the staff
 * screens (500). edit_user updates names, phone, order_nr and active (unchecked = inactive), changes
 * the password only when it is filled in and confirmed, and replaces the groups only when some are
 * posted. The list shows active users, `?show_inactive=1` adds the inactive ones.
 *
 * Every test creates its own users (unique e-mail); the seeded users are never changed.
 */
final class MemberTest extends HttpTestCase
{
    private const PASSWORD = 'LongEnoughPass1';

    /** The complete create form, as the browser posts it. */
    private function createForm(string $email, array $over = []): array
    {
        return $over + [
            'submit' => 'create_user',
            'email' => $email,
            'password' => self::PASSWORD,
            'password_confirm' => self::PASSWORD,
            'first_name' => 'Nora',
            'last_name' => 'Newuser',
            'phone' => '0470111222',
            'order_nr' => '654321',
        ];
    }

    private function uniqueEmail(): string
    {
        return strtolower(Factory::unique('member')) . '@test.local';
    }

    /** Creates a user through the form (as the logged in admin) and returns its id. */
    private function createUser(array $over = [], ?string &$email = null): int
    {
        $email ??= $this->uniqueEmail();
        $this->post('member/create_user', $this->createForm($email, $over));
        $id = $this->scalar('SELECT id FROM users WHERE email = ?', [$email]);
        $this->assertNotFalse($id, 'the user was created');

        return (int) $id;
    }

    /** @return list<int> */
    private function groupIds(int $userId): array
    {
        return array_map('intval', array_column($this->rows('SELECT group_id FROM users_groups WHERE user_id = ? ORDER BY group_id', [$userId]), 'group_id'));
    }

    private function editForm(array $over = []): array
    {
        return $over + [
            'submit' => 'edit_user',
            'first_name' => 'Nora',
            'last_name' => 'Newuser',
            'phone' => '0470111222',
            'order_nr' => '654321',
            'password' => '',
            'password_confirm' => '',
            'active' => '1',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    // ---- create ------------------------------------------------------------------------------

    public function testTheCreateFormListsTheGroups(): void
    {
        $page = $this->get('member/create_user');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('name="groups[]"', (string) $page->getBody());
        foreach ([Fixtures::GROUP_ADMIN, Fixtures::GROUP_MEMBERS, Fixtures::GROUP_VET, Fixtures::GROUP_ACCOUNTING] as $id) {
            $this->assertStringContainsString('name="groups[]" id="' . ['', 'admin', 'members', 'vet', 'accounting'][$id] . '" value="' . $id . '"', (string) $page->getBody());
        }
    }

    public function testCreatingAUserStoresTheDetailsAndPutsThemInTheChosenGroupOnly(): void
    {
        $email = $this->uniqueEmail();
        $logBefore = $this->logCount('new_user');

        $page = $this->post('member/create_user', $this->createForm($email) + ['groups' => [Fixtures::GROUP_VET]]);

        $this->assertStatus($page, 200);
        $user = $this->row('SELECT * FROM users WHERE email = ?', [$email]);
        $this->assertNotNull($user);
        $this->assertSame('Nora', $user['first_name']);
        $this->assertSame('Newuser', $user['last_name']);
        $this->assertSame('0470111222', $user['phone']);
        $this->assertSame('654321', $user['order_nr']);
        $this->assertSame(1, (int) $user['active']);
        $this->assertNotSame(self::PASSWORD, $user['password'], 'the password is hashed');
        $this->assertSame([Fixtures::GROUP_VET], $this->groupIds((int) $user['id']), 'vet only, not members');
        $this->assertSame(1, $this->logCount('new_user') - $logBefore, 'one log row');
    }

    #[Group('known-bug')]
    public function testTheUsernameColumnIsLeftEmptyOnCreate(): void
    {
        // KNOWN BUG: the controller builds "First Last" but Ion_auth uses its first argument as the
        // login identity (the e-mail column), so users.username stays '' until the user is edited.
        $id = $this->createUser();

        $this->assertSame('', $this->find('users', $id)['username']);
    }

    public function testACreatedVetCanLogInAndOpenTheHomePage(): void
    {
        $email = $this->uniqueEmail();
        $this->createUser(['groups' => [Fixtures::GROUP_VET]], $email);

        $this->cookies->clear();
        $login = $this->attemptLogin($email, self::PASSWORD);
        $this->assertRedirectsTo($login, '');
        $this->assertRedirectsTo($this->get('welcome/change_location/' . Fixtures::LOCATION_PRACTICE), '');
        $home = $this->get('/');

        $this->assertStatus($home, 200);
    }

    public function testSeveralGroupsCanBeChosen(): void
    {
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET, Fixtures::GROUP_ACCOUNTING]]);

        $this->assertSame([Fixtures::GROUP_VET, Fixtures::GROUP_ACCOUNTING], $this->groupIds($id));
    }

    public function testAUserCreatedWithoutGroupsIsOnlyAMemberAndGetsAnErrorOnTheHomePage(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser([], $email);
        $this->assertSame([Fixtures::GROUP_MEMBERS], $this->groupIds($id));

        $this->cookies->clear();
        $this->assertRedirectsTo($this->attemptLogin($email, self::PASSWORD), '', 'login itself works');
        $home = $this->get('/');

        // KNOWN BUG: the form lets you create a user nobody can use; staff screens answer 500
        $this->assertStatus($home, 500);
        $this->assertSee($home, 'Not part of the correct group');
    }

    public function testMismatchedPasswordsShowAWarningAndCreateNothing(): void
    {
        $email = $this->uniqueEmail();

        $page = $this->post('member/create_user', $this->createForm($email, ['password_confirm' => 'SomethingElse123']));

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'email or password not correct;');
        $this->assertRowMissing('users', ['email' => $email]);
    }

    public function testAnEmptyEmailOrPasswordShowsTheSameWarning(): void
    {
        $before = $this->countWhere('users');

        $noMail = $this->post('member/create_user', $this->createForm('', []));
        $noPass = $this->post('member/create_user', $this->createForm($this->uniqueEmail(), ['password' => '', 'password_confirm' => '']));

        $this->assertSee($noMail, 'email or password not correct;');
        $this->assertSee($noPass, 'email or password not correct;');
        $this->assertSame($before, $this->countWhere('users'));
    }

    #[Group('known-bug')]
    public function testADuplicateEmailCreatesASecondUser(): void
    {
        // KNOWN BUG: Ion_auth checks "identity exists" against the first argument ("First Last"), not
        // the e-mail, so the same address can be registered twice - decide in CI4 whether to keep
        $email = $this->uniqueEmail();
        $this->createUser([], $email);

        $this->post('member/create_user', $this->createForm($email));

        $this->assertSame(2, $this->countWhere('users', ['email' => $email]));
    }

    public function testAShortPasswordIsNotCheckedByTheCreateForm(): void
    {
        // the 12-40 character rule is not enforced here; the account is created with the short password
        $email = $this->uniqueEmail();

        $this->post('member/create_user', $this->createForm($email, ['password' => 'short', 'password_confirm' => 'short']));

        $created = $this->countWhere('users', ['email' => $email]);
        $this->cookies->clear();
        $login = $this->attemptLogin($email, 'short');
        $this->assertSame(1, $created);
        $this->assertRedirectsTo($login, '');
    }

    public function testAPartialFormIsADatabaseError(): void
    {
        // order_nr is NOT NULL and the controller passes the missing field on as NULL
        $email = $this->uniqueEmail();

        $response = $this->post('member/create_user', ['submit' => 'create_user', 'email' => $email, 'password' => self::PASSWORD, 'password_confirm' => self::PASSWORD]);

        $this->assertStatus($response, 500);
        $this->assertRowMissing('users', ['email' => $email]);
    }

    // ---- edit --------------------------------------------------------------------------------

    public function testTheEditFormShowsTheUserAndTheirGroups(): void
    {
        $id = $this->createUser(['groups' => [Fixtures::GROUP_ACCOUNTING], 'first_name' => 'Zora']);

        $page = $this->get('member/edit_user/' . $id);

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="Zora"', (string) $page->getBody());
        $this->assertStringContainsString('value="654321"', (string) $page->getBody());
        $this->assertMatchesRegularExpression('/id="accounting"\s+value="4"\s+checked/', (string) $page->getBody());
    }

    public function testEditingChangesTheDetailsAndSetsTheUsername(): void
    {
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]]);

        $page = $this->post('member/edit_user/' . $id, $this->editForm(['first_name' => 'Nina', 'last_name' => 'Nieuw', 'phone' => '0499000111', 'order_nr' => '111222']));

        $this->assertStatus($page, 200);
        $user = $this->find('users', $id);
        $this->assertSame('Nina', $user['first_name']);
        $this->assertSame('Nieuw', $user['last_name']);
        $this->assertSame('0499000111', $user['phone']);
        $this->assertSame('111222', $user['order_nr']);
        $this->assertSame('Nina Nieuw', $user['username']);
        $this->assertSame(1, (int) $user['active']);
    }

    public function testTheEmailAddressCannotBeChanged(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser([], $email);

        $this->post('member/edit_user/' . $id, $this->editForm(['email' => 'other@test.local']));

        $this->assertSame($email, $this->find('users', $id)['email']);
    }

    public function testAnUncheckedActiveBoxDeactivatesAndThePasswordStaysWhenLeftEmpty(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]], $email);
        $hash = $this->find('users', $id)['password'];
        $form = $this->editForm();
        unset($form['active']);

        $this->post('member/edit_user/' . $id, $form);

        $this->assertSame(0, (int) $this->find('users', $id)['active']);
        $this->assertSame($hash, $this->find('users', $id)['password'], 'empty password = unchanged');
        $this->cookies->clear();
        $login = $this->attemptLogin($email, self::PASSWORD);
        $this->assertRedirectsTo($login, 'auth/login', 'an inactive user cannot log in');

        // positive control: ticking it again lets them in with the old password
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('member/edit_user/' . $id, $this->editForm());
        $this->cookies->clear();
        $this->assertRedirectsTo($this->attemptLogin($email, self::PASSWORD), '');
    }

    public function testAConfirmedNewPasswordReplacesTheOldOne(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]], $email);

        $this->post('member/edit_user/' . $id, $this->editForm(['password' => 'AnotherLongPass99', 'password_confirm' => 'AnotherLongPass99']));

        $this->cookies->clear();
        $this->assertRedirectsTo($this->attemptLogin($email, self::PASSWORD), 'auth/login', 'old password refused');
        $this->cookies->clear();
        $this->assertRedirectsTo($this->attemptLogin($email, 'AnotherLongPass99'), '');
    }

    public function testAMismatchedNewPasswordIsIgnoredWhileTheRestIsSaved(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]], $email);
        $hash = $this->find('users', $id)['password'];

        $this->post('member/edit_user/' . $id, $this->editForm(['first_name' => 'Changed', 'password' => 'AnotherLongPass99', 'password_confirm' => 'different']));

        $this->assertSame($hash, $this->find('users', $id)['password']);
        $this->assertSame('Changed', $this->find('users', $id)['first_name']);
    }

    public function testPostedGroupsReplaceTheOldOnesAndNoGroupsLeavesThemAlone(): void
    {
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]]);

        $this->post('member/edit_user/' . $id, $this->editForm(['groups' => [Fixtures::GROUP_ACCOUNTING, Fixtures::GROUP_MEMBERS]]));
        $this->assertSame([Fixtures::GROUP_MEMBERS, Fixtures::GROUP_ACCOUNTING], $this->groupIds($id));

        $this->post('member/edit_user/' . $id, $this->editForm());
        $this->assertSame([Fixtures::GROUP_MEMBERS, Fixtures::GROUP_ACCOUNTING], $this->groupIds($id), 'nothing posted = unchanged');
    }

    public function testEditingWithoutTheSubmitValueChangesNothing(): void
    {
        $id = $this->createUser();

        $this->post('member/edit_user/' . $id, ['first_name' => 'Hacked', 'active' => '0']);

        $this->assertSame('Nora', $this->find('users', $id)['first_name']);
        $this->assertSame(1, (int) $this->find('users', $id)['active']);
    }

    public function testAnUnknownUserSendsYouBackToTheList(): void
    {
        $response = $this->get('member/edit_user/99999');

        $this->assertRedirectsTo($response, 'member');
    }

    #[Group('known-bug')]
    public function testAnAdminCanDeactivateHimself(): void
    {
        // KNOWN BUG: nothing stops the logged in admin from switching off his own account (own
        // throw-away admin here, the seeded admin is not touched)
        $email = $this->uniqueEmail();
        $id = $this->createUser(['groups' => [Fixtures::GROUP_ADMIN]], $email);
        $this->cookies->clear();
        $this->assertRedirectsTo($this->attemptLogin($email, self::PASSWORD), '');
        $this->get('welcome/change_location/' . Fixtures::LOCATION_PRACTICE);
        $form = $this->editForm();
        unset($form['active']);

        $this->post('member/edit_user/' . $id, $form);

        $this->assertSame(0, (int) $this->find('users', $id)['active']);
    }

    // ---- list --------------------------------------------------------------------------------

    public function testTheListShowsActiveUsersWithTheirGroupsAndHidesInactiveOnes(): void
    {
        $active = $this->createUser(['groups' => [Fixtures::GROUP_ACCOUNTING], 'last_name' => 'Zichtbaar']);
        $off = $this->createUser(['last_name' => 'Verborgen']);
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . $off);

        $page = $this->get('member');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Zichtbaar');
        $this->assertSee($page, 'admin@test.local');
        $this->assertDontSee($page, 'Verborgen');
        $this->assertDontSee($page, 'inactive@test.local');
        $this->assertStringContainsString('member/edit_user/' . $active, (string) $page->getBody());
    }

    public function testShowInactiveAddsTheInactiveUsers(): void
    {
        $off = $this->createUser(['last_name' => 'Verborgen']);
        $this->db()->exec('UPDATE users SET active = 0 WHERE id = ' . $off);

        $page = $this->get('member', ['show_inactive' => 1]);

        $this->assertSee($page, 'Verborgen');
        $this->assertSee($page, 'inactive@test.local');
        $this->assertSee($page, 'Inactive');
    }

    // ---- access ------------------------------------------------------------------------------

    public function testOnlyAdminsMayManageMembers(): void
    {
        $email = $this->uniqueEmail();
        $id = $this->createUser(['groups' => [Fixtures::GROUP_VET]]);
        foreach (['vet', 'acc'] as $role) {
            $this->cookies->clear();
            $this->loginAs($role);
            $this->assertRedirectsTo($this->get('member'), '', $role);
            $this->assertRedirectsTo($this->post('member/create_user', $this->createForm($email)), '', $role);
            $this->assertRedirectsTo($this->post('member/edit_user/' . $id, $this->editForm(['first_name' => 'Hacked'])), '', $role);
        }

        $this->assertRowMissing('users', ['email' => $email]);
        $this->assertSame('Nora', $this->find('users', $id)['first_name']);
    }
}
