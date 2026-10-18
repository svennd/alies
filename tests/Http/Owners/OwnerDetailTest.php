<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * T052 - Owners::detail: a client with their pets and open bills.
 * Seed: owner 1001 has Rex and Mimi (alive), Oudje (dead a year ago), Zwerver and Verhuisd Hondje
 * (transferred away) and Weggegooid (soft deleted).
 */
final class OwnerDetailTest extends HttpTestCase
{
    private function open(int $ownerId)
    {
        return $this->get('owners/detail/' . $ownerId);
    }

    public function testTheHeaderShowsTheClientAndTheirNumber(): void
    {
        $this->loginAs('vet');

        $page = $this->open(Fixtures::OWNER_JANSSENS);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Janssens');
        $this->assertSee($page, '(#1001)');
        $this->assertStringContainsString('owners/edit/1001', (string) $page->getBody());
        $this->assertStringContainsString('owners/invoices/1001', (string) $page->getBody());
        $this->assertStringContainsString('owners/products/1001', (string) $page->getBody());
    }

    public function testAlivePetsAreListedWithBreedWeightAndConsultLink(): void
    {
        $this->loginAs('vet');

        $page = $this->open(Fixtures::OWNER_JANSSENS);
        $body = (string) $page->getBody();

        $this->assertSee($page, 'Rex');
        $this->assertSee($page, 'Mimi');
        $this->assertSee($page, 'Labrador');
        $this->assertSee($page, '25.50 kg');
        $this->assertStringContainsString('pets/fiche/' . Fixtures::PET_REX, $body);
        $this->assertStringContainsString('events/new_event/' . Fixtures::PET_REX, $body);
        $this->assertStringContainsString('pets/add/1001', $body);
    }

    #[Group('known-bug')]
    public function testThePetsAreInDatabaseOrderBecauseTheIntendedSortingDoesNothing(): void
    {
        // KNOWN BUG: the model is asked to sort by birth (youngest first) but order_by(array("birth, death"), "desc")
        // becomes `ORDER BY 0 ...`, which is ignored; the pets come out in the order they were added.
        $owner = Factory::owner();
        $older = Factory::pet($owner, ['name' => 'Addedfirst', 'birth' => '2015-01-01']);
        $younger = Factory::pet($owner, ['name' => 'Addedlast', 'birth' => '2024-01-01']);
        $this->loginAs('vet');

        $body = (string) $this->open($owner)->getBody();

        $this->assertLessThan(strpos($body, 'pets/fiche/' . $younger), strpos($body, 'pets/fiche/' . $older));
    }

    public function testTransferredAndDeletedPetsAreNotShown(): void
    {
        $this->loginAs('vet');

        $page = $this->open(Fixtures::OWNER_JANSSENS);

        $this->assertDontSee($page, 'Zwerver');
        $this->assertDontSee($page, 'Verhuisd Hondje');
        $this->assertDontSee($page, 'Weggegooid');
    }

    public function testADeadPetIsListedSeparatelyAsPassedAway(): void
    {
        $this->loginAs('vet');

        $page = $this->open(Fixtures::OWNER_JANSSENS);

        $this->assertSee($page, 'Overleden');
        $this->assertSee($page, 'Oudje');
        $this->assertStringContainsString('pets/fiche/' . Fixtures::PET_DEAD, (string) $page->getBody());
    }

    public function testALostPetIsListedSeparately(): void
    {
        $owner = Factory::owner();
        Factory::pet($owner, ['name' => 'Wandelaar', 'lost' => 1]);
        $this->loginAs('vet');

        $page = $this->open($owner);

        $this->assertSee($page, 'Wandelaar');
        $this->assertStringNotContainsString('events/new_event/', (string) $page->getBody(), 'no consult link for a lost pet');
    }

