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
		$this->has_many['uploads'] = array(
					'foreign_model' => 'Events_upload_model',
					'foreign_table' => 'events_upload',
					'foreign_key' => 'event',
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

	public function register_out_snapshot($search_from, $search_to, $snapshot_month, $generated_at)
	{
		$search_from_sql = $this->db->escape($search_from . ' 00:00:00');
		$search_to_sql = $this->db->escape($search_to . ' 23:59:59');
		$snapshot_month_sql = $this->db->escape($snapshot_month);
		$generated_at_sql = $this->db->escape($generated_at);
		$search_from_date_sql = $this->db->escape($search_from);
		$search_to_date_sql = $this->db->escape($search_to);

		$sql = "
			SELECT
				" . $snapshot_month_sql . " AS snapshot_month,
				" . $search_from_date_sql . " AS snapshot_period_start,
				" . $search_to_date_sql . " AS snapshot_period_end,
				" . $generated_at_sql . " AS snapshot_generated_at,
				
				prod.id AS product_id,
				prod.name AS product_name,
				prod.vhbcode AS product_vhbcode,
				prod.cnk AS product_cnk,
				prod.supplier AS product_supplier,
				ep.volume AS used_volume,
				prod.unit_sell AS product_unit_sell,
				prod.buy_price AS product_buy_price,
				prod.btw_sell AS product_btw_sell,

				e.id AS event_id,
				e.created_at AS event_created_at,

				ep.price_net AS total_sell_price,
				ep.price_brut AS total_sell_price_brut,
				ep.unit_price AS unit_sell_price,
				ep.btw AS event_product_btw,

				stock.id AS stock_id,
				stock.lotnr AS stock_lotnr,
				stock.eol AS stock_eol,
				stock.in_price AS stock_in_price,

				book.code AS booking_code,
				book.category AS booking_category,
				book.btw AS booking_btw,

				vet.id AS vet_id,
				vet.order_nr AS vet_ordernr,
				
				event_loc.name AS event_location_name,

				pets.id AS pet_id,
				pets.name AS pet_name,
				pets.chip AS pet_chip,

				owners.id AS owner_id,
				owners.last_name AS owner_last_name,
				owners.btw_nr AS owner_btw_nr

			FROM
				events_products AS ep
			INNER JOIN
				events AS e
			ON
				e.id = ep.event_id
			LEFT JOIN
				products AS prod
			ON
				prod.id = ep.product_id
			LEFT JOIN
				booking_codes AS book
			ON
				book.id = ep.booking
			LEFT JOIN
				users AS vet
			ON
				vet.id = e.vet
			LEFT JOIN
				pets
			ON
				pets.id = e.pet
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			LEFT JOIN
				stock
			ON
				stock.id = ep.stock_id
			LEFT JOIN
				stock_location AS event_loc
			ON
				event_loc.id = e.location
				
			WHERE
				e.created_at >= " . $search_from_sql . "
			AND
				e.created_at <= " . $search_to_sql . "
			ORDER BY
				e.created_at ASC,
				ep.id ASC
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
			events.created_at > DATE_ADD(NOW(), INTERVAL -14 DAY)
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
				array( "all" =>
					$this->events
						->where('created_at >= STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->where('created_at <= LAST_DAY(STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i"))', null, null, false, false, true)
						->count_rows(),
						"ope" =>
						$this->events
							->where(array('type' => OPERATION))
							->where('created_at >= STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
							->where('created_at <= LAST_DAY(STR_TO_DATE("' . $date->format('Y-m-d') . ' 23:59", "%Y-%m-%d %H:%i"))', null, null, false, false, true)
							->count_rows(),
					);
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
				product_id, products.name as product_name, products.unit_sell, events_products.volume, pets.id as pet_id, pets.name as pet_name,
				events.created_at as event_date
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

	/*
		used in reports/search_event
		search for events by anamnese and title
	*/
	public function search_event(string $query, $search_from, $search_to, bool $anamnese = false)
	{
		if ($anamnese) {
			// search in anamnese and title
			return $this->search_in_anamnese($query, $search_from, $search_to);
		} else {
			// search only in title
			return $this->search_in_title($query, $search_from, $search_to);
		}
	}

	private function search_in_title(string $query, $search_from, $search_to)
	{
		$sql = "
			SELECT 
				events.id, events.title, events.anamnese, events.created_at, events.updated_at,
				pets.id as pet_id, pets.type as pet_type, pets.name as pet_name,
				stock_location.name as loc_name,
				owners.id as owner_id, owners.last_name as owner_name,
				users.first_name as vet_name, users.id as vet_id,
				1 AS title_match
			FROM
				events
			JOIN pets ON pets.id = events.pet
			JOIN stock_location ON stock_location.id = events.location
			JOIN users ON users.id = events.vet
			JOIN owners ON owners.id = pets.owner
			WHERE
				MATCH(events.title) AGAINST('" . $this->db->escape_like_str($query) . "' IN BOOLEAN MODE)
			AND
				events.no_history = 0
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			ORDER BY
				events.created_at DESC
			LIMIT 100;
		";

		return $this->db->query($sql)->result_array();
	}

	private function search_in_anamnese(string $query, $search_from, $search_to)
	{
		$sql = "
			SELECT 
				events.id, events.title, events.anamnese, events.created_at, events.updated_at,
				pets.id as pet_id, pets.type as pet_type, pets.name as pet_name,
				stock_location.name as loc_name,
				owners.id as owner_id, owners.last_name as owner_name,
				users.first_name as vet_name, users.id as vet_id,
				MATCH(title) AGAINST('fip') > 0 AS title_match
			FROM
				events
			JOIN pets ON pets.id = events.pet
			JOIN stock_location ON stock_location.id = events.location
			JOIN users ON users.id = events.vet
			JOIN owners ON owners.id = pets.owner
			WHERE
				MATCH(events.title, events.anamnese) AGAINST('" . $this->db->escape_like_str($query) . "' IN BOOLEAN MODE)
			AND
				events.no_history = 0
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			ORDER BY
				events.created_at DESC
			LIMIT 100;
		";

		return $this->db->query($sql)->result_array();
	}

	// used in pets
	public function get_pet_history(int $pet_id)
	{
		$events = $this->db
			->select('e.*, 
					v.id AS vet_id,
					v.first_name AS vet_name,
					vs1.id AS vet_support_1_id,
					vs1.first_name AS vet_support_1_name,
					vs2.id AS vet_support_2_id,
					vs2.first_name AS vet_support_2_name,
					sl.name AS location_name,
					(SELECT COUNT(*) FROM events_upload eu WHERE eu.event = e.id) AS upload_count,
					(SELECT COUNT(*)
						FROM events_labs el
						INNER JOIN lab_report lr ON lr.id = el.lab_id
						WHERE el.event_id = e.id
							AND lr.pet_id = e.pet
							AND lr.deleted_at IS NULL) AS lab_count')
			->from('events e')
			->join('users v', 'v.id = e.vet', 'left')
			->join('users vs1', 'vs1.id = e.vet_support_1', 'left')
			->join('users vs2', 'vs2.id = e.vet_support_2', 'left')
			->join('stock_location sl', 'sl.id = e.location', 'left')
			->where('e.pet', $pet_id)
			->where('e.no_history', 0)
			->order_by('e.created_at', 'DESC')
			->get()
			->result_array();

		foreach ($events as &$event) {
			$event['veterinarians'] = array();
			$veterinarian_assignments = array(
				array('id' => 'vet_id', 'name' => 'vet_name'),
				array('id' => 'vet_support_1_id', 'name' => 'vet_support_1_name'),
				array('id' => 'vet_support_2_id', 'name' => 'vet_support_2_name'),
			);
			$seen_veterinarian_tokens = array();

			foreach ($veterinarian_assignments as $assignment) {
				$veterinarian_id = isset($event[$assignment['id']]) ? (int) $event[$assignment['id']] : 0;
				$veterinarian_name = isset($event[$assignment['name']]) ? trim($event[$assignment['name']]) : '';

				if ($veterinarian_name === '') {
					continue;
				}

				$normalized_name = preg_replace('/\s+/', ' ', $veterinarian_name);
				$normalized_name = function_exists('mb_strtolower')
					? mb_strtolower($normalized_name, 'UTF-8')
					: strtolower($normalized_name);
				$filter_token = ($veterinarian_id > 0)
					? 'id:' . $veterinarian_id
					: 'name:' . sha1($normalized_name);

				if (isset($seen_veterinarian_tokens[$filter_token])) {
					continue;
				}

				$seen_veterinarian_tokens[$filter_token] = true;
				$event['veterinarians'][] = array(
					'id' => $veterinarian_id,
					'name' => $veterinarian_name,
					'filter_token' => $filter_token,
				);
			}

			$event['products'] = $this->db
				->select('ep.*, p.name, p.unit_sell')
				->from('events_products ep')
				->join('products p', 'p.id = ep.product_id', 'left')
				->where('ep.event_id', $event['id'])
				->get()
				->result_array();

			$event['procedures'] = $this->db
				->select('pr.*, prc.name')
				->from('events_procedures pr')
				->join('procedures prc', 'prc.id = pr.procedures_id', 'left')
				->where('pr.event_id', $event['id'])
				->get()
				->result_array();
		}

		return $events;
	}

}
