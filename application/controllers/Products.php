<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends Vet_Controller
{

	const SEARCH_LIMIT = 15;

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Product_label_model', 'prod_label');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Product_price_model', 'pprice');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Booking_code_model', 'booking');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Stock_limit_model', 'stock_limit');
		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Register_in_model', 'registry_in');

		# helpers
		$this->load->helper('gs1');
	}

	public function index($location = false, $success = false)
	{
		$clocation = ($location) ? $location : $this->_get_user_location();
		$products = ($location == "all") ? 
					$this->stock->get_all_products_count() 
					: 
					$this->stock->get_all_products($clocation);
		$search_query = trim((string) $this->input->get('search_query'));
		$search_results = $this->build_search_results($search_query, (int) $this->_get_user_location());

		$data = array(
						"search_q"					=> $search_query,
						"types" 					=> $this->prod_type->get_all(),
						"expired"					=> $this->stock
																		->fields('eol, volume')
																		->where('eol < DATE_ADD(NOW(), INTERVAL +90 DAY)', null, null, false, false, true)
																		->where('eol > DATE_ADD(NOW(), INTERVAL -10 DAY)', null, null, false, false, true)
																		->where(array('state' => STOCK_IN_USE, 'location' => $clocation))
																		->with_products('fields: id, name, unit_sell')
																		->order_by('eol', 'ASC')
																		->count_rows(),
						"locations" 			=> $this->locations,
						"user_location"			=> $this->_get_user_location(),
						"is_admin"				=> $this->ion_auth->in_group("admin"),
						"success" 				=> $success,
						"curlocation"			=> $clocation,
						"search_results"		=> $search_results,
						"products" 				=> $products,
						);

		$this->_render_page('product/index', $data);
	}

	public function search_catalog()
	{
		$query = trim((string) $this->input->get('q'));
		$results = $this->build_search_results($query, (int) $this->_get_user_location());

		$html = $this->load->view(
			'product/partials/search_results',
			array(
				'results' => $results,
				'is_admin' => $this->ion_auth->in_group("admin"),
			),
			true
		);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'query' => $query,
				'count' => count($results),
				'html' => $html,
			)));
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
		list($local_stock, $global_stock) = $this->stock->get_stock_levels($id, $this->_get_user_location());

		# check if there is a local limit
		$local_limit_query = $this->stock_limit->fields('volume')->where(array('product_id' => $id, 'stock' => $this->_get_user_location()))->get();
		$local_limit = ($local_limit_query) ? $local_limit_query['volume'] : 0;

		$product = $this->products->
					with_prices('fields:id, volume, price|order_inside:volume asc')->
					with_type('fields:name')->
					with_booking_code('fields:category, code, btw')->
					with_stock('fields: id as stock_id, location, eol, lotnr, volume, state, created_at', 'where:`state`='. STOCK_IN_USE .' ')->
					get($id);

		# if i do with_wholesale this stock thing breaks.
		$w = $this->wholesale->fields('bruto, netto_overflow')->get($product['wholesale']);

		# in a corner case where the netto price is higher then the bruto price
		# show the netto_overflow (eg. due to a extra tax on netto not shown on bruto)
		$cat_price = $w	? max($w['bruto'], $w['netto_overflow']) : $product['buy_price'];

		$data = array(
				'product' 		=> $product,
				'product_labels'=> $this->prod_label->get_for_product($id),
				'cat_price'		=> $cat_price,
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
		$types = $this->prod_type->get_all();
		$types[] = array(
			'id' => 0,
			'name' => '<i class="fa-solid fa-xmark" style="color: red;"></i> ' . $this->lang->line('saleable')
		);
		$types[] = array(
			'id' => -1,
			'name' => '<i class="fa-solid fa-bacteria" style="color: rgb(202, 112, 85);"></i> Antibiotic'
		);
		$types[] = array(
			'id' => -2,
			'name' => '<i class="fa-solid fa-syringe" style="color: rgb(95, 124, 219);"></i> Vaccin'
		);

		$data = array(
						"query"			=> $id,
						"types" 		=> $types
					);

		$this->_render_page('product/list', $data);
	}

	/*
		get all products of a certain type
		- ajax for datatables
	*/
	public function get(int $type_id)
	{
		$x = $this->products->get_products_by_type_with_stock($type_id, $this->_get_user_location());

		if (!$x)
		{
			echo json_encode(array("aaData" => array()));
			return 0;
		}

		foreach($x as $product)
		{
			$global = $product['volume_count'] + 0;
			$local  = $product['volume_location'] + 0;
			
			$is_ab = ($product['is_antibiotic']) ? " <i class='fa-solid fa-bacteria' style='color: rgb(202, 112, 85);'></i>" : "";	
			$is_vaccin = ($product['vaccin']) ? " <i class='fa-solid fa-syringe' style='color: rgb(95, 124, 219);'></i>" : "";

			$aaData[] = array(
				"<a href='". base_url('products/profile/' . $product['product_id']) ."'>" . $product['name'] . "</a>",
				($global > 0 && $local > 0) ? $local . " / " . $global . " " . $product['unit_sell'] : "",
				($product['sellable']) ? "<i class='fa-solid fa-bag-shopping' style='color: green;'></i>" . $is_ab . $is_vaccin : "<i class='fa-solid fa-xmark' style='color: red;'></i>" . $is_ab . $is_vaccin,
				"",
				"<a href='". base_url('products/product/' . $product['product_id']) ."' class='btn btn-sm btn-outline-primary'>edit</a>"
			);

		}
		echo json_encode(array("aaData" => $aaData));
	}

	public function product(int $id)
	{
		$update = false;

		if ($this->input->post('submit') && $this->ion_auth->in_group("admin")) {
			$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));
			$default_indication = $this->input->post('default_indication');

			$input = array(
								"name" 					=> $this->input->post('name'),
								"wholesale_name" 		=> $this->input->post('input_wh_name'),
								"producer" 				=> $this->input->post('producer'),
								"supplier" 				=> $this->input->post('supplier'),
								"type" 					=> $this->input->post('type'),
								"dead_volume"			=> $this->input->post('dead_volume'),
								"vaccin_disease"		=> $this->input->post('vaccin_disease'),
								"buy_volume" 			=> $this->input->post('buy_volume'),
								"sell_volume" 			=> $this->input->post('sell_volume'),
								"unit_buy" 				=> $this->input->post('unit_buy'),
								"unit_sell" 			=> $this->input->post('unit_sell'),
								"input_barcode" 		=> (empty($this->input->post('input_barcode')) ? NULL : $this->input->post('input_barcode')),
								"btw_buy" 				=> $this->input->post('btw_buy'),
								"btw_sell" 				=> $booking['btw'],
								"vaccin" 				=> (is_null($this->input->post('vaccin')) ? 0 : 1),
								"vaccin_freq" 			=> $this->input->post('vaccin_freq'),
								"is_antibiotic"			=> (is_null($this->input->post('is_antibiotic')) ? 0 : 1),
								"default_indication"	=> ($default_indication == "null")? NULL : $default_indication,
								"ab_unit"				=> $this->input->post('ab_unit'),
								"ab_unit_volume"		=> $this->input->post('ab_unit_volume'),
								"booking_code" 			=> $this->input->post('booking_code'),
								"comment_admin" 		=> $this->input->post('comment_admin'),
								"vhbcode" 				=> $this->input->post('vhbcode'),
								"cnk" 					=> $this->input->post('cnk'),
								"cti_e"					=> $this->input->post('cti_e'),
								"wholesale"				=> $this->input->post('wholesale'),
								"sellable" 				=> (is_null($this->input->post('sellable')) ? 0 : 1),
								"discontinued" 			=> (is_null($this->input->post('discontinued')) ? 0 : 1),
								"limit_stock" 			=> $this->input->post('limit_stock')
							);

			$update = $this->products->update($input, $id);

			$this->prod_label->replace_product_labels($id, (array) $this->input->post('labels'));
			
			# add or update local limits
			$this->set_local_limits($this->input->post('limit'), $id);

			# log this
			# reduce log blob
			foreach (array('wholesale_name', 'producer', 'supplier', 'type', 'buy_volume', 'sell_volume', 'unit_buy', 'unit_sell') as $key) { unset($input[$key]); }
			foreach ($input as $key => $value) { if (is_null($value)) { unset($input[$key]); } }
			
			$this->logs->logger(INFO, "update_product", " id : " . $id . " data:" . var_export($input, true));
		}

		$data = array(
						'product' 			=> $this->products->get($id),
						'type' 				=> $this->prod_type->get_all(),
						'labels'			=> $this->prod_label->order_by('name', 'ASC')->get_all(),
						'product_labels'	=> $this->prod_label->get_for_product($id),
						'update'			=> $update,
						'llimit'			=> $this->stock_limit->with_stock_locations('fields:name')->where(array('product_id' => $id))->get_all(),
						'stock_locations'	=> $this->stock_location->get_all(),
						'booking'			=> $this->booking->get_all()
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
				list($last_net, $last_brut, $last_date) = $this->registry_in->get_last_net_brut($r['id']);
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
												"last_net"			=> $last_net,
												"last_brut"			=> $last_brut,
												"last_date"			=> $last_date,
											)
							);
			}
		}
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}

	# search by booking code to products and procedures
	public function search_by_booking(int $booking_id)
	{
		$data = array(
						"booking"		=> $this->booking->get($booking_id),
						"products"		=> $this->products->where('booking_code', $booking_id)->get_all(),
						"procedures"	=> $this->procedures->where('booking_code', $booking_id)->get_all(),
					);

		$this->_render_page('product/list_by_booking', $data);
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
		# max 30 results (15+15)
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}

	# probably broken due to type switch
	private function get_gs1_barcode(array $gsl): array {

		// init
		$return = array();

		// lookup in database
		$stck = $this->stock->gs1_lookup($gsl['GTIN'], $gsl['LOTNR'], gs1_get_due_date($gsl), $this->_get_user_location());

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
                                        "is_antibiotic"     => $r['is_antibiotic'],
										"type"				=> PRODUCT_BARCODE
									)
					);

		return $return;
	}

    /*
    * function to get products even if no stock or multiple stocks
    * note: used in get_product_or_procedure
    */
    private function get_products(string $query, $return)
    {
        $rows = $this->products->get_products_stocks($query);
        if (!$rows) { return $return; }

        $products = [];

        foreach ($rows as $r) {
            $pid = $r['product_id'];

            if (!isset($products[$pid])) {
                $products[$pid] = [
                    'value' => $r['name'],
                    'data' => [
                        'id'            => $pid,
                        'stock'         => [],
                        'unit'          => $r['unit_sell'],
                        'btw'           => $r['btw_sell'],
                        'booking'       => $r['booking_code'],
                        'vaccin'        => (int)$r['vaccin'],
                        'vaccin_freq'   => $r['vaccin_freq'],
                        'is_antibiotic' => (int)$r['is_antibiotic'],
                        'type'          => PRODUCT
                    ]
                ];
            }

            if ($r['stock_id']) {
                $products[$pid]['data']['stock'][] = [
                    'id'        => $r['stock_id'],
                    'location'  => $r['location'],
                    'eol'       => $r['eol'],
                    'lotnr'     => $r['lotnr'],
                    'volume'    => $r['volume']
                ];
            }
        }

        return array_merge($return, array_values($products));
    }

	/*
		query procedures
	*/
	private function get_procedures(string $query, array $list) {
		$result = $this->procedures
							->fields('id, name, price')
							->with_booking_code('fields:btw')
							->where('name', 'like', $query, true)
							->limit(self::SEARCH_LIMIT)
							->get_all();

		if (!$result) { return $list; }

		foreach ($result as $r) {
			$list[] = array(
								"value" => $r['name'],
								"data" 	=> array(
												"id" 			=> $r['id'],
												"btw"			=> (isset($r['booking_code']['btw'])) ? $r['booking_code']['btw'] : "21",
												"booking"		=> $r['booking_code']['id'],
												"type"			=> PROCEDURE
											)
							);
		}

		return $list;
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
						"vaccin_disease"	=> $this->input->post('vaccin_disease'),
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

	private function build_search_results(string $query, int $location): array
	{
		if ($query === '' || strlen($query) < 2) {
			return array();
		}

		$results = array();

		foreach ($this->products->search_product_with_stock($query, $location, self::SEARCH_LIMIT) as $product) {
			$results[] = array(
				'id' => (int) $product['id'],
				'type' => 'product',
				'name' => $product['name'],
				'sellable' => (bool) $product['sellable'],
				'is_antibiotic' => (bool) $product['is_antibiotic'],
				'vaccin' => (bool) $product['vaccin'],
				'unit_sell' => $product['unit_sell'],
				'local_stock' => $this->format_stock_volume($product['local_stock']),
				'global_stock' => $this->format_stock_volume($product['global_stock']),
				'profile_url' => base_url('products/profile/' . $product['id']),
				'edit_url' => base_url('products/product/' . $product['id']),
			);
		}

		foreach ($this->procedures->search_procedure($query, self::SEARCH_LIMIT) ?: array() as $procedure) {
			$results[] = array(
				'id' => (int) $procedure['id'],
				'type' => 'procedure',
				'name' => $procedure['name'],
				'price' => $procedure['price'],
				'edit_url' => base_url('pricing/proc_edit/' . $procedure['id']),
			);
		}

		usort($results, static function ($left, $right) {
			$left_not_sellable = ($left['type'] === 'product' && empty($left['sellable'])) ? 1 : 0;
			$right_not_sellable = ($right['type'] === 'product' && empty($right['sellable'])) ? 1 : 0;

			if ($left_not_sellable !== $right_not_sellable) {
				return $left_not_sellable <=> $right_not_sellable;
			}

			if ($left['type'] !== $right['type']) {
				return strcmp($left['type'], $right['type']);
			}

			return strcasecmp($left['name'], $right['name']);
		});

		return $results;
	}

	private function format_stock_volume($volume): string
	{
		$formatted = number_format((float) $volume, 2, '.', '');
		return rtrim(rtrim($formatted, '0'), '.');
	}
}
