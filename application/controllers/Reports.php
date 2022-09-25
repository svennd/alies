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
		$this->load->model('Booking_code_model', 'booking');

		# helpers
		$this->load->helper('file_download');

	}


	public function index()
	{
		$this->_render_page('reports/index', array());
	}

	public function accounting(string $search_from = "", string $search_to = "", int $booking_id = 0, bool $csv = false)
	{
		if ($csv)
		{
			$usage = $this->booking->get_usage($booking_id, $search_from, $search_to);
			$csv = $this->load->view('reports/accounting_csv', array("usage" => $usage), true);
			array_to_csv_download($csv, 'accounting_code_' .  $booking_id .'.csv');
			return 0;
		}

		/* input */
		$search_from 	= $this->input->post('search_from');
		$search_to 		= $this->input->post('search_to');
		$booking_id 	= $this->input->post('booking');

		$data = array(
			"usage"				=> is_null($booking_id) ? false : $this->booking->get_usage($booking_id, $search_from, $search_to),
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
		$product_info =  $this->products->get($product_id);

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
			"search"	=> ($this->input->post('submit') == "search_product") ?
											$this->
											products->
											group_start()->
												like('name', $this->input->post('name'), 'both')->
												or_like('short_name', $this->input->post('name'), 'both')->
											group_end()->
												limit(25)
											->get_all() : false,

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

	# show used products for the defined range
	public function product_range($range)
	{
		$accepted_ranges = array('day' => 1, 'week' => 7, 'month' => 31, 'quarter' => 90, 'halfyear' => 182, 'year' => 365);

		if (!array_key_exists($range, $accepted_ranges))
		{
			redirect('reports/products');
		}

		$result = $this->events->get_all_event_products($accepted_ranges[$range]);


		$data = array(
						'results' => $result,
						);

		$this->_render_page('reports/product_range', $data);
	}

	public function bills()
	{
		/* input set default to 30 days */
		$today = new DateTime();
		$input_to = ($this->input->post('search_to')) ? $this->input->post('search_to') : $today->format('Y-m-d');
		$today->modify('-30 day');
		$input_from = ($this->input->post('search_from')) ? $this->input->post('search_from') : $today->format('Y-m-d');

		$bill_overview = $this->bills
			->where('created_at > STR_TO_DATE("' . $input_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $input_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->with_location('fields:name')
			->with_vet('fields:first_name')
			->with_owner('fields:last_name,id,low_budget,debts,btw_nr')
			->order_by('created_at', 'asc')
			->get_all();

		$data = array(
			"bills" 			=> $bill_overview,
			"search_from"	=> $input_from,
			"search_to"		=> $input_to
		);

		$this->_render_page('reports/bill', $data);
	}


	public function vaccine()
	{
		/* input set default to 30 days */
		$today = new DateTime();
		$input_to = ($this->input->post('search_to')) ? $this->input->post('search_to') : $today->format('Y-m-d');
		$today->modify('-30 day');
		$input_from = ($this->input->post('search_from')) ? $this->input->post('search_from') : $today->format('Y-m-d');

		$vaccines = $this->vaccine
			->where('redo > STR_TO_DATE("' . $input_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('redo < STR_TO_DATE("' . $input_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->with_location('fields:name')
			->with_vet('fields:first_name')
			->with_product('fields:name')
			->with_owners('fields:id as owner_id, last_name, first_name, street, nr, city, zip, mail, mobile, contact')
			->with_pet('fields:id, owner, name')
			->order_by('redo', 'asc')
			->get_all();

		$data = array(
			"vaccines" 		=> $vaccines,
			"search_from"	=> $input_from,
			"search_to"		=> $input_to
		);
		$this->_render_page('reports/vaccine', $data);
	}


	public function clients(int $days = 0)
	{
		/* cache this for 6 hours */
		$this->output->cache(360);

		$data = array(
						"last_bill_clients" => $this->chart_last_bill(10),
						"total_clients"		=> $this->owners->count_rows(),
					);
		
		if($days) {
				$data['days'] = $days;
				$data['clients'] = $this->owners
					->group_start()
						->where('created_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
						->where('created_at < NOW()', null, null, false, false, true)
					->group_end()
					->or_group_start()
						->where('updated_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
						->where('updated_at < NOW()', null, null, false, false, true)
					->group_end()
					->get_all();
		}
		
		$this->_render_page('reports/clients', $data);
	}

	private function chart_last_bill(int $years = 5)
	{
		/* 
			going from data : 
			array(3) {
				["y"]=>
				string(4) "2018"
				["total"]=>
				string(1) "6"
				["vet"]=>
				string(5) "Svenn"
			}

			to data :
			label : 2018, 2019, ...
			dataset : 
				label : svenn
				data: 1 2 3 4 5
				label : annelies
				data: 2 3 1 4 7
		*/
		$r = $this->owners->last_bill_by_year_month_init_vet($years);
		

		$years = array_unique(array_column($r, 'y'));
		$vets = array_unique(array_column($r, 'vet'));
		foreach ($vets as $vet)
		{
			$filter_per_vet = array_filter($r, fn ($n) => $n['vet'] == $vet);
			$data[$vet] = array_column($filter_per_vet, 'total');
		}
		return array("years" => $years, "vets" => $vets, "data" => $data);
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
					->fields('volume, net_price')
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
					$product[$pid]['net_price'][$pos] += $us['net_price'];
					$product[$pid]['volume'][$pos] += $us['volume'];
				} else {
					$product[$pid]['net_price'][] 	= $us['net_price'];
					$product[$pid]['volume'][] 			= $us['volume'];
					$product[$pid]['in_price'][]		= $in_stock_price;
				}
			} else {
				$product[$pid] = array(
										"net_price" 	=> array($us['net_price']),
										"volume" 			=> array($us['volume']),
										"in_price" 		=> array($in_stock_price),
										"product"			=> array('name' => $us['product']['name'], 'unit_sell' => $us['product']['unit_sell'])
									);
			}
		}
		return $product;
	}
}
