<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Products_model extends MY_Model
{

	public $table = 'products';
	public $primary_key = 'id';
	
	// how many products does product search return
	const PRODUCT_SEARCH_LIMIT = 10;
	
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
			stock_location.name as stockname
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
			stock_location
		ON
			events.location = stock_location.id
		LEFT JOIN
			stock
		ON
			ep.stock_id = stock.id
		AND
			events.location = stock.location
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

	// used in products controller
	public function get_products(string $query)
	{
		$sql = "
		SELECT 
			products.id, products.name, unit_sell, btw_sell, booking_code, vaccin, vaccin_freq
		FROM 
			products
		LEFT JOIN
			stock
		ON
			products.id = stock.product_id
		WHERE 
			name LIKE '%" . $query ."%' ESCAPE '!'
		AND 
			sellable = 1
		AND 
			products.deleted_at IS NULL
		GROUP BY
			products.name
		LIMIT
			". self::PRODUCT_SEARCH_LIMIT ."
			;";
		return $this->db->query($sql)->result_array();
	}

	/*
	* function: get_product_id_by_wholesale
	* get product id by wholesale id
	*/
	function get_id_by_wholesale(int $wholesale_id)
	{
		var_dump($this->fields('id')->where(array("wholesale" => $wholesale_id))->get());
		$product_info = $this->fields('id')->where(array("wholesale" => $wholesale_id))->get();
		return ($product_info) ? (int) $product_info['id'] : NULL;
	}
}
