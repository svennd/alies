<?php

declare(strict_types=1);

/**
 * T220 - Vaccine reminder overview (vaccine/index/{month offset}).
 * Source: application/controllers/Vaccine.php, Vaccine_model::get_expiring_vaccines, views/vaccine/overview.php
 *
 * The list shows vaccines whose `redo` date is in the month (offset from this month) and drops the vaccine of a
 * dead, lost or transferred pet, a vaccine with no_rappel = 1, and one whose owner is disabled. The owner is the
 * pet's companion when one is set (then the companion's disabled flag decides). Offsets of 3 or more are for admins.
 * The seed has vaccines 11001-11003 for owner 1001/1002, so every case here uses its own unique names.
 */
final class OverviewTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function nextMonth(string $day = '15'): string
    {
        return date('Y-m-', strtotime('first day of next month')) . $day;
    }

    /** @return array{0: int, 1: string, 2: string} vaccine id, pet name, owner last name */
    private function case(array $pet = [], array $vaccine = [], array $owner = [], ?string $redo = null): array
    {
        $ownerName = Factory::unique('OvOwner');
        $petName = Factory::unique('OvPet');
        $ownerId = Factory::owner($owner + ['last_name' => $ownerName]);
        $petId = Factory::pet($ownerId, $pet + ['name' => $petName]);
        $id = Factory::vaccine($petId, $vaccine + ['redo' => $redo ?? $this->nextMonth()]);

        return [$id, $petName, $ownerName];
    }

    public function testAVaccineDueNextMonthIsListedWithOwnerAndPet(): void
    {
        [, $pet, $owner] = $this->case();

        $page = $this->get('vaccine/index/1');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $pet);
        $this->assertSee($page, $owner);
        $this->assertSee($page, date('F', strtotime('first day of next month')));
    }

    public function testSeededNextMonthReminderIsListedAndTheSwitchedOffAndExpiredOnesAreNot(): void
    {
        $page = $this->get('vaccine/index/1');

        // 11001: Mimi, due next month, 11003 (Bobby) has no_rappel = 1, 11002 (Rex) expired two months ago
        $this->assertSee($page, 'Mimi');
        $this->assertDontSee($page, 'Bobby');
        $this->assertDontSee($page, 'Rex');
    }

    public function testDeadLostAndTransferredPetsAreLeftOut(): void
    {
        [, $ok] = $this->case();
        [, $dead] = $this->case(['death' => 1]);
        [, $lost] = $this->case(['lost' => 1]);
        [, $transferred] = $this->case(['transfered' => 1]);

        $page = $this->get('vaccine/index/1');

        $this->assertSee($page, $ok);
        $this->assertDontSee($page, $dead);
        $this->assertDontSee($page, $lost);
        $this->assertDontSee($page, $transferred);
    }

    public function testAVaccineWithoutReminderIsLeftOut(): void
    {
        [, $ok] = $this->case();
        [, $off] = $this->case([], ['no_rappel' => 1]);

        $page = $this->get('vaccine/index/1');

        $this->assertSee($page, $ok);
        $this->assertDontSee($page, $off);
    }

    public function testADisabledOwnerIsLeftOut(): void
    {
        [, $ok] = $this->case();
        [, $disabled] = $this->case([], [], ['disabled' => 1]);

        $page = $this->get('vaccine/index/1');

        $this->assertSee($page, $ok);
        $this->assertDontSee($page, $disabled);
    }

    public function testTheCompanionDecidesWhenAPetHasOne(): void
    {
        // pet of an enabled owner whose companion is disabled: left out
        $companionOff = Factory::owner(['last_name' => Factory::unique('CompOff'), 'disabled' => 1]);
        $ownerA = Factory::owner(['last_name' => Factory::unique('OwnerA')]);
        $petA = Factory::pet($ownerA, ['name' => Factory::unique('CompPetA'), 'companion' => $companionOff]);
        Factory::vaccine($petA, ['redo' => $this->nextMonth()]);

        // pet of a disabled owner whose companion is enabled: listed, under the companion's name
        $companionOn = Factory::owner(['last_name' => Factory::unique('CompOn')]);
        $ownerB = Factory::owner(['last_name' => Factory::unique('OwnerB'), 'disabled' => 1]);
        $petB = Factory::pet($ownerB, ['name' => Factory::unique('CompPetB'), 'companion' => $companionOn]);
        Factory::vaccine($petB, ['redo' => $this->nextMonth()]);

        $page = $this->get('vaccine/index/1');

        $this->assertDontSee($page, $this->find('pets', $petA)['name']);
        $this->assertSee($page, $this->find('pets', $petB)['name']);
        $this->assertSee($page, $this->find('owners', $companionOn)['last_name']);
        $this->assertDontSee($page, $this->find('owners', $ownerB)['last_name']);
    }

    public function testTheMonthBoundariesAreInclusive(): void
    {
        [, $first] = $this->case([], [], [], $this->nextMonth('01'));
        [, $last] = $this->case([], [], [], date('Y-m-t', strtotime('first day of next month')));
        [, $before] = $this->case([], [], [], date('Y-m-t')); // last day of this month
        [, $after] = $this->case([], [], [], date('Y-m-01', strtotime('first day of next month +1 month')));

        $page = $this->get('vaccine/index/1');

        $this->assertSee($page, $first);
        $this->assertSee($page, $last);
        $this->assertDontSee($page, $before);
        $this->assertDontSee($page, $after);
    }

    public function testMonthZeroShowsTheCurrentMonth(): void
    {
        [, $now] = $this->case([], [], [], date('Y-m-10'));
        [, $next] = $this->case();

        $page = $this->get('vaccine/index/0');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $now);
        $this->assertDontSee($page, $next);
        $this->assertSee($page, date('F'));
    }

    public function testOffsetsOfThreeOrMoreAreForAdminsOnly(): void
    {
        $this->db()->exec('DELETE FROM log');
        [, $far] = $this->case([], [], [], date('Y-m-15', strtotime('first day of this month +3 month')));

        // a vet is sent home and the attempt is logged
        $this->assertRedirectsTo($this->get('vaccine/index/3'), '');
        $this->assertSame(1, $this->logCount('boundary_vaccine_attack'));

        // the same page works for an admin
        $this->cookies->clear();
        $this->loginAs('admin');
        $page = $this->get('vaccine/index/3');
        $this->assertStatus($page, 200);
        $this->assertSee($page, $far);
    }
}
