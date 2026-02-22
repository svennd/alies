<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Tools
class Tools extends Admin_Controller
{
	
	// ci specific
	public $input;

	public function __construct()
	{
		parent::__construct();
        $this->load->model('Owners_model', 'owners');
		$this->load->model('Pets_model', 'pets');
        $this->load->model('ApiKey_model', 'apikey');
	}

    public function index()
    {
        $data = array();
        $this->_render_page('admin/tools/index', $data);
    }

    /*
        OWNER TOOLS
    */

    public function duplicate_clients()
    {
        $this->_render_page('admin/tools/duplicate_clients', array(
            'duplicates' => $this->owners->duplicate_owner_street()
        ));
    }
    
    public function duplicate_phones()
    {
        $this->_render_page('admin/tools/duplicate_phone', array(
            'duplicates' => $this->owners->duplicate_phone()
        ));
    }  

    /*
        PET TOOLS
    */
    public function duplicate_chips()
    {
        $this->_render_page('admin/tools/duplicate_chips', array(
            'duplicates' => $this->pets->duplicate_chips()
        ));
    }   

    /*
    * function: merge_clients
    * merge two client records into one
    * client 1 will be disabled 
    * cleint 2 will be kept
    */
    public function merge_clients(int $client_id_1, int $client_id_2)
    {
        $this->owners->disable($client_id_1, $client_id_2);

        // transfer - pet(s)
        $pets = $this->pets->where('owner', $client_id_1)->get_all();

        foreach($pets as $pet) {
            $this->pets->transfer_pet($pet['id'], $client_id_2);
        }
        // redirect to client detail page
        redirect(base_url('tools/duplicate_clients'));
    }

    public function show_api_keys()
    {
        if ($this->input->post('create_key')) {
            $device = $this->input->post('device');
            $x = $this->createKey($device);
        }

        if ($this->input->post('revoke_key')) {
            $key_id = (int)$this->input->post('key_id');
            $this->apikey->where(array("id" => $key_id))->update(array('active' => 0));
        }

        $data = array(
            'api_keys' => $this->apikey->where('active', 1)->get_all()
        );
        $this->_render_page('admin/tools/api_keys', $data);
    }

    private function createKey(string $device): int
    {
        $plain = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $plain);

        $this->apikey->insert([
            'key_hash' => $hash,
            'device'   => $device,
            'active'   => 1,
        ]);
        return $this->db->insert_id();
    }
}
