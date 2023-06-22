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
		$this->load->model('Delivery_slip_model', 'slip');
		$this->load->model('Register_in_model', 'registry_in');

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
						"show_all"		=> $all,
						"stock_usage" 	=> $this->stock->get_usage($pid)
						);
		$this->_render_page('stock/details', $data);
	}

	/*
		stock that is close to or has expired.
	*/
	public function expired_stock()
	{
		$expired = $this->stock
			->fields('eol, lotnr, volume, barcode')
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

	public function move_stock()
	{
		if ($this->input->post('submit') == "barcode") {
			$warnings = array();

			$from_location 	= $this->input->post('from_location');
			$new_location 	= $this->input->post('to_location');
			$barcodes 	  	= (empty($this->input->post('barcodes'))) ? array() : explode(",", substr($this->input->post('barcodes'), 0, -1));

			$stock_list	  = array();

			if ($barcodes && count($barcodes) > 0) {
				foreach ($barcodes as $barcode) {
					// probably unlikely to be used atm
					// gs1 code means we know the product/eol/... but not the location
					if (parse_gs1($barcode)) 
					{
						$x = parse_gs1($barcode);
						$stock_product = $this->stock->gs1_lookup($x['pid'], $x['lotnr'], $x['date'], $from_location)[0];
						if ($stock_product) {
							$stock_list[$stock_product['barcode']] = array(
													"name" 		=> $stock_product['pname'],
													"eol" 		=> $stock_product['eol'],
													"lotnr" 	=> $stock_product['lotnr'],
													"volume" 	=> $stock_product['volume'],
													"barcode" 	=> $stock_product['barcode'],
													"sell_unit" => $stock_product['unit_sell']
												);
						}
						# unknown barcode
						else {
							$this->logs->logger(WARN, "unknown_stock_move", "did not recognize stock barcode : ". $barcode);
							$warnings[] = "Did not recognize barcode : " . $barcode . " on this location";
						}
					}
					else 
					{
						# a stock can be split so multiple results could be generated
						$stock_product = $this->stock->with_products('fields:name, unit_sell')->where(array("barcode" => $barcode, "location" => $from_location, "state" => STOCK_IN_USE))->get();
						# its a known stock product
						if ($stock_product) {
							
							# index : safety check for doubles
							$stock_list[$barcode] = array(
													"name" 		=> $stock_product['products']['name'],
													"eol" 		=> $stock_product['eol'],
													"lotnr" 	=> $stock_product['lotnr'],
													"volume" 	=> $stock_product['volume'],
													"barcode"	=> $barcode,
													"from_stock"=> $this->stock->get_stock_levels($stock_product['products']['id'], $from_location),
													"to_stock" 	=> $this->stock->get_stock_levels($stock_product['products']['id'], $new_location),
													"from_limit"=> $this->stock_limit->where(array("product_id" => $stock_product['products']['id'], "stock" => $from_location))->get(),
													"to_limit"  => $this->stock_limit->where(array("product_id" => $stock_product['products']['id'], "stock" => $new_location))->get(),
													"sell_unit" => $stock_product['products']['unit_sell']
												);
						}
						# unknown barcode
						else {
							$this->logs->logger(WARN, "unknown_stock_move", "did not recognize stock barcode : ". $barcode);
							$warnings[] = "Did not recognize barcode : " . $barcode . " on this location";
						}

					}

				}
			} else {
				$warnings[] = "no barcodes provided.";
			}

			$data = array(
							"locations" => $this->location,
							"warnings" 	=> $warnings,
							"move_list" => $stock_list,
							"from_location" => $from_location,
							"new_location" => $new_location
						);

			$this->_render_page('stock/move_quantities', $data);

		} elseif ($this->input->post('submit') == "quantities") {

			$from 			= $this->input->post('from_location');
			$to 			= $this->input->post('new_location');
			$move_volumes 	= $this->input->post('move_volume');

			foreach ($move_volumes as $barcode => $value) {
				$this->logs->logger(INFO, "move_stock", "barcode:". $barcode . " from:" . $from . "=>" . $to. " volume:" . $value);
				$this->stock->reduce_product($barcode, $from, $value);
				$this->stock->add_product_to_stock($barcode, $from, $to, $value);
			}
			redirect('/products/index/' . 1);
		}
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
										"location" 		=> $this->user->current_location,
										"lotnr" 		=> $this->input->post('lotnr'),
										"in_price" 		=> $this->input->post('in_price'),
										"state" 		=> STOCK_CHECK
									))->get();

				# increase current verify stock
				if ($result) {
					$this->logs->stock(DEBUG, "add_stock_re", $this->input->post('pid'), $this->input->post('new_volume'));
					$sql = "UPDATE stock SET volume=volume+" . $this->input->post('new_volume') . " WHERE id = '" . $result['id'] . "' AND state = '" . STOCK_CHECK . "' limit 1;";
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

					$this->logs->stock(DEBUG, "add_stock", $this->input->post('pid'), $this->input->post('new_volume'));
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
				$error = ($this->input->post('new_volume') > 5000) ? "Invalid volume (>5000)" : "Not a valid product or no volume...";

				# log this
				$this->logs->logger(WARN, "bad_stock_entry", "pid: " . $this->input->post('pid') . " eol: " . $this->input->post('eol') . " in_price" . $this->input->post('in_price') . " lotnr:" . $this->input->post('lotnr') . " volume:" . $this->input->post('new_volume'));
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

	public function verify_stock()
	{
		$slip = $this->slip->insert(array(
					"vet"		=> $this->user->id,
					"note"		=> $this->input->post('delivery_slip'),
					"regdate"	=> $this->input->post('regdate'),
					"location" 	=> $this->user->current_location
			));
		
		# registry_in
		$stock = $this->stock->fields('product_id, eol, volume, in_price, lotnr')->where(array("state" => STOCK_CHECK))->get_all();
		foreach ($stock as $stoc)
		{
			$this->registry_in->insert(array(
						"product" 	=> $stoc['product_id'],
						"eol" 		=> $stoc['eol'],
						"volume" 	=> $stoc['volume'],
						"in_price" 	=> $stoc['in_price'],
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

	public function write_off(string $barcode = '', int $location = -1)
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
		
		} elseif (!empty($barcode) && $location != -1) {

			$product = $this->stock->with_products('fields:name, unit_sell')
							->where(array("barcode" => $barcode, "location" => $location))
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
				$this->logs->stock(WARN, "writeoff", $this->input->post("product_id"), -$this->input->post("volume"), $this->input->post("location"));

				# clean stock since most likely this will result in a 0 line, no need to print stuff
				$this->stock_clean(false);
			}
			redirect('stock/expired_stock', 'refresh');
		}
	}

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
	covetrus specific output
	*/
	public function quick_stock()
	{
		// not ready
		exit;
		$this->load->model('Delivery_model', 'delivery');

		if ($this->input->post('submit')) 
		{
			$data = $this->input->post('text');
			$lines = explode("\n", $data);
			$data_lines = array();
			$updated = array();

			foreach($lines as $line)
			{
				if(empty($line)) { continue; }

				# Besteldatum|Bestelbonnr|Mijn Referentie|Art. nr.|Omschrijving|CNK nummer|Leveringsdatum|Leveringsbon nummer|bruto prijs|netto prijs|verk.pr.  apoth.|BTW|aantal|Lotnummer|Vervaldatum|Facturatie
				list($order_date, $order_nr, $my_ref, $art_nr, $description, $cnk, $delivery_date, $delivery_nr, $bruto, $netto, $suggetion_price, $btw, $amount, $lotnr, $due_date, $facturatie) = explode("|", $line);
				
				# skip header || footer
				if ($order_date == "Besteldatum" || empty($order_date)) { continue; }
				
				// insert it into 
				$intel = $this->delivery->record(array(
					"order_date"	=> $order_date,
					"order_nr" 		=> $order_nr,
					"my_ref" 		=> $my_ref,
					"wholesale_artnr" => $art_nr,
					"delivery_date" => $delivery_date,
					"delivery_nr" 	=> $delivery_nr,
					"bruto_price" 	=> $bruto,
					"netto_price" 	=> $netto,
					"amount"		=> $amount,
					"lotnr" 		=> $lotnr,
					"due_date" 		=> $due_date,
				));
				$data_lines[] = $intel['id'];
				$updated[$intel['id']] = $intel['updated_bruto_price'];
			}

			$delivery_data = $this->delivery->where("id", $data_lines)->with_wholesale()->with_product()->get_all();
		}
		$this->_render_page('stock/quick', array(
				"delivery" => $delivery_data, 
				"updated" => $updated, 
				"lines" => count($lines), 
				"extra_footer" 	=> '<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>'
	));		
	}
}
