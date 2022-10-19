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
        $this->bills
                    ->where(array('owner_id' => $owner))
                    ->update(array('status' => PAYMENT_PAID));

        $this->logz->logger($this->user->id, WARN, "reset_events_owner", "reset owner: " . $owner);

        redirect('debug/index/' . $owner);
    }
	
    /*
        will delete owner (soft delete)
        - first delete all pets (soft)
        - then delete owner
    */
    public function delete_owner(int $owner)
    {
        # delete all pets
        $this->pets->where(array('owner' => $owner))->delete();
        $this->logz->logger($this->user->id, WARN, "delete_owner_pets", "removed pets for : " . $owner);

        # delete client
        $this->owners->delete($owner);
        $this->logz->logger($this->user->id, WARN, "delete_owner", "removed owner : " . $owner);

        # we just removed the owner so it should be gone
        redirect('/');
    }

    public function downgrade()
	{
        if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->load->library('migration');

		$version = $this->db->query("SELECT * FROM `migrations`")->result_array()[0]['version'];
        echo "current version : $version<br/>";
        echo "downgrading to : " . $version - 1 . "<br/>";
		$this->migration->version($version-1);
        
        $this->db->query('UPDATE migrations SET version = ' . $version-1 . ';');
        echo "downgraded.<br/>";
	}

    public function upgrade()
	{
        if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->load->library('migration');

		$version = $this->migration->latest();
		if ($version === false) {
			show_error($this->migration->error_string());
		}
        
        echo "upgraded : " . implode($version);
	}
}
