<?php

declare(strict_types=1);

final class LabPendingPreviewTest extends PHPUnit\Framework\TestCase
{
	private CI_Controller $ci;

	protected function setUp(): void
	{
		parent::setUp();
		$this->ci = get_instance();
		$this->ci->load->library('lab_pending_preview');
	}

	public function testBuildsStructuredCurrentPayloadPreview(): void
	{
		$result = $this->ci->lab_pending_preview->build($this->pending('lmscan', json_encode([
			'serial_number' => 'SER-1', 'sample_id' => 'unknown',
			'test_end_time' => '2026-08-27 12:00:00', 'project_number' => 'P1',
			'project_name' => 'GLU', 'sample_type' => 'blood', 'result' => '5.5',
			'unit' => 'mmol/L', 'ref_low' => '4', 'ref_high' => '7',
		])));

		$this->assertNull($result['warning']);
		$this->assertSame('GLU', $result['preview']['results'][0]['code']);
		$this->assertSame('5.5', (string) $result['preview']['results'][0]['value']);
		$this->assertStringContainsString("\n", $result['raw_json']);
	}

	public function testBuildsLegacyPayloadPreview(): void
	{
		$pending = $this->pending('medilab', json_encode([
			'legacy_table' => 'lab',
			'lab' => ['id' => 7, 'lab_id' => 8, 'source' => 'legacy', 'lab_date' => '2026-08-20'],
			'details' => [[
				'lab_code' => 1, 'lab_code_text' => 'WBC', 'value' => '4.5',
				'string_value' => '', 'unit' => 'G/L', 'lower_limit' => '2', 'upper_limit' => '8',
			]],
		]));
		$pending['reason'] = 'legacy_missing_pet';
		$result = $this->ci->lab_pending_preview->build($pending);

		$this->assertNull($result['warning']);
		$this->assertSame('WBC', $result['preview']['results'][0]['code']);
		$this->assertSame('legacy', $result['preview']['source']);
	}

	public function testPreservesPartialDataForUnsupportedPayload(): void
	{
		$result = $this->ci->lab_pending_preview->build($this->pending('unknown', json_encode([
			'sample_date' => '2026-08-01',
			'results' => [['lab_name' => 'ALT', 'value' => '12', 'unit' => 'U/L', 'min' => 1, 'max' => 20]],
		])));

		$this->assertNotNull($result['warning']);
		$this->assertSame('ALT', $result['preview']['results'][0]['code']);
		$this->assertSame('2026-08-01', $result['preview']['sample_date']);
	}

	public function testMalformedPayloadKeepsLiteralContent(): void
	{
		$result = $this->ci->lab_pending_preview->build($this->pending('lmscan', '{"unsafe":"<script>"'));

		$this->assertNotNull($result['warning']);
		$this->assertSame('{"unsafe":"<script>"', $result['raw_json']);
		$this->assertSame([], $result['preview']['results']);
	}

	private function pending(string $device, string $raw): array
	{
		return [
			'id' => 11, 'device' => $device, 'source' => null, 'source_id' => 'source-11',
			'raw_payload' => $raw, 'identifiers' => '{"owner_name":"Owner"}', 'reason' => 'pet_not_found',
		];
	}
}
