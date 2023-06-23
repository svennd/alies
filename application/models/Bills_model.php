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


	// 
	public function get_bill_details(int $bill_id)
	{
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Events_model', 'events');

		$bill = $this->fields('id, status, owner_id')->get($bill_id);
			$owner_id = $bill['owner_id'];
			$bill_status = $bill['status'];

		$pets = $this->pets->where(array("owner" => $owner_id))->fields(array('id', 'name', 'chip'))->get_all();

		if (!$pets) { 
			return false; 
		}
		
		list($print_bill, $bill_total_tally, $event_info, $pet_id_array) = $this->bill_per_pet($pets, $bill_id, $bill_status);
		
		# calculate the full bill
		$bill_total = 0.0;
		foreach ($bill_total_tally as $btw => $total) {
			$bill_total += $total * (1 + ($btw/100));
		}

		return array($print_bill, $bill_total_tally, $event_info, $pet_id_array, $bill_total);
	}

	private function bill_per_pet(array $pets, int $bill_id, int $bill_status)
	{
		$print_bill = array();
		$bill_total_tally = array();
		$event_info = array();
		$pet_id_array = array();

		foreach ($pets as $pet) {
			# for easy access
			$pet_id = $pet['id'];

			# get all events for this pet that have an open payment
			# this could be multiple (consult + op) for example
			$pet_events = $this->events
									->where("pet", "=", $pet['id'])
										->group_start()
											->where("payment", "=", NO_BILL)
											->where("payment", "=", $bill_id, true)
										->group_end()
									->fields("id, location, payment, created_at, updated_at")
									->get_all();

			# no event for this pet, skip all togheter
			if (!$pet_events) {
				continue;
			}

			# create array if there is going to be
			# events linked to this animal
			$print_bill[$pet_id] = array();

			# should generally only be 1 event
			# but in case of open events
			# could be more
			foreach ($pet_events as $event) {
				#
				$event_id = $event['id'];

				# get the calculated bill
				# for all procedures and products for this pet
				$event_bill = $this->events->get_products_and_procedures($event_id);

				# update event if its not part of this bill yet
				# only if creating a new bill, else we would add events to a closed invoice
				$this->link_events_to_bill($event_id, $event['payment'], $bill_id, $bill_status);

				# add to the total
				foreach ($event_bill['tally'] as $btw => $total) {
					$bill_total_tally[$btw] = (isset($bill_total_tally[$btw])) ? $bill_total_tally[$btw] + $total : $total;
				}

				# printable
				$print_bill[$pet_id][$event_id] = $event_bill;

				# a list with event description
				$event_info[$pet_id][$event_id] = $event;
			}

			# for making a printable bill
			$pet_id_array[$pet_id] = $pet;
		}

		return array($print_bill, $bill_total_tally, $event_info, $pet_id_array);
	}

	# update events so they are linked
	# to this bill
	private function link_events_to_bill(int $event_id, int $event_link, int $bill_id, int $bill_status)
	{
		if ($event_link != $bill_id && $bill_status != PAYMENT_PAID) {
			# update event so that we know on what bill it was placed (and its no longer NO_BILL)
			$this->events->update(array("payment" => $bill_id), $event_id);
		}
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
					) + 1
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

}
