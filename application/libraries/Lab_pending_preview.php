<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lab_pending_preview
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('lab_device_adapter_factory');
		$this->CI->load->library('lab_legacy_pending_mapper');
		$this->CI->load->library('lab_result_presenter');
	}

	public function build(array $pending): array
	{
		$raw = (string) ($pending['raw_payload'] ?? '');
		$payload = json_decode($raw, true);
		$valid_json = json_last_error() === JSON_ERROR_NONE && is_array($payload);
		$preview = $this->fallback($pending, $valid_json ? $payload : array());
		$warning = null;

		if ($valid_json) {
			try {
				if (($pending['reason'] ?? null) === 'legacy_missing_pet') {
					$parsed = $this->CI->lab_legacy_pending_mapper->map($payload);
				} else {
					$adapter = $this->CI->lab_device_adapter_factory->create($pending['device'] ?? null);
					if (!$adapter) {
						throw new UnexpectedValueException('Unsupported lab device.');
					}
					$parsed = $adapter->parse($payload);
				}
				$preview = array_replace($preview, $parsed);
			} catch (Throwable $error) {
				$warning = 'The payload could only be shown partially because its structured report could not be parsed.';
				log_message('error', 'Pending lab preview failed for #' . (int) ($pending['id'] ?? 0) . ': ' . $error->getMessage());
			}
		} else {
			$warning = 'The retained payload is not valid JSON, so no structured report preview is available.';
		}

		$preview['device'] = $preview['device'] ?? ($pending['device'] ?? null);
		$preview['source'] = $preview['source'] ?? ($pending['source'] ?? null);
		$preview['source_id'] = $preview['source_id'] ?? ($pending['source_id'] ?? null);
		$preview['results'] = $this->present_results((array) ($preview['results'] ?? array()));
		$preview['plots'] = (array) ($preview['plots'] ?? array());
		$preview['metadata'] = (array) ($preview['metadata'] ?? array());

		return array(
			'preview' => $preview,
			'warning' => $warning,
			'raw_json' => $valid_json
				? (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
				: $raw,
		);
	}

	private function fallback(array $pending, array $payload): array
	{
		$identifiers = json_decode((string) ($pending['identifiers'] ?? ''), true);
		return array(
			'device' => $pending['device'] ?? null,
			'source' => $pending['source'] ?? null,
			'source_id' => $pending['source_id'] ?? null,
			'sample_date' => $payload['sample_date'] ?? $payload['test_end_time'] ?? null,
			'software_version' => $payload['software_version'] ?? null,
			'identifiers' => is_array($identifiers) ? $identifiers : array(),
			'metadata' => array(),
			'results' => $this->generic_results($payload),
			'plots' => is_array($payload['plots'] ?? null) ? $payload['plots'] : array(),
		);
	}

	private function generic_results(array $payload): array
	{
		$rows = is_array($payload['results'] ?? null) ? $payload['results'] : array();
		$results = array();
		foreach ($rows as $row) {
			if (!is_array($row)) continue;
			$results[] = array(
				'code' => $row['code'] ?? $row['lab_name'] ?? $row['lab_code'] ?? $row['N'] ?? 'UNKNOWN',
				'value' => $row['value'] ?? $row['text_value'] ?? ($row['I'][0] ?? null),
				'unit' => $row['unit'] ?? ($row['I'][3] ?? null),
				'ref_min' => $row['ref_min'] ?? $row['min'] ?? ($row['I'][1] ?? null),
				'ref_max' => $row['ref_max'] ?? $row['max'] ?? ($row['I'][2] ?? null),
			);
		}
		return $results;
	}

	private function present_results(array $results): array
	{
		$normalized = array();
		foreach ($results as $result) {
			if (!is_array($result)) continue;
			$value = $result['value'] ?? $result['value_num'] ?? $result['value_text'] ?? null;
			$normalized[] = array(
				'code' => (string) ($result['code'] ?? 'UNKNOWN'),
				'value_num' => is_numeric($value) ? (float) $value : null,
				'value_text' => is_numeric($value) ? null : (string) $value,
				'unit' => $result['unit'] ?? null,
				'ref_min' => is_numeric($result['ref_min'] ?? null) ? (float) $result['ref_min'] : null,
				'ref_max' => is_numeric($result['ref_max'] ?? null) ? (float) $result['ref_max'] : null,
			);
		}
		return $this->CI->lab_result_presenter->normalize_many($normalized);
	}
}
