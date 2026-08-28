<?php

declare(strict_types=1);

final class LabPendingDetailViewTest extends CodeIgniterDatabaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->ci->lang->load('vet', 'dutch');
	}

	public function testDetailShowsPreviewTwoStepAssignmentDeleteAndCollapsedEscapedRawPayload(): void
	{
		$html = $this->ci->load->view('lab/pending_detail', [
			'pending' => ['id' => 44, 'device' => 'lmscan', 'source' => 'remote', 'source_id' => 'S-44', 'reason' => 'pet_not_found', 'created_at' => '2026-08-27 10:00:00'],
			'identifiers' => ['owner_name' => 'Owner', 'pet_name' => 'Fido'],
			'preview' => [
				'device' => 'lmscan', 'source' => 'remote', 'source_id' => 'S-44', 'sample_date' => '2026-08-27', 'software_version' => '1.0',
				'results' => [['code' => 'GLU', 'value' => '5.5', 'unit' => 'mmol/L', 'limit' => '4 - 7', 'draw_plot' => true, 'pct' => 50, 'is_out' => false]],
				'metadata' => ['sample_type' => 'blood'], 'plots' => ['curve' => [1, 2, 3]],
			],
			'preview_warning' => null,
			'raw_json' => '{"unsafe":"<script>alert(1)</script>"}',
			'pending_message' => null, 'pending_message_type' => null,
		], true);

		$this->assertStringContainsString('GLU', $html);
		$this->assertStringContainsString('mmol/L', $html);
		$this->assertStringContainsString('sample_type', $html);
		$this->assertStringContainsString('name="owner_id"', $html);
		$this->assertStringContainsString('name="pet_id"', $html);
		$this->assertStringContainsString('lab\\/search_owners', $html);
		$this->assertStringContainsString('lab\\/search_pets', $html);
		$this->assertStringContainsString("pet.val(null).trigger('change')", $html);
		$this->assertStringContainsString('lab/recover_pending/44', $html);
		$this->assertStringContainsString('lab/delete_pending/44', $html);
		$this->assertStringContainsString('<details class="mt-4">', $html);
		$this->assertStringNotContainsString('<script>alert(1)</script>', $html);
		$this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
	}

	public function testPartialPreviewWarningStillShowsRawPayloadAndAssignment(): void
	{
		$html = $this->ci->load->view('lab/pending_detail', [
			'pending' => ['id' => 45, 'device' => 'unknown', 'source' => null, 'source_id' => null, 'reason' => 'pet_not_found', 'created_at' => '2026-08-27 10:00:00'],
			'identifiers' => [],
			'preview' => ['device' => 'unknown', 'source' => null, 'source_id' => null, 'sample_date' => null, 'software_version' => null, 'results' => [], 'metadata' => [], 'plots' => []],
			'preview_warning' => 'Could not parse this payload.', 'raw_json' => '{broken',
			'pending_message' => null, 'pending_message_type' => null,
		], true);

		$this->assertStringContainsString('Could not parse this payload.', $html);
		$this->assertStringContainsString('{broken', $html);
		$this->assertStringContainsString('name="owner_id"', $html);
	}
}