    public function testAClientWithoutPetsIsStillShown(): void
    {
        $owner = Factory::owner(['last_name' => 'Petless']);
        $this->loginAs('vet');

        $page = $this->open($owner);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Petless');
        $this->assertStringContainsString('pets/add/' . $owner, (string) $page->getBody());
    }

    /** @return array<string, array{int, bool}> status => listed as open */
    public static function billStatuses(): array
    {
        return [
            'draft' => [0, true],
            'pending' => [1, true],
            'unpaid' => [2, true],
            'incomplete' => [3, true],
            'paid' => [4, false],
            'overdue' => [5, true],
            'onhold' => [6, true],
            'historical' => [7, false],
            'invalid' => [-1, false],
        ];
    }

    #[DataProvider('billStatuses')]
    public function testOnlyUnfinishedBillsAreListedAsOpen(int $status, bool $listed): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, $status, ['total_brut' => 12.34]);
        $this->loginAs('vet');

        $page = $this->open($owner);

        $link = 'invoice/get_bill/' . $bill;
        if ($listed) {
            $this->assertStringContainsString($link, (string) $page->getBody());
            $this->assertSee($page, '12.34');
        } else {
            $this->assertStringNotContainsString($link, (string) $page->getBody());
        }
    }

    public function testASoftDeletedBillAndTheBillOfAnotherClientAreNotListed(): void
    {
        $owner = Factory::owner();
        $deleted = Factory::bill($owner, 1, ['deleted_at' => Factory::raw('NOW()')]);
        $foreign = Factory::bill(Factory::owner(), 1);
        $this->loginAs('vet');

        $body = (string) $this->open($owner)->getBody();

        $this->assertStringNotContainsString('invoice/get_bill/' . $deleted, $body);
        $this->assertStringNotContainsString('invoice/get_bill/' . $foreign, $body);
    }

    public function testNoOpenBillMeansNoWarning(): void
    {
        $this->loginAs('vet');

        $this->assertDontSee($this->open(Fixtures::OWNER_JANSSENS), 'Openstaande');
    }

    public function testADisabledClientIsMarked(): void
    {
        $this->loginAs('vet');

        $page = $this->open(Fixtures::OWNER_DISABLED);

        $this->assertSee($page, 'Deze klant is uitgeschakeld.');
        $this->assertStringContainsString('text-danger', (string) $page->getBody());
    }

    public function testAClientDisabledByAMergeLinksToTheOtherClient(): void
    {
        $owner = Factory::owner(['disabled' => 1, 'msg' => 'merge_id_disable:1001:2026-01-02 10:30:00']);
        $this->loginAs('vet');

        $page = $this->open($owner);

        $this->assertSee($page, 'Deze klant is uitgeschakeld.');
        $this->assertStringContainsString('owners/detail/1001', (string) $page->getBody());
        $this->assertSee($page, '2026-01-02 10:30:00');
    }

    #[Group('known-bug')]
    public function testAnOwnerThatDoesNotExistGivesAPageFullOfWarnings(): void
    {
        // KNOWN BUG: no existence check. The view works with `false`; the response is still 200
        // (or an error page) instead of a 404. Pin the status as it is today.
        $this->loginAs('vet');

        $missing = $this->open(999999);
        $deleted = $this->open(Fixtures::OWNER_DELETED);

        $this->assertSame($missing->getStatusCode(), $deleted->getStatusCode(), 'a deleted client looks like a missing one');
        $this->assertContains($missing->getStatusCode(), [200, 404, 500]);
        $this->assertDontSee($deleted, 'Verwijderd');
    }

    public function testAccountingUsersCanOpenTheDetailPage(): void
    {
        $this->loginAs('acc');

        $this->assertStatus($this->open(Fixtures::OWNER_JANSSENS), 200);
    }

    public function testAnonymousVisitorsAreSentToTheLogin(): void
    {
        $this->assertRedirectsTo($this->open(Fixtures::OWNER_JANSSENS), 'auth/login');
    }
}
