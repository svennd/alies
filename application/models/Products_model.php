<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Products_model extends MY_Model
{

	public $table = 'products';
	public $primary_key = 'id';
	
	// how many products does product search return
	const PRODUCT_SEARCH_LIMIT = 15;
	
	public function __construct()
	{
		$this->soft_deletes = true;
		$this->has_many['prices'] = array(
					'foreign_model' => 'Product_price_model',
					'foreign_table' => 'products_price',
					'foreign_key' => 'product_id',
					'local_key' => 'id'
				);
						
		$this->has_many['stock'] = array(
					'foreign_model' => 'Stock_model',
					'foreign_table' => 'stock',
					'foreign_key' => 'product_id',
					'local_key' => 'id'
				);
						
		$this->has_one['type'] = array(
							'foreign_model' => 'Product_type_model',
							'foreign_table' => 'products_type',
							'foreign_key' => 'id',
							'local_key' => 'type'
						);
						
		$this->has_one['wholesale'] = array(
							'foreign_model' => 'Wholesale_model',
							'foreign_table' => 'wholesale',
							'foreign_key' => 'id',
							'local_key' => 'wholesale'
						);
						
		$this->has_one['booking_code'] = array(
							'foreign_model' => 'Booking_code_model',
							'foreign_table' => 'booking_codes',
							'foreign_key' => 'id',
							'local_key' => 'booking_code'
						);
		parent::__construct();
	}

	/*
		search a product based on name
		search/index
	*/
	public function search_product($name)
	{
		return ($name) ?
					$this->
					group_start()->
						or_like('name', $name, 'both')->
						or_like('wholesale_name', $name, 'both')->
						or_like('short_name', $name, 'both')->
					group_end()->
					limit(50)->
					get_all() 
					: 
					false;
	}

	/*
		search products and include local/global stock totals for live search
	*/
	public function search_product_with_stock(string $name, int $location, int $limit = 25)
	{
		if ($name === '') {
			return array();
		}

		$escaped = $this->db->escape_like_str($name);
		$like = '%' . $escaped . '%';

		$sql = "
			SELECT
				p.id,
				p.name,
				p.sellable,
				p.is_antibiotic,
				p.vaccin,
				p.unit_sell,
				p.short_name,
				COALESCE(SUM(CASE WHEN s.location = ? THEN s.volume ELSE 0 END), 0) AS local_stock,
				COALESCE(SUM(s.volume), 0) AS global_stock
			FROM
				products p
			LEFT JOIN
				stock s
			ON
				s.product_id = p.id
				AND s.state = ?
			WHERE
				p.deleted_at IS NULL
				AND (
					p.name LIKE ? ESCAPE '!'
					OR p.wholesale_name LIKE ? ESCAPE '!'
					OR p.short_name LIKE ? ESCAPE '!'
				)
			GROUP BY
				p.id, p.name, p.sellable, p.unit_sell, p.short_name
			ORDER BY
				p.name ASC
			LIMIT ?";

		return $this->db->query(
			$sql,
			array(
				$location,
				STOCK_IN_USE,
				$like,
				$like,
				$like,
				$limit
			)
		)->result_array();
	}

	/*
		update the comment on a product
	*/
	public function update_comment(int $pid, $msg)
	{
		return (!empty($msg)) ?
				$this->update(array("comment" => $msg), $pid)
			:
				false
			;
	}

	/*
		set backorder to 0 if it was set to 1
	*/
	public function set_backorder_filled( int $product_id )
	{
		return $this->limit(1)->where(array("id" => $product_id, "backorder" => 1))->update(array("backorder" => 0));
	}


	public function usage_detail( int $product_id, string $search_from, string $search_to)
	{
		$sql = "
		select 
			ep.volume,
			events.id as event_id, events.created_at as event_created_at, 
			users.first_name,
			stock.lotnr, stock.eol, stock.in_price,
			pets.name as petname, pets.id as pet_id,
			owners.id, owners.last_name,
			event.name as event_location_name,
			st.name as stock_location_name

		from 
			events_products as ep
		LEFT JOIN
			events
		ON
			events.id = ep.event_id
		LEFT JOIN
			users
		ON
			vet = users.id
		LEFT JOIN 
			stock_location as event
		ON
			events.location = event.id
		LEFT JOIN
			stock
		ON
			ep.stock_id = stock.id

		LEFT JOIN 
			stock_location as st
		ON
			stock.location = st.id

		LEFT JOIN
			pets
		ON
			pets.id = events.pet
		LEFT JOIN
			owners
		ON
			owners.id = pets.owner
		where 
			ep.product_id = '" . $product_id . "' 
		AND
			events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
		AND
			events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
		group by
			ep.id
		order by
			ep.created_at DESC
		";
		return $this->db->query($sql)->result_array();
	}

	# used to generate charts -todo-
	public function product_monthly_use(int $product_id, string $type = "none", int $month = 36)
	{
		$date = date("Y-m-1 00:00", strtotime("-" . $month . " months"));

		# none =)
		$sql = "
			select 
				year(ep.created_at) as y, 
				month(ep.created_at) as m, 
				sum(volume) as p
			from 
				events_products as ep
			JOIN
				events
			ON
				events.id = ep.event_id
			where 
				ep.product_id = '" . $product_id . "' 
			and
				events.created_at > '" . $date . "'
			group by 
				year(ep.created_at), 
				month(ep.created_at)
			order by
				ep.created_at DESC
		";

		if ($type == "per_vet")
		{
			$sql = "
			select 
					year(ep.created_at) as y, 
					month(ep.created_at) as m, 
					sum(volume) as p,
					first_name
				from 
					events_products as ep
				JOIN
					events
				ON
					events.id = ep.event_id
				JOIN
					users
				ON
					vet = users.id
				where 
					ep.product_id = '" . $product_id . "' 
				and
					events.created_at > '" . $date . "'
				group by 
					year(ep.created_at), 
					month(ep.created_at),
					vet
			";
		}
		elseif ($type == "per_location")
		{
			$sql = "
			select 
					year(ep.created_at) as y, 
					month(ep.created_at) as m, 
					sum(volume) as p,
					stock_location.name as stockname
				from 
					events_products as ep
				JOIN
					events
				ON
					events.id = ep.event_id
				join
					stock_location
				on
					stock_location.id = events.location
				where 
					ep.product_id = '" . $product_id . "' 
				and
					events.created_at > '" . $date . "'
				group by 
					year(ep.created_at), 
					month(ep.created_at),
					stockname
			";
		}

		return $this->db->query($sql)->result_array();
	}

	/*
		get product id or return false if not linked
	*/
	public function get_product_id(int $wholesale_id) {
		$product_info = $this->fields('id')->where(array("wholesale" => $wholesale_id))->get();
		return ($product_info) ? (int) $product_info['id']: false;
	}

    /*
    * get all products even if no stock or multiple stocks
	* note: used in products controller
    */
	public function get_products_stocks(string $search_query)
	{
        $sql = "
            SELECT 
                p.id AS product_id, p.name, p.unit_sell, p.btw_sell, p.booking_code, 
                p.vaccin, p.vaccin_freq, p.is_antibiotic, s.id AS stock_id, s.location, s.eol, s.lotnr, s.volume 
            FROM 
                products p 

            LEFT JOIN 
                stock s 
                ON s.product_id = p.id 
                AND s.volume > 0 
                AND s.state = ?

            WHERE 
                p.name LIKE ? ESCAPE '!' 
                AND p.sellable = 1 
                AND p.deleted_at IS NULL

            ORDER BY 
                p.usage_count DESC, 
                p.name ASC, 
                s.location ASC, 
                s.eol ASC 

            LIMIT ?";


			return $this->db->query($sql, 
                        [
                        STOCK_IN_USE,
                        '%' . $search_query . '%', 
                        self::PRODUCT_SEARCH_LIMIT
                        ])->result_array();
	}

	/*
	* get all stocks for a product
	* note: used in products controller
	*/
	public function get_products_by_type_with_stock(int $type_id, int $location_id = null)
	{
		$params = array($location_id, STOCK_IN_USE);

		$sql = "
				SELECT 
					p.id AS product_id,
					p.sellable,
					p.unit_sell,
					p.name,
					p.is_antibiotic,
					p.vaccin,
					COALESCE(SUM(s.volume), 0) AS volume_count,
					COALESCE(SUM(CASE WHEN s.location = ? THEN s.volume ELSE 0 END), 0) AS volume_location
				FROM products p
				LEFT JOIN stock s
					ON s.product_id = p.id
					AND s.volume > 0
					AND s.state = ?
				LEFT JOIN wholesale w
					ON w.id = p.wholesale
				WHERE 
					p.deleted_at IS NULL
		";

		if ($type_id === 0) {
			$sql .= "
					AND p.sellable = 0
			";
		} elseif ($type_id === -1) {
			$sql .= "
					AND p.is_antibiotic = 1
					AND p.sellable = 1
			";
		} elseif ($type_id === -2) {
			$sql .= "
					AND p.vaccin = 1
					AND p.sellable = 1
			";
		} else {
			$sql .= "
					AND p.type = ?
					AND p.sellable = 1
			";
			$params[] = $type_id;
		}

		$sql .= "
				GROUP BY
					p.id, p.sellable, p.unit_sell, p.name
				ORDER BY
					p.name ASC

		";
		
		return $this->db->query($sql, $params)->result_array();

	}
}
