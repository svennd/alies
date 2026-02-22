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
            'raw_payload'  => $data['raw_payload'],
            'identifiers'  => $data['identifiers'] ?? null,
            'reason'       => $data['reason'],
            'created_at'   => date('Y-m-d H:i:s')
        ]);
    }
}
