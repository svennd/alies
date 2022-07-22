<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Debug extends Vet_Controller
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
		$this->load->model('Logs_model', 'logz'); # not sure why but autoload doesn't come here :/ 
	}

	// debug interfaces
	public function index(int $owner)
	{
		$data = array(
			"owner" 	=> $this->owners->get($owner)
		);
		
		$this->_render_page('debug/owner', $data);
	}

    /*
    debug : events, close all the events of this owner. (and linked payments)

        - close all events
        - close all bills
        - mark this debug step in logs
    */
    public function events(int $owner)
    {
        # all the events of the owners pets will be set to STATUS_CLOSED
        $all_pets = $this->pets->fields('id')->where(array('owner' => $owner))->get_all();
        foreach($all_pets as $pet)
        {
            // could be multiple events
            // note : updated_at will be updated aswell :(
            $this->events
                    ->where(array('pet' => $pet['id']))
                    ->update(array('status' => STATUS_CLOSED));
            
            // log this
            $this->logz->logger($this->user->id, INFO, "manual_close", "close events for pet_id: " . $pet['id']);
        }

        # all bills will be closed
        $all_bills = $this->bills
                        ->where(array('owner_id' => $owner))
                        ->update(array('status' => PAYMENT_PAID));

        $this->logz->logger($this->user->id, WARN, "reset_events_owner", "reset owner: " . $owner);

        redirect('debug/index/' . $owner);
    }
	
}
