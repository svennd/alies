<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Vamreg_in_buffer_model extends MY_Model
{
	public $table = 'vamreg_in_buffer';
	public $primary_key = 'id';
	
	public function __construct()
	{
        # different volumes
        $this->has_many['vamreg_index'] = array(
                    'foreign_model' => 'Vamreg_index_model',
                    'foreign_table' => 'vamreg_index',
                    'foreign_key'   => 'cnk',
                    'local_key'     => 'cnk'
                );
        
        $this->has_one['wholesale'] = array(
                    'foreign_model' => 'Wholesale_model',
                    'foreign_table' => 'wholesale',
                    'foreign_key'   => 'id',
                    'local_key'     => 'wholesale_id'
                );

	    parent::__construct();
	}

	public function get_all_drafts_by_date(string $startDate, string $endDate)
	{
		return $this->db
			->select('in.*')
			->from($this->table . ' in')
			->where('in.status', 'DRAFT')
			->where('in.delivery >=', $startDate)
			->where('in.delivery <=', $endDate)
			->get()
			->result_array();
	}

	public function send_draft_aggregate(string $startDate, string $endDate): array
	{
		$row = $this->db
			->select("SUM(CASE WHEN status = 'DRAFT' THEN 1 ELSE 0 END) AS draft_rows", false)
			->from($this->table)
			->where('delivery >=', $startDate)
			->where('delivery <=', $endDate)
			->get()
			->row_array();

		return [
			'draft_rows' => (int)($row['draft_rows'] ?? 0),
		];
	}
	

}
