<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Product_price_model', 'pprice');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Booking_code_model', 'booking');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Stock_limit_model', 'stock_limit');
		$this->load->model('Wholesale_model', 'wholesale');

		# helpers
		$this->load->helper('gs1');
	}

	public function index($location = false, $success = false)
	{

		$products = array();
		$clocation = ($location) ? $location : $this->user->current_location;
		
		$products = ($location == "all") ? 
					$this->stock->get_all_products_count() 
					: 
					$this->stock->get_all_products($clocation);

		$data = array(
						"search_q"					=> $this->input->post('name'),
						"types" 					=> $this->prod_type->get_all(),
						"expired"					=> $this->stock
																		->fields('eol, volume')
																		->where('eol < DATE_ADD(NOW(), INTERVAL +90 DAY)', null, null, false, false, true)
																		->where('eol > DATE_ADD(NOW(), INTERVAL -10 DAY)', null, null, false, false, true)
																		->where(array('state' => STOCK_IN_USE, 'location' => $this->user->current_location))
																		->with_products('fields: id, name, unit_sell')
																		->order_by('eol', 'ASC')
																		->count_rows(),
						"locations" 			=> $this->location,
						"success" 				=> $success,
						"clocation"				=> $clocation,
						"search"				=> ($this->input->post('submit')) ? $this->products->group_start()->like('name', $this->input->post('name'), 'both')->or_like('short_name', $this->input->post('name'), 'both')->group_end()->limit(25)->get_all() : false,
						"products" 				=> $products,
						);

		$this->_render_page('product/index', $data);
	}


	/*
		semi "public" profile of a product
	*/
	public function profile($id)
	{
		# update comment
		$comment_update = false;
		if ($this->input->post('submit')) {
			$this->products
					->where(array(
									"id" 	=> (int) $id
							))
					->update(array(
									"comment" => $this->input->post('message'),
							));
			$comment_update = true;
		}

		# check the stocks
		// local is not required anymore 
		list($local_stock, $global_stock) = $this->stock->get_stock_levels($id, $this->user->current_location);

		# check if there is a local limit
		$local_limit_query = $this->stock_limit->fields('volume')->where(array('product_id' => $id, 'stock' => $this->user->current_location))->get();
		$local_limit = ($local_limit_query) ? $local_limit_query['volume'] : 0;

		$data = array(
				'product' 		=> $this->products->
										with_prices('fields:id, volume, price|order_inside:volume asc')->
										with_type('fields:name')->
										with_booking_code('fields:category, code, btw')->
										with_stock('fields: location, eol, lotnr, volume, barcode, state, created_at', 'where:`state`=\'1\'')->
										get($id),
				'global_stock' 	=> $global_stock,
				'local_stock' 	=> $local_stock,
				'local_limit' 	=> $local_limit,
				'comment_update'=> $comment_update,
				'locations'		=> $this->location,
				'history_1m'	=> $this->eprod->select('SUM(volume) as sum_vol', false)->fields()->where('created_at > DATE_ADD(NOW(), INTERVAL -30 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->group_by('product_id')->get(),
				'history_6m'	=> $this->eprod->select('SUM(volume) as sum_vol', false)->fields()->where('created_at > DATE_ADD(NOW(), INTERVAL -180 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->group_by('product_id')->get(),
				'history_1y'	=> $this->eprod->select('SUM(volume) as sum_vol', false)->fields()->where('created_at > DATE_ADD(NOW(), INTERVAL -365 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->group_by('product_id')->get(),
				);

		$this->_render_page('product/profile', $data);
	}

	public function new($step = false, $pid = false)
	{
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }

		if ($this->input->post('submit') && !$step)
		{
			$this->new_product_step_1();
		}
		elseif ($this->input->post('submit') && $step == 2 && $pid)
		{
			$this->new_product_step_2($pid);
		}

		# populate the data array
		$data = array(
				'step'		=> (!$step) ? 1 : (int)$step,
				'type' 		=> $this->prod_type->get_all(),
				'booking'	=> $this->booking->get_all(),
				'product'	=> ($step) ?
							$this->products
								->with_prices('fields:volume, price, id|order_inside:volume asc')
								->where(array("sellable" => 1))
								->fields('id, name, buy_volume, buy_price, updated_at, unit_sell')
								->get($pid)
							: false,
				);
		$this->_render_page('product/product_new', $data);
	}

	public function product_price($id = false)
	{
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }

		# show list with all products prices
		# unless single product is selected
		if (!$id) {
			$this->list_product_page();
			return true;
		}

		# modification
		if ($this->input->post('submit') && $this->input->post('submit') == "edit") {
			# price id
			$price_id = $this->input->post('price_id');
			$volume = $this->input->post('volume');
			$new_price = $this->input->post('price');

			# log this change
			$this->logs->logger(INFO, "modify_price", "Change product_id:" . (int) $id . " on price_id:" . (int) $price_id . " for volume: ". (float) $volume . " to (price)" . (float) $new_price);
			$this->pprice
					->where(array(
									"id" 	=> $price_id
							))
					->update(array(
									"price" => $new_price,
									"volume" => $volume,
							));
			
		# new price
		} elseif($this->input->post('submit')) {
			$this->pprice->insert(array(
										'volume' 		=> $this->input->post('volume'),
										'price' 		=> $this->input->post('price'),
										'product_id' 	=> $id
								));
		}

		$data = array(
						"product" 		=> $this->products
												->with_prices('fields:volume, price, id|order_inside:volume asc')
												->with_wholesale()
												->where(array("sellable" => 1))
												->fields('id, name, buy_volume, buy_price, updated_at, unit_buy, unit_sell')
												->get($id),

						"stock_price"	=> $this->stock
												->where(array("product_id" => $id))
												->fields('in_price, volume, created_at')
												->order_by('created_at', 'DESC')
												->limit(5)
												->get_all()
					);
		$this->_render_page('product/price', $data);

	}

	/*
		generate a list with pricings for all products
	*/
	private function list_product_page()
	{
		$data = array(
						"products" 		=> $this->products
												->with_prices('fields:volume, price|order_inside:volume asc')
												->fields('name, buy_volume, buy_price, sellable, updated_at, unit_sell')
												->where(array("sellable" => 1))
												->get_all()
					);

		$this->_render_page('product_price_list', $data);
	}

	public function remove_product_price($id, $new = false)
	{
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }

		$to_remove_price = $this->pprice->get($id);
		$this->pprice->delete($id);

		if (!$new)
		{
			redirect('/products/product_price/' . $to_remove_price['product_id']);
		}
		else
		{
			redirect('/products/new/2/' . $to_remove_price['product_id']);
		}
	}

	/*
	generate a list with all products based on category
	or if none is set "other"
	*/
	public function product_list($id_or_product = false)
	{
		$id = ($id_or_product == "other") ? 0 : $id_or_product;
		$data = array(
						"products" 		=> ($id_or_product) ? $this->products
																		->with_prices('fields:volume, price|order_inside:volume asc')
																		->with_booking_code()
																		->with_type('fields:name')
																		->where('type', $id)
																		->get_all() : false,
						"types" 			=> $this->prod_type->get_all()
					);

		$this->_render_page('products_list', $data);
	}

	public function product(int $id)
	{
		$update = false;
		if ($this->input->post('submit')) {
			$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));

			$input = array(
								"name" 					=> $this->input->post('name'),
								"short_name" 			=> $this->input->post('short_name'),
								"producer" 				=> $this->input->post('producer'),
								"supplier" 				=> $this->input->post('supplier'),
								"posologie" 			=> $this->input->post('posologie'),
								"toedieningsweg" 		=> $this->input->post('toedieningsweg'),
								"type" 					=> $this->input->post('type'),
								"dead_volume"			=> $this->input->post('dead_volume'),
								"buy_volume" 			=> $this->input->post('buy_volume'),
								"sell_volume" 			=> $this->input->post('sell_volume'),
								"buy_price"				=> $this->input->post('buy_price'),
								"unit_buy" 				=> $this->input->post('unit_buy'),
								"unit_sell" 			=> $this->input->post('unit_sell'),
								"input_barcode" 		=> (empty($this->input->post('input_barcode')) ? NULL : $this->input->post('input_barcode')),
								"btw_buy" 				=> $this->input->post('btw_buy'),
								"btw_sell" 				=> $booking['btw'],
								"vaccin" 				=> (is_null($this->input->post('vaccin')) ? 0 : 1),
								"vaccin_freq" 			=> $this->input->post('vaccin_freq'),
								"booking_code" 			=> $this->input->post('booking_code'),
								"delay" 				=> $this->input->post('delay'),
								"comment" 				=> $this->input->post('comment'),
								"vhbcode" 				=> $this->input->post('vhbcode'),
								"wholesale"				=> $this->input->post('wholesale'),
								"buy_price_date" 		=> $this->input->post('buy_price_date'),
								"sellable" 				=> (is_null($this->input->post('sellable')) ? 0 : 1),
								"limit_stock" 			=> $this->input->post('limit_stock')
							);

			$update = $this->products->update($input, $id);
			
			# add or update local limits
			$this->set_local_limits($this->input->post('limit'), $id);

			# log this
			$this->logs->logger(INFO, "update_product", " id : " . $id . " data:" . var_export($input, true));
		}

		$data = array(
						'product' 			=> $this->products->with_prices('fields:id, volume, price|order_inside:volume asc')->get($id),
						'type' 				=> $this->prod_type->get_all(),
						'update'			=> $update,
						'llimit'			=> $this->stock_limit->with_stock_locations('fields:name')->where(array('product_id' => $id))->get_all(),
						'stock_locations'	=> $this->stock_location->get_all(),
						'booking'			=> $this->booking->get_all(),
						'history_1m'		=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -30 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_6m'		=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -180 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_1y'		=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -365 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						);
		$this->_render_page('product/details', $data);
	}

	/*
		set or add local limits
	*/
	private function set_local_limits(array $limits, int $product_id)
	{
		foreach($limits as $stock => $value)
		{
			$key = array_keys($value)[0];
			$val = array_values($value)[0];

			# not a new key : do update
			if($key != -1)
			{
				$this->stock_limit->where(array('id' => $key))->update(array('volume' => $val));
			}
			else
			{
				# skip 0 volumes
				if ($val == 0) { continue; }
				$this->stock_limit->insert(array('stock' => (int) $stock, 'product_id' => $product_id, 'volume' => $val ));
			}
		}
	}

	/*
	delete product
	*/
	public function delete_product($id)
	{
		# in order to delete a product, it might be worth it to check wheter we still have stock ?
		$this->products->delete($id);
		redirect('/products/product_list');
	}

	# ajax request to return lot nr and eol date (in case there is no lotnr)
	public function get_lot_nr()
	{
		$result = $this->stock
			->fields('lotnr, eol, volume, barcode, location')
			->where(array(
							"product_id" 		=> $this->input->post('pid')
							))
			->get_all();

		echo ($result) ? json_encode($result) : json_encode(array());
	}

	# get product by barcode, ajax return
	public function get_product_by_barcode()
	{
		/* this should be deprecated : perhaps for internal barcodes ? */

		// $query = $this->input->get('barcode');
		// $loc = $this->input->get('loc');
		// $gsl = parse_gs1($query);
		// var_dump($gsl);
		// // not right format
		// if (!$gsl) { return $return; }
		//
		// // search for product w/ this barcode
		// $return = $this->get_gs1_barcode($gsl);
		//
		// echo json_encode(array("query" => $query, "suggestions" => $return));

		$result = $this->stock
					->fields('eol, barcode, volume')
					->with_products('fields: name, unit_sell, btw_sell, booking_code')
				->where(array(
					'barcode' 	=> $this->input->get('barcode'),
					'location' 	=> $this->input->get('loc')
				))->get();

		echo ($result) ? json_encode($result) : json_encode(array());
	}


	/*
		get_product is used in stock_add
		cause we can only add stock products
	*/
	public function get_product()
	{
		$query = $this->input->get('query');

		# too short
		if (strlen($query) <= 1) { echo json_encode(array("query" => $query, "suggestions" => array())); return 0; }

		/*
			if string is 26 chars long try to check if its a gs1 code
		*/
		$gsl = (strlen($query) >= 26) ? parse_gs1($query) : false;

		// gs1 lookup or generic product name
		$result = ($gsl) ? 
						$this->products
							->fields('id, name, type, buy_volume, unit_buy, sell_volume, unit_sell, buy_price')
							->with_type()
							->where(array('input_barcode' => $gsl['pid']))
							->get_all() // only 1 can return but code expects an array!
						:
						$this->products
							->fields('id, name, type, buy_volume, unit_buy, sell_volume, unit_sell, buy_price')
							->with_type()
							->where('name', 'like', $query, true)
							->where('short_name', 'like', $query, true) // not always visible
							->limit(20)
							->order_by("type", "ASC")
							->get_all()
						;
		# in case no results
		if (!$result) { echo json_encode(array("query" => $query, "suggestions" => array())); return 0; }

		$return = array();
		foreach ($result as $r) {
			$return[] = array(
						"value" => $r['name'],
						"data" 	=> array(
											"type" 				=> (isset($r['type']['name']) ? $r['type']['name'] : "other"),
											"id" 				=> $r['id'],
											"buy_volume"		=> $r['buy_volume'],
											"unit_buy"			=> $r['unit_buy'],
											"sell_volume"		=> $r['sell_volume'],
											"unit_sell"			=> $r['unit_sell'],
											"buy_price"			=> $r['buy_price'],
										)
						);
		}
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}

	/*
		similar function gto gs1_to_barcode
		this is used in stock_add.php
	*/
	public function gs1_to_product($gls = false, $return = false)
	{
		$gs1 = ($gls) ? $gls : $this->input->get('gs1');

		$result = $this->products
							->fields('id, name, buy_volume, unit_buy, sell_volume, unit_sell, buy_price')
							->limit(2)
							->where('input_barcode', $gs1)
							->get();

		if (!$return) {
			echo ($result) ? json_encode(array("state" => 1, $result)) : json_encode(array("state" => 0));
		} else {
			return ($result) ? json_encode(array("state" => 1, $result)) : json_encode(array("state" => 0));
		}
	}

	# return an ajax readable object of possible results
	public function get_product_or_procedure()
	{
		$query = $this->input->get('query');
		$return = array();

		/*
			if string is 20 chars long its most likely GS1 barcode
		*/
		if (strlen($query) >= 20)
		{
			$gsl = parse_gs1($query);

			// not right format
			if (!$gsl) { return $return; }

			// search for product w/ this barcode
			$return = $this->get_gs1_barcode($gsl);
		}

		if (strlen($query) > 1)
		{
			$return = $this->get_products($query, $return);
			$return = $this->get_procedures($query, $return);
		}

		echo json_encode(array("query" => $query, "suggestions" => $return));
	}

	private function get_gs1_barcode(array $gsl) {

		// init
		$return = array();

		// lookup in database
		$stck = $this->stock->gs1_lookup($gsl['pid'], $gsl['lotnr'], $gsl['date'], $this->user->current_location);

		if (!$stck) { return $return; }

		# log this if there are multiple returns (==> bad gsl code )
		if (count($stck) > 1)
		{
			$this->logs->logger(ERROR, "multi hit on gsl code", var_export($stck, true));
		}

		# should only return a single result
		$result = $stck['0'];
		// var_dump($result);

		$query_prices = $this->pprice->where(array('product_id' => (int)$result['pid']))->order_by('volume', 'ASC')->get_all();
		$prices = array();
		foreach ($query_prices as $s) {
			$prices[] = array(
								"volume" 	=> $s['volume'],
								"price" 	=> $s['price'],
								);
		}

		$return[] = array(
						"value" => $result['pname'],
						"data" => array(
								"type" 		=> "barcode",
								"id" 			=> $result['pid'],
								"lotnr"		=> $gsl['lotnr'],
								"prices" 	=> $prices,
								"barcode"		=> $result['barcode'], // internal barcode
								"unit"		=> $result['unit_sell'],
								"volume"		=> $result['volume'],
								"btw" 		=> $result['btw_sell'],
								"booking" => $result['booking_code'],
								"vaccin"			=> $result['vaccin'],
								"vaccin_freq"	=> $result['vaccin_freq'],
								"prod" 		=> 1,
							));

		return $return;
	}

	private function get_products($query, $return) {
		# products
		$result = $this->products
							->fields('id, name, type, unit_sell, btw_sell, booking_code, vaccin, vaccin_freq')
							->with_type()
							->with_prices('fields: volume, price|order_inside:volume asc')
							->with_stock('fields: location, eol, lotnr, volume, barcode, state|order_inside:eol asc', 'where:`state`=\'1\'')
							->where('name', 'like', $query, true)
							->where('sellable', '1')
							->limit(250) # this will count both products + prices + stock (somehow)
							->order_by("type", "ASC")
							->get_all();

		# in case no results
		if (!$result) { return $return; }

		foreach ($result as $r) {
			$stock = array();
			$prices = array();

			# there is stock
			if (isset($r['stock'])) {
				foreach ($r['stock'] as $s) {
					$stock[] = array(
										"location" 	=> $s['location'],
										"lotnr" 	=> $s['lotnr'],
										"volume" 	=> $s['volume'],
										"barcode" 	=> $s['barcode'],
										"eol" 		=> $s['eol']
										);
				}
			}

		# there are prices
		if ($r['prices']) {
			foreach ($r['prices'] as $s) {
				$prices[] = array(
									"volume" 	=> $s['volume'],
									"price" 	=> $s['price'],
									);
			}
		}

		$return[] = array(
					"value" => $r['name'],
					"data" 	=> array(
										"type" 				=> (isset($r['type']['name']) ? $r['type']['name'] : "other"),
										"id" 					=> $r['id'],
										"stock"				=> $stock,
										"prices"			=> $prices,
										"unit"				=> $r['unit_sell'],
										"btw"					=> $r['btw_sell'],
										"booking"			=> $r['booking_code'],
										"vaccin"			=> $r['vaccin'],
										"vaccin_freq"	=> $r['vaccin_freq'],
										"prod"				=> 1
									)
					);
		}
		return $return;
	}

	/*
		query procedures
	*/
	private function get_procedures(string $query, array $list) {

		$result = $this->procedures
							->fields('id, name, price, booking_code')
							->where('name', 'like', $query, true)
							->get_all();

		if (!$result) { return $list; }

		foreach ($result as $r) {
			$list[] = array(
								"value" => $r['name'],
								"data" 	=> array(
												"type" 		=> "Proc",
												"id" 			=> $r['id'],
												"price"		=> $r['price'],
												"btw"			=> "21",
												"booking"	=> $r['booking_code'],
												"prod"		=> 0
											)
							);
		}
		return $list;
	}

	/*

	*/
	public function a_pid_by_type( $id )
	{
		$products = $this->products
								->fields('id, name, unit_sell')
								->with_stock('fields:volume')
								->where('type', $id)
								->get_all();

			$return = array();
			foreach ($products as $pod) {

				# if there is volume, calculate howmuch
				# a single query could improve this by doing
				# a SUM operation
				$stock = 0;
				if (isset($pod['stock']))
				{
					foreach ($pod['stock'] as $st)
					{
						$stock += $st['volume'];
					}
				}

				$return [] = array($pod['id'], $pod['name'], $pod['unit_sell'], $stock);
			}
		echo json_encode(array("data" => $return));
	}

	// enter the basic details of the product in the products table
	private function new_product_step_1()
	{
		$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));

		$input = array(
						"name" 				=> $this->input->post('name'),
						"short_name" 		=> $this->input->post('short_name'),
						"producer" 			=> $this->input->post('producer'),
						"supplier" 			=> $this->input->post('supplier'),
						"type" 				=> $this->input->post('type'),
						"dead_volume"		=> $this->input->post('dead_volume'),
						"buy_volume" 		=> $this->input->post('buy_volume'),
						"sell_volume" 		=> $this->input->post('sell_volume'),
						"buy_price"			=> 1,
						"unit_buy" 			=> $this->input->post('unit_buy'),
						"unit_sell" 		=> $this->input->post('unit_sell'),
						"input_barcode" 	=> (empty($this->input->post('input_barcode')) ? NULL : $this->input->post('input_barcode')),
						"btw_buy" 			=> $this->input->post('btw_buy'),
						"btw_sell" 			=> $booking['btw'],
						"vaccin" 			=> (is_null($this->input->post('vaccin')) ? 0 : 1),
						"vaccin_freq" 		=> $this->input->post('vaccin_freq'),
						"booking_code" 		=> $this->input->post('booking_code'),
						"sellable" 			=> (is_null($this->input->post('sellable')) ? 0 : 1),
						"limit_stock" 		=> $this->input->post('limit_stock')
					);

		# new product
		$pid = $this->products->insert($input);

		# log this
		$this->logs->logger(INFO, "new_product", "product_name: " . $this->input->post('name') . " id : " . $pid);
		
		# redirect to next step
		redirect( 'products/new/2/' . $pid );
	}

	// update the pricing of a product of a new product
	private function new_product_step_2(int $pid)
	{
		# update buy_price
		if (!empty($this->input->post('buy_price'))) {
			$this->products->update(array("buy_price" => $this->input->post('buy_price')), $pid);
		}

		# modification
		if ($this->input->post('submit') == "edit") {
			$this->pprice
					->where(array(
									"id" 	=> $this->input->post('price_id')
							))
					->update(array(
									"volume" => $this->input->post('volume'),
									"price" => $this->input->post('price'),
							));
		# new price
		} elseif ($this->input->post('submit') != "store_buy_price") {
			$this->pprice->insert(array(
										'volume' 		=> $this->input->post('volume'),
										'price' 		=> $this->input->post('price'),
										'product_id' 	=> $pid
								));
		}
	}
}
