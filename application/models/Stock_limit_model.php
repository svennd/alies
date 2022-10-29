<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stock_limit_model extends MY_Model
{
	public $table = 'stock_limit';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['products'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'id',
			'local_key' => 'product_id'
		);

		$this->has_one['stock_locations'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'stock'
		);
		parent::__construct();
	}

	public function local_shortage(int $location)
	{
		$last_month = date('m', strtotime(date('Y-m-d')." -1 month"));
		$sql = "
		SELECT
			sum(stock.volume) as available_volume,
			stock_limit.stock,
			stock_limit.volume as required_volume,
			stock_limit.product_id as product_detail,
			products.name, products.unit_sell, products.limit_stock as global_limit,
			(select sum(stock.volume) from stock where product_id = stock_limit.product_id and state = '" . STOCK_IN_USE . "') as all_volume,
			(select sum(events_products.volume) from events_products where product_id = stock_limit.product_id and MONTH(created_at) = '" . $last_month . "') as global_use,
			(
				select 
					sum(events_products.volume) 
				from 
					events_products 
				left join
					events
				on
					events.id = events_products.event_id
				where 
					product_id = stock_limit.product_id 
				and 
					events.location = stock_limit.stock
			) as local_use
		FROM
			stock_limit
		LEFT JOIN
			stock
		ON
			stock.product_id = stock_limit.product_id
			and
			stock.location = stock_limit.stock
		JOIN
			products
		ON
			stock_limit.product_id = products.id
		GROUP BY
			stock.product_id,
			stock.location
		HAVING
			available_volume < required_volume
		and
			stock_limit.stock = '" . (int) $location . "'
		";
		return $this->db->query($sql)->result_array();
	}
}
