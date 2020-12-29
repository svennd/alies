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
		$sql = "UPDATE stock SET volume=volume-" . $value. " WHERE barcode = '" . $barcode . "' and location = '" . $from . "' limit 1;";
		$this->db->query($sql);
	}
	
	public function add_product_to_stock($barcode, $from, $to, $value)
	{
		# check if there is already stock there, if so increase
		# else add new
		$product_on_to = $this->where(array("barcode" => $barcode, "location" => $to))->get();
		if ($product_on_to) {
			// var_dump($product_on_to);
			// $product_on_to['id']
			$sql = "UPDATE stock SET volume=volume+" . $value. " WHERE barcode = '" . $barcode . "' and location = '" . $to . "' limit 1;";
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
	
	# return stock limits
	public function get_local_stock_shortages()
	{
		$sql = "select 
					stock_limit.stock as location,
					stock_limit.volume as required_volume, 
					stock.volume as available_volume, 
					products.name,
					products.unit_sell
				from 
					stock_limit 
				LEFT JOIN 
					stock 
				on 
					stock.product_id = stock_limit.product_id 
					and 
					stock.location = stock_limit.stock 
				JOIN 
					products 
				on 
					stock_limit.product_id = products.id
					
				where 
					(
					stock_limit.volume > stock.volume
					or
					stock.volume is null
					)
				";
					
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
}
