<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

/**
 * T031 - successful login, then choosing a location.
 * Source: Auth::login, Welcome::change_location, Vet_Controller (location picker and location changer).
 *
 * After login the session has no location (0). The home page then shows a modal that links to
 * welcome/change_location/{id} for every location; after choosing, the header shows a dropdown with
 * the current location and links to the others.
 */
final class LoginAndLocationTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db()->exec('UPDATE users SET current_location = 0, last_login = NULL');
        $this->db()->exec('DELETE FROM login_attempts');
    }

    /**
     * Location links of the picker modal, id => button class (green = suggested, orange = other).
     *
     * @return array<int, string>
     */
    private function pickerLinks(ResponseInterface $response): array
    {
        preg_match_all('#class="btn (btn-outline-\w+)"\s+href="[^"]*welcome/change_location/(\d+)"#', (string) $response->getBody(), $m, PREG_SET_ORDER);

        return array_column(array_map(static fn (array $x): array => [(int) $x[2], $x[1]], $m), 1, 0);
    }

    private function login(string $email = 'vet@test.local'): ResponseInterface
    {
        return $this->attemptLogin($email, Fixtures::PASSWORD);
    }

    public function testLoginRedirectsToTheHomePage(): void
    {
        $this->assertRedirectsTo($this->login(), '');
    }

    public function testRightAfterLoginTheHomePageOffersEveryLocationButNotTheDeletedOne(): void
    {
        $this->login();

        $home = $this->get('/');

        $this->assertStatus($home, 200);
        $this->assertStringContainsString('id="staticBackdrop"', (string) $home->getBody());
        $this->assertSame([1, 2], array_keys($this->pickerLinks($home)));
        $this->assertSee($home, 'Praktijk');
        $this->assertSee($home, 'Wagen');
        $this->assertDontSee($home, 'Oude locatie');
    }

    public function testWithoutASuggestionEveryLocationIsHighlightedAsSuggested(): void
    {
        $this->login();

        $this->assertSame([1 => 'btn-outline-success', 2 => 'btn-outline-success'], $this->pickerLinks($this->get('/')));
    }

    public function testTheSuggestionCookieHighlightsTheSuggestedLocation(): void
    {
        $this->get('auth/login/2');
        $this->login();

        $this->assertSame([1 => 'btn-outline-warning', 2 => 'btn-outline-success'], $this->pickerLinks($this->get('/')));
    }

    public function testChoosingALocationStoresItAndReturnsToTheHomePage(): void
    {
        $this->login();

        $response = $this->get('welcome/change_location/2');

        $this->assertRedirectsTo($response, '');
        $this->assertSame(2, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
    }

    public function testAfterChoosingTheModalIsGoneAndTheHeaderOffersTheOtherLocation(): void
    {
        $this->login();
        $this->get('welcome/change_location/1');

        $home = $this->get('/');

        $this->assertStringNotContainsString('id="staticBackdrop"', (string) $home->getBody());
        $this->assertSee($home, 'Praktijk');
        // the dropdown links to the locations that are not the current one
        $this->assertStringContainsString('welcome/change_location/2', (string) $home->getBody());
        $this->assertStringNotContainsString('welcome/change_location/1', (string) $home->getBody());
    }

    public function testTheLocationCanBeChangedAgain(): void
    {
        $this->login();
        $this->get('welcome/change_location/1');
        $this->get('welcome/change_location/2');

        $home = $this->get('/');

        $this->assertSame(2, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
        $this->assertStringContainsString('welcome/change_location/1', (string) $home->getBody());
        $this->assertStringNotContainsString('welcome/change_location/2', (string) $home->getBody());
    }

    public function testLoginIsLoggedAndTheLastLoginIsStored(): void
    {
        $this->login();

        $log = $this->row("SELECT * FROM log WHERE event = 'login_success' ORDER BY id DESC LIMIT 1");
        $this->assertNotNull($log, 'no login_success log row');
        $this->assertSame(5, (int) $log['level'], 'DEBUG level');
        $this->assertSame('user : vet@test.local', $log['msg']);
        $this->assertGreaterThan(time() - 60, (int) $this->find('users', Fixtures::USER_VET)['last_login']);
    }

    public function testLoginResetsTheStoredLocationSoTheUserMustChooseAgain(): void
    {
        $this->db()->exec('UPDATE users SET current_location = 2 WHERE id = ' . Fixtures::USER_VET);

        $this->login();

        $this->assertSame(0, (int) $this->find('users', Fixtures::USER_VET)['current_location']);
        $this->assertRowExists('log', ['event' => 'unset_location']);
        $this->assertStringContainsString('id="staticBackdrop"', (string) $this->get('/')->getBody());
    }

    public function testTheSessionIdChangesAtLogin(): void
    {
        $this->get('auth/login');
        $before = $this->cookies->getCookieByName('alies_demo_root')?->getValue();

        $this->login();
        $after = $this->cookies->getCookieByName('alies_demo_root')?->getValue();

        $this->assertNotNull($before);
        $this->assertNotNull($after);
        $this->assertNotSame($before, $after, 'the session must be regenerated at login (session fixation)');
    }

    public function testTheSeededPasswordHashesAreAlreadyAtTheCostIonAuthWants(): void
    {
        // Ion_auth rewrites the hash at login when its cost differs (12 for admins, 10 for others).
        // If this fails the seed no longer matches application/config/ion_auth.php.
        $before = $this->rows('SELECT id, password FROM users WHERE id IN (1, 2) ORDER BY id');

        $this->login('admin@test.local');
        $this->cookies->clear();
        $this->login('vet@test.local');

        $this->assertSame($before, $this->rows('SELECT id, password FROM users WHERE id IN (1, 2) ORDER BY id'));
    }

    public function testEveryStaffRoleCanLogInAndChooseALocation(): void
    {
        foreach (['admin', 'vet', 'vet2', 'acc'] as $role) {
            $this->cookies->clear();
            $this->loginAs($role, Fixtures::LOCATION_CAR);

            $this->assertStatus($this->get('/'), 200, $role);
            $this->assertSame(
                Fixtures::LOCATION_CAR,
                (int) $this->find('users', Fixtures::USERS[$role][1])['current_location'],
                $role
            );
        }
    }
}
