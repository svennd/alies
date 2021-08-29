<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Owners extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Events_model', 'events');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Zipcodes_model', 'zipcode');
	}
	
	public function index()
	{
		$data = array();
		$this->_render_page('search', $data);
	}
	
	# legacy - please remove links to here
	public function search()
	{
		redirect('owners', 'refresh');
	}
		
	public function add()
	{
		if ($this->input->post('submit')) {
			$new_id = $this->owners->insert(array(
									"first_name" 		=> $this->input->post('first_name'),
									"last_name" 		=> $this->input->post('last_name'),
									"street" 			=> $this->input->post('street'),
									"nr" 				=> $this->input->post('nr'),
									"zip"	 			=> $this->input->post('zip'),
									"city" 				=> $this->input->post('city'),
									"telephone" 		=> $this->input->post('phone'),
									"mobile" 			=> $this->input->post('mobile'),
									"main_city" 		=> $this->input->post('main_city'),
									"province" 			=> $this->input->post('province'),
									"mail" 				=> $this->input->post('mail'),
									"btw_nr" 			=> $this->input->post('btw_nr'),
									"invoice_addr" 		=> $this->input->post('invoice_addr'),
									"invoice_contact"	=> $this->input->post('invoice_contact'),
									"invoice_tel" 		=> $this->input->post('invoice_tel'),
									"low_budget" 		=> (is_null($this->input->post('low_budget'))) ? 0 : 1,
									"debts" 			=> (is_null($this->input->post('debts'))) ? 0 : 1,
									"contact" 			=> (is_null($this->input->post('contact'))) ? 0 : 1,
									"msg" 				=> $this->input->post('msg'),
									"initial_vet"		=> $this->user->id,
									"initial_loc"		=> $this->user->current_location,
								));
								
			$this->logs->logger($this->user->id, INFO, "add_client", "Added client " . $this->input->post('last_name') . " (". $new_id . ")");
			
			redirect('/owners/detail/' . (int) $new_id, 'refresh');
		}

		$this->_render_page('owners_add');
	}
	
	public function edit($owner_id)
	{
		if ($this->input->post('submit')) {
			$this->owners->update(
				array(
									"first_name" 		=> $this->input->post('first_name'),
									"last_name" 		=> $this->input->post('last_name'),
									"street" 			=> $this->input->post('street'),
									"nr" 				=> $this->input->post('nr'),
									"zip"	 			=> $this->input->post('zip'),
									"city" 				=> $this->input->post('city'),
									"main_city" 		=> $this->input->post('main_city'),
									"province" 			=> $this->input->post('province'),
									"telephone" 		=> $this->input->post('phone'),
									"mobile" 			=> $this->input->post('mobile'),
									"mail" 				=> $this->input->post('mail'),
									"btw_nr" 			=> $this->input->post('btw_nr'),
									"invoice_addr" 		=> $this->input->post('invoice_addr'),
									"invoice_contact"	=> $this->input->post('invoice_contact'),
									"invoice_tel" 		=> $this->input->post('invoice_tel'),
									"low_budget" 		=> (is_null($this->input->post('low_budget'))) ? 0 : 1,
									"contact" 			=> (is_null($this->input->post('contact'))) ? 0 : 1,
									"debts" 			=> (is_null($this->input->post('debts'))) ? 0 : 1,
									"msg" 				=> $this->input->post('msg')
							),
				$owner_id
			);
			redirect('/owners/detail/' . (int) $owner_id, 'refresh');
		}
		
		$data = array(
					"owner" => $this->owners->get($owner_id),
				);
		
		$this->_render_page('owners_edit', $data);
	}

	public function detail($id = false, $update = false)
	{
		# todo : improve error
		if (!$id) {
			exit;
		}
		
		$open_bill = $this->bills
					->where("owner_id", "=", $id)
					->group_start()
						->where("status", "=", PAYMENT_UNPAID)
						->where("status", "=", PAYMENT_PARTIALLY, true)
						->where("status", "=", PAYMENT_OPEN, true)
					->group_end()
					->get_all();
		$data = array(
						"owner" 	=> $this->owners->get($id),
						"open_bill"	=> $open_bill,
						"pets" 		=> $this->pets->with_breeds()->with_pets_weight()->where(array("owner" =>$id))->order_by(array("birth, death"), "desc")->get_all(),
						"update" 	=> $update
					);
					
		$this->_render_page('owner_detail', $data);
	}
	
	public function invoices($id)
	{
		$data = array(
						"owner" 	=> $this->owners->get($id),
						"bills"		=> $this->bills->with_vet('fields:first_name')->with_location('fields:name')->where(array("owner_id" => $id))->order_by("id", "desc")->get_all(),
			);
		$this->_render_page('owner_invoices', $data);
	}
	
	public function get_zip($zip)
	{
		$result = $this->zipcode->where(array('zip' => $zip))->get();
		echo ($result) ? json_encode($result) : json_encode(array());
	}
}
