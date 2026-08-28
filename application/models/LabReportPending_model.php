<?php

class LabReportPending_model extends CI_Model {

    public $table = 'lab_report_pending';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

    public function create(array $data)
    {
        $this->db->insert($this->table, [
            'device'       => $data['device'],
            'source'       => $data['source'] ?? null,
            'source_id'    => $data['source_id'] ?? null,
            'raw_payload'  => $data['raw_payload'],
            'identifiers'  => $data['identifiers'] ?? null,
            'reason'       => $data['reason'],
            'created_at'   => date('Y-m-d H:i:s')
        ]);

		return (int) $this->db->insert_id();
    }

	public function get_active()
	{
		return $this->db
			->where('resolved_at IS NULL', null, false)
			->where('deleted_at IS NULL', null, false)
			->order_by('created_at', 'desc')
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

	public function mark_resolved(int $pending_id, int $report_id, int $pet_id, int $user_id): bool
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
