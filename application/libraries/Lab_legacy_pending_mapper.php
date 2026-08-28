<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lab_legacy_pending_mapper
{
	public function map(array $payload): array
	{
		if (($payload['legacy_table'] ?? null) !== 'lab' || !is_array($payload['lab'] ?? null)) {
			throw new InvalidArgumentException('Unsupported legacy lab payload.');
		}

		$lab = $payload['lab'];
		$details = is_array($payload['details'] ?? null) ? $payload['details'] : array();
		$results = array();
		$detail_comments = array();

		foreach ($details as $detail) {
			if (!is_array($detail)) continue;
			$value = $this->legacy_value($detail);
			if (is_string($value) && strcasecmp($value, 'niet medegedeeld') === 0) continue;

			$code = $this->null_if_empty($detail['lab_code_text'] ?? null);
			if ($code === null || $code === '---') $code = (string) ($detail['lab_code'] ?? 'UNKNOWN');
			$results[] = array(
				'code' => $code,
				'value' => $value,
				'unit' => $this->null_if_empty($detail['unit'] ?? null),
				'ref_min' => $this->decimal_or_null($detail['lower_limit'] ?? null),
				'ref_max' => $this->decimal_or_null($detail['upper_limit'] ?? null),
			);

			$comment = $this->null_if_empty($detail['comment'] ?? null);
			if ($comment !== null && $comment !== $this->null_if_empty($detail['string_value'] ?? null)) {
				$detail_comments[] = array('code' => $code, 'comment' => $comment);
			}
		}

		$metadata = array(
			'legacy_lab_table_id' => (int) ($lab['id'] ?? 0),
			'legacy_lab_id' => (int) ($lab['lab_id'] ?? 0),
		);
		foreach (array(
			'legacy_lab_patient_id' => 'lab_patient_id',
			'legacy_lab_comment' => 'lab_comment',
			'legacy_comment' => 'comment',
			'legacy_lab_created_at' => 'lab_created_at',
			'legacy_lab_updated_at' => 'lab_updated_at',
			'legacy_deleted_at' => 'deleted_at',
		) as $metadata_key => $lab_key) {
			$value = $this->null_if_empty($lab[$lab_key] ?? null);
			if ($value !== null) $metadata[$metadata_key] = $value;
		}
		if ($detail_comments) $metadata['legacy_detail_comments'] = $detail_comments;

		$source = substr((string) ($lab['source'] ?? 'legacy'), 0, 64);
		return array(
			'device' => $source,
			'source' => $source,
			'source_id' => substr((string) ($lab['lab_id'] ?? $lab['id'] ?? ''), 0, 64),
			'sample_date' => $this->sample_date($lab),
			'software_version' => null,
			'metadata' => $metadata,
			'results' => $results,
			'plots' => array(),
			'created_at' => $this->created_at($lab),
		);
	}

	private function legacy_value(array $detail)
	{
		$string_value = $this->null_if_empty($detail['string_value'] ?? null);
		if ($string_value !== null) return is_numeric($string_value) ? (float) $string_value : $string_value;
		return $this->decimal_or_null($detail['value'] ?? null);
	}

	private function sample_date(array $lab): string
	{
		if ($this->null_if_empty($lab['lab_date'] ?? null) !== null) return $lab['lab_date'] . ' 00:00:00';
		return $this->first_date($lab, array('created_at', 'updated_at'));
	}

	private function created_at(array $lab): string
	{
		return $this->first_date($lab, array('lab_updated_at', 'lab_created_at', 'updated_at', 'created_at'));
	}

	private function first_date(array $row, array $keys): string
	{
		foreach ($keys as $key) {
			$value = $this->null_if_empty($row[$key] ?? null);
			if ($value !== null) return $value;
		}
		return date('Y-m-d H:i:s');
	}

	private function decimal_or_null($value)
	{
		return ($value === null || $value === '') ? null : (float) $value;
	}

	private function null_if_empty($value)
	{
		if (is_string($value)) $value = trim($value);
		return ($value === null || $value === '') ? null : $value;
	}
}
