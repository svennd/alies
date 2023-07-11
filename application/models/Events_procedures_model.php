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

	public function get_net_income(int $proc_id)
	{
		$sql = "

			SELECT
				COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) AND procedures_id = '" . $proc_id . "' THEN 1 END) AS total_count_3m,
				COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND procedures_id = '" . $proc_id . "' THEN 1 END) AS total_count_1y,
				SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) AND procedures_id = '" . $proc_id . "' THEN amount ELSE 0 END) AS total_amount_3m,
				SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND procedures_id = '" . $proc_id . "' THEN amount ELSE 0 END) AS total_amount_1y,
				SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) AND procedures_id = '" . $proc_id . "' THEN price ELSE 0 END) AS total_net_price_3_months,
				SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND procedures_id = '" . $proc_id . "' THEN price ELSE 0 END) AS total_net_price_1_year
			FROM events_procedures;	  
		;";

		return $this->db->query($sql)->result_array()[0];
	}
}
