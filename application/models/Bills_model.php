<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Bills_model extends MY_Model
{
	public $table = 'bills';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->soft_deletes = true;
		$this->has_one['owner'] = array(
					'foreign_model' => 'Owners_model',
					'foreign_table' => 'owners',
					'foreign_key' => 'id',
					'local_key' => 'owner_id'
				);
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'vet'
				);
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'location'
				);
		parent::__construct();
	}
	
	public function bill_update(int $bill_id, array $data)
	{
		$sql = "
			UPDATE 
				`bills` 
			SET 
				`vet` = '" . (int) $data['vet'] . "', 
				`location` = '" . (int) $data['location'] . "', 
				`amount` = '" . $data['amount'] . "',
				`cash` = '" . $data['cash'] . "', 
				`card` = '" . $data['card'] . "', 
				`status` = '" . (int) $data['status'] . "', 
				`created_at` = '" . $data['created'] ."' 
			WHERE 
				`bills`.`id` = '" . $bill_id . "';
		";
		return ($this->db->query($sql));
	}

	public function get_status($bill_id)
	{
		$status = $this->fields('status')->get($bill_id);
		return ($status['status']);
	}
	
	// deprecated ?
	public function get_last_months_earnings_stack_location($month)
	{
		# calculate date from the first day of the month
		$date = date("Y-m-1 00:00", strtotime("-" . $month . " months"));
		
		$sql = "select 
					year(bills.created_at) as y, 
					month(bills.created_at) as m, 
					sum(amount) as p,
					name
				from 
					bills 
				join
					stock_location
				on
					stock_location.id = bills.location
				where 
					status = '" . PAYMENT_PAID . "' 
				and
					bills.created_at > '" . $date . "'
				group by 
					year(bills.created_at), 
					month(bills.created_at),
					name
				
			";
	
		return ($this->db->query($sql)->result_array());
	}
	
	// deprecated ?
	public function get_income_overview($month)
	{
		# calculate date from the first day of the month
		$date = date("Y-m-1 00:00", strtotime("-" . $month . " months"));
		
		// echo $date;
		$sql = "select 
					sum(amount) as p,
					first_name
				from 
					bills 
				join
					users
				on bills.vet = users.id
				where 
					status = '" . PAYMENT_PAID . "' 
				and
					bills.created_at > '" . $date . "'
				group by 
					vet
				order by
					p
				asc
				
			";
	
		return ($this->db->query($sql)->result_array());
	}
	
	// deprecated ?
	public function get_avg_per_consult($month)
	{
		# calculate date from the first day of the month
		$date = date("Y-m-1 00:00", strtotime("-" . $month . " months"));
		
		$sql = "select 
					year(bills.created_at) as y, 
					month(bills.created_at) as m, 
					avg(amount) as avg,
					first_name
				from 
					bills 
				join
					users
				on bills.vet = users.id
				where 
					status = '" . PAYMENT_PAID . "' 
				and
					bills.created_at > '" . $date . "'
				group by 
					year(bills.created_at), 
					month(bills.created_at),
					vet
				";
	
		return ($this->db->query($sql)->result_array());
	}

	// called from accounting
	public function get_monthly_earning(datetime $date)
	{
		$sql = "
			SELECT 
				sum(amount) as total
			FROM
				bills
			WHERE
				DATE(created_at) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
			AND
				DATE(created_at) <= LAST_DAY('" . $date->format('Y-m-d') . "')
			AND
				status = '" . PAYMENT_PAID . "' 
			";

		$result = $this->db->query($sql)->result_array();

		return (is_null($result[0]['total'])) ? 0 : round($result[0]['total'], 2);
	}

	// called from accounting
	public function get_yearly_earnings(datetime $date)
	{
		$date->modify('first day of january');
		$first_day = $date->format('Y-m-d');
		$date->modify('last day of december');
		$last_day = $date->format('Y-m-d');

		$sql = "
			SELECT 
				sum(amount) as total
			FROM
				bills
			WHERE
				DATE(created_at) >= STR_TO_DATE('" . $first_day . "', '%Y-%m-%d')
			AND
				DATE(created_at) <= STR_TO_DATE('" . $last_day . "', '%Y-%m-%d')
			AND
				status = '" . PAYMENT_PAID . "' 
			";

		$result = $this->db->query($sql)->result_array();

		return (is_null($result[0]['total'])) ? 0 : round($result[0]['total'], 2);
	}

	// called from accounting
	public function get_yearly_earnings_by_month(datetime $date)
	{
		$selected_date = $date->format('Y-m-d');
		$date->modify('-11 months');

		$sql = "SELECT 
					year(bills.created_at) as y, 
					month(bills.created_at) as m, 
					sum(amount) as total
				FROM 
					bills
				WHERE 
					status = '" . PAYMENT_PAID . "' 
				AND
					DATE(created_at) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
				AND	
					DATE(created_at) < LAST_DAY(STR_TO_DATE('" . $selected_date . "', '%Y-%m-%d'))
				GROUP BY 
					year(bills.created_at), 
					month(bills.created_at)
				ORDER BY
					bills.created_at ASC				
			";
		
		return ($this->db->query($sql)->result_array());

	}
}
