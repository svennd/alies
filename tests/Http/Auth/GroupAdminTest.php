<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T041 - Auth::create_group and Auth::edit_group (admin only, anyone else is sent to "auth").
 *
 * The built-in groups (admin, members, vet, accounting) are never edited here: the tests create
 * their own group.
 */
final class GroupAdminTest extends HttpTestCase
{
    private function insertGroup(string $name = 'testgroup', string $description = 'A test group'): int
    {
        return Factory::insert('groups', ['name' => $name, 'description' => $description]);
    }

    // ---- create_group -----------------------------------------------------------------------

    public function testTheCreateFormListsTheExistingGroups(): void
    {
        $this->loginAs('admin');

        $page = $this->get('auth/create_group');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('name="group_name"', (string) $page->getBody());
        $this->assertStringContainsString('name="description"', (string) $page->getBody());
        foreach (['admin', 'members', 'vet', 'accounting'] as $group) {
            $this->assertSee($page, $group);
        }
    }

    public function testAnAdminCanCreateAGroup(): void
    {
        $this->loginAs('admin');

        $response = $this->post('auth/create_group', ['group_name' => 'new_group', 'description' => 'Made by a test']);

        $this->assertRedirectsTo($response, 'auth');
        $group = $this->row("SELECT * FROM groups WHERE name = 'new_group'");
        $this->assertNotNull($group);
        $this->assertSame('Made by a test', $group['description']);
    }

    /** @return array<string, array{string}> */
    public static function badNames(): array
    {
        return ['spaces' => ['two words'], 'punctuation' => ['bad!name'], 'empty' => ['']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badNames')]
    public function testANameWithSpacesOrPunctuationIsRefused(string $name): void
    {
        $this->loginAs('admin');
        $before = $this->countWhere('groups');

        $response = $this->post('auth/create_group', ['group_name' => $name, 'description' => 'x']);

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="group_name"', (string) $response->getBody(), 'the form is shown again');
        $this->assertSame($before, $this->countWhere('groups'));
    }

    public function testNamesWithDashesAndUnderscoresAreAllowed(): void
    {
        $this->loginAs('admin');

        $this->post('auth/create_group', ['group_name' => 'dash-and_under', 'description' => '']);

        $this->assertRowExists('groups', ['name' => 'dash-and_under']);
    }

    #[Group('known-bug')]
    public function testANameLongerThanTwentyCharactersIsSilentlyCut(): void
    {
        // KNOWN BUG: groups.name is varchar(20); the form accepts more and the database quietly truncates it
        $this->loginAs('admin');

        $response = $this->post('auth/create_group', ['group_name' => 'with-dash_and_underscore', 'description' => '']);

        $this->assertRedirectsTo($response, 'auth', 'looks like a success');
        $this->assertRowExists('groups', ['name' => 'with-dash_and_unders']);
        $this->assertRowMissing('groups', ['name' => 'with-dash_and_underscore']);
    }

    #[Group('known-bug')]
    public function testCreatingAGroupThatAlreadyExistsShowsABlankPage(): void
    {
        // KNOWN BUG: validation passes, the library refuses the duplicate, and the controller then
        // renders nothing at all (no error message, no form)
        $this->loginAs('admin');
        $before = $this->countWhere('groups');

        $response = $this->post('auth/create_group', ['group_name' => 'vet', 'description' => 'duplicate']);

        $this->assertStatus($response, 200);
        $this->assertSame('', trim((string) $response->getBody()));
        $this->assertSame($before, $this->countWhere('groups'));
    }

    public function testAVetCannotCreateAGroup(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('groups');

        $response = $this->post('auth/create_group', ['group_name' => 'vet_made', 'description' => 'x']);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertSame($before, $this->countWhere('groups'));
    }

    public function testAnAnonymousVisitorCannotCreateAGroup(): void
    {
        $before = $this->countWhere('groups');

        $this->assertRedirectsTo($this->post('auth/create_group', ['group_name' => 'anon_made', 'description' => 'x']), 'auth');
        $this->assertSame($before, $this->countWhere('groups'));
    }

    #[Group('known-bug')]
    public function testTheAuthPageThatEveryRedirectPointsToDoesNotExist(): void
    {
        // KNOWN BUG: after creating, editing or refusing, the user is sent to "auth": there is no Auth::index
        $this->loginAs('admin');
        $response = $this->post('auth/create_group', ['group_name' => 'ends_in_404', 'description' => '']);

        $this->assertRedirectsTo($response, 'auth');
        $this->assertStatus($this->followRedirect($response), 404);
    }

    // ---- edit_group -------------------------------------------------------------------------

    public function testTheEditFormShowsTheCurrentNameAndDescription(): void
    {
        $id = $this->insertGroup('editable', 'Current description');
        $this->loginAs('admin');

        $page = $this->get('auth/edit_group/' . $id);

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="editable"', (string) $page->getBody());
        $this->assertStringContainsString('value="Current description"', (string) $page->getBody());
    }

    #[Group('known-bug')]
    public function testEditingAGroupNeverWorksBecauseTheXssCleanRuleIsGone(): void
    {
        // KNOWN BUG: the form is validated with the rule `xss_clean`, which this CodeIgniter version no
        // longer has. Every save, however valid, fails validation with "unspecified error (xss_clean)"
        // and the form is shown again. The intended behaviour: rename the group and redirect to "auth".
        $id = $this->insertGroup('before_edit', 'Old');
        $this->loginAs('admin');

        $response = $this->post('auth/edit_group/' . $id, ['group_name' => 'after_edit', 'group_description' => 'New description']);

        $this->assertStatus($response, 200);
        $this->assertNotRedirect($response);
        $this->assertSee($response, '(xss_clean)');
        $group = $this->find('groups', $id);
        $this->assertSame('before_edit', $group['name']);
        $this->assertSame('Old', $group['description']);
    }

    public function testAnInvalidNameLeavesTheGroupAndShowsTheFormAgain(): void
    {
        $id = $this->insertGroup('keep_me', 'Old');
        $this->loginAs('admin');

        $response = $this->post('auth/edit_group/' . $id, ['group_name' => 'not valid!', 'group_description' => 'x']);

        $this->assertStatus($response, 200);
        $this->assertSame('keep_me', $this->find('groups', $id)['name']);
    }

    public function testAVetCannotEditAGroup(): void
    {
        $id = $this->insertGroup('vet_proof', 'Old');
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->post('auth/edit_group/' . $id, ['group_name' => 'hacked', 'group_description' => 'x']), 'auth');
        $this->assertRedirectsTo($this->get('auth/edit_group/' . $id), 'auth');
        $this->assertSame('vet_proof', $this->find('groups', $id)['name']);
    }

    public function testAnAnonymousVisitorCannotEditAGroup(): void
    {
        $id = $this->insertGroup('anon_proof', 'Old');

        $this->assertRedirectsTo($this->post('auth/edit_group/' . $id, ['group_name' => 'hacked', 'group_description' => 'x']), 'auth');
        $this->assertSame('anon_proof', $this->find('groups', $id)['name']);
    }

    public function testEditingWithoutAGroupIdGoesBackToAuth(): void
    {
        $this->loginAs('admin');

        $this->assertRedirectsTo($this->get('auth/edit_group/0'), 'auth');
    }
}
