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
		$clocation = ($location) ? $location : $this->user->current_location;
		
		$products = ($location == "all") ? 
					$this->stock->get_all_products_count() 
					: 
					$this->stock->get_all_products($clocation);

		$data = array(
						"search_q"					=> $this->input->get('search_query'),
						"types" 					=> $this->prod_type->get_all(),
						"expired"					=> $this->stock
																		->fields('eol, volume')
																		->where('eol < DATE_ADD(NOW(), INTERVAL +90 DAY)', null, null, false, false, true)
																		->where('eol > DATE_ADD(NOW(), INTERVAL -10 DAY)', null, null, false, false, true)
																		->where(array('state' => STOCK_IN_USE, 'location' => $this->user->current_location))
																		->with_products('fields: id, name, unit_sell')
																		->order_by('eol', 'ASC')
																		->count_rows(),
						"locations" 			=> $this->locations,
						"user_location"			=> $this->user->current_location,
						"success" 				=> $success,
						"clocation"				=> $clocation,
						"search_product"		=> $this->products->search_product($this->input->get('search_query')),
						"search_procedure"		=> $this->procedures->search_procedure($this->input->get('search_query')),
						"products" 				=> $products,
						);

		$this->_render_page('product/index', $data);
	}

	/*
		set a product in order status
	*/
	public function set_backorder(int $pid)
	{
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->products->update(array("backorder" => 1), $pid);
		$this->logs->logger(DEBUG, "set_product_in_backorder", "product: " . $pid);
		
		redirect('limits/global');
	}

	/*
		set a product in order status
	*/
	public function unset_backorder(int $pid)
	{
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->products->update(array("backorder" => 0), $pid);
		$this->logs->logger(DEBUG, "unset_product_in_backorder", "product: " . $pid);
		
		redirect('limits/order');
	}

	/*
		semi "public" profile of a product
	*/
	public function profile(int $id)
	{
		# update comment
		$comment_update = $this->products->update_comment($id, $this->input->post('message'));

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
										with_stock('fields: id as stock_id, location, eol, lotnr, volume, state, created_at', 'where:`state`='. STOCK_IN_USE .' ')->
										get($id),
				'global_stock' 	=> $global_stock,
				'local_stock' 	=> $local_stock,
				'local_limit' 	=> $local_limit,
				'comment_update'=> $comment_update,
				'locations'		=> $this->locations,
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


	/*
	generate a list with all products based on category
	or if none is set "other"
	*/
	public function product_list(int $id = 1)
	{
		$data = array(
						"query"			=> $id,
						"types" 		=> $this->prod_type->get_all()
					);

		$this->_render_page('product/list', $data);
	}

	/*
		get all products of a certain type
		- ajax for datatables
	*/
	public function get(int $id)
	{
		$x = $this->products
			->with_booking_code()
			->with_type('fields:name')
			->where('type', $id)
			->get_all();

		if (!$x)
		{
			echo json_encode(array("aaData" => array()));
			return 0;
		}

		foreach($x as $product)
		{
			$aaData[] = array(
				"<a href='". base_url('products/profile/' . $product['id']) ."'>" . $product['name'] . "</a>",
				"<small>" . $product['short_name']
				. ($product['wholesale_name']) ? "<br/>" .$product['wholesale_name'] : "". "</small>",

				$product['type']['name']
			);
		}
		echo json_encode(array("aaData" => $aaData));
	}

	public function product(int $id)
	{
		$update = false;
		if ($this->input->post('submit')) {
			$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));

			$input = array(
								"name" 					=> $this->input->post('name'),
								"short_name" 			=> $this->input->post('short_name'),
								"wholesale_name" 		=> $this->input->post('input_wh_name'),
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
								"discontinued" 				=> (is_null($this->input->post('discontinued')) ? 0 : 1),
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
		$this->logs->logger(WARN, "delete_product", " id : " . $id);
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

	/*
		get_product is used in stock_add
	*/
	public function get_product()
	{
		$query = $this->input->get('query');

		# too short
		if (strlen($query) <= 1) { echo json_encode(array("query" => $query, "suggestions" => array())); return 0; }

		/*
			if string is 26 chars long try to check if its a gs1 code
		*/
		$gs1 = (strlen($query) >= GS1_CODE) ? gs1($query) : false;

		if ($gs1)
		{
			$result =  $this->products
							->fields('id, name, type, buy_volume, unit_buy, sell_volume, unit_sell, supplier, buy_price')
							->with_type()
							->where(array('input_barcode' => $gs1['GTIN']))
							->get();

			if (!$result) { 
				echo json_encode(array("query" => $query, "suggestions" => array(), "gs1" => $gs1));
				return 0;
			}

			$return[] = array(
				"value" => $result['name'],
				"data" 	=> array(
									"type" 				=> (isset($result['type']['name']) ? $result['type']['name'] : "other"),
									"id" 				=> $result['id'],
									"buy_volume"		=> $result['buy_volume'],
									"unit_buy"			=> $result['unit_buy'],
									"supplier"			=> $result['supplier'],
									"sell_volume"		=> $result['sell_volume'],
									"unit_sell"			=> $result['unit_sell'],
									"buy_price"			=> $result['buy_price'],
									"gs1"				=> $gs1
								)
				);
		}
		else
		{
			$result = $this->products
								->fields('id, name, buy_volume, unit_buy, sell_volume, unit_sell, supplier, buy_price')
								->group_start() // required for IS_NULL on deleted items
									->where('name', 'like', $query, true)
									->where('short_name', 'like', $query, true) // not always visible
								->group_end()
								->limit(20)
								->get_all()
							;
			# in case no results
			if (!$result) { echo json_encode(array("query" => $query, "suggestions" => array())); return 0; }

			$return = array();
			foreach ($result as $r) {
				$return[] = array(
							"value" => $r['name'],
							"data" 	=> array(
												"id" 				=> $r['id'],
												"buy_volume"		=> $r['buy_volume'],
												"unit_buy"			=> $r['unit_buy'],
												"supplier"			=> $r['supplier'],
												"sell_volume"		=> $r['sell_volume'],
												"unit_sell"			=> $r['unit_sell'],
												"buy_price"			=> $r['buy_price'],
											)
							);
			}
		}
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}

	# return an ajax readable object of possible results
	public function get_product_or_procedure()
	{
		$query = $this->input->get('query');
		$return = array();

		/*
			if string is 26 chars long its most likely GS1 barcode
		*/
		if (strlen($query) >= GS1_CODE)
		{
			$gsl = gs1($query);
			// if not right format,
			// then it might be a very long name!
			if ($gsl) {
				// search for product w/ this barcode
				$return = $this->get_gs1_barcode($gsl);
			}
		}

		if (strlen($query) > 1)
		{
			$return = $this->get_products($query, $return);
			$return = $this->get_procedures($query, $return);
		}

		// limit to max 50 results
		echo json_encode(array("query" => $query, "suggestions" => array_slice($return, 0, 50)));
	}

	# probably broken due to type switch
	private function get_gs1_barcode(array $gsl): array {

		// init
		$return = array();

		// lookup in database
		$stck = $this->stock->gs1_lookup($gsl['GTIN'], $gsl['LOTNR'], gs1_get_due_date($gsl), $this->user->current_location);

		if (!$stck) { return $return; }

		$list = array();
		
		foreach ($stck as $s)
		{
			$list[] = array(
								"id" 		=> $s['stock_id'],
								"location" 	=> $s['location'],
								"eol" 		=> $s['eol'],
								"lotnr" 	=> $s['lotnr'],
								"volume" 	=> $s['volume']
								);
		}

		# every line is the same product
		$r = $stck[0];

		$return[] = array(
					"value" => $r['pname'],
					"data" 	=> array(
										"id" 				=> $r['pid'],
										"stock"				=> $list,
										"unit"				=> $r['unit_sell'],
										"btw"				=> $r['btw_sell'],
										"booking"			=> $r['booking_code'],
										"vaccin"			=> $r['vaccin'],
										"vaccin_freq"		=> $r['vaccin_freq'],
										"type"				=> PRODUCT_BARCODE
									)
					);

		return $return;
	}

	private function get_products($query, $return) {
		# products
		$result = $this->products->get_products($query);

		# in case no results
		if (!$result) { return $return; }

		# maximum 10 results
		foreach ($result as $r) {
			$stock = array();
			// $prices = array();
			$product_id = $r['id'];

		# there are prices
		// if ($r['price_volume']) {
		// 	$volumes 	= explode(",", $r['price_volume']);
		// 	$prices 	= explode(",", $r['price_price']);
		// 	for($i = 0; $i < count($volumes); $i++) {
		// 		$prices[] = array(
		// 							"volume" 	=> $volumes[$i],
		// 							"price" 	=> $prices[$i],
		// 							);
		// 	}
		// }

		$stock = $this->stock->fields('id, location, eol, lotnr, volume')->where(array("product_id" => $product_id, "state" => STOCK_IN_USE, "volume >" => 0))->order_by(array("location" => "ASC", "eol" => "ASC"))->get_all();
		$list = array();
		# there is stock
		if ($stock) {
			foreach ($stock as $s)
			{
				$list[] = array(
									"id" 		=> $s['id'],
									"location" 	=> $s['location'],
									"eol" 		=> $s['eol'],
									"lotnr" 	=> $s['lotnr'],
									"volume" 	=> $s['volume']
									);
			}
		}

		$return[] = array(
					"value" => $r['name'],
					"data" 	=> array(
										"id" 				=> $r['id'],
										"stock"				=> $list,
										// "prices"			=> $prices,
										"unit"				=> $r['unit_sell'],
										"btw"				=> $r['btw_sell'],
										"booking"			=> $r['booking_code'],
										"vaccin"			=> $r['vaccin'],
										"vaccin_freq"		=> $r['vaccin_freq'],
										"type"				=> PRODUCT
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
							->fields('id, name, price')
							->with_booking_code('fields:btw')
							->where('name', 'like', $query, true)
							->get_all();

		if (!$result) { return $list; }

		foreach ($result as $r) {
			$list[] = array(
								"value" => $r['name'],
								"data" 	=> array(
												"id" 			=> $r['id'],
												// "price"			=> $r['price'],
												"btw"			=> (isset($r['booking_code']['btw'])) ? $r['booking_code']['btw'] : "21",
												"booking"		=> $r['booking_code']['id'],
												"type"			=> PROCEDURE
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

	public function get_monthly_usage(int $product, int $months = 12)
	{
		$usage = $this->eprod->get_monthly_usage($product, $months);
		echo json_encode($usage);
	}
}
