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
		$this->load->model('Logs_model', 'logs');
	}
	
	public function index()
	{
		$data = array(
						"last_created" 		=> $this->products->fields('id, name, created_at')->limit(5)->order_by("created_at", "desc")->get_all(),
						"last_modified" 	=> $this->products->fields('id, name, updated_at')->limit(5)->order_by("updated_at", "desc")->get_all(),
						//"total_products" 	=> $this->products->count_rows(),
						"search_q"			=> $this->input->post('name'),
						"search"			=> ($this->input->post('submit')) ? $this->products->group_start()->like('name', $this->input->post('name'), 'both')->or_like('short_name', $this->input->post('name'), 'both')->group_end()->limit(25)->get_all() : false,
						"product_types"		=> $this->prod_type->with_products('fields:*count*')->get_all()
						);
						
		$this->_render_page('product_index', $data);
	}
	
	
	public function new($step = false, $pid = false)
	{
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		
		# populate the data array
		$data = array(
				'step'		=> (!$step) ? 1 : (int)$step,
				'type' 		=> $this->prod_type->get_all(),
				'booking'	=> $this->booking->get_all(),
				);
		
		if ($this->input->post('submit')) {
			if (!$step)
			{
				$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));
				
				$input = array(
									"name" 				=> $this->input->post('name'),
									"short_name" 		=> $this->input->post('short_name'),
									"producer" 			=> $this->input->post('producer'),
									"supplier" 			=> $this->input->post('supplier'),
									"type" 				=> $this->input->post('type'),
									"offset"			=> $this->input->post('offset'),
									"buy_volume" 		=> $this->input->post('buy_volume'),
									"sell_volume" 		=> $this->input->post('sell_volume'),
									"buy_price"			=> 1,
									// "buy_price"			=> $this->input->post('buy_price'),
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
					$this->logs->logger($this->user->id, INFO, "new_product", "product_name: " . $this->input->post('name') . " id : " . $pid);
					
					# redirect to next step
					redirect( 'products/new/2/' . $pid );
			}
			elseif ($step == 2)
			{
				# new price
				if ($this->input->post('submit')) {

					# update buy_price
					if (!empty($this->input->post('buy_price')))
					{
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
					} else {
						$this->pprice->insert(array(
													'volume' 		=> $this->input->post('volume'),
													'price' 		=> $this->input->post('price'),
													'product_id' 	=> $pid
											));
					}
				}
			}
		}
		
		if ($step) 
		{
			$data['product'] = $this->products
					->with_prices('fields:volume, price, id')
					->where(array("sellable" => 1))
					->fields('id, name, buy_volume, buy_price, updated_at, unit_sell')
					->get($pid);
		}
		
		$this->_render_page('product/product_new', $data);
	}
	
	public function product_price($id = false)
	{
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		
		# product specific
		if ($id) {
			# new price
			if ($this->input->post('submit')) {
				# modification
				if ($this->input->post('submit') == "edit") {
					$this->pprice
							->where(array(
											"id" 	=> $this->input->post('price_id')
									))
							->update(array(
											"price" => $this->input->post('price'),
									));
				# new price
				} else {
					$this->pprice->insert(array(
												'volume' 		=> $this->input->post('volume'),
												'price' 		=> $this->input->post('price'),
												'product_id' 	=> $id
										));
				}
			}
		
			$data = array(
							"product" 		=> $this->products
													->with_prices('fields:volume, price, id')
													->where(array("sellable" => 1))
													->fields('id, name, buy_volume, buy_price, updated_at, unit_sell')
													->get($id),
													
							"stock_price"	=> $this->stock
													->where(array("product_id" => $id, "state <" => STOCK_HISTORY, "volume >" => 0))
													->fields('in_price, volume, created_at')
													->get_all()
						);
			$this->_render_page('product_price_edit', $data);
		
		# full list of products
		} else {
			$data = array(
							"products" 		=> $this->products
													->with_prices('fields:volume, price')
													->fields('name, buy_volume, buy_price, sellable, updated_at, unit_sell')
													->where(array("sellable" => 1))
													->get_all()
						);
					
			$this->_render_page('product_price_list', $data);
		}
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
	
	public function product_list($id_or_product = false)
	{
		// defaults
		$products = false;
		
		if ($id_or_product) {
			if ($id_or_product == "other") {
				$products = $this->products
									->with_prices('fields:volume, price')
									->with_booking_code()
									->with_type('fields:name')
									->where('type', '0')
									->get_all();
			} elseif ($id_or_product) {
				$products = $this->products
									->with_prices('fields:volume, price')
									->with_booking_code()
									->with_type('fields:name')
									->where('type', $id_or_product)
									->get_all();
			}
		}
		$data = array(
						"products" 		=> $products,
						"types" 		=> $this->prod_type->get_all()
					);
			
		$this->_render_page('products_list', $data);
	}
	
	public function product($id = false)
	{
		$update = false;
		if ($this->input->post('submit')) {
			$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));
			
			$input = array(
								"name" 				=> $this->input->post('name'),
								"short_name" 		=> $this->input->post('short_name'),
								"producer" 			=> $this->input->post('producer'),
								"supplier" 			=> $this->input->post('supplier'),
								"posologie" 		=> $this->input->post('posologie'),
								"toedieningsweg" 	=> $this->input->post('toedieningsweg'),
								"type" 				=> $this->input->post('type'),
								"offset"			=> $this->input->post('offset'),
								"buy_volume" 		=> $this->input->post('buy_volume'),
								"sell_volume" 		=> $this->input->post('sell_volume'),
								"buy_price"			=> $this->input->post('buy_price'),
								"unit_buy" 			=> $this->input->post('unit_buy'),
								"unit_sell" 		=> $this->input->post('unit_sell'),
								"input_barcode" 	=> (empty($this->input->post('input_barcode')) ? NULL : $this->input->post('input_barcode')),
								"btw_buy" 			=> $this->input->post('btw_buy'),
								"btw_sell" 			=> $booking['btw'],
								"vaccin" 			=> (is_null($this->input->post('vaccin')) ? 0 : 1),
								"vaccin_freq" 		=> $this->input->post('vaccin_freq'),
								"booking_code" 		=> $this->input->post('booking_code'),
								"delay" 			=> $this->input->post('delay'),
								"comment" 			=> $this->input->post('comment'),
								"sellable" 			=> (is_null($this->input->post('sellable')) ? 0 : 1),
								"limit_stock" 		=> $this->input->post('limit_stock')
							);
							
			if ($this->input->post('submit') == "add") {
				# new product
				$id = $this->products->insert($input);
				$update = $id;
				
				# log this
				$this->logs->logger($this->user->id, INFO, "new_product", "product_name: " . $this->input->post('name') . " id : " . $id);
				
			} elseif ($this->input->post('submit') == "edit") {
				$update = $this->products->update($input, $id);
			}
		}
		
		$data = array(
						'product' 	=> ($id) ? $this->products->with_prices('fields:id, volume, price')->get($id) : false,
						'type' 		=> $this->prod_type->get_all(),
						'update'	=> $update,
						'booking'	=> $this->booking->get_all(),
						'history_1m'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -30 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_6m'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -180 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_1y'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -365 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						);
		$this->_render_page('product_detail', $data);
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
		$barcode = $this->input->get('barcode');
		$location = $this->input->get('loc');
		$result = $this->stock
					->fields('eol, barcode, volume')
					->with_products('fields: name, unit_sell, btw_sell, booking_code')
				->where(array(
				'barcode' 	=> $barcode,
				'location' 	=> $location
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
		
		$return = array();
		
		if (strlen($query) > 1) {
			# products
			$result = $this->products
								->fields('id, name, type, buy_volume, unit_buy, sell_volume, unit_sell, buy_price')
								->with_type()
								->where('name', 'like', $query, true)
								->limit(20)
								->order_by("type", "ASC")
								->get_all();

			# in case no results
			if ($result) {
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
			}
		}
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}
	
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
			if string is 32 chars long its most likely GS1 barcode
			
			Searching for a :
				- product w/ stock
				- procedure
				- product on barcode

		*/
		if (strlen($query) == 32)
		{
			$gsl = $this->parse_gs1($query);
			
			if (!$gsl) { return false; }
			
			$stck = $this->stock->gs1_lookup($gsl['pid'], $gsl['lotnr'], $gsl['date'], $this->user->current_location);
			
			if ($stck)
			{
				# should only return a single result
				$result = $stck['0'];
				
				
				$query_prices = $this->pprice->get_all($result['pid']);
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
										"lotnr"		=> $gsl['lotnr'],
										"id" 		=> $result['pid'],
										"price" 	=> $prices,
										"btw" 		=> $result['btw_sell'],
										"booking" 	=> $result['booking_code'],
										"prod" 		=> 1,
									));
			}
		}
		elseif (strlen($query) > 1) {
			# products
			$result = $this->products
								->fields('id, name, type, unit_sell, btw_sell, booking_code, vaccin, vaccin_freq')
								->with_type()
								->with_prices('fields: volume, price')
								->with_stock('fields: location, eol, lotnr, volume, barcode, state', 'where:`state`=\'1\'')
								->where('name', 'like', $query, true)
								->where('sellable', '1')
								->limit(250) # this will count both products + prices + stock (somehow)
								->order_by("type", "ASC")
								->get_all();
								
			# in case no results
			if ($result) {
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
													"type" 		=> (isset($r['type']['name']) ? $r['type']['name'] : "other"),
													"id" 		=> $r['id'],
													"stock"		=> $stock,
													"prices"	=> $prices,
													"unit"		=> $r['unit_sell'],
													"btw"		=> $r['btw_sell'],
													"booking"	=> $r['booking_code'],
													"vaccin"	=> $r['vaccin'],
													"vaccin_freq"	=> $r['vaccin_freq'],
													"prod"		=> 1
												)
								);
				}
			}
			
			# procedures
			$result = $this->procedures
								->fields('id, name, price, booking_code')
								->where('name', 'like', $query, true)
								->get_all();
						
			if ($result) {
				foreach ($result as $r) {
					$return[] = array(
										"value" => $r['name'],
										"data" 	=> array(
														"type" 		=> "Proc",
														"id" 		=> $r['id'],
														"price"		=> $r['price'],
														"btw"		=> "21",
														"booking"	=> $r['booking_code'],
														"prod"		=> 0
													)
									);
				}
			}
		}
	
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}
	
	
	# based on the gs1 code we can get information on the product from the database
	# even if not we can get date & lotnr and "product id" (not internal product_id)
	private function parse_gs1($barcode)
	{
		# this accepts 2 formats of gs1 code
		if (preg_match('/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/', $barcode, $result))
		{
			$pid = $result[1];
			$date = (!$result[3]) ? $result[6] : $result[4];
			$lotnr = (!$result[3]) ? $result[7] : $result[3];
			
			$day = (substr($date, 4, 2) == "00") ? "01" : substr($date, 4, 2);
				
			return array(
						'date' 	=> "20" . substr($date, 0, 2) . "-" . substr($date, 2,2) . "-" . $day,
						'lotnr' => $lotnr,
						'pid' 	=> $pid
					);	
		}
		
		return false;
	}
}
