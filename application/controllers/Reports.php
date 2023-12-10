<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Products_model', 'products');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Users_model', 'users');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Events_model', 'events');
		$this->load->model('Vaccine_model', 'vaccine');
		$this->load->model('Register_in_model', 'register_in');
		$this->load->model('Booking_code_model', 'booking');

		# helpers
		$this->load->helper('file_download');

	}

	public function accounting(string $search_from = "", string $search_to = "", int $booking_id = 0, bool $csv = false)
	{
		if ($csv)
		{
			$usage = $this->booking->get_usage_detail($booking_id, $search_from, $search_to);
			$csv = $this->load->view('reports/accounting_csv', array("usage" => $usage), true);
			array_to_csv_download($csv, 'accounting_code_' .  $booking_id .'.csv');
			return 0;
		}

		/* input */
		$search_from 	= $this->input->post('search_from');
		$search_to 		= $this->input->post('search_to');
		$booking_id 	= $this->input->post('booking');

		$data = array(
			"usage"				=> is_null($booking_id) ? false : $this->booking->get_usage_detail($booking_id, $search_from, $search_to),
			"booking"			=> $this->booking->get_all(),
			"search_from"		=> is_null($search_from) ? false : $search_from,
			"search_to"			=> is_null($search_to) ? false : $search_to,
			"booking_s"			=> is_null($booking_id) ? false : $booking_id,
		);
		$this->_render_page('reports/accounting', $data);

	}

	public function stock_list($location = false)
	{
		if (!$location)
		{
			$data = array(
							"locations"		=> $this->location,
						);

			$this->_render_page('reports/stock_list_overview', $data);
		}
		else
		{
			$stock = $this->stock->get_stock_list($location);

			$csv = $this->load->view('reports/stock_list', array("stock_list" => $stock), true);
			array_to_csv_download($csv, 'stocklist_' . (int) $location . '.csv');
		}
	}

	/*
		give a simple list of the usage
		--> HEAVY JOIN QUERY
	*/
	public function usage(int $product_id)
	{
		/* input */
		$search_from 	= $this->input->post('search_from');
		$search_to 		= $this->input->post('search_to');

		# check usage
		# if non given show from -3m to today
		$usage = ($this->input->post('submit') == "usage" && $search_from && $search_to) ? 
			$this->products->usage_detail($product_id, $search_from, $search_to) 
				: 
			$this->products->usage_detail($product_id, date("Y-m-d", strtotime("-3 months")), date("Y-m-d"));
		
		$data = array(
			"prod_info"			=> $this->products->get($product_id),
			"usage" 			=> $usage,
			"search_from"		=> is_null($search_from) ? date("Y-m-d", strtotime("-3 months")) : $search_from,
			"search_to"			=> is_null($search_to) ? date("Y-m-d") : $search_to,
		);
		$this->_render_page('reports/usage_detail', $data);
	}

	public function usage_csv(int $product_id, $search_from, $search_to)
	{
		$product = $this->products->usage_detail($product_id, $search_from, $search_to);
		// $product_info =  $this->products->get($product_id);

		$csv_lines = array();
		# headers
		$csv_lines[] = array('volume','vet', 'pet_id', 'client_id', 'lotnr', 'eol', 'created_at');

		foreach ($product as $p) {
			$csv_lines[] = array(
									$p['volume'],
									$p['first_name'],
									$p['pet_id'],
									$p['id'],
									$p['lotnr'],
									$p['eol'],
									$p['created_at'],
									);
		}
		array_to_csv($csv_lines);
	}

	/*
		generate a list with sold products, combined.
		Also split if inprice/net income was not the same
	*/
	public function products()
	{
		/* input */
		$search_from 	= $this->input->post('search_from');
		$search_to 		= $this->input->post('search_to');

		/* check usage */
		$product = ($this->input->post('submit') == "usage" && $search_from && $search_to) ? $this->get_usage($search_from, $search_to) : false;

		$data = array(
			"usage"					=> $product,
			"search_from"			=> $search_from,
			"search_to"				=> $search_to,
			);

		$this->_render_page('reports/products', $data);
	}

	/*
		make a "raw" dump of the sold products in csv
	*/
	public function products_csv($search_from = false, $search_to = false)
	{
		$product = $this->get_usage($search_from, $search_to, true);

		$csv_lines = array();
		# headers
		$csv_lines[] = array('name', 'volume', 'unit', 'inprice', 'net_price', 'barcode');

		foreach ($product as $p) {
			$csv_lines[] = array(
												$p['product']['name'],
												$p['volume'],
												$p['product']['unit_sell'],
												(isset($p['stock']) ? $p['stock']['in_price'] : '-1'),
												$p['net_price'],
												$p['barcode'],
									);
		}
		array_to_csv($csv_lines);
	}

	// nowhere linked for now
	// public function clients(int $days = 0)
	// {
	// 	/* cache this for 6 hours */
	// 	$this->output->cache(360);

	// 	$data = array(
	// 					"last_bill_clients" => $this->chart_last_bill(10),
	// 					"total_clients"		=> $this->owners->count_rows(),
	// 				);
		
	// 	if($days) {
	// 			$data['days'] = $days;
	// 			$data['clients'] = $this->owners
	// 				->group_start()
	// 					->where('created_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
	// 					->where('created_at < NOW()', null, null, false, false, true)
	// 				->group_end()
	// 				->or_group_start()
	// 					->where('updated_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
	// 					->where('updated_at < NOW()', null, null, false, false, true)
	// 				->group_end()
	// 				->get_all();
	// 	}
		
	// 	$this->_render_page('reports/clients', $data);
	// }

	public function register_in()
	{
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-d", strtotime("-1 months")) : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-d") : $this->input->post('search_to');

		$register_in = $this->register_in->date_lookup($search_from, $search_to);
		
		$this->_render_page('reports/register_in', array(
			"register_in" 	=> $register_in,
			"search_from"	=> $search_from,
			"search_to"		=> $search_to,
		));	
	}

	public function register_out()
	{
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-d", strtotime("-1 months")) : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-d") : $this->input->post('search_to');

		$register_out = $this->events->register_out($search_from, $search_to);
		
		$this->_render_page('reports/register_out', array(
			"register_out" 	=> $register_out,
			"search_from"	=> $search_from,
			"search_to"		=> $search_to,
		));	
	}

	/*
		get all product usage for a certain time frame
	*/
	private function get_usage($search_from, $search_to, $csv = false)
	{
		$product = array();
		$usage = $this->eprod
					->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
					->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
					->fields('volume, price_net')
					->with_stock('fields:in_price')
					->with_product('fields:name, unit_sell')
					->get_all();

		/* if no usage found */
		if (!$usage)
		{
			return $product;
		}

		if ($csv)
		{
			return $usage;
		}

		foreach ($usage as $us) {
			$pid = $us['product_id'];
			$in_stock_price = (isset($us['stock']['in_price']) ? $us['stock']['in_price'] : '-1');

			# generate a array with the products
			if (isset($product[$pid])) {
				$pos = array_search($in_stock_price, $product[$pid]['in_price']);

				# check if we already got this value
				if ($pos !== false) {
					$product[$pid]['price_net'][$pos] += $us['price_net'];
					$product[$pid]['volume'][$pos] += $us['volume'];
				} else {
					$product[$pid]['price_net'][] 	= $us['price_net'];
					$product[$pid]['volume'][] 			= $us['volume'];
					$product[$pid]['in_price'][]		= $in_stock_price;
				}
			} else {
				$product[$pid] = array(
										"price_net" 	=> array($us['price_net']),
										"volume" 			=> array($us['volume']),
										"in_price" 		=> array($in_stock_price),
										"product"			=> array('name' => $us['product']['name'], 'unit_sell' => $us['product']['unit_sell'])
									);
			}
		}
		return $product;
	}
}
