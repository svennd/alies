<?php


// application/models/LabResult_model.php
class LabResult_model extends MY_Model {

    public $table = 'lab_results';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

    public function save($report_id, array $data)
    {
        $this->db->insert($this->table, [
            'report_id'  => $report_id,
            'code'       => $data['code'],
            'value_num'  => $data['value_num'],
            'value_text' => $data['value_text'],
            'unit'       => $data['unit'],
            'ref_min'    => $data['ref_min'],
            'ref_max'    => $data['ref_max']
        ]);
    }

    public function deleteByReport($report_id)
    {
        $this->db->where('report_id', $report_id)->delete($this->table);
    }

	public function get_grouped_by_reports(array $report_ids): array
	{
		if (!$report_ids) {
			return array();
		}

		$rows = $this->db
			->where_in('report_id', array_map('intval', $report_ids))
			->order_by('id', 'ASC')
			->get($this->table)
			->result_array();
		$grouped = array();

		foreach ($rows as $row) {
			$grouped[(int) $row['report_id']][] = $row;
		}

		return $grouped;
	}
}
