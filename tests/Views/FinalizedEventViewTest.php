<?php

declare(strict_types=1);

final class FinalizedEventViewTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ci->lang->load('vet', 'dutch');
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
}
