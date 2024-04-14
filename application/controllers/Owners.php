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
			
	public function add()
	{
		if ($this->input->post('submit')) {
			if ( (!empty($this->input->post('last_name'))) ) {
				$new_id = $this->owners->insert(array(
										"first_name" 		=> $this->input->post('first_name'),
										"last_name" 		=> $this->input->post('last_name'),
										"street" 			=> $this->input->post('street'),
										"nr" 				=> $this->input->post('nr'),
										"zip"	 			=> $this->input->post('zip'),
										"city" 				=> $this->input->post('city'),
										"telephone" 		=> $this->input->post('phone'),
										"phone2" 			=> $this->input->post('phone2'),
										"phone3" 			=> $this->input->post('phone3'),
										"mobile" 			=> $this->input->post('mobile'),
										"main_city" 		=> $this->input->post('main_city'),
										"province" 			=> $this->input->post('province'),
										"mail" 				=> $this->input->post('mail'),
										"btw_nr" 			=> $this->input->post('btw_nr'),
										"invoice_addr" 		=> $this->input->post('invoice_addr'),
										"invoice_contact"	=> $this->input->post('invoice_contact'),
										"invoice_tel" 		=> $this->input->post('invoice_tel'),
										"last_bill" 		=> date("Y-m-d"),
										"low_budget" 		=> (is_null($this->input->post('low_budget'))) ? 0 : 1,
										"debts" 			=> (is_null($this->input->post('debts'))) ? 0 : 1,
										"contact" 			=> (is_null($this->input->post('contact'))) ? 0 : 1,
										"msg" 				=> $this->input->post('msg'),
										"initial_vet"		=> $this->user->id,
										"initial_loc"		=> $this->_get_user_location(),
									));
									
				$this->logs->logger(INFO, "add_client", "Added client " . $this->input->post('last_name') . " (". $new_id . ")");
				
				redirect('/owners/detail/' . (int) $new_id, 'refresh');
			}
		}
		
		$this->_render_page('owners/add', array());
	}
	
	public function edit(int $owner_id)
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
									"phone2" 			=> $this->input->post('phone2'),
									"phone3" 			=> $this->input->post('phone3'),
									"mobile" 			=> $this->input->post('mobile'),
									"mail" 				=> $this->input->post('mail'),
									"btw_nr" 			=> $this->input->post('btw_nr'),
									"invoice_addr" 		=> $this->input->post('invoice_addr'),
									"invoice_contact"	=> $this->input->post('invoice_contact'),
									"invoice_tel" 		=> $this->input->post('invoice_tel'),
									"low_budget" 		=> (is_null($this->input->post('low_budget'))) ? 0 : 1,
									"disabled" 			=> (is_null($this->input->post('disabled'))) ? 0 : 1,
									"contact" 			=> (is_null($this->input->post('contact'))) ? 0 : 1,
									"debts" 			=> (is_null($this->input->post('debts'))) ? 0 : 1,
									"msg" 				=> $this->input->post('msg')
							),
				$owner_id
			);
			$this->logs->logger(INFO, "update_client", "client " . $this->input->post('last_name') . " (". $owner_id . ")");
			redirect('/owners/detail/' . $owner_id . '/true', 'refresh');
		}
		
		$data = array(
					"owner" => $this->owners->get($owner_id),
				);
		
		$this->_render_page('owners/edit', $data);
	}

	public function detail(int $owner, $update = false)
	{		
		$data = array(			
						"owner" 	=> $this->owners->get($owner),
						"open_bill"	=> $this->bills->get_open_bills($owner),
						"pets" 		=> $this->pets->get_all_pets($owner),
						"update" 	=> $update
					);
					
		$this->_render_page('owners/detail', $data);
	}
	
	// set email at bill page
	public function set_email(int $owner_id)
	{
		// ajax push
		$this->owners->update(array("mail" => $this->input->post('email')), $owner_id);

		// gdpr sensitive
		$this->logs->logger(INFO, "add_mail", "owner_id:" . $owner_id);

		return true;
	}

	/*
	*  show all invoices for a certain owner
	*/
	public function invoices(int $id)
	{
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-d", strtotime("-12 months")) : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-d") : $this->input->post('search_to');

		$bills = $this->bills
						->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->with_vet('fields:first_name')->with_location('fields:name')->where(array("owner_id" => $id))->order_by("id", "desc")->get_all();

		$data = array(
						"search_from" 	=> $search_from,
						"search_to" 	=> $search_to,
						"owner" 		=> $this->owners->get($id),
						"bills"			=> $bills,
			);
		$this->_render_page('owners/invoices', $data);
	}
	
	public function products(int $id)
	{		
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-d", strtotime("-12 months")) : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-d") : $this->input->post('search_to');

		$products = $this->events->get_products_owner($id, $search_from, $search_to);	

		$data = array(
			"search_from" 	=> $search_from,
			"search_to" 	=> $search_to,
			"owner" 		=> $this->owners->get($id),
			"products"		=> $products,
		);

		$this->_render_page('owners/products', $data);
	}

	public function get_zip($zip)
	{
		$result = $this->zipcode->where(array('zip' => $zip))->get();
		echo ($result) ? json_encode($result) : json_encode(array());
	}

	/*
		check for duplicate owners
	*/
	public function phone()
	{
		$phone = format_phone($this->input->post('phone'));
		$return_info = array("exists" => false);
		if($phone)
		{
			$owners = $this->owners->search_by_phone_ex($phone, 1);
			if ($owners) {
				$return_info = (array("exists" => true, "owner" => $owners[0]));
			} 
		}
		
		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($return_info));
	}
}
