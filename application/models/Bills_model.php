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
				`cash` = '" . $data['cash'] . "', 
				`card` = '" . $data['card'] . "', 
				`transfer` = '" . $data['transfer'] . "', 
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

	// invoice/bill
	// calculate the full bill, events
	public function calculate_bill(int $bill_id, int $bill_status)
	{
		$this->load->model('Events_model', 'events');

		$events = $this->events->fields('id')->where(array("payment" => $bill_id))->get_all();
		
		# transform to array with only id's
		$events_list = array_map(function($item) { return (int)$item['id']; }, $events);
				
		$products = ($this->events->get_all_items($events_list, PRODUCT));
		$procedures = ($this->events->get_all_items($events_list, PROCEDURE));

		// combine products & procedures in simple array
		// btw => sum(products+procedures)
		$total_items = $products;
		foreach ($procedures as $key => $value) {
			if (array_key_exists($key, $total_items)) {
				$total_items[$key] += $value;
			} else {
				$total_items[$key] = $value;
			}
		}

		$total_net = 0.0;
		$total_btw_0 = 0.0;
		$total_btw_6 = 0.0;
		$total_btw_21 = 0.0;

		foreach ($total_items as $btw => $item)
		{
			$total_net += $item;
			switch ($btw) {
				case 0:
					$total_btw_0 += $item;
					break;
				case 6:
					$total_btw_6 += $item;
					break;
				case 21:
					$total_btw_21 += $item;
					break;
			}
		}

		# this makes the error consistent
		# for example 
		# btw 6% : 3.786 btw 21% : 15.435
		# would give 			3.79 + 15.44 	= 19.23
		# while sum would be 	3.786 + 15.435 	= 19.221 ~= 19.22
		
		$total_brut = $total_net + number_format($total_btw_6*0.06, 2) + number_format($total_btw_21*0.21, 2);

		$this->update(
					array(
							"total_brut" 	=> $total_brut,
							"total_net" 	=> $total_net,
							"BTW_0" 		=> $total_btw_0,
							"BTW_6" 		=> $total_btw_6,
							"BTW_21"		=> $total_btw_21,
							"status" 		=> ($bill_status == BILL_DRAFT) ? BILL_PENDING : $bill_status, // upgrade from BILL_DRAFT
				), $bill_id);
				
		return array($total_net, $total_brut, array(
												"0" => array("over" => $total_btw_0, "calculated" => 0),
												"6" => array("over" => $total_btw_6, "calculated" => number_format($total_btw_6*0.06, 2)),
												"21" => array("over" => $total_btw_21, "calculated" => number_format($total_btw_21*0.21, 2))
										));	
	}

	// invoice/bill
	// does not change anything
	public function get_details(int $bill_id, int $owner_id)
	{
		// init
		$print_bill = array();

		// get all pets for this owner
		$pets = $this->pets->where(array("owner" => $owner_id))->fields(array('id', 'name'))->get_all();

		foreach ($pets as $pet) {
			# for easy access
			$pet_id = $pet['id'];

			# get all events for this pet and bill_id
			# this could be multiple (consult + op) for example
			$pet_events = $this->events
									->where(array(
											"pet" 		=> $pet_id,
											"payment" 	=> $bill_id
									))
									->fields("id")
									->get_all();

			# no event for this pet, skip all togheter
			if (!$pet_events) { continue; }

			# transform to array with only id's
			$event_list = array_map(function($item) { return (int)$item['id']; }, $pet_events);

			# create array if there is going to be
			# events linked to this animal
			$print_bill[$pet_id] = array(
				"pet"			=> $pet,
				"events" 		=> $event_list,
				"products" 		=> $this->events->get_printable_items($event_list, PRODUCT),
				"procedures" 	=> $this->events->get_printable_items($event_list, PROCEDURE),
			);
		}
		return $print_bill;
	}

	// called from accounting
	public function get_monthly_earning(datetime $date)
	{
		$sql = "
			SELECT 
				sum(total_net) as total
			FROM
				bills
			WHERE
				DATE(invoice_date) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
			AND
				DATE(invoice_date) <= LAST_DAY('" . $date->format('Y-m-d') . "')
			AND
			(
				status = '" . BILL_PAID . "' 
				OR
				status = '" . BILL_HISTORICAL . "'
			)
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
				sum(total_net) as total
			FROM
				bills
			WHERE
				DATE(created_at) >= STR_TO_DATE('" . $first_day . "', '%Y-%m-%d')
			AND
				DATE(created_at) <= STR_TO_DATE('" . $last_day . "', '%Y-%m-%d')
			AND
			(
				status = '" . BILL_PAID . "' 
				OR
				status = '" . BILL_HISTORICAL . "'
			)
			";

		$result = $this->db->query($sql)->result_array();

		return (is_null($result[0]['total'])) ? 0 : round($result[0]['total'], 2);
	}

	// called from accounting
	// todo : check for merging w/ get_yearly_earnings_by_date
	public function get_yearly_earnings_by_month(datetime $date)
	{
		$selected_date = $date->format('Y-m-d');
		$date->modify('-11 months');

		$sql = "SELECT 
					year(bills.invoice_date) as y, 
					month(bills.invoice_date) as m, 
					sum(total_net) as total,
					sum(total_brut) as total_brut,
					count(invoice_id) as invoices
				FROM 
					bills
				WHERE 
					DATE(invoice_date) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
				AND	
					DATE(invoice_date) < LAST_DAY(STR_TO_DATE('" . $selected_date . "', '%Y-%m-%d'))
				AND
					invoice_id IS NOT NULL
				AND
					deleted_at IS NULL
				GROUP BY 
					year(bills.invoice_date), 
					month(bills.invoice_date)
				ORDER BY
					bills.invoice_date ASC				
			";
		
		return ($this->db->query($sql)->result_array());

	}

	// used in export/index
	// used in reports 
	public function get_yearly_earnings_by_date($from, $to, $group = false)
	{
		// before we looked also at the status
		// but since its a invoice we expect this to be paid
		$sql = "SELECT 
					" . (($group) ? "year(bills.invoice_date) as y, month(bills.invoice_date) as m," : "") . "
					sum(total_net) as total,
					sum(total_brut) as total_brut,
					count(invoice_id) as invoices
				FROM 
					bills
				WHERE 
					bills.invoice_date > STR_TO_DATE('" . $from . " 00:00', '%Y-%m-%d %H:%i')
				AND
					bills.invoice_date < STR_TO_DATE('" . $to . " 23:59', '%Y-%m-%d %H:%i')
				AND
					invoice_id IS NOT NULL
				AND
					deleted_at IS NULL
				" . (($group) ? "GROUP BY year(bills.invoice_date), month(bills.invoice_date)" : "") . "
				ORDER BY
					bills.invoice_date ASC
			";

		return ($this->db->query($sql)->result_array());
	}

	// check if events under this bill were
	// manually changed by a vet
	public function is_bill_modified(int $bill_id) 
	{
		$this->load->model('Events_model', 'events');
		$events = $this->events->fields('id')->where(array('payment' => $bill_id))->get_all();
		if($events)
		{
			foreach($events as $event)
			{
				$is_event_modified = $this->events->is_modified($event['id']);
				if($is_event_modified) { return true; }
			}
		}
		return false;
	}

	public function set_invoice_id(int $bill_id)
	{
		// if the last id has year == last_year we should reset to 1
		// basically count + 1 and every year reset to 1
		// COALESCE(MAX(invoice_id),0) ==> IFNULL(MAX(invoice_id, 0))
		// limit 1 is overkill 
		$sql = "
			UPDATE 
				bills
			SET
				invoice_id = (
					SELECT 
						COALESCE(MAX(invoice_id),0)
					FROM 
						bills
					WHERE
						year(created_at) = '" . date('Y') . "'
					) + 1,
					invoice_date = '" .  date('Y-m-d H:i:s')  . "'
			WHERE
				id = '" . $bill_id . "'
				AND
				invoice_id IS NULL
			LIMIT
				1
			;
		";
		$this->db->trans_start();
		$this->db->query($sql);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			$this->logs->logger(ERROR, "set_invoice_id", "failed to do transaction for bill_id:" . (int) $bill_id);
		}
	}

	// get open bills, used in owners & invoice
	public function get_open_bills(int $owner, int $current_bill = BILL_INVALID)
	{
		return $this
			->group_start()
				->where("status", "=", BILL_DRAFT)
				->where("status", "=", BILL_PENDING, true)
				->where("status", "=", BILL_UNPAID, true)
				->where("status", "=", BILL_INCOMPLETE, true)
				->where("status", "=", BILL_OVERDUE, true)
				->where("status", "=", BILL_ONHOLD, true)
			->group_end()
				->where("owner_id", "=", $owner)
				->where("id", "!=", $current_bill)
			->get_all();
	}
}