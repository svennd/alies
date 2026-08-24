<?php

declare(strict_types=1);

final class PetMedicalHistoryViewTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ci->lang->load('vet', 'dutch');
    }

    public function testLinkedLabsRenderAsCountedAccessibleFragmentAction(): void
    {
        $html = $this->renderHistory(2, 0);

        $this->assertStringContainsString('events/event/77#event-lab-results', $html);
        $this->assertStringContainsString('class="fas fa-flask"', $html);
        $this->assertStringContainsString('<span class="sr-only">Labresultaten:</span>', $html);
        $this->assertMatchesRegularExpression('/Labresultaten:<\/span>\s*2/', $html);
        $this->assertStringNotContainsString('pet-history__actions pet-history__actions--no-mobile', $html);
    }

    public function testNoLinkedLabsRenderNoLabAction(): void
    {
        $html = $this->renderHistory(0, 0);

        $this->assertStringNotContainsString('#event-lab-results', $html);
        $this->assertStringNotContainsString('class="fas fa-flask"', $html);
        $this->assertStringContainsString('pet-history__actions pet-history__actions--no-mobile', $html);
    }

    public function testAttachmentOnlyActionStillRemainsAvailableOnPhones(): void
    {
        $html = $this->renderHistory(0, 1);

        $this->assertStringContainsString('events/event/77#files', $html);
        $this->assertStringNotContainsString('pet-history__actions pet-history__actions--no-mobile', $html);
    }

    public function testFinalizedLabPanelProvidesExactlyOneFragmentTarget(): void
    {
        $html = $this->ci->load->view('event/report/block_lab_results', [
            'event_id' => 77,
            'linked_labs' => [[
                'id' => 501,
                'sample_date' => '2026-08-24 09:00:00',
                'created_at' => '2026-08-24 09:00:00',
                'device' => 'phpunit',
                'source' => 'phpunit',
                'results' => [[
                    'code' => 'ALT',
                    'value' => '12.00',
                    'limit' => '1.00 - 5.00',
                    'unit' => 'U/L',
                    'is_out' => true,
                ]],
            ]],
            'linkable_labs' => [],
            'consumables' => [],
            'procedures_d' => [],
            'user' => (object) ['user_date' => 'd/m/Y'],
        ], true);

        $this->assertSame(1, substr_count($html, 'id="event-lab-results"'));
        $this->assertStringContainsString('lab/detail/501', $html);
        $this->assertStringContainsString('12.00', $html);
        $this->assertStringContainsString('event-lab-result-out', $html);
    }

    private function renderHistory(int $labCount, int $uploadCount): string
    {
        return $this->ci->load->view('pets/fiche/block_history', [
            'pet' => ['id' => 11],
            'user' => (object) ['user_date' => 'd/m/Y'],
            'pet_history' => [[
                'id' => 77,
                'type' => DISEASE,
                'title' => 'Consultation',
                'anamnese' => '',
                'created_at' => '2026-08-24 10:00:00',
                'report' => REPORT_DONE,
                'location_name' => 'Clinic',
                'upload_count' => $uploadCount,
                'lab_count' => $labCount,
                'veterinarians' => [],
                'products' => [],
                'procedures' => [],
            ]],
        ], true);
    }
}
