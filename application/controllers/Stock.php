<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Stock
class Stock extends Vet_Controller
{

	# initialise
	public $input, $stock, $product, $registry_in, $liquidate, $wholesale, $delivery, $logs, $slip;

	# limit for adding stock
	const LIMIT_ADD_VOLUME = 5000;

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Products_model', 'product');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Stock_limit_model', 'stock_limit');
		$this->load->model('Delivery_slip_model', 'slip');
		$this->load->model('Register_in_model', 'registry_in');
		$this->load->model('Liquidate_model', 'liquidate');
		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Delivery_model', 'delivery');

		# helpers
		$this->load->helper('gs1');
	}

	public function stock_detail($pid, $all = false)
	{
		// for charts this function from model can be used
		// "product_use" => $this->products->product_monthly_use($product_id, "none", 24),
		$stock_detail = ($all) ?
									$this->stock->where(array('product_id' => $pid))
									->with_products('fields: name, unit_sell, buy_price')
									->with_stock_locations('fields: name')
									->get_all()
									:
									$this->stock->where(array('state' => STOCK_IN_USE, 'product_id' => $pid))
									->with_products('fields: name, unit_sell, buy_price')
									->with_stock_locations('fields: name')
									->get_all()
									;

		$data = array(
						"product"		=> $this->product->fields('id, name, unit_sell')->get($pid),
						"stock_detail" 	=> $stock_detail,
						"show_all"		=> $all
						);
		$this->_render_page('stock/details', $data);
	}

	/*
	*	function: move
	* 	move stock from one location to another
	*/
	public function move(bool $move_complete = false)
	{
		if ($this->input->post("submit"))
		{
			$products 		= $this->input->post('move_volume');
			$from_location 	= $this->input->post('location_hidden');
			foreach ($products as $stock_id => $volume)
			{
				/*
					get all info from stock
				*/
				$selection[$stock_id] = array (
												$this->stock->fields('id, eol, lotnr, volume')->with_products('fields:id, name, unit_sell')->get((int) $stock_id), 
												$volume
											);
			}
			
			$this->_render_page('stock/move_overview', array(
										"stocks" 		=> $this->locations,
										"selection" 	=> $selection,
										"user_location"	=> $this->_get_user_location(),
										"from_location" => $from_location,
									));
		}
		else
		{
			$data = array(
							"move_complete" => $move_complete,
							"stocks" 		=> $this->locations,
							"user_location"	=> $this->_get_user_location(),
							"extra_footer" 	=> '<script src="'. base_url('assets/js/jquery.autocomplete.min.js') .'"></script>'
			);
			$this->_render_page('stock/move', $data);
		}
	}

	/*
	* 	function: move_verification
	* 	the user was able to verify it and now we execute the move
	*/
	public function move_verification()
	{
		if ($this->input->post('submit'))
		{
			$volumes 		= $this->input->post('move_volume');
			$max_volumes 	= $this->input->post('max_move');
			$from_location 	= $this->input->post('from');
			$products 		= $this->input->post('move_prod');
			$to_location 	= $this->_get_user_location();
			
			foreach ($volumes as $stock_id => $volume)
			{
				$message = ($max_volumes[$stock_id] < $volume) ? "OVERDRAW_MOVE" : null;
				$this->stock->move($stock_id, $from_location, $to_location, $volume, $products[$stock_id], $message);
			}
		}
		redirect('/stock/move/1');
	}

	public function add_stock($preselected = false)
	{
		$error = false;
		if ($this->input->post('submit')) {
			if (!empty($this->input->post('pid')) && !empty($this->input->post('new_volume')) && $this->input->post('new_volume') < 5000) {
				# check if this is already in the verify list
				$result = $this->stock->where(array(
										"product_id" 	=> $this->input->post('pid'),
										"eol" 			=> (empty($this->input->post('eol')) ? null : $this->input->post('eol')),
										"location" 		=> $this->_get_user_location(),
										"lotnr" 		=> $this->input->post('lotnr'),
										"in_price" 		=> $this->input->post('in_price'),
										"state" 		=> STOCK_CHECK
									))->get();

				# increase current verify stock
				if ($result) {
					# we can create this more simple by making update if exist else insert (instead of reading, updating or inserting)
					$this->logs->stock(DEBUG, "add_stock_re", $this->input->post('pid'), $this->input->post('new_volume'));
					$sql = "UPDATE stock SET volume=volume+" . $this->input->post('new_volume') . " WHERE id = '" . $result['id'] . "' AND state = '" . STOCK_CHECK . "' limit 1;";
					$this->db->query($sql);
				}
				# create new verify stock
				else {
					# generate barcode
					# reduce time with 01/12/2019
					# move to a base36 (to use letters)
					$barcode = base_convert((time() - 1575158400), 10, 36);

					$this->logs->stock(DEBUG, "add_stock", $this->input->post('pid'), $this->input->post('new_volume'));
					$this->stock->insert(array(
											"product_id" 		=> $this->input->post('pid'),
											"eol" 				=> $this->input->post('eol'),
											"location" 			=> $this->_get_user_location(),
											"in_price" 			=> $this->input->post('in_price'),
											"lotnr" 			=> $this->input->post('lotnr'),
											"supplier" 			=> (!empty($this->input->post('supplier'))) ? $this->input->post('supplier') : NULL,
											"barcode"			=> $barcode,
											"volume" 			=> $this->input->post('new_volume'),
											"state"				=> STOCK_CHECK
										));

					if ($this->input->post('new_barcode_input')) {
						$this->product
								->where(array("id" => $this->input->post('pid')))
								->update(array("input_barcode" => $this->input->post('barcode_gs1')));
						$this->logs->logger(INFO, "new_gs1", "pid: " . $this->input->post('pid') . " gs1:" . $this->input->post('barcode_gs1'));
			
					}
				}
				$this->product->set_backorder_filled($this->input->post('pid'));
			} else {
				$error = ($this->input->post('new_volume') > self::LIMIT_ADD_VOLUME) ? "Invalid volume (>5000)" : "Not a valid product or no volume...";

				# log this
				$this->logs->logger(INFO, "bad_stock_entry", "pid: " . $this->input->post('pid') . " eol: " . $this->input->post('eol') . " in_price" . $this->input->post('in_price') . " lotnr:" . $this->input->post('lotnr') . " volume:" . $this->input->post('new_volume'));
			}
		}

		$data = array(
						"error" 		=> $error,
						"preselected"	=> ($preselected) ? $this->product->fields('id, name, unit_buy')->get($preselected) : false,
						"products" 		=> $this->stock->with_products('fields: id, name, unit_sell, buy_price')->where(array('state' => STOCK_CHECK))->get_all(),
						"extra_footer" 	=> '<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>'
					);
		$this->_render_page('stock/add', $data);
	}

	public function delete_stock($stock_id)
	{
		# if logging is required also log this remove
		if ($this->logs->min_log_level == DEBUG)
		{
			$info = $this->stock->where(array("state" => STOCK_CHECK, "id" => $stock_id))->get();
			$this->logs->stock(DEBUG, "delete_stock", $info['product_id'], -$info['volume']);
		}

		# remove from list
		$this->stock->where(array("state" => STOCK_CHECK))->delete($stock_id);
		redirect('stock/add_stock', 'refresh');
	}

	/*
	*	function: verify_stock
	* 	show all stock that is in check state
	*	also give the option to check the list
	*	and prices if admin
	*/
	public function verify_stock()
	{
		if ($this->input->post('submit')) {
			// all stock is fine, insert now
			$slip = $this->slip->insert(array(
						"vet"		=> $this->user->id,
						"note"		=> $this->input->post('delivery_slip'),
						"regdate"	=> $this->input->post('regdate'),
						"location" 	=> $this->_get_user_location()
				));
			
			# registry_in
			$stock = $this->stock->fields('product_id, eol, volume, in_price, supplier, lotnr')->where(array("state" => STOCK_CHECK))->get_all();
			foreach ($stock as $stoc)
			{
				$this->registry_in->insert(array(
							"product" 	=> $stoc['product_id'],
							"eol" 		=> $stoc['eol'],
							"volume" 	=> $stoc['volume'],
							"in_price" 	=> $stoc['in_price'],
							"supplier" 	=> $stoc['supplier'],
							"lotnr" 	=> $stoc['lotnr'],
							"delivery_slip"	=> $slip
				));
			}

			# all products currently in check state are now considered in use
			$total = $this->stock->where(array("state" => STOCK_CHECK))->update(array("state" => STOCK_IN_USE));

			# verify the stock
			$this->logs->logger(INFO, "stock_verify", "products verified: " . $total);

			redirect('stock/add_stock', 'refresh');
		}
		// show all stock that is in check state
		else
		{
			$products_in_check = $this->stock->with_products('fields: name, unit_sell, buy_price, wholesale')->where(array('state' => STOCK_CHECK))->get_all();

			$pricing = array();
			
			foreach ($products_in_check as $prod)
			{
				$prod_id = $prod['product_id'];
				$pricing[$prod_id]['delivery'] = false;
				$pricing[$prod_id]['wholesale'] = $prod['products']['wholesale'];
				$pricing[$prod_id]['last_net_buy'] = false;

				// we could try to get the last price based on 
				// the automatic pulled prices
				if ($prod['products']['wholesale'] != 0)
				{
					$pricing[$prod_id]['delivery'] = $this->delivery->fields('netto_price, delivery_date')->where(array('wholesale_id' => $prod['products']['wholesale']))->order_by('delivery_date', 'DESC')->get();
				}

				// last net buy price
				$last_net_buy = $this->registry_in->with_delivery_slip('fields: regdate|order_by:regdate, desc')->where(array("product" => $prod_id))->limit(1)->get();
				if ($last_net_buy)
				{
					$pricing[$prod_id]['last_net_buy'] = $last_net_buy['in_price'];
				}
			}

			$data = array(
							"products" => $products_in_check,
							"pricing"  => $pricing
						);
			$this->_render_page('stock/verify', $data);
		}
	}


	/*
	*	function: expired_stock
	*	stock that is close to or has expired.
	*/
	public function expired_stock()
	{
		$expired = $this->stock
			->fields('eol, lotnr, volume, id')
			->where('eol < DATE_ADD(NOW(), INTERVAL +90 DAY)', null, null, false, false, true)
			->where('eol > DATE_ADD(NOW(), INTERVAL -360 DAY)', null, null, false, false, true)
			->where(array('state' => STOCK_IN_USE))
			->with_products('fields: name, unit_buy')
			->with_stock_locations('fields: name')
			->order_by('eol', 'ASC')
			->get_all();

		$data = array(
						"stock_gone_bad" => $expired
						);
		$this->_render_page('stock/expired', $data);
	}


	/*
	*	function: write_off_full
	*	write off the full stock
	*/
	public function write_off_full(int $stock)
	{
		// store in liquidate list
		$info = $this->stock->get($stock);

		// get product name
		$name = $this->product->fields('name')->get($info['product_id'])['name'];
		
		$this->liquidate->insert(array(
			"product_id"	=> $info['product_id'],
			"product_name"	=> $name, // in case they remove it
			"volume" 		=> $info['volume'],
			"eol" 			=> $info['eol'],
			"lotnr" 		=> $info['lotnr'],
			"reason" 		=> "EXPIRED",
			"user"			=> $this->user->id,
			"location" 		=> $info['location'],
			"stock_id" 		=> $info['id'] // this could go away at some point
		));
		
		// log write off action
		$this->logs->logger(DEBUG, "write_off", "stock_id:" . $stock . " volume: " . $info['volume'] . " user:" . $this->user->id);

		// log in stock log
		$this->logs->stock(INFO, "write_off", $info['product_id'], $info['volume'], $info['location']);
		
		// remove it from stock
		$this->stock->write_off($stock, $info['volume']);

		// back home
		redirect('stock/expired_stock', 'refresh');
	}

	/*
	*	function: write_off
	*	write_off partial stock
	*/
	public function write_off(int $stock_id)
	{
		# return the details of the selected product
		if ($this->input->post('submit')) {

			// store in liquidate list
			$info = $this->stock->get($stock_id);

			// get product name
			$name = $this->product->fields('name')->get($info['product_id'])['name'];
			
			$this->liquidate->insert(array(
				"product_id"	=> $info['product_id'],
				"product_name"	=> $name, // in case they remove it
				"volume" 		=> $this->input->post('volume'),
				"eol" 			=> $info['eol'],
				"lotnr" 		=> $info['lotnr'],
				"reason" 		=> $this->input->post('reason'),
				"user"			=> $this->user->id,
				"location" 		=> $info['location'],
				"stock_id" 		=> $stock_id
			));

			// log write off action
			$this->logs->logger(DEBUG, "write_off", "stock_id:" . $stock_id . " volume: " . $this->input->post('volume') . " user:" . $this->user->id);

			// log in stock log
			$this->logs->stock(INFO, "write_off", $info['product_id'], $this->input->post('volume'), $info['location']);
			
			// remove it from stock
			$this->stock->write_off($stock_id, $this->input->post('volume'));

			// back home
			redirect('products/profile/' . $info['product_id'], 'refresh');

		} else {
			$this->_render_page('stock/write_off_volume', array(
																"product" => $this->stock->with_stock_locations('fields:name')->with_products('fields:name, unit_sell')->get($stock_id)
																));	
		}
	}

	/*
	*	function: edit
	*	edit stock -only by admin-
	*/
	public function edit(int $stock_id)
	{
		if (!$this->ion_auth->in_group("admin")) { redirect('/'); }

		if ($this->input->post('submit')) {
			$this->logs->logger(INFO, "admin_stock_edit", "debug: " . implode(',', $this->input->post()));
			$this->stock
						->where(array("id" => $stock_id))
						->update(array(
								"eol" 		=> $this->input->post('eol'),
								"in_price"	=> $this->input->post('in_price'),
								"lotnr" 	=> $this->input->post('lotnr'),
								"volume" 	=> $this->input->post('new_volume'),
								"state" 	=> $this->input->post('state'),
						));

			$lookup = $this->stock->with_products('fields:id')->get($stock_id);
			if ($this->input->post('ori_volume') != $this->input->post('new_volume'))
			{
				$this->logs->stock(WARN, "admin_stock_edit", $lookup['products']['id'], -$this->input->post('ori_volume'), $lookup['location']);
				$this->logs->stock(WARN, "admin_stock_edit", $lookup['products']['id'], $this->input->post('new_volume'), $lookup['location']);
			}
			redirect('/stock/stock_detail/'. $lookup['products']['id']);
		}

		$data = array(
						"stock"	=> $this->stock->with_products('fields:name,unit_sell,buy_price')->get($stock_id)
					);
		$this->_render_page('stock/edit.admin.php', $data);
	}
	
	/*
	* function: stock_check
	* for problems with stock
	*/
	public function stock_check()
	{
		if (!$this->ion_auth->in_group("admin")) { redirect('/'); }

		$data = array(
			"stock"	=> $this->stock->get_problems()
		);
		$this->_render_page('stock/stock.admin.php', $data);	
	}

	/*
		clear the error set the value to 0
	*/
	public function clear_error(int $stock_id)
	{
		# get the info for logging purpose
		$stock_info = $this->stock->with_products('fields: name')->get($stock_id);
		
		# happens when product is deleted
		$product_name = (isset($stock_info['products']['name'])) ? $stock_info['products']['name']: "unknown";

		# log in general logs
		$this->logs->logger(INFO, "clear_error", "s:" . $stock_info['id'] . " p:" . $product_name . " v:" . $stock_info['volume'] . " l:" . $stock_info['lotnr']);

		# log in stock
		$this->logs->stock(ERROR, "clear_error", $stock_info['product_id'], -$stock_info['volume']);

		# clear the error
		$this->stock->where(array("id" => $stock_id))->update(array("state" => STATUS_HISTORY, "volume" => 0));

		redirect('/stock/stock_check', 'refresh');
	}

	/*
	* function: get_product_stock
	* used for autocomplete in move_stock
	*/
	public function get_product_stock(int $location)
	{
		// todo gs1 code
		$term = $this->input->get('query');

		$stock = $this->stock->get_product_stock($term, $location);
		$stock_list = array();

		
		foreach ($stock as $stoc) {
			$stock_list[] = array(
									"value" => $stoc['name'],
									"data" 	=> array(
													"id" 		=> explode(',', $stoc['stock_ids']),
													"volume" 	=> explode(',', $stoc['stock_volumes']),
													"lotnr" 	=> explode(',', $stoc['stock_lotnrs']),
													"eol" 		=> explode(',', $stoc['stock_eols']),
													"prod" 		=> $stoc['name'],
													"unit" 		=> $stoc['unit_sell'],
												)
								);
		}
		echo json_encode(array("query" => $term, "suggestions" => $stock_list));
	}
}
