<?php

declare(strict_types=1);

final class LabPendingViewTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ci->lang->load('vet', 'dutch');
    }

    public function testActiveQueueShowsSafeHintsAndLinksToInspectionWithoutPayloadOrMutationForms(): void
    {
        $html = $this->ci->load->view('lab/pending', [
            'pending_results' => [[
                'id' => 44, 'device' => 'medilab', 'source' => 'remote', 'source_id' => 'S-22',
                'reason' => 'pet_not_found', 'created_at' => '2026-08-27 10:00:00',
				'last_received_at' => '2026-08-27 11:00:00',
                'identifiers' => ['owner_name' => 'Example Owner', 'pet_name' => 'Fido'],
            ]],
            'can_manage_pending' => true,
            'pending_message' => 'Recovered',
            'pending_message_type' => 'success',
        ], true);

        $this->assertStringContainsString('Example Owner', $html);
        $this->assertStringContainsString('Fido', $html);
        $this->assertStringContainsString('medilab', $html);
        $this->assertStringContainsString('S-22', $html);
		$this->assertStringContainsString('2026-08-27 11:00:00', $html);
		$this->assertStringNotContainsString('2026-08-27 10:00:00', $html);
		$this->assertStringContainsString('lab/pending_detail/44', $html);
		$this->assertStringNotContainsString('lab/recover_pending/44', $html);
		$this->assertStringNotContainsString('name="pet_id"', $html);
		$this->assertStringNotContainsString('lab/delete_pending/44', $html);
        $this->assertStringNotContainsString('raw_payload', $html);
    }

    public function testEmptyAndQueueInspectionStatesDoNotRenderMutationForms(): void
    {
        $empty = $this->ci->load->view('lab/pending', [
            'pending_results' => [], 'can_manage_pending' => true,
            'pending_message' => null, 'pending_message_type' => null,
        ], true);
        $readOnly = $this->ci->load->view('lab/pending', [
            'pending_results' => [[
                'id' => 45, 'device' => 'legacy', 'source' => null, 'source_id' => null,
                'reason' => 'legacy_missing_pet', 'created_at' => '2020-01-01 00:00:00',
                'identifiers' => ['legacy_lab_id' => '99'],
            ]],
            'can_manage_pending' => false,
            'pending_message' => null, 'pending_message_type' => null,
        ], true);

		$this->assertStringContainsString('Alle labresultaten zijn toegewezen.', $empty);
        $this->assertStringContainsString('legacy_missing_pet', $readOnly);
		$this->assertStringContainsString('lab/pending_detail/45', $readOnly);
		$this->assertStringNotContainsString('lab/recover_pending/45', $readOnly);
		$this->assertStringNotContainsString('lab/delete_pending/45', $readOnly);
    }

    public function testNavigationAndLabIndexExposePendingQueueAndDetailHasNoDeadControls(): void
    {
        $vetHeader = file_get_contents(APPPATH . 'views/blocks/header_vet.php');
        $adminHeader = file_get_contents(APPPATH . 'views/blocks/header_admin.php');
        $index = file_get_contents(APPPATH . 'views/lab/index.php');
        $detail = file_get_contents(APPPATH . 'views/lab/detail.php');
        $controller = file_get_contents(APPPATH . 'libraries/Vet_Controller.php');

        $this->assertStringContainsString("base_url('lab/pending')", $vetHeader);
        $this->assertStringContainsString("base_url('lab/pending')", $adminHeader);
        $this->assertStringContainsString("base_url('lab/pending')", $index);
        $this->assertStringContainsString('count_active()', file_get_contents(APPPATH . 'models/LabReportPending_model.php'));
        $this->assertStringContainsString('count_recent()', $controller);
        $this->assertStringNotContainsString('reset_lab_link', $detail);
		$this->assertStringContainsString('lab/reassign/', $detail);
		$this->assertStringContainsString('name="owner_id"', $detail);
		$this->assertStringContainsString('name="pet_id"', $detail);
        $this->assertStringContainsString("base_url('lab/print/'", $detail);
        $this->assertStringContainsString('showMore', $detail);
    }

    public function testPendingLanguageKeysExistInEnglishAndDutch(): void
    {
        $keys = [
            'lab_pending_title', 'lab_pending_action', 'lab_pending_identifiers', 'lab_pending_empty',
            'lab_pending_select_owner', 'lab_pending_select_pet', 'lab_pending_recover', 'lab_pending_delete',
            'lab_pending_delete_confirm', 'lab_pending_read_only', 'lab_pending_recovered',
            'lab_pending_deleted', 'lab_pending_post_only', 'lab_pending_forbidden', 'lab_all_results',
			'lab_pending_inspect', 'lab_pending_back', 'lab_pending_results', 'lab_pending_no_results',
            'lab_pending_metadata', 'lab_pending_plots', 'lab_pending_assign', 'lab_pending_raw_json',
			'lab_first_received', 'lab_last_received',
			'lab_reassign_title', 'lab_reassign_warning', 'lab_reassign_confirm', 'lab_reassign_submit',
			'lab_reassigned', 'lab_reassign_noop',
        ];
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
