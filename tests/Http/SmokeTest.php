<?php

declare(strict_types=1);

/**
 * Proves that the HTTP test foundation works end to end: server, testing environment, seeded
 * database, login, redirects and the PDO helpers. If this class fails, nothing else is worth running.
 */
final class SmokeTest extends HttpTestCase
{
    public function testAnonymousVisitorIsSentToTheLoginPage(): void
    {
        $response = $this->get('/');

        $this->assertRedirectsTo($response, 'auth/login');
    }

    public function testLoginPageRenders(): void
    {
        $response = $this->get('auth/login');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('name="identity"', (string) $response->getBody());
        $this->assertStringContainsString('name="password"', (string) $response->getBody());
    }

    public function testVetCanLogInAndSeesTheHomePage(): void
    {
        $this->loginAs('vet');

        $response = $this->get('/');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Uitloggen');
        // seeded locations are shown in the header
        $this->assertSee($response, 'Praktijk');
        $this->assertSee($response, 'Wagen');
        // the soft deleted location is not
        $this->assertDontSee($response, 'Oude locatie');

        $profile = $this->get('vet/pub/' . Fixtures::USER_VET);
        $this->assertStatus($profile, 200);
        $this->assertSee($profile, 'Victor');
    }

    public function testLoginWithWrongPasswordGoesBackToTheLoginPage(): void
    {
        $response = $this->attemptLogin('vet@test.local', 'definitely-wrong-password');

        $this->assertRedirectsTo($response, 'auth/login');
    }

    public function testRolesWithoutVetGroupAreRefused(): void
    {
        $this->loginAs('nogroup');

        $response = $this->get('/');

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Not part of the correct group');
    }

    public function testAdminAreaIsClosedToVetsAndOpenToAdmins(): void
    {
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('member'), '');

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('member'), 200);
    }

    public function testDatabaseHelpersSeeTheSeed(): void
    {
        $this->assertRowCount(7, 'users');
        $this->assertRowExists('owners', ['id' => Fixtures::OWNER_JANSSENS, 'last_name' => 'Janssens']);
        $this->assertRowMissing('owners', ['last_name' => 'Does Not Exist']);
        $this->assertSame('Rex', $this->scalar('SELECT name FROM pets WHERE id = ?', [Fixtures::PET_REX]));
    }

    public function testFactoryCreatesUsableRows(): void
    {
        $owner = Factory::owner(['last_name' => 'Factorytest']);
        $pet = Factory::pet($owner, ['name' => 'Fido']);

        $this->assertSame('Fido', $this->find('pets', $pet)['name']);
        $this->assertSame($owner, (int) $this->find('pets', $pet)['owner']);

        $this->loginAs('vet');
        $this->assertSee($this->get('owners/detail/' . $owner), 'Factorytest');
    }
}
