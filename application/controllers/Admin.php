<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends Admin_Controller
{
	// initialize
	public $products, $users, $setting, $stock_location, $book, $prod_type;
	
	// ci specific
	public $input;

	# constructor
	public function __construct()
	{
		parent::__construct();
				
		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Booking_code_model', 'book');
		$this->load->model('Users_model', 'users');
		$this->load->model('Config_model', 'setting');
	}

	/*
	* function: enable_vsens
	* enable sensitive data to be visible
	*/
	public function enable_vsens()
	{
		$this->users->where(array('id' => $this->user->id))->update(array('vsens' => 1));
		redirect('accounting/dashboard');
	}

	/*
	* function: settings
	* managing of site wide settings
	*/
	public function settings()
	{
		if ($this->input->post('submit')) {
			foreach($this->input->post() as $k => $v)
			{
				# config field & not empty value
				if (substr($k, 0, 5) == "conf_" && $v != "")
				{
					// base64 is only for accidental shoulder surfers protection
					// we need these credentials in plain text to connect to services
					$this->setting->store(substr($k, 5), $v);
				}
			}

			# check_* don't send if not checked
			$keys = array('autodisable', 'invoice_prefix', 'pruning', 'autdeath');
			foreach ($keys as $key)
			{
				if ($this->input->post('check_' . $key) == "on") {
					$this->setting->store($key, 1);
				}
				else {
					$this->setting->store($key, 0);
				}
			}			
		}
		
		$temp_settings = $this->settings->get_all();
		$conf = array();
		foreach($temp_settings as $c)
		{
			$conf[$c['name']] = $c['value'];
		}

		$data = array(
						"config" => $conf,
					);
					
		$this->_render_page('admin/configuration', $data);
	}

	// Group: location functions
	// _____________________________________

	/*
	* function: locations
	* manage locations
	*/
	public function locations()
	{
		if ($this->input->post('submit') == "add_location") {
			$this->stock_location->insert(array("name" => $this->input->post('name')));
		}
		
		if ($this->input->post('submit') == "update_location_name") {
			$this->stock_location->update(
				array(
									"name" 	=> $this->input->post('name')
								),
				array(
									"id" => (int) $this->input->post('id')
								)
			);
		}
		
		# race condition
		$this->location = $this->stock_location->get_all();
		
		$data = array(
						"locations" => $this->location,
					);
					
		$this->_render_page('admin/locations', $data);
	}
	
	/*
	* function: locations_rm
	* remove location (soft delete)
	*/
	public function locations_rm(int $id)
	{
		$this->stock_location->delete($id);
		redirect('admin/locations', 'refresh');
	}

	// Group: booking code functions
	// _____________________________________
	/*
	* function: booking
	* managing of booking codes
	*/
	public function booking()
	{
		if ($this->input->post('submit') == "add_booking_code") {
			$this->book->insert(array(
										"category" 	=> $this->input->post('category'),
										"code" 		=> $this->input->post('code'),
										"btw" 		=> $this->input->post('btw')
									));
		}
				
		$data = array(
						"booking" => $this->book->get_all()
					);
					
		$this->_render_page('admin/booking_codes', $data);
	}

	/*
	* function: booking_rm
	* remove booking code
	*/
	public function booking_rm(int $id)
	{
		$this->book->delete($id);
		redirect('admin/booking', 'refresh');
	}
	


	// Group: product_types functions
	// _____________________________________

	/*
	* function: product_types
	* manage product types
	*/
	public function product_types()
	{
		if ($this->input->post('submit') == "add_product_type") {
			$this->prod_type->insert(array("name" => $this->input->post('name')));
		}
		
		if ($this->input->post('submit') == "update_product_type") {
			$this->prod_type->update(
				array(
									"name" 	=> $this->input->post('name')
								),
				array(
									"id" => (int) $this->input->post('id')
								)
			);
		}
		
		$data = array(
						"prod_type" => $this->prod_type->get_all(),
					);
	

		$this->_render_page('admin/product_types', $data);
	}
	
	/*
	* function: product_types_rm
	* delete products
	*/
	public function product_types_rm(int $id)
	{
		// remove type
		$this->prod_type->delete($id);
		
		// set products with from this type to 0
		$this->products->where(array('type' => $id))->update(array('type' => 0));
		
		redirect('admin/product_types', 'refresh');
	}
}
