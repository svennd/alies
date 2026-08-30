<?php

class LabReportPending_model extends CI_Model {

    public $table = 'lab_report_pending';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
		$this->load->library('lab_source_identity');
	}

    public function create(array $data)
    {
		return (int) $this->create_or_refresh($data)['id'];
    }

	public function create_or_refresh(array $data): array
	{
		$received_at = $data['received_at'] ?? date('Y-m-d H:i:s');
		$data['device'] = $this->lab_source_identity->normalize_value($data['device'] ?? null);
		$data['source'] = $this->lab_source_identity->normalize_value($data['source'] ?? null);
		$data['source_id'] = $this->lab_source_identity->normalize_value($data['source_id'] ?? null);
		$identity = $this->lab_source_identity->derive(
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'] ?? null
		);

		if ($identity === null) {
			$this->db->insert($this->table, array(
				'device' => $data['device'] ?? null,
				'source' => $data['source'] ?? null,
				'source_id' => $data['source_id'] ?? null,
				'identity_hash' => null,
				'raw_payload' => $data['raw_payload'],
				'identifiers' => $data['identifiers'] ?? null,
				'reason' => $data['reason'],
				'last_received_at' => $received_at,
				'created_at' => $received_at,
			));
			return array('id' => (int) $this->db->insert_id(), 'state' => 'created', 'identity_hash' => null);
		}

		$is_device = $identity['kind'] === 'device';
		$identity_condition = $is_device
			? '(TRIM(`device`) <=> ? AND TRIM(`source_id`) <=> ?)'
			: '((`device` IS NULL OR TRIM(`device`) = \'\') AND TRIM(`source`) <=> ? AND TRIM(`source_id`) <=> ?)';
		$active_condition = '(`resolved_at` IS NULL AND `deleted_at` IS NULL AND ' . $identity_condition . ')';
		$sql = 'INSERT INTO `lab_report_pending` '
			. '(`device`, `source`, `source_id`, `identity_hash`, `raw_payload`, `identifiers`, `reason`, `last_received_at`, `created_at`) '
			. 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) '
			. 'ON DUPLICATE KEY UPDATE '
			. '`id` = LAST_INSERT_ID(`id`), '
			. '`source` = IF(' . $active_condition . ', VALUES(`source`), `source`), '
			. '`raw_payload` = IF(' . $active_condition . ', VALUES(`raw_payload`), `raw_payload`), '
			. '`identifiers` = IF(' . $active_condition . ', VALUES(`identifiers`), `identifiers`), '
			. '`reason` = IF(' . $active_condition . ', VALUES(`reason`), `reason`), '
			. '`last_received_at` = IF(' . $active_condition . ', VALUES(`last_received_at`), `last_received_at`), '
			. '`updated_at` = IF(' . $active_condition . ', VALUES(`last_received_at`), `updated_at`)';

		$condition_values = array($identity['authority'], $identity['source_id']);
		$bindings = array(
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'] ?? null,
			$identity['hash'],
			$data['raw_payload'],
			$data['identifiers'] ?? null,
			$data['reason'],
			$received_at,
			$received_at,
		);
		for ($i = 0; $i < 6; $i++) {
			$bindings = array_merge($bindings, $condition_values);
		}

		$this->db->query($sql, $bindings);
		$id = (int) $this->db->insert_id();
		$row = $this->db->where('identity_hash', $identity['hash'])->get($this->table)->row_array();
		if (!$row || !$this->lab_source_identity->matches(
			$identity,
			$row['device'] ?? null,
			$row['source'] ?? null,
			$row['source_id'] ?? null
		)) {
			throw new RuntimeException('Pending lab source identity collision detected.');
		}

		$active = empty($row['resolved_at']) && empty($row['deleted_at']);
		return array(
			'id' => $id ?: (int) $row['id'],
			'state' => $active ? 'active' : 'suppressed',
			'identity_hash' => $identity['hash'],
		);
	}

	public function get_active()
	{
		return $this->db
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->order_by('COALESCE(last_received_at, created_at)', 'desc', false)
			->order_by('id', 'desc')
			->get($this->table)
			->result_array();
	}

	public function count_active()
	{
		return (int) $this->db
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->count_all_results($this->table);
	}

	public function count_recent()
	{
		return $this->count_active();
	}

	public function get_active_by_id(int $pending_id)
	{
		return $this->db
			->where('id', $pending_id)
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->get($this->table)
			->row_array();
	}

	public function lock_active(int $pending_id)
	{
		return $this->db->query(
			'SELECT * FROM `lab_report_pending` WHERE `id` = ? AND `resolved_at` IS NULL AND `deleted_at` IS NULL FOR UPDATE',
			array($pending_id)
		)->row_array();
	}

	public function mark_resolved(int $pending_id, int $report_id, int $pet_id, ?int $user_id): bool
	{
		$this->db
			->where('id', $pending_id)
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->update($this->table, array(
				'resolved_at' => date('Y-m-d H:i:s'),
				'resolved_by' => $user_id,
				'resolved_pet_id' => $pet_id,
				'report_id' => $report_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));

		return $this->db->affected_rows() === 1;
	}

	public function resolve_active_identity(?string $identity_hash, int $report_id, int $pet_id, ?int $user_id = null): bool
	{
		if ($identity_hash === null) {
			return true;
		}

		$this->db
			->where('identity_hash', $identity_hash)
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->update($this->table, array(
				'resolved_at' => date('Y-m-d H:i:s'),
				'resolved_by' => $user_id,
				'resolved_pet_id' => $pet_id,
				'report_id' => $report_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));

		return $this->db->trans_status() !== false;
	}

	public function soft_delete_active(int $pending_id, int $user_id): bool
	{
		$this->db
			->where('id', $pending_id)
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->update($this->table, array(
				'deleted_at' => date('Y-m-d H:i:s'),
				'deleted_by' => $user_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));

		return $this->db->affected_rows() === 1;
	}
}
