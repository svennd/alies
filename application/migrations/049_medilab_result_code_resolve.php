<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_medilab_result_code_resolve extends CI_Migration {

	protected $up_version = "049";
	protected $down_version = "048";
	private $last_run_stats = array(
		'eligible' => 0,
		'updated' => 0,
		'unresolved' => 0,
		'skipped_reason' => null,
	);

	public function up()
	{
		$api_key = $this->get_index_api_key();
		if ($api_key === null) {
			$this->last_run_stats['skipped_reason'] = 'missing config index_api_key';
			log_message('error', 'migration 049: missing config index_api_key, skipping medilab code resolution');
			return $this->up_version;
		}

		$base_url = rtrim((string) dirname(config_item('base_url')), '/');
		if ($base_url === '') {
			$this->last_run_stats['skipped_reason'] = 'missing base_url config';
			log_message('error', 'migration 049: missing base_url config, skipping medilab code resolution');
			return $this->up_version;
		}

		$rows = $this->db
			->select('lr.id, lr.code')
			->from('lab_results lr')
			->join('lab_report rpt', 'rpt.id = lr.report_id', 'inner')
			->where('rpt.device', 'medilab')
			->where("lr.code REGEXP '^[0-9]+$'", null, false)
			->get()
			->result_array();
		$this->last_run_stats['eligible'] = count($rows);

		if (!$rows) {
			return $this->up_version;
		}

		$resolved_cache = array();
		$updated = 0;

		foreach ($rows as $row)
		{
			$test_id = (string) $row['code'];

			if (!array_key_exists($test_id, $resolved_cache)) {
				$resolved_cache[$test_id] = $this->resolve_test_code($base_url, $api_key, $test_id);
			}

			$resolved_code = $resolved_cache[$test_id];
			if ($resolved_code === null) {
				$this->last_run_stats['unresolved']++;
				continue;
			}

			$this->db
				->where('id', (int) $row['id'])
				->update('lab_results', array('code' => $resolved_code));

			if ($this->db->affected_rows() > 0) {
				$updated++;
			}
		}

		log_message('error', 'migration 049: updated medilab result code rows=' . $updated);
		$this->last_run_stats['updated'] = $updated;
		return $this->up_version;
	}

	public function get_last_run_stats()
	{
		return $this->last_run_stats;
	}

	public function down()
	{
		// Irreversible data migration: resolved code strings cannot be safely mapped back to original ids.
		return $this->down_version;
	}

	private function get_index_api_key()
	{
		$row = $this->db
			->select('value')
			->where('name', 'index_api_key')
			->limit(1)
			->get('config')
			->row_array();

		if (!$row || !isset($row['value'])) {
			return null;
		}

		$decoded = base64_decode((string) $row['value'], true);
		if ($decoded === false) {
			return null;
		}

		$decoded = trim($decoded);
		return $decoded === '' ? null : $decoded;
	}

	private function resolve_test_code(string $base_url, string $api_key, string $test_id)
	{
		if (!function_exists('curl_init')) {
			log_message('error', 'migration 049: curl extension not available, skipping lookup for test_id=' . $test_id);
			return null;
		}

		$url = $base_url . '/fagg/api/medilab/by-test-id/' . rawurlencode($test_id);
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'X-API-Key: ' . $api_key,
		));

		$raw = curl_exec($ch);
		$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_err = curl_error($ch);
		curl_close($ch);

		if ($raw === false || $http_code >= 400 || $curl_err !== '') {
			log_message('error', 'migration 049: lookup failed for test_id=' . $test_id . ' http=' . $http_code . ' err=' . $curl_err);
			return null;
		}

		$data = json_decode($raw, true);
		if (!is_array($data)) {
			return null;
		}

		$resolved = $this->extract_code($data);
		if ($resolved === null) {
			return null;
		}

		$resolved = substr($resolved, 0, 64);
		if ($resolved === '' || ctype_digit($resolved)) {
			return null;
		}

		return $resolved;
	}

	private function extract_code(array $data)
	{
		$candidates = array();

		if (isset($data['code'])) { $candidates[] = $data['code']; }
		if (isset($data['lab_code_text'])) { $candidates[] = $data['lab_code_text']; }
		if (isset($data['lab_name'])) { $candidates[] = $data['lab_name']; }
		if (isset($data['name'])) { $candidates[] = $data['name']; }
		if (isset($data['test_name'])) { $candidates[] = $data['test_name']; }
		if (isset($data['canonical'])) { $candidates[] = $data['canonical']; }
		if (isset($data['canonical_code'])) { $candidates[] = $data['canonical_code']; }

		if (isset($data['data']) && is_array($data['data'])) {
			$nested = $this->extract_code($data['data']);
			if ($nested !== null) {
				$candidates[] = $nested;
			}
		}

		foreach ($candidates as $candidate)
		{
			if (!is_scalar($candidate)) {
				continue;
			}

			$value = trim((string) $candidate);
			if ($value !== '' && $value !== '---' && !ctype_digit($value)) {
				return $value;
			}
		}

		return null;
	}
}
