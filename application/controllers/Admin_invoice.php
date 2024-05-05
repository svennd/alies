<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Admin_invoice
class Admin_invoice extends Admin_Controller
{

	// initialize
	public $locations, $users, $bills;

	// ci specific
	public $input;
	
	# constructor
	public function __construct()
	{
		parent::__construct();
				
		# models
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Users_model', 'users');
	}


	# function: edit_bill
	# admin can edit the bill
    public function edit_bill(int $bill_id)
    {
        if ($this->input->post('submit')) {
            $this->logs->logger(WARN, "edit_bill", "modified bill" . implode(',', $this->input->post()));
            $this->bills->bill_update($bill_id, array(
										"vet" 		    => $this->input->post('vet'),
										"location"		=> $this->input->post('location'),
										"total_brut"		=> $this->input->post('amount'),
										"cash"		    => $this->input->post('cash'),
										"card"		    => $this->input->post('card'),
										"transfer"	    => $this->input->post('transfer'),
										"status"	    => $this->input->post('status'),
										"created"	    => $this->input->post('created'),
									));
		}

		$data = array(
                        "locations" => $this->locations,
                        "vets"  => $this->users->get_all(),
						"bill" => $this->bills->with_owner('fields:last_name')->with_vet('fields:first_name')->with_location('fields:name')->get($bill_id)
					);
					
		$this->_render_page('admin_invoice/edit_bill', $data);
    }
}
