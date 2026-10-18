<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T091 - Vet::profile, Vet::profile_change and Vet::pub.
 * Rules: the profile form posts to profile_change, which writes the posted fields to the current
 * user (the colour goes into users.sidebar) and goes back to the profile; the public profile of any
 * user shows e-mail, phone, sidebar colour and the number of events. This class changes user vet2,
 * never the other seeded users.
 */
final class VetProfileTest extends HttpTestCase
{
    private function profileForm(array $override = []): array
    {
        return $override + [
            'color' => '#123456',
            'first_name' => 'Nieuwe',
            'last_name' => 'Naam',
            'search_config' => '3',
            'user_date' => 'Y-m-d',
            'phone' => '0499112233',
            'order_nr' => '777888',
        ];
    }

    public function testTheProfilePageShowsTheCurrentValues(): void
    {
        $this->loginAs('vet');

        $page = $this->get('vet/profile');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="Victor"', (string) $page->getBody());
        $this->assertStringContainsString('value="Vet"', (string) $page->getBody());
        $this->assertStringContainsString('vet@test.local', (string) $page->getBody());
    }

    public function testChangingTheProfileUpdatesTheCurrentUserOnly(): void
    {
        $this->loginAs('vet2');
        $vet = $this->find('users', Fixtures::USER_VET);

        $response = $this->post('vet/profile_change', $this->profileForm());

        $this->assertRedirectsTo($response, 'vet/profile');
        $row = $this->find('users', Fixtures::USER_VET2);
        $this->assertSame('#123456', $row['sidebar'], 'color is stored as sidebar');
        $this->assertSame('Nieuwe', $row['first_name']);
        $this->assertSame('Naam', $row['last_name']);
        $this->assertSame(3, (int) $row['search_config']);
        $this->assertSame('Y-m-d', $row['user_date']);
        $this->assertSame('0499112233', $row['phone']);
        $this->assertSame('777888', $row['order_nr']);
        $this->assertSame($vet, $this->find('users', Fixtures::USER_VET), 'the other vet is untouched');
        $this->assertSame('vet2@test.local', $row['email'], 'the e-mail is not editable');

        $page = $this->get('vet/profile');
        $this->assertStringContainsString('value="Nieuwe"', (string) $page->getBody());
    }

    public function testTheNewColourIsUsedForTheSidebar(): void
    {
        $this->loginAs('vet2');
        $this->post('vet/profile_change', $this->profileForm(['color' => '#abcdef']));

        $this->assertStringContainsString('#abcdef', (string) $this->get('')->getBody());
    }

    public function testThePublicProfileShowsAnyUser(): void
    {
        $this->loginAs('vet');

        $admin = $this->get('vet/pub/' . Fixtures::USER_ADMIN);
        $this->assertStatus($admin, 200);
        $this->assertSee($admin, 'Alice Admin');
        $this->assertSee($admin, 'admin@test.local');
        $this->assertSee($admin, '0470000001');
        $this->assertStringContainsString('#4169e1', (string) $admin->getBody());

        $other = $this->get('vet/pub/' . Fixtures::USER_VET2);
        $this->assertStatus($other, 200);
        $this->assertSee($other, 'vet2@test.local');
        $this->assertDontSee($other, 'admin@test.local');
    }

    #[Group('known-bug')]
    public function testThePublicProfileOfAMissingUserRendersAnEmptyProfile(): void
    {
        // KNOWN BUG: no check that the user exists: the page answers 200 with an empty name and
        // empty fields - decide in CI4 whether to answer 404
        $this->loginAs('vet');

        $response = $this->get('vet/pub/99999');

        $this->assertStatus($response, 200);
        $this->assertMatchesRegularExpression('#Dierenarts /\s*</div>#', (string) $response->getBody());
        $this->assertDontSee($response, 'admin@test.local');
    }
}
