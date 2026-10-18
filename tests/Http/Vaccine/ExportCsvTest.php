<?php

declare(strict_types=1);

/**
 * T221 - Vaccine CSV export (vaccine/export/{month offset}).
 * Source: application/controllers/Vaccine.php, views/vaccine/export.php, views/vaccine/export_process.php
 *
 * GET shows the products that can be excluded. POST with `submit` returns an attachment
 * `vaccines_{M_Y}.csv` and writes the `downloaded_vaccine_list` log row. The CSV columns are a contract with
 * external tooling: header and data rows both follow the "minimal" list
 * last_name, street, nr, city, zip, disease, injection_date, pet_name, pet_type (pet type as lower-case word,
 * dates in the posted strftime format). The product name itself is NOT a column, so the tests tell products
 * apart by their disease text.
 */
final class ExportCsvTest extends HttpTestCase
{
    private const HEADER = '"last_name","street","nr","city","zip","disease","injection_date","pet_name","pet_type"';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    /** @return array{owner: string, pet: string, product: int, productName: string, disease: string, injected: string} */
    private function vaccine(array $productOver = [], array $vaccineOver = []): array
    {
        $disease = Factory::unique('Disease');
        $productName = Factory::unique('ExpProd');
        $product = Factory::product($productOver + ['name' => $productName, 'vaccin' => 1, 'vaccin_disease' => $disease]);
        $owner = Factory::unique('ExpOwner');
        $pet = Factory::unique('ExpPet');
        $petId = Factory::pet(Factory::owner(['last_name' => $owner, 'street' => 'Exportlaan', 'nr' => '12b', 'city' => 'Gent', 'zip' => '9000']), ['name' => $pet, 'type' => 0]);
        $injected = date('Y-m-d', strtotime('-11 months'));
        Factory::vaccine($petId, $vaccineOver + [
            'product_id' => $product,
            'created_at' => $injected . ' 10:00:00',
            'redo' => date('Y-m-', strtotime('first day of next month')) . '15',
        ]);

        return ['owner' => $owner, 'pet' => $pet, 'product' => $product, 'productName' => $productName, 'disease' => $disease, 'injected' => $injected];
    }

    public function testTheExportPageListsTheProductsThatCanBeExcluded(): void
    {
        $v = $this->vaccine();

        $page = $this->get('vaccine/export/1');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $v['productName']);
        $this->assertStringContainsString('value="' . $v['product'] . '"', (string) $page->getBody());
    }

    public function testPostingReturnsTheCsvAttachmentAndLogsIt(): void
    {
        $v = $this->vaccine();
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('vaccine/export/1', ['submit' => 1, 'date_format' => '%d/%m/%Y']);

        $this->assertDownload($response, 'application/csv', 'vaccines_' . date('M_Y', strtotime('first day of next month')) . '.csv');
        $this->assertSame(1, $this->logCount('downloaded_vaccine_list'));
        $this->assertStringContainsString($v['disease'], (string) $response->getBody());
    }

    public function testTheHeaderAndADataRowKeepTheContractColumnOrder(): void
    {
        $v = $this->vaccine();

        $response = $this->post('vaccine/export/1', ['submit' => 1, 'date_format' => '%d/%m/%Y']);

        $lines = explode("\n", rtrim((string) $response->getBody()));
        $this->assertSame(self::HEADER, $lines[0]);

        $expected = '"' . implode('","', [
            $v['owner'], 'Exportlaan', '12b', 'Gent', '9000', $v['disease'],
            date('d/m/Y', strtotime($v['injected'])), $v['pet'], 'hond',
        ]) . '"';
        $this->assertContains($expected, $lines, 'the data row of the new vaccine');
    }

    public function testAnExcludedProductIsLeftOutAndTheOthersStay(): void
    {
        $keep = $this->vaccine();
        $drop = $this->vaccine();

        $response = $this->post('vaccine/export/1', [
            'submit' => 1, 'date_format' => '%d/%m/%Y', 'excluded_products' => [$drop['product']],
        ]);

        $body = (string) $response->getBody();
        $this->assertStringContainsString($keep['disease'], $body);
        $this->assertStringContainsString($keep['owner'], $body);
        $this->assertStringNotContainsString($drop['disease'], $body);
        $this->assertStringNotContainsString($drop['owner'], $body);
    }

    public function testAVaccineWithoutReminderIsNotExported(): void
    {
        $off = $this->vaccine([], ['no_rappel' => 1]);

        $body = (string) $this->post('vaccine/export/1', ['submit' => 1, 'date_format' => '%d/%m/%Y'])->getBody();

        $this->assertStringNotContainsString($off['disease'], $body);
    }

    public function testOffsetsOfThreeOrMoreAreForAdminsOnly(): void
    {
        $this->db()->exec('DELETE FROM log');

        $this->assertRedirectsTo($this->post('vaccine/export/3', ['submit' => 1, 'date_format' => '%d/%m/%Y']), '');
        $this->assertSame(1, $this->logCount('boundary_vaccine_download_attack'));
        $this->assertSame(0, $this->logCount('downloaded_vaccine_list'));

        $this->cookies->clear();
        $this->loginAs('admin');
        $response = $this->post('vaccine/export/3', ['submit' => 1, 'date_format' => '%d/%m/%Y']);
        $this->assertDownload($response, 'application/csv', 'vaccines_');
        $this->assertStringStartsWith(self::HEADER, (string) $response->getBody());
    }
}
