<?php

declare(strict_types=1);

final class FinalizedEventViewTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ci->lang->load('vet', 'dutch');
    }

    public function testSummaryUsesEventDateLinksAndExplicitCostStates(): void
    {
        $data = $this->baseData();
        $data['billing_info'] = ['total_brut' => '123.45'];
        $billed = $this->ci->load->view('event/report/block_report_header', $data, true);
        $data['billing_info'] = false;
        $unbilled = $this->ci->load->view('event/report/block_report_header', $data, true);

        $this->assertStringContainsString('owners/detail/22', $billed);
        $this->assertStringContainsString('pets/fiche/11', $billed);
        $this->assertStringContainsString('Test Client', $billed);
        $this->assertStringContainsString('Test Pet', $billed);
        $this->assertStringContainsString('123,45', $billed);
        $this->assertStringContainsString('Kost niet beschikbaar', $unbilled);
        $this->assertStringContainsString('flex-nowrap', $billed);
    }

    public function testAttachmentsPreviewSafeImagesAndUseFileActionsForOthers(): void
    {
        $html = $this->ci->load->view('event/report/block_attachments', [
            'event_uploads' => [
                ['id' => 1, 'mime' => 'image/jpeg', 'filename' => 'photo.jpg'],
                ['id' => 2, 'mime' => 'image/jpeg', 'filename' => 'old_fin.jpeg'],
                ['id' => 3, 'mime' => 'application/pdf', 'filename' => 'report.pdf'],
                ['id' => 4, 'mime' => 'image/svg+xml', 'filename' => 'unsafe.svg'],
            ],
        ], true);

        $this->assertStringContainsString('files/preview/1', $html);
        $this->assertStringContainsString('files/preview/2', $html);
        $this->assertStringNotContainsString('files/preview/3', $html);
        $this->assertStringNotContainsString('files/preview/4', $html);
        $this->assertStringContainsString('files/get_file/3', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    public function testLabPanelEmbedsMultipleResultsAndProvidesLinkAndUnlinkControls(): void
    {
        $labs = [$this->lab(10, 'HIGH', true), $this->lab(9, 'normal', false)];
        $html = $this->ci->load->view('event/report/block_lab_results', array_merge($this->baseData(), [
            'event_id' => 77,
            'linked_labs' => $labs,
            'linkable_labs' => [['id' => 8, 'sample_date' => '2026-08-20 10:00:00', 'created_at' => '2026-08-20 10:00:00', 'device' => 'ms4s2', 'source' => null]],
        ]), true);

        $this->assertStringContainsString('lab/detail/10', $html);
        $this->assertStringContainsString('lab/detail/9', $html);
        $this->assertStringContainsString('HIGH', $html);
        $this->assertStringContainsString('event-lab-result-out', $html);
        $this->assertStringContainsString('event-lab-results-10" class="collapse show', $html);
        $this->assertStringContainsString('event-lab-results-9" class="collapse"', $html);
        $this->assertStringContainsString('form="event-lab-link-form"', $html);
        $this->assertStringContainsString('form="event-lab-unlink-10"', $html);
        $this->assertStringContainsString('confirm(', $html);
    }

    public function testLabPanelShowsAnEmptyStateWithoutLinkedLabs(): void
    {
        $html = $this->ci->load->view('event/report/block_lab_results', array_merge($this->baseData(), [
            'event_id' => 77,
            'linked_labs' => [],
            'linkable_labs' => [],
        ]), true);

        $this->assertStringContainsString('Er zijn geen labresultaten aan dit event gekoppeld.', $html);
        $this->assertStringNotContainsString('<article', $html);
    }

    public function testFinalizedCompositionHasNoLegacyTabsOrDrawingReferences(): void
    {
        $main = file_get_contents(APPPATH . 'views/event/main_report.php');
        $report = file_get_contents(APPPATH . 'views/event/report/block_report.php');

        $this->assertStringNotContainsString('nav-tabs', $main);
        $this->assertStringNotContainsString('block_drawing', $main);
        $this->assertStringNotContainsString('block_closed_bill', $report);
        $this->assertStringContainsString("include 'block_attachments.php'", $report);
        $this->assertStringContainsString("include 'block_lab_results.php'", $report);
    }

    public function testEventLanguageKeysExistInEnglishAndDutch(): void
    {
        $keys = ['event_labs', 'event_lab_link', 'event_lab_choose', 'event_lab_empty', 'event_lab_open', 'event_lab_unlink', 'event_lab_unlink_confirm', 'event_lab_linked', 'event_lab_unlinked', 'event_lab_link_invalid', 'event_lab_unlink_invalid', 'event_lab_post_only', 'cost_unavailable'];

        foreach (['english', 'dutch'] as $language) {
            $translations = (static function (string $path): array {
                $lang = [];
                require $path;
                return $lang;
            })(APPPATH . 'language/' . $language . '/vet_lang.php');

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $translations);
                $this->assertNotSame('', trim($translations[$key]));
            }
        }
    }

    private function baseData(): array
    {
        return [
            'owner' => ['id' => 22, 'last_name' => 'Test Client', 'first_name' => 'Jane'],
            'pet' => ['id' => 11, 'name' => 'Test Pet'],
            'event_info' => ['created_at' => '2026-08-21 09:30:00'],
            'user' => (object) ['user_date' => 'd/m/Y'],
        ];
    }

    private function lab(int $id, string $value, bool $outside): array
    {
        return [
            'id' => $id,
            'sample_date' => '2026-08-2' . ($id === 10 ? '2' : '1') . ' 10:00:00',
            'created_at' => '2026-08-20 10:00:00',
            'device' => 'medilab',
            'source' => 'api',
            'results' => [[
                'code' => 'ALT',
                'value' => $value,
                'limit' => '1 - 5',
                'unit' => 'U/L',
                'is_out' => $outside,
            ]],
        ];
    }
}
