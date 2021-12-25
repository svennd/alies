<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stock_model extends MY_Model
{
	public $table = 'stock';
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
					'local_key' => 'location'
				);
		parent::__construct();
	}
	
	/*
		called in invoice
		* take care to deal with dead volume
	*/
	public function reduce_stock($product_id, $volume, $location = false, $barcode = false)
	{
		# we aint doing that
		if ($volume == 0) {
			return false;
		}
		
		# get product offset
		# this is a amount/volume that is lost every
		# sell; for example dead volume in a needle
		$sql = "SELECT id, offset FROM products WHERE id = '" . $product_id . "' LIMIT 1;";
		$product = $this->db->query($sql)->result_array()[0];
		$volume += $product['offset'];
		
		# if we get a barcode and a location, we can substract easy
		if ($barcode && $location) {
			$sql = "UPDATE stock SET volume=volume-" . $volume. " WHERE barcode = '" . $barcode . "' and location = '" . $location . "' limit 1;";
			$this->db->query($sql);
			
			$affected = $this->db->affected_rows();
			
			# TODO : what if there is no stock there ! (this would pass) testing ...
			# woops there was no barcode here (it must have been from somewhere else)
			if ($affected == 0) {
				$this->stock->insert(array(
							"product_id" 	=> $product['id'],
							"location" 		=> $location,
							"volume" 		=>	-$volume,
							"barcode" 		=> $barcode,
							"state"			=> STOCK_ERROR
						));
			}
		} elseif ($location) {
			# check if for this product there is only one stock;
			$result = $this->stock->where(array("product_id" => $product_id, "location" => $location))->fields('barcode')->get_all();
			
			if ($result && count($result) == 1) {
				$sql = "UPDATE stock SET volume=volume-" . $volume. " WHERE barcode = '" . $result[0]['barcode'] . "' and location = '" . $location . "' limit 1;";
				$this->db->query($sql);
			} else {
				$this->insert(array("product_id" => $product_id, "volume" => -$volume, "location" => $location, "state" => STOCK_ERROR));
			}
		}
		# no location
		else {
			$this->insert(array("product_id" => $product_id, "volume" => -$volume, "state" => STOCK_ERROR));
		}
		
		$this->maintain_stock_state($product_id);
	}
	
	private function maintain_stock_state($product_id)
	{
		# check for empty bottles
		$sql = "UPDATE stock SET state = " . STOCK_HISTORY . " WHERE product_id='" . $product_id . "' AND state = " . STOCK_IN_USE . " AND volume = '0';";
		$this->db->query($sql);
		
		# check for issues
		$sql = "UPDATE stock SET state = " . STOCK_ERROR . " WHERE product_id='" . $product_id . "' AND volume < '0';";
		$this->db->query($sql);
	}
	
	public function reduce_product($barcode, $from, $value)
	{
		$sql = "UPDATE stock SET volume=volume-" . $value. " WHERE barcode = '" . $barcode . "' and location = '" . $from . "' and state = '" . STOCK_IN_USE . "' limit 1;";
		$this->db->query($sql);
	}
	
	public function add_product_to_stock($barcode, $from, $to, $value)
	{		
		# check if there is already stock there, if so increase
		# else add new
		$product_on_to = $this->where(array("barcode" => $barcode, "location" => $to))->get();
		
		if ($product_on_to) {
			$sql = "UPDATE stock SET volume=volume+" . $value. ", state='" . STOCK_IN_USE . "' WHERE barcode = '" . $barcode . "' and location = '" . $to . "' limit 1;";
			$this->db->query($sql);
			
		} else {
			$from_info = $this->where(array("barcode" => $barcode, "location" => $from))->get();
			$this->insert(array(
								"product_id" 	=> $from_info['product_id'],
								"eol" 			=> $from_info['eol'],
								"location" 		=> $to,
								"in_price" 		=> $from_info['in_price'],
								"lotnr" 		=> $from_info['lotnr'],
								"volume" 		=> $value,
								"barcode" 		=> $barcode,
								"state"			=> STOCK_IN_USE
								));
		}
	}
	
	# check for stock that is going to become bad soonish
	public function get_bad_products($location = false, $days = 90)
	{
		$sql = "";
		return $this->db->query($sql)->result_array();
	}
	
	# return stock limits
	public function get_local_stock_shortages($location = false)
	{
		$sql = "SELECT 
					stock_limit.stock as location,
					stock_limit.volume as required_volume, 
					sum(stock.volume) as available_volume, 
					stock_limit.product_id as product_detail,
					(select sum(stock.volume) from stock where product_id = stock_limit.product_id) as all_volume,
					products.name,
					products.unit_sell
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
				";
			
		if ($location)
		{
			$sql = "SELECT 
						stock_limit.stock as location,
						stock_limit.volume as required_volume, 
						sum(stock.volume) as available_volume, 
						stock_limit.product_id as product_detail,
						(select sum(stock.volume) from stock where product_id = stock_limit.product_id) as all_volume,
						products.name,
						products.unit_sell
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
		}
		return $this->db->query($sql)->result_array();
	}
	
	# group by & count total volume
	public function get_all_products()
	{
		$sql = "select 
						product_id, 
						SUM(volume) as total_volume, 
						COUNT(stock.id) as total_stock_locations,
						products.name,
						products.unit_sell
				from 
					stock 
				join
					products
				on
					products.id = stock.product_id
				group by 
					product_id;";
					
		return $this->db->query($sql)->result_array();
	}
	
	public function gs1_lookup($input_barcode, $lotnr, $eol, $location)
	{
		$sql = "SELECT 
						p.id as pid, 
						p.name as pname, 
						btw_sell, 
						booking_code, 
						p.input_barcode, 
						s.eol,
						s.lotnr,
						s.barcode,
						s.id as stock_id
					FROM `products` as p 
					LEFT JOIN stock as s ON 
						p.id = s.product_id
					WHERE 
						p.input_barcode = '" . $input_barcode . "' and 
						s.lotnr = '" . $lotnr . "' and 
						s.eol = '" . $eol . "' and
						s.location = '" . $location . "' and 
						s.state = ". STOCK_IN_USE ."
					ORDER BY
						s.in_price
					LIMIT
						1
				";
				
		return $this->db->query($sql)->result_array();
	}
	
	/*
		used on stock/stock_detail to show the usage
	*/
	public function get_usage($product_id)
	{
		$sql = "select  
					month(created_at) as month, 
					year(created_at) as year,
					product_id, 
					count(volume) as volume
				from 
					events_products 
				where 
					created_at >= (NOW() - INTERVAL 6 MONTH)
				and
					product_id = '" . (int) $product_id . "'
				GROUP BY 
					month(created_at);
			";
		return ($this->db->query($sql)->result_array());
	}
	
	
	/*
		reports/stock_list
	*/
	public function get_stock_list($location)
	{
		$sql = "SELECT  
					SUM(volume) as volume,
					GROUP_CONCAT(volume SEPARATOR '; ') as concat_volume,
					GROUP_CONCAT(eol SEPARATOR '; ') as eol,
					GROUP_CONCAT(lotnr SEPARATOR '; ') as lotnr,
					GROUP_CONCAT(DATE_FORMAT(s.created_at, '%H:%i %d/%c/%y') SEPARATOR '; ') as created_at,
					p.name, 
					p.unit_buy
				FROM 
					stock as s
				JOIN products as p ON 
					p.id = product_id
				WHERE 
					location = '" . $location . "' 
				AND
					state = ". STOCK_IN_USE ."
				GROUP BY 
					product_id
				ORDER BY
					p.name ASC;
			";
		return ($this->db->query($sql)->result_array());
	}
}
