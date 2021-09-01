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
		$this->load->model('Users_model', 'users');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Vaccine_model', 'vaccine');
	}
	

	public function index()
	{
		$this->_render_page('reports/index', array());
	}
	
	public function graphs()
	{
		$data = array(
			"last_6_month"		=> $this->get_last_x_month_chart(12),
			"income_per_vet"	=> $this->get_income_overview_chart(12),
			"avg_per_vet"		=> $this->get_avg_per_consult(12),
			"last_bill_chart"	=> $this->last_bill_by_year_month_init_vet(),
			"extra_footer" 		=> '<script src="'. base_url() .'assets/js/Chart.min.js"></script>',
		);

		/* cache this for 5 minutes */
		$this->output->cache(5);
		$this->_render_page('reports/graphs', $data);
	}
	
	public function products()
	{
		$product 		= false;
		$search_from 	= $this->input->post('search_from');
		$search_to 		= $this->input->post('search_to');
		
		if ($this->input->post('submit') == "usage" && $search_from && $search_to) {
			$usage = $this->eprod
						->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->fields('volume, net_price')
						->with_stock('fields:in_price')
						->with_product('fields:name, unit_sell')
						->get_all();
						
			$product = array();
			
			if ($usage)
			{
				foreach ($usage as $us) {
					$pid = $us['product_id'];
					$in_stock_price = (isset($us['stock']['in_price']) ? $us['stock']['in_price'] : '-1');
					
					if (isset($product[$pid])) {
						$pos = array_search($in_stock_price, $product[$pid]['in_price']);
						
						# check if we already got this value
						if ($pos !== false) {
							$product[$pid]['net_price'][$pos] += $us['net_price'];
							$product[$pid]['volume'][$pos] += $us['volume'];
						} else {
							$product[$pid]['net_price'][] 	= $us['net_price'];
							$product[$pid]['volume'][] 		= $us['volume'];
							$product[$pid]['in_price'][]	= $in_stock_price;
						}
					} else {
						$product[$pid] = array(
												"net_price" 	=> array($us['net_price']),
												"volume" 		=> array($us['volume']),
												"in_price" 		=> array($in_stock_price),
												"product"		=> array('name' => $us['product']['name'], 'unit_sell' => $us['product']['unit_sell'])
											);
					}
				}
			}
		}
		
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
											
			"usage"				=> $product,
			"search_from"		=> $search_from,
			"search_to"			=> $search_to,
			);
	
		$this->_render_page('reports/products', $data);
	}
	
	public function products_csv($search_from = false, $search_to = false)
	{
		$usage = $this->eprod
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->fields('volume, net_price')
			->with_stock('fields:in_price')
			->with_product('fields:name, unit_sell')
			->get_all();
		
		/*
			format to $product's array
			copied from products()
		*/
		$product = array();
		foreach ($usage as $us) {
			$pid = $us['product_id'];
			$in_stock_price = (isset($us['stock']['in_price']) ? $us['stock']['in_price'] : '-1');
			
			if (isset($product[$pid])) {
				$pos = array_search($in_stock_price, $product[$pid]['in_price']);
				
				# check if we already got this value
				if ($pos !== false) {
					$product[$pid]['net_price'][$pos] += $us['net_price'];
					$product[$pid]['volume'][$pos] += $us['volume'];
				} else {
					$product[$pid]['net_price'][] 	= $us['net_price'];
					$product[$pid]['volume'][] 		= $us['volume'];
					$product[$pid]['in_price'][]	= $in_stock_price;
				}
			} else {
				$product[$pid] = array(
										"net_price" 	=> array($us['net_price']),
										"volume" 		=> array($us['volume']),
										"in_price" 		=> array($in_stock_price),
										"product"		=> array('name' => $us['product']['name'], 'unit_sell' => $us['product']['unit_sell'])
									);
			}
		}
	}
	
	
	public function product($product_id)
	{
		$data = array(
				"product_info" => $this->
										products->
										fields('name')->
										with_stock('fields:barcode,state,volume,lotnr,eol,in_price,location,updated_at,created_at|where:`state`=\'2\' or `state` = \'3\'|join:true')->
										order_by('stock.state', 'asc')->
										order_by('stock.created_at', 'desc')->
										get_all($product_id),
				"locations"	=> $this->stock_location->get_all(),
				"eprod" => $this->
							eprod->
							with_event()->
							where(array("product_id" => $product_id))->
							get_all(),
			);
		$this->_render_page('reports/product_detail', $data);
	}
	
	public function bills()
	{
		$read_limit = 500;
		$bill_overview = array();

		$today = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $today->format('Y-m-d');
		$today->modify('-30 day');
		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $today->format('Y-m-d');
		
		if ($this->input->post('search_from') && $this->input->post('search_to')) {
			$search_from = $this->input->post('search_from');
			$search_to = $this->input->post('search_to');
		}
		
		$bill_overview = $this->bills
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->with_location('fields:name')
			->with_vet('fields:first_name')
			->order_by('created_at', 'asc')
			->limit($read_limit)
			->get_all();
		
		$data = array(
			"bills" 		=> $bill_overview,
			"search_from"	=> (isset($search_from)) ? $search_from : '',
			"search_to"		=> (isset($search_to)) ? $search_to : '',
			"read_limit"	=> $read_limit,
		);
		$this->_render_page('reports/bill', $data);
	}
	
	
	public function vaccine()
	{
		$vaccines = false;
		
		if ($this->input->post('search_from') && $this->input->post('search_to')) {
			$search_from = $this->input->post('search_from');
			$search_to = $this->input->post('search_to');
			
			// $search_from = "2018-01-17";
			// $search_to = "2018-01-18";
			
			$vaccines = $this->vaccine
				->where('redo > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
				->where('redo < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
				->with_location('fields:name')
				->with_vet('fields:first_name')
				->with_product('fields:name')
				->with_owners('fields:id as owner_id, last_name, first_name, street, nr, city, zip, mail, mobile, contact')
				->with_pet('fields:id, owner, name')
				->order_by('redo', 'asc')
				->get_all();
		}
		$data = array(
			"vaccines" 		=> $vaccines,
			"search_from"	=> (isset($search_from)) ? $search_from : '',
			"search_to"		=> (isset($search_to)) ? $search_to : ''
		);
		$this->_render_page('reports/vaccine', $data);
	}

	
	public function clients()
	{
		/* cache this for 6 hours */
		$this->output->cache(360);
		
		$data = array(
						"total_clients"		=> $this->owners->count_rows(),
					);
					
		$this->_render_page('reports/clients', $data);
	}
	
	/*
		allot of preprocessing to get it easy in the template
	*/
	private function get_last_x_month_chart($months)
	{
		$last_6_month = $this->bills->get_last_months_earnings_stack_location($months);

		$r = array();
		$locations = array();
		$result = false;
		$result_line = false;
		
		/* generate 0 values for empty data */
		foreach ($last_6_month as $last6m) {
			$r[$last6m['y'] . '.' . sprintf('%02d', $last6m['m']) . '.' . $last6m['name']] = $last6m['p'];
			$locations[$last6m['name']] = 1;
		}
		
		// var_dump($r);
		for ($i = -$months; $i <= 0; $i++) {
			$d = date("Y.m", strtotime($i . " months"));
			
			$y_total = 0;
			foreach ($locations as $l => $dummy) {
				$y = isset($r[$d . "." . $l]) ? $r[$d . "." . $l] : 0;
				$result[$l][] = array("t" => $d, "y" => $y);
				
				$y_total += $y;
			}
			
			$result_line[] = array("t" => $d, "y" => round($y_total, 2));
		}
		// ksort($r);
		// var_dump($result_line);
		return array("line" => $result_line, "bar" => $result);
	}
	
	/*
		clean up the data so we don't need allot of processing
		inside the template files
	*/
	private function get_income_overview_chart($months)
	{
		$income_overview = $this->bills->get_income_overview($months);
		$values = array();
		$vets 	= array();
		
		foreach ($income_overview as $vet) {
			$values[] 	= $vet['p'];
			$vets[] 	= $vet['first_name'];
		}
		
		return array("vets" => $vets, "income" => $values);
	}
	
	private function get_avg_per_consult($months)
	{
		$avg_overview = $this->bills->get_avg_per_consult($months);

		// var_dump($avg_overview);
		
		/* generate 0 values for empty data */
		$r = array();
		$vets = array();
		$result = false;
		
		foreach ($avg_overview as $avg) {
			$r[$avg['y'] . '.' . sprintf('%02d', $avg['m']) . '.' . $avg['first_name']] = $avg['avg'];
			$vets[$avg['first_name']] = 1;
		}
		
		/* drop in nice array */
		for ($i = -$months; $i <= 0; $i++) {
			$d = date("Y.m", strtotime($i . " months"));
			
			$y_total = 0;
			foreach ($vets as $vet => $dummy) {
				$y = isset($r[$d . "." . $vet]) ? $r[$d . "." . $vet] : 0;
				$result[$vet][] = array("t" => $d, "y" => round($y, 2));
				$y_total += $y;
			}
		}
		return $result;
	}

	private function last_bill_by_year_month_init_vet()
	{
		$last_bill = $this->owners->last_bill_by_year_month_init_vet();
		
		// var_dump($last_bill);
		
		$years = array();
		$total_last_bill = array();
		$result = array();
		$vets_list = array();
		
		// total can be made
		// for init_vet we need to deal with 0 values
		foreach ($last_bill as $lb) {
			# clients with no last bill
			if (is_null($lb['y'])) {
				continue;
			}
			
			$total_last_bill[$lb['y']] = (isset($total_last_bill[$lb['y']])) ? $total_last_bill[$lb['y']] + $lb['total'] : $lb['total'];
			
			
			$years[$lb['y']][$lb['first_name']] = $lb['total'];
			$vets_list[$lb['first_name']] = 1;
		}
		
		
		// fix the 0 issue
		foreach ($years as $year => $data) {
			foreach ($vets_list as $vet => $dummy) {
				$years[$year][$vet] = (isset($years[$year][$vet])) ? $years[$year][$vet] : 0;
			}
		}
		
		// format
		foreach ($years as $year => $data) {
			foreach ($data as $vet => $value) {
				$result[$vet][] = array("t" => (string)$year, "y" => $value);
			}
		}
		
		// var_dump($result);

		$line = array();
		foreach ($total_last_bill as $year => $total) {
			$line[] = array("t" => (string)$year, "y" => $total);
		}
		
		return array("line" => $line, "bar" => $result, "ori_line" => $total_last_bill);
	}
}
