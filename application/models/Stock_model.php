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
	* show errors in stock
	*/
	public function get_problems()
	{
		$sql = "SELECT
					stock.*,
					products.name as product_name,
					products.id as product_id,
					products.unit_sell,
					stock_location.name as st_location
				FROM
					stock
				join
					products
				on
					products.id = stock.product_id
				join
					stock_location
				on
					stock_location.id = stock.location
				WHERE
					state = ". STOCK_ERROR ."
					OR
					(state = ". STOCK_IN_USE ." AND volume < 0)
				;";
		return $this->db->query($sql)->result_array();
	}

	/*
		called in stock/expired
	*/
	public function write_off(int $stock_id, float $volume)
	{
		# update the stock
		$sql = "
				UPDATE
					stock
				SET
					state = CASE
							WHEN (volume - " . $volume . ") < 0 THEN '" . STOCK_ERROR . "'
							WHEN (volume - " . $volume . ") = 0 THEN '" . STOCK_HISTORY . "'
							ELSE '" . STOCK_IN_USE . "'
						END,
					volume = volume - " . $volume . ",
    				updated_at = NOW()
				WHERE
					id = '" . $stock_id . "'
				LIMIT 1;
			";
		return $this->db->query($sql);
	}

	/*
	* function: reduce
	* reduce the stock with a given volume and a specified stock_id
	* optional location(int), enable_dead_volume(bool)
	* used in stock_model/move
	*/
	public function reduce(int $stock_id, int $product_id, float $volume, int $location = INVALID, bool $dead_volume = true)
	{	
		// if there is dead volume, add this to be removed from stock
		$volume += ($dead_volume) ? $this->get_dead_volume($product_id) : 0;

		# log this remove
		$this->logs->stock(DEBUG, "stock/reduce", $product_id, -$volume, $location);
				
		# update the stock
		return $this->min($stock_id, $volume);
	}

	/*
		in case there is no stock_id, we need to keep track of this
		this is straight up to the error table :(
	*/
	public function fallback_reduce(int $product_id, float $volume, int $location, string $info = NULL)
	{
		// if there is dead volume, add this to be removed from stock
		$volume += $this->get_dead_volume($product_id);

		# if logging is required also log this remove
		if ($this->logs->min_log_level == DEBUG)
		{
			$this->logs->stock(DEBUG, "stock/fallback_reduce", $product_id, -$volume, $location);
		}

		# update the stock
		$this->insert(array(
					"product_id" 	=> $product_id,
					"location" 		=> $location,
					"volume" 		=> -$volume,
					"state"			=> STOCK_ERROR,
					"info"			=> $info
				));
		return true;
	}

	/*
	* function: move
	* move a product from one location to another
	* called in stock/move
	*/
	public function move(int $stock_id, int $from, int $to, float $volume, int $pid, string $info = NULL)
	{
		if ($volume == 0) { 
			$this->logs->logger(DEBUG, "move_stock", "detected 0 unit move " . $from . " to " . $to . " pid:" . $pid . " volume:" . $volume);
			return false;
		}

		# the source row can be gone (removed or merged since the form was opened), nothing to move then
		$original_stock = $this->get($stock_id);
		if (!$original_stock)
		{
			$this->logs->logger(ERROR, "move_stock", "source stock not found, id:" . $stock_id . " from " . $from . " to " . $to . " pid:" . $pid . " volume:" . $volume);
			return false;
		}

		$this->logs->stock(DEBUG, "move_stock_from", $pid, -$volume, $from);
		$this->logs->stock(DEBUG, "move_stock_to", $pid, $volume, $to);
		
		# reduce the stock
		if (!$this->min($stock_id, $volume))
		{
			# shouldn't really happen
			$this->logs->logger(ERROR, "move_stock", "failed to reduce stock, from " . $from . " to " . $to . " pid:" . $pid . " volume:" . $volume);
			$this->insert(array(
				"product_id" 	=> $pid,
				"location" 		=> $from,
				"volume" 		=> -$volume,
				"state"			=> STOCK_ERROR,
				"info"			=> $info 
			));
		}

		# add to the new stock 
		# 2 options here there is already stock (update) or not (insert)
		if (!$this->move_add($stock_id, $volume, $to))
		{
			# since we had an error here once
			# lets trace this
			$this->logs->logger(DEBUG, "move_stock", "none_found for pid:" . $pid . " volume:" . $volume . " from:" . $from . " to:" . $to);

			# we can't find any stock at $to 
			# so create a new one based on the original one (irrelevant of the current state)
			$this->new($pid, $to, $volume, $original_stock['lotnr'], $original_stock['in_price'], $original_stock['eol'], $original_stock['supplier']);
		}
	}

	# group by & count total volume
	public function get_all_products_count()
	{
		$sql = "select
						product_id,
						SUM(volume) as total_volume,
						COUNT(stock.id) as total_stock_locations,
						products.name,
						products.unit_sell,
						products_type.name as type
				from
					stock
				join
					products
				on
					products.id = stock.product_id
				left join
					products_type
				on
					products.type = products_type.id
				WHERE
					state = ". STOCK_IN_USE . "
				group by
					product_id
					;";

		return $this->db->query($sql)->result_array();
	}

	/*
	* called in reports/stock_list
	*/
	public function get_list(int $location = 0, int $procent = 0) 
	{
        // before 0 fix
        // ROUND( (in_price / buy_volume ), 2) as INKOOPPRIJS_STUK,
        // ROUND( ( in_price / buy_volume ) * SUM(volume) , 2) as WAARDE,
        // in_price as INKOOPPRIJS_VERPAKKING

		$sql = "
				SELECT
					products.name,
					SUM(volume) as AANTAL,
					products.unit_sell as VERKOOPSEENHEID,
                    ROUND( (CASE WHEN in_price = 0 THEN 0.01 ELSE in_price END / buy_volume), 2) AS INKOOPPRIJS_STUK,
					lotnr,
					eol,
					products.btw_buy as BTW,
                    ROUND( (CASE WHEN in_price = 0 THEN 0.01 ELSE in_price END / buy_volume) * SUM(volume), 2) AS WAARDE,
                    CASE WHEN in_price = 0 THEN 0.1 ELSE in_price END AS INKOOPPRIJS_VERPAKKING
				FROM 
					stock
				JOIN
					products
				ON
					products.id = stock.product_id
				WHERE
					state = ". STOCK_IN_USE . "
					". (($procent != 0) ? 'AND btw_buy = '. $procent : '')  ."
					". (($location != 0) ? 'AND location = '. $location : '')  ."
				GROUP BY
					products.id, lotnr, in_price
				ORDER BY
					products.name ASC
		";

		return $this->db->query($sql)->result_array();
	}

	public function get_all_products(int $location)
	{
		$sql = "select
						stock.*,
						products.name as product_name,
						products.id as product_id,
						products.unit_sell,
						products_type.name as type,
						stock.updated_at as updated_at,
						stock.created_at as created_at
				from
					stock
				join
					products
				on
					products.id = stock.product_id
				left join
					products_type
				on
					products.type = products_type.id
				WHERE
					state = ". STOCK_IN_USE . "
				AND
					location = '" . $location ."'
				order by
					stock.updated_at DESC
				;";

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
                        p.is_antibiotic,
						s.eol,
						s.lotnr,
						s.volume,
						s.location,
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
		$result = $this->db->query($sql)->result_array();
		
		# in case the location is faulty
		# we wouldn't find this product
		# so we try to find it without the location
		if (!$result)
		{
			$sql = "SELECT
				p.id as pid,
				p.name as pname,
				btw_sell,
				booking_code,
				p.unit_sell,
				p.vaccin,
				p.vaccin_freq,
                p.is_antibiotic,
				p.input_barcode,
				s.eol,
				s.lotnr,
				s.volume,
				s.location,
				s.id as stock_id
			FROM `products` as p
			LEFT JOIN stock as s ON
				p.id = s.product_id
			WHERE
				p.input_barcode = '" . $input_barcode . "' and
				s.lotnr = '" . $lotnr . "' and
				s.eol = '" . $eol . "' and
				s.state = ". STOCK_IN_USE ."
			";

			$result = $this->db->query($sql)->result_array();

			if(count($result) > 1)
			{
				$this->logs->logger(DEBUG, "wrong_gs1_location", "");
			}
		}
		return $result;
	}

	/*
		get stock level (local & global)
		used : stock.php/move_stock (and other places)
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
				$this->logs->logger(FATAL, "merge_stock_failed_to_find_lines", "pid:". $product['product_id'] . "debug:" . implode($product));
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

			$this->logs->logger(INFO, "merge_stock", "pid:" . $product['product_id'] . " had " . $x . " duplicate lines");
			
			$new_merged_products++;
			$lines += $product['product_counter'];
		}
		return array('lines_merged' => $lines, 'new_merged' => $new_merged_products);
	}

	/*
		get all the lotnr for a certain product
		usde in stock/move
	*/
	public function get_product_stock(string $query, int $location)
	{
		$sql = "select 
					name, unit_sell,
					GROUP_CONCAT(stock.id) as stock_ids, GROUP_CONCAT(stock.volume) as stock_volumes, GROUP_CONCAT(stock.lotnr) as stock_lotnrs, GROUP_CONCAT(stock.eol) as stock_eols
				from 
					products 
				RIGHT JOIN
					stock
				ON
					stock.product_id = products.id
				where 
					products.name like '" . $query . "%'
				AND
					stock.state = " . STOCK_IN_USE . "
				AND
					stock.location = " . $location . "
				GROUP BY
					name
				";

		return $this->db->query($sql)->result_array();
	}

	/*
	*	called in cli/stock_clean
	*	When a product is removed, but there remains stock in the system
	*	this stock is removed
	*/
	public function remove_dead_products_from_stock()
	{
		$sql = "SELECT 
					stock.id, 
					products.name,
					stock.volume
                FROM products
                JOIN stock 
				ON products.id = stock.product_id 
                WHERE 
					products.deleted_at IS NOT NULL 
				AND 
					stock.volume > 0
				AND 
					stock.state = " . STOCK_IN_USE
				;
				
		$products = $this->db->query($sql)->result_array();
		foreach ($products as $product)
		{
			$this->logs->logger(WARN, "rm_dead_products", "removed: " . $product['name'] . " volume: " . $product['volume']);
		}
		
		$this->db->query("UPDATE stock JOIN products ON products.id = stock.product_id SET stock.state = " . STOCK_HISTORY . " WHERE products.deleted_at IS NOT NULL AND stock.volume > 0;");
		return $this->db->affected_rows();
	}

	/*
	*	update the usage count on products
	*	based on the last 90 days 
	* 	ran nightly by cron/cli
	*/
	public function recalculate_usage()
	{
		$sql = "
			UPDATE products p
			JOIN (
				SELECT product_id, COUNT(*) AS cnt
				FROM events_products
				WHERE created_at >= NOW() - INTERVAL 90 DAY
				GROUP BY product_id
			) u ON p.id = u.product_id
			SET p.usage_count = u.cnt;
		";
		$this->db->query($sql);
		return $this->db->affected_rows();
	}
	/*
		PRIVATE functions
	*/
	# get product offset
	# this is a amount/volume that is lost every
	# sell; for example dead volume in a needle
	private function get_dead_volume(int $product_id)
	{
		$sql = "SELECT dead_volume FROM products WHERE id = '" . $product_id . "' LIMIT 1;";
		return $this->db->query($sql)->result_array()[0]['dead_volume'];
	}


	/*
	* function: new
	* add a new product to the stock
	*/
	private function new(int $pid, int $location, float $volume, string $lotnr, float $in_price, $eol = null, $supplier = null): bool
	{
		# insert the new stock
		return $this->insert(array(
					"product_id" 	=> $pid,
					"eol" 			=> $eol,
					"location" 		=> $location,
					"in_price" 		=> $in_price,
					"lotnr" 		=> $lotnr,
					"volume" 		=> $volume,
					"supplier"		=> $supplier,
					"state"			=> STOCK_IN_USE
				));
	}

	/*
	* function: add
	* add a product to the stock
	*/
	private function add(int $stock_id, float $volume): bool
	{
		$sql = "
				UPDATE
					stock
				SET
					state = CASE
								WHEN (volume + " . $volume . ") < 0 THEN '" . STOCK_ERROR . "'
								WHEN (volume + " . $volume . ") = 0 THEN '" . STOCK_HISTORY . "'
								ELSE '" . STOCK_IN_USE . "'
							END,
					updated_at = NOW(),
					volume = volume + " . $volume . "
				WHERE
					id = '" . $stock_id . "'
				LIMIT 1;
			";

		# run the query
		$this->db->query($sql);

		# should return 0 (no rows affected) or 1 (one row affected)
		return (bool) $this->db->affected_rows();
	}

	/*
	* function: min
	* reduce the stock with a given volume and a specified stock_id
	*/
	private function min(int $stock_id, float $volume): bool
	{
		return $this->add($stock_id, -$volume);
	}

	/*
	* function: move_add
	* add a product to the stock, this function tries to add
	* to an existing stock_id and returns if this was succefull or not
	* used in stock_model/move
	*/	
	private function move_add(int $stock_id, float $volume, int $to): bool
	{
		$sql = "
			UPDATE 
				stock
			SET
				state = CASE
							WHEN (volume + " . $volume . ") < 0 THEN '" . STOCK_ERROR . "'
							WHEN (volume + " . $volume . ") = 0 THEN '" . STOCK_HISTORY . "'
							ELSE '" . STOCK_IN_USE . "'
						END,
				updated_at = NOW(),
				volume = volume + " . $volume . "
			WHERE 
				(product_id, lotnr, in_price, state) = (SELECT product_id, lotnr, in_price, state FROM stock WHERE id = " . $stock_id . ")
			AND 
				-- possible null values
				 ((supplier IS NULL AND (SELECT supplier FROM stock WHERE id = " . $stock_id . ") IS NULL) OR supplier = (SELECT supplier FROM stock WHERE id = " . $stock_id . "))
			AND
				-- possible null values
				 ((eol IS NULL AND (SELECT eol FROM stock WHERE id = " . $stock_id . ") IS NULL) OR eol = (SELECT eol FROM stock WHERE id = " . $stock_id . "))
			AND
				location = ". $to ."
			LIMIT 1;
			";

		# run the query
		$this->db->query($sql);
		
		# should return 0 (no rows affected) or 1 (one row affected)
		return (bool) $this->db->affected_rows();
	}
}
