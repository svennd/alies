<?php

declare(strict_types=1);

/**
 * T067 - Pets::export (history as PDF). Source: application/controllers/Pets.php, views/pets/export.php, views/export/pdf_pet_history.php.
 * GET shows the selection form; POST with submit streams a PDF attachment named export_{id}_{date}.pdf.
 */
final class PetExportTest extends HttpTestCase
{
    private function petWithHistory(): array
    {
        $owner = Factory::owner(['last_name' => 'Exportowner']);
        $pet = Factory::pet($owner, ['name' => 'Exportpet']);
        $first = Factory::event($pet, 1, 4, ['title' => 'Eerste export', 'anamnese' => 'Eerste verslag', 'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))]);
        $second = Factory::event($pet, 1, 4, ['title' => 'Tweede export', 'anamnese' => 'Tweede verslag']);
        Factory::eventProduct($second, Fixtures::PRODUCT_TIERED, 1.0);
        Factory::eventProcedure($second);
        Factory::event($pet, 1, 4, ['title' => 'Verborgen export', 'no_history' => 1]);

        return [$pet, $first, $second];
    }

    public function testTheFormListsTheVisibleEventsWithCheckboxes(): void
    {
        [$pet, $first, $second] = $this->petWithHistory();
        $this->loginAs('vet');

        $page = $this->get('pets/export/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Exportowner');
        $this->assertSee($page, 'Exportpet');
        $this->assertSee($page, 'Eerste export');
        $this->assertSee($page, 'Tweede export');
        $this->assertDontSee($page, 'Verborgen export');
        $body = (string) $page->getBody();
        $this->assertStringContainsString('name="history_to_take[]" value="' . $first . '"', $body);
        $this->assertStringContainsString('name="history_to_take[]" value="' . $second . '"', $body);
        $this->assertStringContainsString('name="submit"', $body);
    }

    public function testPostingTheSelectionStreamsAPdfAttachment(): void
    {
        [$pet, $first, $second] = $this->petWithHistory();
        $this->loginAs('vet');

        $response = $this->post('pets/export/' . $pet, ['submit' => '1', 'history_to_take' => [(string) $second]]);

        $this->assertDownload($response, 'application/pdf', 'export_' . $pet . '_');
        $this->assertStringContainsString('.pdf', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
        $this->assertGreaterThan(1000, strlen((string) $response->getBody()));
    }

    public function testAPetWithoutHistoryStillGivesAPdf(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');

        $page = $this->get('pets/export/' . $pet);
        $response = $this->post('pets/export/' . $pet, ['submit' => '1']);

        $this->assertStatus($page, 200);
        $this->assertDownload($response, 'application/pdf', 'export_' . $pet . '_');
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
    }

    public function testAMissingOrDeletedPetRedirectsHome(): void
    {
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('pets/export/987654'), '');
        $this->assertRedirectsTo($this->get('pets/export/' . Fixtures::PET_DELETED), '');
        $this->assertStatus($this->get('pets/export/' . Fixtures::PET_REX), 200, 'positive control');
    }
}
