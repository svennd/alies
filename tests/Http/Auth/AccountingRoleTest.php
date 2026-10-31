<?php

declare(strict_types=1);

/**
 * T037 - the accounting role. Accounting_Controller (pricing) lets admin and accounting in;
 * everything that extends Admin_Controller (also the accounting dashboard) is admin only.
 * T038 - users without a staff group.
 */
final class AccountingRoleTest extends HttpTestCase
{
    public function testAnAccountingUserCanOpenThePricingScreens(): void
    {
        $this->loginAs('acc');

        $this->assertStatus($this->get('pricing/proc'), 200);
        $this->assertStatus($this->get('pricing/prod'), 200);
    }

    public function testAnAdminCanOpenThePricingScreensToo(): void
    {
        $this->loginAs('admin');

        $this->assertStatus($this->get('pricing/proc'), 200);
    }

    public function testAVetGetsAServerErrorNotARedirectOnPricing(): void
    {
        // Accounting_Controller answers with show_error() because a redirect could loop
        $this->loginAs('vet');

        $response = $this->get('pricing/proc');

        $this->assertStatus($response, 500);
        $this->assertNotRedirect($response);
        $this->assertSee($response, 'Not part of the correct group');
    }

    public function testTheAccountingDashboardIsClosedToTheAccountingRole(): void
    {
        // inconsistency: the dashboard is for accountants, yet it extends Admin_Controller, so the
        // accounting role is sent to the home page. Decide in CI4 whether that was intended.
        $this->loginAs('acc');

        $this->assertRedirectsTo($this->get('accounting/dashboard'), '');
    }

    public function testAnAccountingUserCanUseTheVetScreens(): void
    {
        $this->loginAs('acc');

        $this->assertStatus($this->get('owners/add'), 200);
        $this->assertStatus($this->get('invoice'), 200);
    }

    public function testAnAccountingUserMayEditPricesButAVetMayNot(): void
    {
        $product = Factory::product(['price' => 10.0]);

        $this->loginAs('vet');
        $this->post('pricing/prod/' . $product, ['submit' => 1, 'volume' => [1], 'price' => [99]]);
        $this->assertRowMissing('products_price', ['product_id' => $product, 'price' => 99]);

        $this->cookies->clear();
        $this->loginAs('acc');
        $this->post('pricing/prod/' . $product, ['submit' => 1, 'volume' => [1], 'price' => [99]]);
        $this->assertRowExists('products_price', ['product_id' => $product, 'price' => 99]);
    }

    /** @return array<string, array{string}> */
    public static function outsiders(): array
    {
        return ['members group only' => ['member'], 'no group at all' => ['nogroup']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('outsiders')]
    public function testUsersOutsideTheStaffGroupsAreRefusedWithAServerError(string $role): void
    {
        $this->loginAs($role);

        foreach (['owners/add', 'pets/fiche/' . Fixtures::PET_REX, 'products', 'invoice', 'pricing/proc'] as $route) {
            $response = $this->get($route);

            $this->assertStatus($response, 500, $route);
            $this->assertSee($response, 'Not part of the correct group', $route);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('outsiders')]
    public function testUsersOutsideTheStaffGroupsDoNotChangeAnyData(string $role): void
    {
        $this->loginAs($role);
        $before = $this->countWhere('owners');

        $this->post('owners/add', ['submit' => 1, 'last_name' => 'Outsider', 'street' => 'x', 'nr' => '1']);

        $this->assertSame($before, $this->countWhere('owners'));
    }

    public function testTheLoginItselfWorksForUsersOutsideTheStaffGroups(): void
    {
        // they can log in, they just cannot do anything: the home page is already refused
        $this->loginAs('member');

        $this->assertStatus($this->get('/'), 500);
        $this->assertStatus($this->get('auth/login'), 200);
    }
}
