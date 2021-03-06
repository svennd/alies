<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stock extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Products_model', 'product');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Stock_limit_model', 'stock_limit');
		$this->load->model('Stock_write_off_model', 'stock_write_off_log');
	}
	
	public function index($filter = false, $success = false)
	{
		$products = array();
		if ($filter) {
			if ($filter == "all") {
				$products = $this->stock->where(array('state' => STOCK_IN_USE))->get_all_products();
			} else {
				$products = $this->stock->where(array('state' => STOCK_IN_USE, 'location' => (int)$filter))->with_products('fields:name, unit_sell')->get_all();
			}
		}
		
		$data = array(
					"locations" => $this->location,
					"filter" 	=> $filter,
					"success" 	=> $success,
					"products" 	=> $products,
					);
					
		$this->_render_page('stock_index', $data);
	}
	
	public function stock_detail($pid)
	{
		$stock_detail = $this->stock->where(array('product_id' => $pid))
									->with_products('fields: name, unit_sell, buy_price')
									->with_stock_locations('fields: name')
									->get_all();
		
		$data = array(
						"stock_detail" => $stock_detail
						);
		$this->_render_page('stock_detail', $data);
	}
	
	public function move_stock()
	{
		if ($this->input->post('submit') == "barcode") {
			$warnings = array();
			$new_location = $this->input->post('location');
			$barcodes 	  = (empty($this->input->post('barcodes'))) ? array() : preg_split("/\r\n|\n|\r/", $this->input->post('barcodes'));
			$stock_list	  = array();
			
			if ($barcodes && count($barcodes) > 0) {
				foreach ($barcodes as $barcode) {
					# a stock can be split so multiple results could be generated
					$stock_product = $this->stock->with_products()->where(array("barcode" => $barcode, "location" => $this->user->current_location))->get();
					
					# its a known stock product
					if ($stock_product) {
						# index : safety check for doubles
						$stock_list[$barcode] = array(
												"name" 		=> $stock_product['products']['name'],
												"eol" 		=> $stock_product['eol'],
												"lotnr" 	=> $stock_product['lotnr'],
												"volume" 	=> $stock_product['volume'],
												"sell_unit" => $stock_product['products']['unit_sell']
											);
					}
					# unknown barcode
					else {
						$this->logs->insert(array(
													"event" => "unknown_stock_move",
													"level" => INFO,
													"msg" 	=> "did not recognize stock barcode : ". $barcode . " from location ". $this->user->current_location . " (location)"
												));
						$warnings[] = "Did not recognize barcode : " . $barcode . " on this location";
					}
				}
			} else {
				$warnings[] = "no barcodes provided.";
			}
			
			$data = array(
							"locations" => $this->location,
							"warnings" 	=> $warnings,
							"move_list" => $stock_list,
							"new_location" => $new_location
						);

			$this->_render_page('stock_move_quantities', $data);
		} elseif ($this->input->post('submit') == "quantities") {
						
			$from 			= $this->user->current_location;
			$to 			= $this->input->post('location');
			// $move_volumes 	= $this->input->post('move_volume'); // unused
			
			foreach ($this->input->post('move_volume') as $barcode => $value) {
				$this->stock->reduce_product($barcode, $from, $value);
				$this->stock->add_product_to_stock($barcode, $from, $to, $value);
			}
			redirect('/stock/' . $to . '/' . 1);
		}
	}
	
	public function add_stock()
	{
		$error = false;
		
		if ($this->input->post('submit')) {
			if (!empty($this->input->post('pid')) && !empty($this->input->post('new_volume'))) {
				# check if this is already in the verify list
				$result = $this->stock->where(array(
										"product_id" 	=> $this->input->post('pid'),
										"eol" 			=> (empty($this->input->post('eol')) ? null : $this->input->post('eol')),
										"location" 		=> $this->user->current_location,
										"lotnr" 		=> $this->input->post('lotnr'),
										"in_price" 		=> $this->input->post('in_price'),
										"state" 		=> STOCK_CHECK
									))->get();
									
				# increase current verify stock
				if ($result) {
					$sql = "UPDATE stock SET volume=volume+" . $this->input->post('new_volume') . " WHERE id = '" . $result['id'] . "' limit 1;";
					$this->db->query($sql);
				}
				# create new verify stock
				else {
					# also generate a barcode here
					$this->load->library('barcode');
					 
					# generate barcode
					# reduce time with 01/12/2019
					# move to a base36 (to use letters)
					$barcode = base_convert((time() - 1575158400), 10, 36);
					$this->barcode->generate($barcode);
				
					$this->stock->insert(array(
											"product_id" 		=> $this->input->post('pid'),
											"eol" 				=> $this->input->post('eol'),
											"location" 			=> $this->user->current_location,
											"in_price" 			=> $this->input->post('in_price'),
											"lotnr" 			=> $this->input->post('lotnr'),
											"barcode"			=> $barcode,
											"volume" 			=> $this->input->post('new_volume'),
											"state"				=> STOCK_CHECK
										));
										
					if ($this->input->post('new_barcode_input')) {
						$this->product
								->where(array("id" => $this->input->post('pid')))
								->update(array("input_barcode" => $this->input->post('barcode_gs1')));
					}
				}
			} else {
				$error = "Not a valid product, or no volume...";
			}
		}
		
		$data = array(
						"error" 	=> $error,
						"products" 	=> $this->stock->with_products('fields: id, name, unit_sell, buy_price')->where(array('state' => STOCK_CHECK))->get_all(),
						"extra_footer" => '<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>'
					);
		$this->_render_page('stock_add', $data);
	}
	
	public function delete_stock($stock_id)
	{
		$this->stock->where(array("state" => STOCK_CHECK))->delete($stock_id);
		redirect('stock/add_stock', 'refresh');
	}
	
	public function verify_stock()
	{
		$this->stock->where(array("state" => STOCK_CHECK))->update(array("state" => STOCK_IN_USE));
		redirect('stock/add_stock', 'refresh');
	}
	
	public function write_off()
	{
		# return the details of the selected product
		if ($this->input->post('submit') == "writeoff") {
			$product = $this->stock->with_products('fields:name, unit_sell')
							->where(array("barcode" => $this->input->post('barcode'), "location" => $this->input->post("location")))
							->get();
			$data = array(
							"locations" => $this->location,
							"product"	=> $product
						);
			$this->_render_page('stock_write_off_quantities', $data);
		} else {
			# reduce stock as requested
			if ($this->input->post('submit') == "write_off_q") {
				$this->stock->reduce_product($this->input->post("barcode"), $this->input->post("location"), $this->input->post("volume"));
				$this->stock_write_off_log->insert(array(
												"product_id" 	=> $this->input->post("product_id"),
												"volume" 		=> $this->input->post("volume"),
												"location" 		=> $this->input->post("location"),
												"barcode" 		=> $this->input->post("barcode"),
												"vet" 			=> $this->user->id,
												
									));
			}

			redirect('stock/' . $this->input->post("location") . "/" . 2, 'refresh');
		}
	}
	
	public function limit()
	{
		# global shortages
		$r = $this->product->where('limit_stock >', 0)->fields('id, unit_sell, name, limit_stock')->get_all();
		
		$result = array();
		
		if ($r) {
			
			foreach ($r as $prod) {
				$stock = $this->stock->select('SUM(volume) as sum_vol', false)->fields()->where(array('product_id' => $prod['id']))->group_by('product_id')->get();
		
				# false if none found
				if ($stock && $stock['sum_vol'] < $prod['limit_stock']) {
					$result[] = array(
							"id" 				=> $prod['id'],
							"name" 				=> $prod['name'],
							"unit_sell" 		=> $prod['unit_sell'],
							"limit_stock" 		=> $prod['limit_stock'],
							"in_stock" 			=> (($stock['sum_vol']) ? $stock['sum_vol'] : '0'),
						);
				}
			}
		}
		
		$data = array(
						"global_stock" 	=> $result,
						"locations" 	=> $this->location,
						"local_stock" 	=> $this->stock->get_local_stock_shortages()
					);
		$this->_render_page('stock_shortages', $data);
	}
	
	public function stock_limit()
	{
		if ($this->input->post('submit') == "add") {
			$added = $this->stock_limit->insert(array(
									"product_id" 		=> $this->input->post('product_id'),
									"stock" 			=> $this->input->post('location'),
									"volume" 			=> $this->input->post('volume')
								));
		}
		$data = array(
				"products" 			=> $this->product->get_all(),
				"stock_limit" 		=> $this->stock_limit->with_products()->get_all(),
				"locations" 		=> $this->location,
				"added"				=> (isset($added)) ? $added : false,
			);
		$this->_render_page('stock_limit', $data);
	}
	
	public function stock_limit_rm($id)
	{
		$this->stock_limit->delete($id);
		redirect('/stock/stock_limit', 'refresh');
	}
}
