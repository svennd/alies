<?php

class Lab_source_identity
{
	private const VERSION = 'lab-source-v1';

	public function derive($device, $source, $source_id): ?array
	{
		$source_id = $this->normalize_value($source_id);
		if ($source_id === null) {
			return null;
		}

		$device = $this->normalize_value($device);
		$source = $this->normalize_value($source);
		if ($device !== null) {
			return $this->build('device', $device, $source_id);
		}
		if ($source !== null) {
			return $this->build('source', $source, $source_id);
		}

		return null;
	}

	public function candidates($device, $source, $source_id): array
	{
		$source_id = $this->normalize_value($source_id);
		if ($source_id === null) {
			return array();
		}

		$candidates = array();
		$device = $this->normalize_value($device);
		$source = $this->normalize_value($source);
		if ($device !== null) {
			$candidates[] = $this->build('device', $device, $source_id);
		}
		if ($source !== null) {
			$candidates[] = $this->build('source', $source, $source_id);
		}

		return $candidates;
	}

	public function matches(array $identity, $device, $source, $source_id): bool
	{
		foreach ($this->candidates($device, $source, $source_id) as $candidate) {
			if (hash_equals($identity['canonical'], $candidate['canonical'])) {
				return true;
			}
		}

		return false;
	}

	private function build(string $kind, string $authority, string $source_id): array
	{
		$canonical = self::VERSION
			. '|' . strlen($kind) . ':' . $kind
			. '|' . strlen($authority) . ':' . $authority
			. '|' . strlen($source_id) . ':' . $source_id;

		return array(
			'kind' => $kind,
			'authority' => $authority,
			'source_id' => $source_id,
			'canonical' => $canonical,
			'hash' => hash('sha256', $canonical),
		);
	}

	public function normalize_value($value): ?string
	{
		if ($value === null || is_array($value) || is_object($value)) {
			return null;
		}

		$value = trim((string) $value);
		return $value === '' ? null : $value;
	}
}
