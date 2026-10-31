<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T093 + T094 - the home page (Welcome::index) and the help pages (Help).
 *
 * Home: the search form, the sidebar counters computed in Vet_Controller (stickies, open reports of
 * the vet, stock errors for admins, pending lab items) and the migration run on every hit. The
 * page does not print the user's name. Expected counters come from the database, not from the page.
 */
final class HomeAndHelpTest extends HttpTestCase
{
    /** The number printed in the badge of a sidebar item, or null when there is none. */
    private function counter($page, string $itemId): ?int
    {
        if (preg_match('#id="' . $itemId . '">((?:(?!</li>).)*)</li>#s', (string) $page->getBody(), $item) !== 1) {
            return null;
        }

        return preg_match('#class="counter"[^>]*>\s*(\d+)\s*<#', $item[1], $number) === 1 ? (int) $number[1] : null;
    }

    public function testTheHomePageShowsTheSearchFormAndALogoutLink(): void
    {
        $this->loginAs('vet');

        $page = $this->get('');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('name="search_query"', (string) $page->getBody());
        $this->assertStringContainsString('action="' . rtrim((string) $this->client->getConfig('base_uri'), '/') . '/search"', (string) $page->getBody());
        $this->assertStringContainsString('auth/logout', (string) $page->getBody());
        $this->assertSee($page, 'Uitloggen');
    }

    public function testTheHomePageDoesNotPrintTheUsersName(): void
    {
        $this->loginAs('vet');

        $page = $this->get('');

        $this->assertDontSee($page, 'Victor');
        $this->assertDontSee($page, 'Victor Vet');
        // the public profile does
        $this->assertSee($this->get('vet/pub/' . Fixtures::USER_VET), 'Victor');
    }

    public function testTheSidebarCountersMatchTheDatabase(): void
    {
        $this->loginAs('vet');
        $reports = (int) $this->scalar(
            "SELECT COUNT(*) FROM events WHERE vet = ? AND no_history = 0 AND report != 2 AND updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [Fixtures::USER_VET]
        );
        $stickies = (int) $this->scalar('SELECT COUNT(*) FROM sticky WHERE deleted_at IS NULL');
        $this->assertSame(2, $reports, 'seed: events 6001 and 6003');
        $this->assertSame(1, $stickies, 'seed: one sticky');

        $page = $this->get('');

        $this->assertSame($reports, $this->counter($page, 'reports'));
        $this->assertSame($stickies, $this->counter($page, 'sticky'));
        $this->assertMatchesRegularExpression('#(\d+)\s*Verslagen#', $this->excerptAll($page));
        $this->assertNull($this->counter($page, 'labo'), 'no pending lab items in the seed: no counter');
    }

    public function testTheCountersFollowNewData(): void
    {
        $this->loginAs('vet');
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        // open report of this vet (counts), one of another vet, one finished (report 2) and one without history (do not count)
        Factory::event($pet, 0, 0, ['vet' => Fixtures::USER_VET, 'report' => 0]);
        Factory::event($pet, 0, 0, ['vet' => Fixtures::USER_VET2, 'report' => 0]);
        Factory::event($pet, 0, 0, ['vet' => Fixtures::USER_VET, 'report' => 2]);
        Factory::event($pet, 0, 0, ['vet' => Fixtures::USER_VET, 'report' => 0, 'no_history' => 1]);
        Factory::event($pet, 0, 0, ['vet' => Fixtures::USER_VET, 'report' => 0, 'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days'))]);
        Factory::insert('sticky', ['user_id' => 2, 'location' => 1, 'note' => 'extra', 'private' => 0, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        Factory::insert('sticky', ['user_id' => 2, 'location' => 1, 'note' => 'gone', 'private' => 0, 'deleted_at' => Factory::raw('NOW()')]);
        foreach ([[null], [null, true], ['x']] as $i => $_) {
            Factory::insert('lab_report_pending', [
                'identity_hash' => hash('sha256', 'home-test-' . $i . random_int(1, 999999)),
                'created_at' => Factory::raw('NOW()'),
                'resolved_at' => $i === 2 ? Factory::raw('NOW()') : null,
                'deleted_at' => $i === 1 ? Factory::raw('NOW()') : null,
            ]);
        }

        $page = $this->get('');

        $this->assertSame(3, $this->counter($page, 'reports'), 'seed 2 + 1 new open report of this vet');
        $this->assertSame(2, $this->counter($page, 'sticky'), 'a deleted sticky does not count');
        $this->assertSame(1, $this->counter($page, 'labo'), 'resolved and deleted pending items do not count');
    }

    public function testAnAdminSeesTheNumberOfStockErrors(): void
    {
        $this->loginAs('admin');
        $errors = (int) $this->scalar('SELECT COUNT(*) FROM stock WHERE state = 3');
        $this->assertSame(1, $errors, 'seed: stock 5007');

        $page = $this->get('');

        $this->assertStatus($page, 200);
        $this->assertSame($errors, $this->counter($page, 'admin'));
    }

    public function testEveryVisitRunsTheMigrationsWithoutChangingTheVersion(): void
    {
        $this->loginAs('vet');
        $before = $this->scalar('SELECT MAX(version) FROM migrations');

        $this->get('');
        $this->get('');

        $this->assertSame($before, $this->scalar('SELECT MAX(version) FROM migrations'));
        $this->assertSame(1, (int) $this->scalar('SELECT COUNT(*) FROM migrations'));
    }

    public function testTheHomePageIsClosedToUsersWithoutAStaffGroup(): void
    {
        $this->loginAs('member');

        $this->assertStatus($this->get(''), 500);
    }

    public function testTheVetHelpPageIsOpenToVets(): void
    {
        $this->loginAs('vet');

        $page = $this->get('help/vet');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Help / Dierenarts');
    }

    // KNOWN BUG: a vet is redirected to `vet/dashboard`, a page that does not exist (404) - decide in CI4
    #[Group('known-bug')]
    public function testTheAdminHelpPageIsForAdminsOnly(): void
    {
        $this->loginAs('admin');
        $admin = $this->get('help/admin');
        $this->assertStatus($admin, 200);
        $this->assertSee($admin, 'Help');
        $this->assertSee($admin, 'Admin');

        $this->cookies->clear();
        $this->loginAs('vet');
        $vet = $this->get('help/admin');
        $this->assertRedirectsTo($vet, 'vet/dashboard');
        $this->assertStatus($this->followRedirect($vet), 404);
    }

    private function excerptAll($page): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($this->text($page))));
    }
}
