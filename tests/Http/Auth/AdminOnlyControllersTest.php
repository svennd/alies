<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * T036 - controllers that extend Admin_Controller: a vet is sent to the home page, an admin gets in.
 * Also T038 (second half): users outside the staff groups get a 500 before the admin check.
 * Source: application/libraries/Admin_Controller.php (redirect('/')) and Vet_Controller.php.
 */
final class AdminOnlyControllersTest extends HttpTestCase
{
    /** @return array<string, array{string}> */
    public static function adminRoutes(): array
    {
        $routes = [
            'admin/settings',
            'member',
            'reports/revenue',
            'export',
            'vamreg',
            'wholesale',
            'logs/nlog',
            'tools',
            'restore/booking',
            'stats',
            'accounting/dashboard',
            'admin_invoice/edit_bill/' . Fixtures::BILL_PAID,
        ];

        return array_combine($routes, array_map(static fn (string $r): array => [$r], $routes));
    }

    #[DataProvider('adminRoutes')]
    public function testAVetIsSentToTheHomePage(string $route): void
    {
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get($route), '');
    }

    #[DataProvider('adminRoutes')]
    public function testAnAdminGetsIn(string $route): void
    {
        $this->loginAs('admin');

        $response = $this->get($route);

        $this->assertStatus($response, 200);
        $this->assertNotRedirect($response);
    }

    #[DataProvider('adminRoutes')]
    public function testAnAccountingUserIsSentToTheHomePageToo(string $route): void
    {
        $this->loginAs('acc');

        $this->assertRedirectsTo($this->get($route), '');
    }

    #[DataProvider('adminRoutes')]
    public function testAUserOutsideTheStaffGroupsGetsAServerErrorInsteadOfARedirect(string $route): void
    {
        // the Vet_Controller group check comes first and answers with show_error()
        $this->loginAs('member');

        $response = $this->get($route);

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Not part of the correct group');
    }

    public function testAnAdminPostIsHandledButAVetPostChangesNothing(): void
    {
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->post('admin/locations', ['submit' => 'add_location', 'name' => 'Vet location']), '');
        $this->assertRowMissing('stock_location', ['name' => 'Vet location']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('admin/locations', ['submit' => 'add_location', 'name' => 'Admin location']);
        $this->assertRowExists('stock_location', ['name' => 'Admin location']);
    }
}
