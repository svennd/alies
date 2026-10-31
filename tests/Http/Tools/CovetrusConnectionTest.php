<?php

declare(strict_types=1);

/**
 * T313 - Tests::test_connection_covetrus/{user}/{password} (admin only).
 *
 * The action builds https://user:password@online.medilab.be/dokter/stalen.json and curls it, so
 * the working path can only be tested with a stub (F07). What is tested here is everything before
 * the network: the access rules, which never reach the curl call for non-admins.
 */
final class CovetrusConnectionTest extends HttpTestCase
{
    public function testAnonymousVisitorsAreSentToTheLoginPage(): void
    {
        $response = $this->get('tests/test_connection_covetrus/user/secretpass');

        $this->assertRedirectsTo($response, 'auth/login');
        $this->assertDontSee($response, 'OK');
        $this->assertDontSee($response, 'FAILED');
    }

    public function testNonAdminsAreSentHomeAndGetNoResult(): void
    {
        foreach (['vet', 'acc'] as $role) {
            $this->cookies->clear();
            $this->loginAs($role);

            $response = $this->get('tests/test_connection_covetrus/user/secretpass');

            $this->assertRedirectsTo($response, '', $role);
            $this->assertSame('', trim((string) $response->getBody()), $role . ': nothing printed, nothing leaked');
        }
    }

    public function testTheSettingsPageWiresTheTestButtonToThisUrl(): void
    {
        $this->loginAs('admin');

        $page = $this->get('admin/settings');

        $this->assertStringContainsString('tests/test_connection_covetrus/', (string) $page->getBody());
        $this->assertStringContainsString('id="test_covetrus"', (string) $page->getBody());
    }

    public function testTheAdminCallReachesMedilab(): void
    {
        $this->markTestSkipped('needs F07 stub: Tests::test_connection_covetrus calls online.medilab.be');
    }
}
