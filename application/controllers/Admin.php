<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Admin
class Admin extends Admin_Controller
{
	// initialize
	public $products, $users, $setting, $stock_location, $book, $prod_type, $prod_label;
	
	// ci specific
	public $input;

	public function __construct()
	{
		parent::__construct();
				
		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Product_label_model', 'prod_label');
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
			$keys = array('autodisable', 'invoice_prefix', 'pruning', 'autdeath', 'vamreg_push');
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

	// Group: location
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

	// Group: booking code
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
						"booking" => $this->book
											->with_products('fields:*count*')
											->with_procedures('fields:*count*')
											->get_all()
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
	


	// Group: product_types
	// _____________________________________

	/*
	* function: product_types
	* manage product types
	*/
	public function product_types()
	{
		if ($this->input->post('submit') == "add_product_type") {
			$name = trim((string) $this->input->post('name'));
			if ($name !== '') {
				$this->prod_type->insert(array(
					"name" => $name,
					"root" => $this->prod_type->normalize_root((int) $this->input->post('root')),
					"icon" => $this->sanitize_type_icon($this->input->post('icon')),
					"icon_color" => $this->sanitize_type_icon_color($this->input->post('icon_color'))
				));
			}
		}
		
		if ($this->input->post('submit') == "update_product_type") {
			$id = (int) $this->input->post('id');
			$name = trim((string) $this->input->post('name'));
			if ($name !== '') {
			$this->prod_type->update(
				array(
									"name" 	=> $name,
									"root"	=> $this->prod_type->normalize_root((int) $this->input->post('root'), $id),
									"icon"	=> $this->sanitize_type_icon($this->input->post('icon')),
									"icon_color" => $this->sanitize_type_icon_color($this->input->post('icon_color'))
								),
				array(
									"id" => $id
				)
			);
			}
		}

		if ($this->input->post('submit') == "add_product_label") {
			$name = strtolower(trim($this->input->post('name')));
			$exists = $this->db->where('name', $name)->get('products_label')->row_array();

			if ($name !== '' && !$exists) {
				$this->prod_label->insert(array("name" => $name));
			}
		}

		if ($this->input->post('submit') == "update_product_label") {
			$id = (int) $this->input->post('id');
			$name = strtolower(trim($this->input->post('name')));
			$exists = $this->db
				->where('name', $name)
				->where('id !=', $id)
				->get('products_label')
				->row_array();

			if ($name !== '' && !$exists) {
				$this->prod_label->update(
					array(
						"name" => $name
					),
					array(
						"id" => $id
					)
				);
			}
		}
		
		$data = array(
						"prod_type" => $this->prod_type->get_admin_listing(),
						"prod_type_roots" => $this->prod_type->get_root_options(),
						"prod_label" => $this->prod_label->order_by('name', 'ASC')->get_all(),
					);
	

		$this->_render_page('admin/product_types', $data);
	}
	
	/*
	* function: product_types_rm
	* delete products
	*/
	public function product_types_rm(int $id)
	{
		$this->prod_type->detach_children($id);

		// remove type
		$this->prod_type->delete($id);
		
		// set products with from this type to 0
		$this->products->where(array('type' => $id))->update(array('type' => 0));
		
		redirect('admin/product_types', 'refresh');
	}

	// Group: product_labels
	// _____________________________________

	/*
	* function: product_labels
	* manage product labels
	*/
	public function product_labels()
	{
		redirect('admin/product_types', 'refresh');
	}

	/*
	* function: product_labels_rm
	* delete product label and unlink products
	*/
	public function product_labels_rm(int $id)
	{
		$this->prod_label->delete_with_links($id);
		redirect('admin/product_types', 'refresh');
	}

	private function sanitize_type_icon($icon)
	{
		$icon = trim((string) $icon);
		if ($icon === '') {
			return null;
		}

		$icon = preg_replace('/[^a-z0-9\\-\\s]/i', '', $icon);
		$icon = preg_replace('/\\s+/', ' ', $icon);
		return ($icon === '') ? null : $icon;
	}

	private function sanitize_type_icon_color($color)
	{
		$color = trim((string) $color);
		if ($color === '') {
			return null;
		}

		if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
			return null;
		}

		return strtolower($color);
	}
}
