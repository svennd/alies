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

		$this->load->library('migration');
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

		# joke'r'bad
		$this->lang->load('bad_jokes', 'english');

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

							"vets" 						=> $this->users->get_active_vets(),
							"local_stock"				=> ($this->user->current_location != 0) ? $this->stock->get_local_stock_shortages($this->user->current_location) : false,
							"global_stock"				=> $result,
							"bad_products"				=> ($this->user->current_location != 0) ?
												$this->stock
													->fields('eol, id, product_id, location')
													->where('eol < DATE_ADD(NOW(), INTERVAL +90 DAY)', null, null, false, false, true)
													->where('eol > DATE_ADD(NOW(), INTERVAL -360 DAY)', null, null, false, false, true) // remove 0000-00-00
													->where(array('location' => $this->user->current_location, 'state' => STOCK_IN_USE))
													->get_all()
												: false,
							);

		$this->_render_page('welcome_message', $data);
	}

	public function change_location($id)
	{
		$this->users->update(array("current_location" => $id), $this->user->id);
		redirect('/', 'refresh');
	}
}
