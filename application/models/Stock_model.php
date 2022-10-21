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
		$sql = "SELECT id, dead_volume FROM products WHERE id = '" . $product_id . "' LIMIT 1;";
		$product = $this->db->query($sql)->result_array()[0];
		$volume += $product['dead_volume'];

		# if we get a barcode and a location, we can substract easy
		if ($barcode && $location) {
			$affected = $this->reduce_product($barcode, $location, $volume);

			# TODO : what if there is no stock there ! (this would pass) testing ...
			# woops there was no barcode here (it must have been from somewhere else)
			if ($affected == 0) {
				$this->log->stock(ERROR, "reduce_stock_barcode_location", $product_id, -$volume, $location);
				$this->stock->insert(array(
							"product_id" 	=> $product_id,
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
				$this->reduce_product($result[0]['barcode'], $location, $volume);
			} else {
				$this->log->stock(ERROR, "reduce_stock_location", $product_id, -$volume, $location);
				$this->insert(array("product_id" => $product_id, "volume" => -$volume, "location" => $location, "state" => STOCK_ERROR));
			}
		}
		# no location
		else {
			$this->log->stock(ERROR, "reduce_stock_unknown", $product_id, -$volume, $location);
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
		$sql = "UPDATE stock SET state = " . STOCK_ERROR . " WHERE product_id='" . $product_id . "' AND volume < '0' AND state != " . STOCK_ERROR . ";";
		$this->db->query($sql);
	}

	public function reduce_product($barcode, $from, $value)
	{
		# if logging is required also log this remove
		if ($this->logs->min_log_level == DEBUG)
		{
			$info = $this->stock->fields('product_id')->where(array("barcode" => $barcode))->get();
			$this->logs->stock(DEBUG, "reduce_product", $info['product_id'], -$value, $from);
		}

		$sql = "UPDATE stock SET volume=volume-" . $value. " WHERE barcode = '" . $barcode . "' and location = '" . $from . "' and state = '" . STOCK_IN_USE . "' limit 1;";
		$this->db->query($sql);

		return $this->db->affected_rows();
	}

	public function add_product_to_stock($barcode, $from, $to, $value)
	{
		# check if there is already stock there, if so increase
		# else add new
		$product_on_to = $this->where(array("barcode" => $barcode, "location" => $to))->get();

		if ($product_on_to) {
			$this->reduce_product($barcode, $to, -$value);
		} else {
			$from_info = $this->where(array("barcode" => $barcode, "location" => $from))->get();
			
			$this->logs->stock(DEBUG, "add_to_stock", $from_info['product_id'], $value, $to);
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
				WHERE
					state = ". STOCK_IN_USE . "
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
						p.unit_sell,
						p.vaccin,
						p.vaccin_freq,
						p.input_barcode,
						s.eol,
						s.lotnr,
						s.barcode,
						s.volume,
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
					sum(volume) as volume
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
		get stock level (local & global)
	*/
	public function get_stock_levels(int $product_id, int $location)
	{
		$sql = "SELECT
							SUM(volume) as sum_volume,
							location
						FROM
							stock
						WHERE
							`product_id` = '" . $product_id . "'
							AND
							`state` = '". STOCK_IN_USE ."'
						GROUP BY
							`location`
				";

		$result = $this->db->query($sql)->result_array();

		if (!$result) { return array(0, 0); }

		$total_volume = 0;
		$local_volume = 0;

		foreach ($result as $r)
		{
			$total_volume += $r['sum_volume'];
			if ($location == $r['location'])
			{
				$local_volume = $r['sum_volume'];
			}
		}

		return array($local_volume, $total_volume);
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

	/*
		reduce duplicates
		used in stock/stock_clean()
	*/
	public function fix_duplicates()
	{
		$sql = "SELECT
					product_id,
					`location`,
					lotnr,
					eol,
					in_price,
					barcode, # take the last one
					SUM(volume) as volume,
					COUNT(product_id) as product_counter
				FROM
					stock
				WHERE
					`state` = ". STOCK_IN_USE ."
				GROUP BY
					product_id,
					`location`,
					lotnr,
					in_price,
					eol
				having
					product_counter > 1
				;
			";

		$products = $this->db->query($sql)->result_array();
		
		$new_merged_products = 0;
		$lines = 0;
		foreach ($products as $product)
		{
			$x = $this->stock
						->where(array(
									'product_id' => $product['product_id'],
									'location' => $product['location'],
									'lotnr' => $product['lotnr'],
									'in_price' => $product['in_price'],
									'eol' => $product['eol']
								))
						->update(array('volume' => 0, 'state' => STOCK_MERGE));
			# this should never happen
			# this means we found a "duplicate" where there are only 0 or 1 lines
			if ($x <= 1)
			{
				$this->logs->logger($this->user->id, FATAL, "merge_stock_failed_to_find_lines", "pid:". $product['product_id'] . "debug:" . implode($product));
			}

			$this->insert(array(
					'product_id' => $product['product_id'],
					'location' => $product['location'],
					'lotnr' => $product['lotnr'],
					'in_price' => $product['in_price'],
					'eol' => $product['eol'],
					'barcode' => $product['barcode'],
					'volume' => $product['volume'],
					'state' => STOCK_IN_USE
			));

			$this->logs->logger($this->user->id, INFO, "merge_stock", "pid:" . $product['product_id'] . " had " . $x . " duplicate lines");
			
			$new_merged_products++;
			$lines += $product['product_counter'];
		}
		return array('lines_merged' => $lines, 'new_merged' => $new_merged_products);
	}
}
