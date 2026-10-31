<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T310 - Tools::index, duplicate_phones, duplicate_chips, duplicate_clients (admin only).
 *
 * Rules: the phone finder groups enabled owners by their first non-empty number (telephone,
 * mobile, phone2, phone3) when it starts with "04"; the chip finder groups pets that are not
 * transferred by chip; both list the ids newest bill first and the "merge" link is
 * merge_clients/{second}/{first}. The address finder is broken SQL.
 */
final class DuplicateFindersTest extends HttpTestCase
{
    private function uniqueMobile(): string
    {
        return '04' . random_int(10000000, 99999999);
    }

    public function testTheToolsIndexLinksToTheFinders(): void
    {
        $this->loginAs('admin');

        $page = $this->get('tools');

        $this->assertStatus($page, 200);
        foreach (['duplicate_clients', 'duplicate_phones', 'duplicate_chips', 'show_api_keys'] as $tool) {
            $this->assertStringContainsStringIgnoringCase('tools/' . $tool, (string) $page->getBody());
        }
    }

    public function testTheSeededPairSharingAMobileNumberIsListed(): void
    {
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_phones');

        $this->assertStatus($page, 200);
        $this->assertSee($page, Fixtures::DUPLICATE_MOBILE);
        $this->assertStringContainsString('owners/detail/' . Fixtures::OWNER_DUPLICATE_A, (string) $page->getBody());
        $this->assertStringContainsString('owners/detail/' . Fixtures::OWNER_DUPLICATE_B, (string) $page->getBody());
    }

    public function testTwoOwnersWithTheSameNumberAreListedNewestBillFirstWithAMergeLink(): void
    {
        $mobile = $this->uniqueMobile();
        $old = Factory::owner(['mobile' => $mobile, 'last_bill' => Factory::raw("DATE_SUB(CURDATE(), INTERVAL 40 DAY)")]);
        $new = Factory::owner(['mobile' => $mobile, 'last_bill' => Factory::raw('CURDATE()')]);
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_phones');

        $this->assertSee($page, $mobile);
        // merge: the older owner (disabled) first, the newer (kept) second
        $this->assertStringContainsString('tools/merge_clients/' . $old . '/' . $new . '"', (string) $page->getBody());
    }

    public function testOnlyEnabledOwnersWithANumberStartingWith04AreCompared(): void
    {
        $landline = '03' . random_int(1000000, 9999999);
        $a = Factory::owner(['telephone' => $landline]);
        $b = Factory::owner(['telephone' => $landline]);
        $mobile = $this->uniqueMobile();
        Factory::owner(['mobile' => $mobile]);
        Factory::owner(['mobile' => $mobile, 'disabled' => 1]);
        $single = $this->uniqueMobile();
        Factory::owner(['mobile' => $single]);
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_phones');

        $this->assertDontSee($page, $landline, 'not a 04 number');
        $this->assertDontSee($page, $mobile, 'the second owner is disabled, so no pair');
        $this->assertDontSee($page, $single);
        $this->assertStringNotContainsString('owners/detail/' . $a, (string) $page->getBody());
        $this->assertNotNull($b);
    }

    public function testTheSeededPairSharingAChipIsListed(): void
    {
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_chips');

        $this->assertStatus($page, 200);
        $this->assertSee($page, Fixtures::CHIP_DUPLICATE);
        $this->assertStringContainsString('pets/fiche/' . Fixtures::PET_DUPLICATE_A, (string) $page->getBody());
        $this->assertStringContainsString('pets/fiche/' . Fixtures::PET_DUPLICATE_B, (string) $page->getBody());
    }

    public function testTransferredPetsAndSingleChipsAreNotListed(): void
    {
        $chip = '9813' . random_int(10000000000, 99999999999);
        $a = Factory::pet(Factory::owner(), ['chip' => $chip]);
        Factory::pet(Factory::owner(), ['chip' => $chip, 'transfered' => 1]);
        $lonely = '9813' . random_int(10000000000, 99999999999);
        Factory::pet(Factory::owner(), ['chip' => $lonely]);
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_chips');

        $this->assertDontSee($page, $chip);
        $this->assertDontSee($page, $lonely);
        $this->assertStringNotContainsString('pets/fiche/' . $a . '"', (string) $page->getBody());
    }

    public function testAPairOfPetsWithTheSameChipIsListed(): void
    {
        $chip = '9813' . random_int(10000000000, 99999999999);
        $a = Factory::pet(Factory::owner(), ['chip' => $chip]);
        $b = Factory::pet(Factory::owner(), ['chip' => $chip]);
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_chips');

        $this->assertSee($page, $chip);
        $this->assertStringContainsString('pets/fiche/' . $a . '"', (string) $page->getBody());
        $this->assertStringContainsString('pets/fiche/' . $b . '"', (string) $page->getBody());
    }

    #[Group('known-bug')]
    public function testTheChipFinderMergeLinkCarriesPetIdsToTheClientMerge(): void
    {
        // KNOWN BUG: the "merge" link of the pet finder is tools/merge_clients/{pet id}/{pet id}, but
        // merge_clients expects owner ids - following it would merge two unrelated owners
        $chip = '9813' . random_int(10000000000, 99999999999);
        $first = Factory::pet(Factory::owner(['last_bill' => Factory::raw('DATE_SUB(CURDATE(), INTERVAL 10 DAY)')]), ['chip' => $chip]);
        $second = Factory::pet(Factory::owner(['last_bill' => Factory::raw('CURDATE()')]), ['chip' => $chip]);
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_chips');

        $this->assertStringContainsString('tools/merge_clients/' . $first . '/' . $second . '"', (string) $page->getBody());
    }

    #[Group('known-bug')]
    public function testTheAddressFinderFailsWithADatabaseError(): void
    {
        // KNOWN BUG: Owners_model::duplicate_owner_street selects "owner.id" (the table is "owners"), so
        // tools/duplicate_clients is always a 500
        $this->loginAs('admin');

        $page = $this->get('tools/duplicate_clients');

        $this->assertStatus($page, 500);
        $this->assertSee($page, 'Database Error');
    }

    public function testOnlyAdminsMayUseTheFinders(): void
    {
        foreach (['vet', 'acc'] as $role) {
            $this->cookies->clear();
            $this->loginAs($role);
            foreach (['tools', 'tools/duplicate_phones', 'tools/duplicate_chips', 'tools/duplicate_clients'] as $url) {
                $this->assertRedirectsTo($this->get($url), '', $role . ' ' . $url);
            }
        }
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('tools/duplicate_phones'), 200);
    }
}
