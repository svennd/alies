<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T224 - External ("martian") vaccine, vaccine/add_martian_vaccine/{pet}.
 * Source: application/controllers/Vaccine.php, Vaccine_model::martian
 *
 * A post stores a vaccine_pet row with the free-text product, event_id 0, created_at = the posted injection date,
 * the logged in vet and the vet's location, then redirects to vaccine/fiche/{pet}. A GET shows the form.
 */
final class MartianVaccineTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    public function testTheFormIsShownOnGet(): void
    {
        $name = Factory::unique('MartianPet');
        $pet = Factory::pet(Factory::owner(), ['name' => $name]);

        $page = $this->get('vaccine/add_martian_vaccine/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertSee($page, $name);
    }

    public function testAPostInsertsTheVaccineWithTheGivenValues(): void
    {
        $pet = Factory::pet(Factory::owner());
        $created = date('Y-m-d', strtotime('-20 days'));
        $redo = date('Y-m-d', strtotime('+345 days'));

        $response = $this->post('vaccine/add_martian_vaccine/' . $pet, [
            'submit' => 1, 'vaccine' => 'Rabies ext', 'date_redo' => $redo, 'no_rappel' => 'on', 'created_at' => $created,
        ]);

        $this->assertRedirectsTo($response, 'vaccine/fiche/' . $pet);
        $rows = $this->rows('SELECT * FROM vaccine_pet WHERE pet = ?', [$pet]);
        $this->assertCount(1, $rows);
        $this->assertSame('Rabies ext', $rows[0]['product']);
        $this->assertSame(0, (int) $rows[0]['event_id']);
        $this->assertSame($redo, $rows[0]['redo']);
        $this->assertSame(0, (int) $rows[0]['no_rappel']);
        $this->assertSame($created, substr($rows[0]['created_at'], 0, 10));
        $this->assertSame(Fixtures::USER_VET, (int) $rows[0]['vet']);
        $this->assertSame(1, (int) $rows[0]['location']);
        $this->assertSame(1, $this->logCount('add_martian_vaccine'));
    }

    public function testWithoutTheCheckboxNoRappelIsOne(): void
    {
        $pet = Factory::pet(Factory::owner());

        $this->post('vaccine/add_martian_vaccine/' . $pet, [
            'submit' => 1, 'vaccine' => 'Rabies off', 'date_redo' => date('Y-m-d', strtotime('+1 year')), 'created_at' => date('Y-m-d'),
        ]);

        $this->assertSame(1, (int) $this->row('SELECT no_rappel FROM vaccine_pet WHERE pet = ?', [$pet])['no_rappel']);
    }

    public function testWithoutSubmitNothingIsStored(): void
    {
        $pet = Factory::pet(Factory::owner());

        $this->post('vaccine/add_martian_vaccine/' . $pet, ['vaccine' => 'X', 'date_redo' => '2030-01-01', 'created_at' => '2029-01-01']);

        $this->assertRowCount(0, 'vaccine_pet', ['pet' => $pet]);
    }

    #[Group('known-bug')]
    public function testAMartianVaccineHasNoProductIdSoItIsNotInTheExpiryList(): void
    {
        $name = Factory::unique('MartianExp');
        $pet = Factory::pet(Factory::owner(['last_name' => Factory::unique('MartianOwner')]), ['name' => $name]);
        $this->post('vaccine/add_martian_vaccine/' . $pet, [
            'submit' => 1, 'vaccine' => 'Rabies ext', 'no_rappel' => 'on', 'created_at' => date('Y-m-d', strtotime('-11 months')),
            'date_redo' => date('Y-m-', strtotime('first day of next month')) . '10',
        ]);

        // KNOWN BUG: product_id stays unset (the column default, 0), so the JOIN on products drops it -- decide in CI4 whether to keep
        $row = $this->row('SELECT * FROM vaccine_pet WHERE pet = ?', [$pet]);
        $this->assertContains($row['product_id'], [null, '0', 0]);
        $this->assertDontSee($this->get('vaccine/index/1'), $name);

        // control: the same pet with a catalogue vaccine does show up
        Factory::vaccine($pet, ['redo' => date('Y-m-', strtotime('first day of next month')) . '10']);
        $this->assertSee($this->get('vaccine/index/1'), $name);
    }
}
