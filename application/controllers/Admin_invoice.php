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

	# function: rm_bill
	# this is very tricky
	# A bill can only be deleted if there is no invoice_id assigned !!!
	public function rm_bill(int $bill_id)
	{
		$this->load->model('Vaccine_model', 'vaccine');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Events_procedures_model', 'eproc');
		$this->load->model('Events_products_model', 'eprod');

        $this->logs->logger(WARN, "rm_bill", "bill : " . $bill_id . " reason : " . $this->input->post('reason'));

		# this bill
		$bill = $this->bills->get($bill_id);

		# verify there is not a invoice id assigned
		if (!is_null($bill['invoice_id'])) 
		{
			$this->logs->logger(ERROR, "attempt_rm_invoice", "failed attempt to rm bill : " . $bill_id . " with invoice_id = " . $bill['invoice_id'] . " reason : " . $this->input->post('reason'));
			
			redirect('admin_invoice/edit_bill/' . $bill_id, 'refresh');

			return 0;
		}

		# get all events from this bill
		$events_from_bill = $this->events->where(array('payment' => $bill_id))->get_all();

		if ($events_from_bill)
		{
			foreach($events_from_bill as $ev)
			{
				# remove procedures
				$this->eproc->where(array('event_id' => $ev['id']))->delete();
				$this->logs->logger(DEBUG, "rm_bill_proc", "deleting all proc with event_id : ". $ev['id']);

				# put the products back in the stock
				$products = $this->eprod->where(array('event_id' => $ev['id']))->get_all();
				foreach ($products as $p)
				{
					$this->stock->increase_stock($p['product_id'], $p['volume'], $ev['location'], $p['barcode'], true);
					$this->logs->logger(DEBUG, "rm_bill_prod", "increasing product ". $p['product_id']);
				}
				# if finished remove from eprod
				$this->eprod->where(array('event_id' => $ev['id']))->delete();
				$this->logs->logger(DEBUG, "rm_bill_prod", "deleting all prod with event_id : ". $ev['id']);
				
				# in case there was a vaccine mapped on this bill
				$this->vaccine->where(array('event_id' => $ev['id']))->delete();
				$this->logs->logger(DEBUG, "rm_bill_vaccine", "deleting all vaccines with event_id : ". $ev['id']);

				# update this event
				$this->events->update(array('status' => STATUS_OPEN, 'payment' => NO_BILL), $ev['id']);
				$this->logs->logger(DEBUG, "rm_event", "set event : ". $ev['id'] . " to a open cleaned state");
			}
			$this->bills->update(array("msg" => $bill['msg'] . "; remove_reason : " . $this->input->post('reason')) ,$bill_id);
		}
		else
		{
			# funky
        	$this->logs->logger(ERROR, "rm_bill", "tried to remove bill ". $bill_id ." but it had no events connected.");
		}
		$this->bills->delete($bill_id);

		$this->_render_page('admin_invoice/rm_bill', array("events_from_bill" => $events_from_bill));
	}
}
