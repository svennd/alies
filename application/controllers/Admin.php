<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
				
		# models
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Events_model', 'events');
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Procedures_model', 'proc');
		$this->load->model('Products_model', 'prod');
		$this->load->model('Vaccine_model', 'vac');
		$this->load->model('Booking_code_model', 'book');
	}
			
	# proc
	public function proc()
	{
		if ($this->input->post('submit') == "add_proc") {
			# log this
			$this->logs->logger(INFO, "new_procedure", "proc_name: " . $this->input->post('name'));
			$this->proc->insert(array(
							"name" 			=> $this->input->post('name'),
							"booking_code" 	=> $this->input->post('booking_code'),
							"price"			=> $this->input->post('price')
							));
		}
		
		if ($this->input->post('submit') == "edit_proc") {
			# log this
			$this->logs->logger(INFO, "update_procedure", "proc_name: " . $this->input->post('name') . " price :" . $this->input->post('price'));
			$this->proc->update(
				array(
									"name" 			=> $this->input->post('name'),
									"booking_code" 	=> $this->input->post('booking_code'),
									"price"			=> $this->input->post('price'),
								),
				array(
									"id" => (int) $this->input->post('id')
								)
			);
		}
		
		$data = array(
						"proc" 		=> $this->proc->with_booking_code('fields:code, category, btw')->get_all(),
						"booking" 	=> $this->book->get_all()
					);
	

		$this->_render_page('admin/procedures', $data);
	}

	# change producedures
	public function proc_edit($id)
	{
		$data = array(
				"proc" 		=> $this->proc->with_booking_code('fields:code, category, btw')->get($id),
				"booking" 	=> $this->book->get_all()
			);
			
		$this->_render_page('admin/procedures_edit', $data);
	}

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
	
	# managing of booking codes
	# for products and procedures
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
	
	# remove booking code
	public function booking_rm($id)
	{
		$this->load->model('Booking_code_model', 'book');
		$this->book->delete($id);
		redirect('admin/booking', 'refresh');
	}
	
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
	
	# remove type
	public function delete_prod($id)
	{
		// remove type
		$this->prod_type->delete($id);
		
		// set products with from this type to 0
		$this->prod->where(array('type' => $id))->update(array('type' => 0));
		
		redirect('admin/product_types', 'refresh');
	}
	
	# remove location (soft delete)
	public function delete_location($id)
	{
		$this->stock_location->delete($id);
		redirect('admin/locations', 'refresh');
	}
	# remove proc (soft delete)
	public function delete_proc($id)
	{
		$this->proc->delete($id);
		redirect('admin/proc', 'refresh');
	}
	
	# remove proc (soft delete)
	public function delete_vac($id)
	{
		$this->vac->delete($id);
		redirect('admin/vacs', 'refresh');
	}
}
