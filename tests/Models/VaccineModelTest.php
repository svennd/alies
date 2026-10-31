<?php

declare(strict_types=1);

/**
 * T225 - Vaccine_model queries.
 * Source: application/models/Vaccine_model.php
 *
 * view(pet): one row per product (vaccines without a catalogue product are not listed), `max_rappel` only looks
 * at rows with no_rappel = 0, `min_no_rappel` is 0 when any row still reminds, ordered reminding products first.
 * get_expiring_vaccines(date, excluded): vaccines whose redo is in the month of `date`; the column list is the
 * contract of the CSV export; the owner is the pet's companion when one is set (that owner's `disabled` decides).
 * get_expiring_vaccines_product_list(date): the products of those vaccines, one row per product name.
 * The seed owns vaccine 11001 in next month, so the tests look rows up by their unique pet names.
 */
final class VaccineModelTest extends CodeIgniterDatabaseTestCase
{
    private $vacs;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->vacs = $this->model('Vaccine_model', 'vacs_t225');
    }

    private function next(string $day = '15'): string
    {
        return date('Y-m-', strtotime('first day of next month')) . $day;
    }

    private function monthStart(): string
    {
        return date('Y-m-01 00:00:00', strtotime('first day of next month'));
    }

    /** @return array<string, array> expiring rows keyed by pet name */
    private function expiring(array $excluded = []): array
    {
        $out = [];
        foreach ($this->vacs->get_expiring_vaccines($this->monthStart(), $excluded) as $row) {
            $out[$row['pet_name']] = $row;
        }

        return $out;
    }

    public function testViewGroupsByProductAndIgnoresSwitchedOffRowsForTheNextReminder(): void
    {
        $pet = Factory::pet(Factory::owner());
        $a = Factory::product(['name' => 'ViewProd A', 'vaccin' => 1]);
        $b = Factory::product(['name' => 'ViewProd B', 'vaccin' => 1]);
        $e1 = Factory::event($pet);
        $e2 = Factory::event($pet);
        // product A: an old reminding row and a later one switched off (must not win max_rappel)
        Factory::vaccine($pet, ['product_id' => $a, 'event_id' => $e1, 'redo' => '2030-03-01', 'no_rappel' => 0, 'created_at' => '2029-03-01 10:00:00']);
        Factory::vaccine($pet, ['product_id' => $a, 'event_id' => $e2, 'redo' => '2035-01-01', 'no_rappel' => 1, 'created_at' => '2029-09-01 10:00:00']);
        // product B: switched off only
        Factory::vaccine($pet, ['product_id' => $b, 'event_id' => 0, 'redo' => '2031-01-01', 'no_rappel' => 1, 'created_at' => '2028-01-01 10:00:00']);
        // no catalogue product (martian): not part of the summary
        Factory::vaccine($pet, ['product_id' => 0, 'product' => 'Martian', 'redo' => '2040-01-01', 'no_rappel' => 0]);

        $rows = $this->vacs->view($pet);

        $this->assertCount(2, $rows);
        $this->assertSame('ViewProd A', $rows[0]['name'], 'reminding products first');
        $this->assertSame('2030-03-01', $rows[0]['max_rappel']);
        $this->assertSame('2029-09-01 10:00:00', $rows[0]['max_injection']);
        $this->assertSame(0, (int) $rows[0]['min_no_rappel']);
        $this->assertEqualsCanonicalizing([(string) $e1, (string) $e2], explode(',', $rows[0]['event_ids']));
        $this->assertSame('ViewProd B', $rows[1]['name']);
        $this->assertNull($rows[1]['max_rappel']);
        $this->assertSame(1, (int) $rows[1]['min_no_rappel']);
        $this->assertSame('2028-01-01 10:00:00', $rows[1]['max_injection']);
    }

    public function testViewOrdersByLatestReminderWithinTheSameState(): void
    {
        $pet = Factory::pet(Factory::owner());
        $early = Factory::product(['name' => 'ViewProd Early', 'vaccin' => 1]);
        $late = Factory::product(['name' => 'ViewProd Late', 'vaccin' => 1]);
        Factory::vaccine($pet, ['product_id' => $early, 'redo' => '2030-01-01']);
        Factory::vaccine($pet, ['product_id' => $late, 'redo' => '2032-01-01']);

        $rows = $this->vacs->view($pet);

        $this->assertSame(['ViewProd Late', 'ViewProd Early'], array_column($rows, 'name'));
    }

    public function testViewOfAPetWithoutVaccinesIsEmpty(): void
    {
        $this->assertSame([], $this->vacs->view(Factory::pet(Factory::owner())));
    }

    public function testExpiringRowsHaveTheExportColumnsInOrder(): void
    {
        $owner = Factory::owner([
            'first_name' => 'Anna', 'last_name' => Factory::unique('ColOwner'), 'street' => 'Kolomstraat', 'nr' => '7',
            'city' => 'Brugge', 'province' => 'West-Vlaanderen', 'zip' => '8000', 'mail' => 'col@example.test',
            'debts' => 12, 'last_bill' => '2026-01-02',
        ]);
        $petName = Factory::unique('ColPet');
        $pet = Factory::pet($owner, ['name' => $petName, 'type' => 1]);
        $product = Factory::product(['name' => 'ColProd', 'vaccin' => 1, 'vaccin_disease' => 'Rage']);
        Factory::vaccine($pet, ['product_id' => $product, 'redo' => $this->next('20'), 'created_at' => '2026-02-03 09:30:00', 'vet' => Fixtures::USER_VET2]);

        $row = $this->expiring()[$petName];

        $this->assertSame([
            'owner_id', 'first_name', 'last_name', 'street', 'nr', 'city', 'province', 'zip',
            'product_name', 'disease', 'injection_date', 'redo_date',
            'pet_name', 'pet_type', 'owner_mail', 'last_bill', 'debts', 'vet_name',
        ], array_keys($row));
        $this->assertSame((string) $owner, (string) $row['owner_id']);
        $this->assertSame('Anna', $row['first_name']);
        $this->assertSame('Kolomstraat', $row['street']);
        $this->assertSame('7', $row['nr']);
        $this->assertSame('Brugge', $row['city']);
        $this->assertSame('West-Vlaanderen', $row['province']);
        $this->assertSame('8000', $row['zip']);
        $this->assertSame('ColProd', $row['product_name']);
        $this->assertSame('Rage', $row['disease']);
        $this->assertSame('2026-02-03 09:30:00', $row['injection_date']);
        $this->assertSame($this->next('20'), $row['redo_date']);
        $this->assertSame('1', (string) $row['pet_type']);
        $this->assertSame('col@example.test', $row['owner_mail']);
        $this->assertSame('2026-01-02', $row['last_bill']);
        $this->assertEqualsWithDelta(12.0, (float) $row['debts'], 0.001);
        $this->assertSame($this->ci->db->where('id', Fixtures::USER_VET2)->get('users')->row()->first_name, $row['vet_name']);
    }

    public function testExpiringFiltersOnMonthPetStateReminderAndOwner(): void
    {
        $make = function (array $pet = [], array $vac = [], array $owner = [], ?string $redo = null): string {
            $name = Factory::unique('FilPet');
            Factory::vaccine(Factory::pet(Factory::owner($owner), $pet + ['name' => $name]), $vac + ['redo' => $redo ?? $this->next()]);

            return $name;
        };
        $ok = $make();
        $dead = $make(['death' => 1]);
        $lost = $make(['lost' => 1]);
        $transferred = $make(['transfered' => 1]);
        $off = $make([], ['no_rappel' => 1]);
        $disabled = $make([], [], ['disabled' => 1]);
        $last = $make([], [], [], date('Y-m-t', strtotime('first day of next month')));
        $first = $make([], [], [], $this->next('01'));
        $before = $make([], [], [], date('Y-m-t'));
        $after = $make([], [], [], date('Y-m-01', strtotime('first day of next month +1 month')));

        $rows = $this->expiring();

        foreach ([$ok, $last, $first] as $name) {
            $this->assertArrayHasKey($name, $rows);
        }
        foreach ([$dead, $lost, $transferred, $off, $disabled, $before, $after] as $name) {
            $this->assertArrayNotHasKey($name, $rows);
        }
    }

    public function testACompanionOwnerReplacesTheOwnerAndItsDisabledFlagDecides(): void
    {
        $enabled = Factory::owner(['last_name' => 'CompEnabled']);
        $disabled = Factory::owner(['last_name' => 'CompDisabled', 'disabled' => 1]);
        $plain = Factory::owner(['last_name' => 'PlainOwner']);

        $a = Factory::unique('CmPetA');   // owner disabled, companion enabled -> listed under the companion
        Factory::vaccine(Factory::pet($disabled, ['name' => $a, 'companion' => $enabled]), ['redo' => $this->next()]);
        $b = Factory::unique('CmPetB');   // owner enabled, companion disabled -> left out
        Factory::vaccine(Factory::pet($plain, ['name' => $b, 'companion' => $disabled]), ['redo' => $this->next()]);
        $c = Factory::unique('CmPetC');   // companion 0 means none: the owner is used
        Factory::vaccine(Factory::pet($plain, ['name' => $c, 'companion' => 0]), ['redo' => $this->next()]);

        $rows = $this->expiring();

        $this->assertSame('CompEnabled', $rows[$a]['last_name']);
        $this->assertSame((string) $enabled, (string) $rows[$a]['owner_id']);
        $this->assertArrayNotHasKey($b, $rows);
        $this->assertSame('PlainOwner', $rows[$c]['last_name']);
    }

    public function testExcludedProductsAreLeftOut(): void
    {
        $keep = Factory::product(['name' => 'ExProd Keep', 'vaccin' => 1]);
        $drop = Factory::product(['name' => 'ExProd Drop', 'vaccin' => 1]);
        $k = Factory::unique('ExPetK');
        $d = Factory::unique('ExPetD');
        $owner = Factory::owner();
        Factory::vaccine(Factory::pet($owner, ['name' => $k]), ['product_id' => $keep, 'redo' => $this->next()]);
        Factory::vaccine(Factory::pet($owner, ['name' => $d]), ['product_id' => $drop, 'redo' => $this->next()]);

        $rows = $this->expiring([(string) $drop]);

        $this->assertArrayHasKey($k, $rows);
        $this->assertArrayNotHasKey($d, $rows);
        $this->assertArrayHasKey($d, $this->expiring(), 'control: without exclusion it is there');
    }

    public function testRowsAreOrderedByReminderDate(): void
    {
        $owner = Factory::owner();
        $late = Factory::unique('OrdLate');
        $early = Factory::unique('OrdEarly');
        Factory::vaccine(Factory::pet($owner, ['name' => $late]), ['redo' => $this->next('25')]);
        Factory::vaccine(Factory::pet($owner, ['name' => $early]), ['redo' => $this->next('02')]);

        $names = array_column($this->vacs->get_expiring_vaccines($this->monthStart()), 'pet_name');

        $this->assertLessThan(array_search($late, $names, true), array_search($early, $names, true));
    }

    public function testProductListHasOneRowPerProductNameWithTheSameFilters(): void
    {
        $product = Factory::product(['name' => 'PList Prod', 'vaccin' => 1]);
        $off = Factory::product(['name' => 'PList Off', 'vaccin' => 1]);
        $owner = Factory::owner();
        Factory::vaccine(Factory::pet($owner), ['product_id' => $product, 'redo' => $this->next()]);
        Factory::vaccine(Factory::pet($owner), ['product_id' => $product, 'redo' => $this->next('20')]);
        Factory::vaccine(Factory::pet($owner), ['product_id' => $off, 'redo' => $this->next(), 'no_rappel' => 1]);

        $list = $this->vacs->get_expiring_vaccines_product_list($this->monthStart());
        $names = array_column($list, 'product_name');

        $this->assertSame(1, count(array_keys($names, 'PList Prod', true)), 'grouped by product name');
        $this->assertNotContains('PList Off', $names);
        $row = $list[array_search('PList Prod', $names, true)];
        $this->assertSame(['product_id', 'product_name'], array_keys($row));
        $this->assertSame((string) $product, (string) $row['product_id']);
    }

    public function testMartianInsertsAnExternalVaccine(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->ci->user = (object) ['id' => Fixtures::USER_VET];

        $this->vacs->martian($pet, ['product' => 'Ext X', 'redo' => '2030-02-02', 'no_rappel' => 0, 'created_at' => '2029-02-02', 'location' => 1]);

        $row = $this->ci->db->where('pet', $pet)->get('vaccine_pet')->row_array();
        $this->assertSame('Ext X', $row['product']);
        $this->assertSame('0', (string) $row['event_id']);
        $this->assertSame('2029-02-02', substr($row['created_at'], 0, 10));
        $this->assertSame((string) Fixtures::USER_VET, (string) $row['vet']);
    }
}
