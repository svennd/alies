<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Limits extends Vet_Controller
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

	}

	public function global()
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
						"locations" 	=> $this->location
					);
		$this->_render_page('limits/global', $data);
	}

	public function local(int $location = 0)
	{
		$filter = ($location) ? $location : $this->user->current_location;
		$local_limit = $this->stock_limit->local_shortage($filter);
		$data = array(
						"filter"		=> $filter,
						"locations" 	=> $this->location,
						"local_stock" 	=> $local_limit,
					);
		$this->_render_page('limits/local', $data);
	}
	
}
