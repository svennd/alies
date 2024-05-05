<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Events_model extends MY_Model
{
	public $table = 'events';
	public $primary_key = 'id';

	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['pet'] = array(
					'foreign_model' => 'Pets_model',
					'foreign_table' => 'pets',
					'foreign_key' => 'id',
					'local_key' => 'pet'
				);
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'location'
				);
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'vet'
				);
		$this->has_one['vet_1_sup'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'vet_support_1'
				);
		$this->has_one['vet_2_sup'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'vet_support_2'
				);

		/*
			has_many
		*/
		$this->has_many['eprod'] = array(
					'foreign_model' => 'Events_products_model',
					'foreign_table' => 'events_products',
					'foreign_key' => 'event_id',
					'local_key' => 'id'
				);
		$this->has_many['eproc'] = array(
					'foreign_model' => 'Events_procedures_model',
					'foreign_table' => 'events_procedures',
					'foreign_key' => 'event_id',
					'local_key' => 'id'
				);

		/*
			pivot
		*/
		$this->has_many_pivot['products'] = array(
						'foreign_model'	=> 'Products_model',
						'pivot_table'	=> 'events_products',
						'local_key'		=> 'id',
						'pivot_local_key' => 'event_id',
						'pivot_foreign_key' => 'product_id',
						'foreign_key' => 'id',
						'get_relate'=> false
		);

		$this->has_many_pivot['procedures'] = array(
						'foreign_model'	=> 'Procedures_model',
						'pivot_table'	=> 'events_procedures',
						'local_key'		=> 'id',
						'pivot_local_key' => 'event_id',
						'pivot_foreign_key' => 'procedures_id',
						'foreign_key' => 'id',
						'get_relate'=> false
		);
		parent::__construct();
	}


	/*
		called in invoice/bill

		Set all open events to this bill
		We need to find all pets linked to this owner
		and then find all events linked to this pet
		alternativly we could add owner_id to events but that would make
		moving pets impossible.
		It's better to keep events linked to pets and the owner not linked to an event
	*/
	public function set_open_events_to_bills(int $owner, int $bill)
	{
		// set all open events from this owner to this bill
		$sql = "
			UPDATE events
			JOIN pets ON pets.id = events.pet
			SET 
				events.payment = " . $bill . "
			WHERE 
				pets.owner = " . $owner . "
			AND 
				events.payment = " . PAYMENT_OPEN . ";
		";

		return $this->db->query($sql);
	}

	/*
		called in bills_model for invoice controller
	*/
	public function get_booking_export(array $event, int $type)
	{
		$table = ($type == PROCEDURE) ?  'events_procedures' : 'events_products';
		$sql = "
				SELECT 
					SUM(price_net) as total_net, booking_codes.code, booking_codes.btw
				FROM `" . $table . "`
				LEFT JOIN
					booking_codes
					on
					booking_codes.id = " . $table . ".booking
				WHERE 
					`event_id` IN (" . implode(',', $event) . ")
				GROUP BY
					booking
		";
		$products = $this->db->query($sql)->result_array();
		
		return $products;
	}

	/*
		called in bills_model for invoice controller
	*/
	public function get_all_items(array $event, int $type)
	{
		$table = ($type == PROCEDURE) ?  'events_procedures' : 'events_products';
		$sql = "
				SELECT 
					SUM(price_net) as total_net, btw
				FROM `" . $table . "`
				WHERE 
					`event_id` IN (" . implode(',', $event) . ")
				GROUP BY
					btw
		";
		$products = $this->db->query($sql)->result_array();
		
		$outputArray = array();

		// format into btw => total sum
		foreach ($products as $item) {
			$outputArray[$item['btw']] = ($outputArray[$item['btw']] ?? 0) + (float)$item['total_net'];
		}

		return $outputArray;
	}

	/*
		called in bill_model for invoice_controller
	*/
	public function get_printable_items(array $event, int $type)
	{
		if ($type == PRODUCT) {
			$sql = "SELECT 
						product_id, volume, price_net, price_brut, events_products.btw, events_products.unit_price as unit_price,
						reduction_reason,
						products.name, products.unit_sell, events_products.created_at
					FROM `events_products`
					JOIN
						`products`
					ON
						product_id = products.id
					WHERE	
						events_products.event_id in (" . implode(',', $event) . ")
					;
					";
		}
		else if ($type == PROCEDURE) {
			$sql = "SELECT 
						procedures_id, volume, price_net, price_brut, events_procedures.btw, events_procedures.unit_price as unit_price,
						reduction_reason,
						procedures.name, events_procedures.created_at
					FROM `events_procedures`
					JOIN
						`procedures`
					ON
						procedures_id = procedures.id
					WHERE	
						events_procedures.event_id in (" . implode(',', $event) . ")
					;
					";

		}

		return $this->db->query($sql)->result_array();
	}

	// give all products for a certain bill 
	public function all_bill_products(int $bill_id)
	{
		// not return all unassigned products
		if ($bill_id == BILL_DRAFT) { return false; }

		$sql = "
			SELECT 
				product_id, volume, stock_id, events.location as location
			FROM
				events_products
			LEFT JOIN
				events
			ON
				events.id = events_products.event_id
			WHERE
				events_products.event_id IN (
					SELECT 
						id
					FROM
						events
					WHERE
						payment = " . $bill_id . "
				)
				";
				
		return $this->db->query($sql)->result_array();
	}

	public function get_status($event_id)
	{
		$status = $this->fields('status')->get($event_id);
		return ($status['status']);
	}

	public function register_out($search_from, $search_to)
	{
		$sql = "
				SELECT
					ep.volume as volume, ep.price_net as total_sell_price, ep.created_at as event_date,
					prod.name as product_name, prod.unit_sell, prod.buy_price, prod.buy_volume as buy_volume, prod.vhbcode, prod.btw_buy as btw_buy,
					users.first_name as vet_name,
					stck.name as stock_name,
					type.name as product_type,
					pets.name as pet_name, pets.id as pet_id,
					owners.id as owner_id, owners.last_name,
					book.code, book.category, book.btw,
					(select stock.in_price from stock where stock.id = ep.stock_id limit 1) as in_price_test,
					(select stock.lotnr from stock where stock.id = ep.stock_id limit 1) as lotnr

				FROM `events` as e
				
				LEFT JOIN events_products as ep
				ON
					ep.event_id = e.id

				RIGHT JOIN products as prod
				ON
					prod.id = ep.product_id

				LEFT JOIN booking_codes as book
				ON
					book.id = ep.booking

				LEFT JOIN users
				ON
					e.vet = users.id

				LEFT JOIN pets
				ON
					e.pet = pets.id

				LEFT JOIN products_type as type
				ON
					type.id = prod.type

				LEFT JOIN owners
				ON
					pets.owner = owners.id

				LEFT JOIN stock_location as stck
				ON
					stck.id = e.location

				WHERE
					e.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
				AND
					e.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			";
		return $this->db->query($sql)->result_array();
	}
	

	// check if prices in this event have been modified by the
	// vet
	public function is_modified(int $event_id)
	{
		// check for products
		$sql = "
				SELECT 
					price_ori_net
				FROM `events_products`
				WHERE `events_products`.`event_id` = " . $event_id . "
		";
		$product_array = $this->db->query($sql)->result_array();
		if ($product_array) {
			foreach ($product_array as $product) {
				if ($product['price_ori_net'] != 0) { return true; }
			}
		}

		// check procedures
		$sql = "
				SELECT price_ori_net
				FROM `events_procedures`
				WHERE `events_procedures`.`event_id` = " . $event_id . "
		";
		$procedure_array = $this->db->query($sql)->result_array();
		if ($procedure_array) {
			foreach ($procedure_array as $proc) {
				if ($proc['price_ori_net'] != 0) { return true; }
			}
		}
		// check for procedures
		return false;
	}

	/*
		used in report (for vet/admin)
	*/
	public function get_current_events(bool $admin = false)
	{
		$sql = "
		SELECT 
			events.id, events.title, events.payment, events.status, events.report, events.updated_at,
			pets.id as pet_id, pets.type as pet_type, pets.name as pet_name,
			stock_location.id as loc_id, stock_location.name as loc_name,
			owners.id as owner_id, owners.last_name as owner_name,
			users.first_name, users.id as vet_id,
			bills.status as bill_status
		FROM
			events
		JOIN pets ON pets.id = events.pet
		JOIN stock_location ON stock_location.id = events.location
		JOIN users ON users.id = events.vet
		JOIN owners ON owners.id = pets.owner
		LEFT JOIN bills ON bills.id = events.payment
		WHERE
			events.created_at > DATE_ADD(NOW(), INTERVAL -7 DAY)
		AND
			events.no_history = 0
		". (($admin) ? "" : "AND ( events.vet = " . $this->user->id . " OR events.vet_support_1 = " . $this->user->id . " OR events.vet_support_2 = " . $this->user->id . ")") ."
		ORDER BY
			events.created_at DESC
		";

		return $this->db->query($sql)->result_array();

	}

	// accounting
	public function get_contacts(datetime $date)
	{
		return 
				$this->events
						->where('created_at >= STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->where('created_at <= LAST_DAY(STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i"))', null, null, false, false, true)
						->count_rows();
	}

	// accounting
	public function get_contacts_year(datetime $date)
	{
		$date->modify('first day of january');

		$last_day_of_the_year = clone $date;
		$last_day_of_the_year->modify('last day of december');

		return 
				$this->events
						->where('created_at >= STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->where('created_at <= LAST_DAY(STR_TO_DATE("' . $last_day_of_the_year->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i"))', null, null, false, false, true)
						->count_rows();
	}


	/*
		used in header (vet_controller)
	*/
	public function get_open_reports(int $user_id)
	{
		$sql = "
			SELECT 
				count(id) as count
			FROM
				events
			WHERE 
				vet = '" . $user_id . "'
			AND
				no_history = 0
			AND
				report != 2
			AND
				updated_at > DATE_ADD(NOW(), INTERVAL -7 DAY)
			LIMIT 
			9;
		";

		return ($this->db->query($sql)->result_array()[0]['count']);
	}

	/*
		vet/pub
		some basic statistcs
	*/
	public function get_event_count(int $user_id)
	{
		$sql = "
			SELECT 
				COUNT(*) AS event_count
			FROM 
				events
			WHERE 
				(vet = '". $user_id ."' OR vet_support_1 = '". $user_id ."'  OR vet_support_2 = '". $user_id ."' )
		  	AND 
				DATEDIFF(NOW(), created_at) <= 365
			;
		";

		return $this->db->query($sql)->result_array()[0]['event_count'];
	}
	

	/*
	* used in owners/invoices to show all products for a certain owner
	*/
	public function get_products_owner(int $owner_id, $search_from, $search_to)
	{
		$sql = "
			SELECT 
				product_id, products.name as product_name, products.unit_sell, events_products.volume, pets.id as pet_id, pets.name as pet_name
			FROM
				events_products
			LEFT JOIN
				events
			ON
				events.id = events_products.event_id
			LEFT JOIN
				products
			ON
				events_products.product_id = products.id
			LEFT JOIN
				pets
			ON
				pets.id = events.pet
			WHERE
				events.payment IN (
					SELECT 
						id 
					FROM 
						`bills` 
					WHERE 
						bills.owner_id = " . $owner_id . "
					AND
						bills.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
					AND
						bills.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
				)
			ORDER by
					events_products.created_at DESC
		";

		return $this->db->query($sql)->result_array();
	}
}
