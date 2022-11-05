<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Events_procedures_model extends MY_Model
{
	public $table = 'events_procedures';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['procedures'] = array(
					'foreign_model' => 'Procedures_model',
					'foreign_table' => 'procedures',
					'foreign_key' => 'id',
					'local_key' => 'procedures_id'
				);
						
		parent::__construct();
	}

	public function get_monthly_earning(datetime $date)
	{
		$sql = "
			SELECT 
				sum(price) as total
			FROM
				events_procedures
			WHERE
				DATE(created_at) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
			AND
				DATE(created_at) <= LAST_DAY('" . $date->format('Y-m-d') . "')
			";

		$result = $this->db->query($sql)->result_array();

		return (is_null($result[0]['total'])) ? 0 : round($result[0]['total'], 2);
	}
}
