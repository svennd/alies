<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Users_model', 'users');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Products_model', 'product');
		$this->load->model('Events_model', 'events');
		$this->load->model('Stock_limit_model', 'stock_limit');

		$this->load->library('migration');

		$this->load->helper('online');
	}
	
	public function index()
	{
		
		# upgrade if available
		# if success = version
		# if none = true
		# if error = false
		$version = $this->migration->latest();
		if ($version === false) {
			show_error($this->migration->error_string());
		}

		$r = $this->product->where('limit_stock >', 0)->fields('id, unit_sell, name, limit_stock')->get_all();
		$result = array();

		if ($r) {

			foreach ($r as $prod) {
				$stock = $this->stock->select('SUM(volume) as sum_vol', false)->fields()->where(array('product_id' => $prod['id']))->group_by('product_id')->get();

				# false if none found
				if ($stock && $stock['sum_vol'] < $prod['limit_stock']) {
					$result[] = array(
							"id" 					=> $prod['id'],
							"name" 				=> $prod['name'],
							"unit_sell" 	=> $prod['unit_sell'],
							"limit_stock" => $prod['limit_stock'],
							"in_stock" 		=> (($stock['sum_vol']) ? $stock['sum_vol'] : '0'),
						);
				}
			}
			
		}

		$data = array(
							"locations" 				=> $this->location,
							"update_to_version" 		=> $version,
							"vets" 						=> $this->users->get_active_vets()
							);

		$this->_render_page('welcome_message', $data);
	}

	public function change_location($id)
	{
		$this->users->update(array("current_location" => $id), $this->user->id);
		redirect('/', 'refresh');
	}
}
