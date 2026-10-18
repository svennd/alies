<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * T035 - nothing behind the login is reachable without logging in.
 *
 * One representative route per controller. The controllers check the login in their constructor, so
 * the action itself never runs for an anonymous visitor (also not the ones that change data).
 */
final class AnonymousAccessTest extends HttpTestCase
{
    /**
     * controller (lower case, as the file name) => a route of that controller.
     * Controllers that are public or not meant for the web are left out on purpose, see EXCLUDED.
     */
    private const ROUTES = [
        'accounting' => 'accounting/dashboard',
        'admin' => 'admin/settings',
        'admin_invoice' => 'admin_invoice/edit_bill/7001',
        'breeds' => 'breeds',
        'events' => 'events/event/6001',
        'events_report' => 'events_report/disable_history/6001',
        'export' => 'export',
        'files' => 'files/get_file/1',
        'help' => 'help/vet',
        'invoice' => 'invoice',
        'lab' => 'lab',
        'limits' => 'limits/global',
        'logs' => 'logs/nlog',
        'member' => 'member',
        'owners' => 'owners/add',
        'pets' => 'pets/fiche/2001',
        'pricing' => 'pricing/proc',
        'products' => 'products',
        'report' => 'report',
        'reports' => 'reports/revenue',
        'restore' => 'restore/booking',
        'rx' => 'rx/list/2001',
        'search' => 'search?search_query=x',
        'stats' => 'stats',
        'sticky' => 'sticky',
        'stock' => 'stock/add_stock',
        'tests' => 'tests/test_connection_covetrus/user/pass',
        'tools' => 'tools',
        'tooth' => 'tooth/fiche/2001',
        'vaccine' => 'vaccine',
        'vamreg' => 'vamreg',
        'vet' => 'vet/profile',
        'welcome' => '',
        'wholesale' => 'wholesale',
    ];

    /** Controllers that are reachable without a login, or only from the command line. */
    private const EXCLUDED = [
        'auth' => 'the login screens themselves',
        'cli' => 'command line only (see T043)',
        'upgrade' => 'command line only (see T043)',
    ];

    /** @return array<string, array{string}> */
    public static function routes(): array
    {
        $cases = [];
        foreach (self::ROUTES as $controller => $route) {
            $cases[$controller . ' (' . ($route === '' ? '/' : $route) . ')'] = [$route];
        }

        return $cases;
    }

    #[DataProvider('routes')]
    public function testAnonymousVisitorIsSentToTheLoginPage(string $route): void
    {
        $this->assertRedirectsTo($this->get($route), 'auth/login');
    }

    public function testEveryControllerOfTheApplicationIsInTheList(): void
    {
        // CodeIgniter 3 layout; for the CI4 port point this at the controllers folder there.
        $directory = dirname(__DIR__, 3) . '/application/controllers';
        if (!is_dir($directory)) {
            $this->markTestSkipped('controllers directory not found: ' . $directory);
        }

        $controllers = array_map(static fn (string $f): string => strtolower(basename($f, '.php')), glob($directory . '/*.php') ?: []);
        $known = array_merge(array_keys(self::ROUTES), array_keys(self::EXCLUDED));

        $this->assertSame(
            [],
            array_values(array_diff($controllers, $known)),
            'new controller(s) without an entry in ROUTES (or EXCLUDED, with a reason)'
        );
        $this->assertSame([], array_values(array_diff($known, $controllers)), 'ROUTES lists a controller that no longer exists');
    }

    public function testTheApiControllerAnswersWithAnApiErrorNotALoginRedirect(): void
    {
        // api/lab has its own key based authentication
        $response = $this->get('api/lab/import/ms4s2');

        $this->assertStatus($response, 401);
        $this->assertNotRedirect($response);
        $this->assertSame('Missing API key', $this->json($response)['message']);
    }

    public function testAnAnonymousPostIsRefusedInTheSameWay(): void
    {
        $before = $this->countWhere('owners');

        $response = $this->post('owners/add', ['submit' => 1, 'last_name' => 'Intruder']);

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertSame($before, $this->countWhere('owners'));
    }

    public function testTheLoginPageItselfIsPublic(): void
    {
        $this->assertStatus($this->get('auth/login'), 200);
    }

    public function testRoutesAreStillProtectedWithTheSessionCookieOfAnotherVisitor(): void
    {
        // an unknown session id is just an anonymous visitor
        $this->cookies->setCookie(new \GuzzleHttp\Cookie\SetCookie([
            'Name' => 'alies_demo_root',
            'Value' => hash('sha1', 'not a real session'),
            'Domain' => '127.0.0.1',
            'Path' => '/',
        ]));

        $this->assertRedirectsTo($this->get('owners/add'), 'auth/login');
    }
}
