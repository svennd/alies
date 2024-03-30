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

	public function order()
	{
		$data = array(
						"order"			=> 1,
						"locations" 	=> $this->locations,
						"in_backorder" 	=> $this->product->fields('id, name, unit_sell')->where(array("backorder" => 1))->with_wholesale()->get_all(),
					);
		$this->_render_page('limits/order', $data);
	}

	public function global()
	{
		$data = array(
						"locations" 	=> $this->locations,
						"global_stock" 	=> $this->stock_limit->global_shortage()
					);
		$this->_render_page('limits/global', $data);
	}

	public function local(int $location = 0)
	{
		$filter = ($location) ? $location : $this->_get_user_location();
		$local_limit = $this->stock_limit->local_shortage($filter);
		$data = array(
						"filter"		=> $filter,
						"locations" 	=> $this->locations,
						"local_stock" 	=> $local_limit,
					);
		$this->_render_page('limits/local', $data);
	}
	
}
