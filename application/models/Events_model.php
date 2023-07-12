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
		called in invoice
	*/
	public function get_products_and_procedures(int $event_id)
	{
		$products 	= array();
		$procedures = array();
		$tally 		= array();
		$booking	= array();

		/* get products on this event */
		$sql = "
				SELECT 
					product_id, volume, net_price, price, btw, events_products.btw, booking, barcode,
					products.name, products.unit_sell
				FROM `events_products`
				JOIN
					`products`
				ON
					product_id = products.id
				WHERE `events_products`.`event_id` = " . $event_id . "
		";
		$product_array = $this->db->query($sql)->result_array();
		if ($product_array) {
			foreach ($product_array as $product) {
				$products[] = $product;

				# index
				$product_btw = (int) $product['btw'];
				$product_booking = (int) $product['booking'];

				# value
				$net_price = (float) $product['net_price'];
				
				# tally products
				if (isset($tally[$product_btw])) {
					$tally[$product_btw] += $net_price;
				} else { 
					$tally[$product_btw] = $net_price;
				}

				# booking products
				if (isset($booking[$product_booking])) {
					$booking[$product_booking] += $net_price;
				} else { 
					$booking[$product_booking] = $net_price;
				}
			}
		}
		/* get procedures on every event */
		$sql = "
				SELECT procedures_id, amount, net_price, booking, events_procedures.price, btw, procedures.name, events_procedures.created_at
				FROM `events_procedures`
				JOIN
					`procedures`
				ON
					procedures_id = procedures.id
				WHERE `events_procedures`.`event_id` = " . $event_id . "
		";
		$procedure_array = $this->db->query($sql)->result_array();
		if ($procedure_array) {
			foreach ($procedure_array as $proc) {
				$procedures[] = $proc;
				$tally[$proc['btw']] = (isset($tally[$proc['btw']])) ? (float) ($tally[$proc['btw']] + $proc['net_price']) : (float) $proc['net_price'];
				$booking[$proc['booking']] = (isset($booking[$proc['booking']])) ? ((float) $booking[$proc['booking']] + $proc['net_price']) : (float) $proc['net_price'];
			}
		}

		return array(
					"prod" 		=> $products,
					"proc" 		=> $procedures,
					"tally"		=> $tally,
					"booking"	=> $booking,
					);
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
					ep.volume as volume, ep.net_price as total_sell_price,
					prod.name as product_name, prod.unit_sell, prod.buy_price, prod.buy_volume as buy_volume, prod.vhbcode,
					users.first_name as vet_name,
					stck.name as stock_name,
					type.name as product_type,
					pets.name as pet_name, pets.id as pet_id,
					owners.id as owner_id, owners.last_name,
					book.code, book.category, book.btw,
					(select stock.in_price from stock where stock.barcode = ep.barcode limit 1) as in_price_test

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
					calc_net_price
				FROM `events_products`
				WHERE `events_products`.`event_id` = " . $event_id . "
		";
		$product_array = $this->db->query($sql)->result_array();
		if ($product_array) {
			foreach ($product_array as $product) {
				if ($product['calc_net_price'] != 0) { return true; }
			}
		}

		// check procedures
		$sql = "
				SELECT calc_net_price
				FROM `events_procedures`
				WHERE `events_procedures`.`event_id` = " . $event_id . "
		";
		$procedure_array = $this->db->query($sql)->result_array();
		if ($procedure_array) {
			foreach ($procedure_array as $proc) {
				if ($proc['calc_net_price'] != 0) { return true; }
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
		return 
				$this->events
						->where('created_at >= STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
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
}
