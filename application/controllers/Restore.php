<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
	Restore accidental deletes;
*/

class Restore extends Admin_Controller
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
		$this->load->model('Breeds_model', 'breeds');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Procedures_model', 'proc');
		$this->load->model('Products_model', 'prod');
		$this->load->model('Vaccine_model', 'vac');
		$this->load->model('Booking_code_model', 'book');
	}
	
	# booking codes can't be removed
	# as they are linked on sales (perhaps not active anymore)
	public function booking($restore_id = false)
	{
		if ($restore_id) {
			$this->book->restore($restore_id);
		}
		
		$data = array(
						"booking" => $this->book->only_trashed()->get_all()
					);
					
		$this->_render_page('restore/booking_codes', $data);
	}
	
	# due to how locations are linked we can't and don't want to
	# remove it; so here is a restore option viable;
	public function locations($restore_id = false)
	{
		if ($restore_id) {
			$this->stock_location->restore($restore_id);
		}
		$data = array(
						"locations" => $this->stock_location->only_trashed()->get_all(),
					);
					
		$this->_render_page('restore/locations', $data);
	}
	
	# procedures
	public function procedures($restore_id = false)
	{
		if ($restore_id) {
			$this->proc->restore($restore_id);
		}
		
		$data = array(
						"proc" => $this->proc->only_trashed()->get_all(),
					);
					
		$this->_render_page('restore/procedures', $data);
	}
}
