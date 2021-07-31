<?php	
defined('BASEPATH') or exit('No direct script access allowed');

class Search extends Vet_Controller
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
		$query = $this->input->post('search_query');
		if ($query)
		{
			
			$last_name  = $this->owners->search_by_last_name($query);
			$first_name = $this->owners->search_by_first_name($query);
			$street		= $this->owners->search_by_street_ex($query);
			
			$possible_phone = false;
			for ($i = 0; $i < strlen($query); $i++) {
				if ( ctype_digit($query[$i]) ) {
					$possible_phone = true;
					break;
				}
			}
			$phone 		= ($possible_phone) ? $this->owners->search_by_phone_ex($query) : array();
			
			if (is_numeric($query))
			{
				# its a chip nr or a pet_id
				$pets	= (strlen($query) > 10) ? $this->pets->search_by_chip_ex($query) : $this->pets->search_by_id($query);				
			}
			else
			{
				$pets		= $this->pets->search_by_name($query);
			}
			
			$data = array(
							'query' 		=> htmlspecialchars($query),
							'name_street'	=> array_merge($last_name, $first_name, $street),
							'last_name'		=> $last_name,
							'first_name'	=> $first_name,
							'street'		=> $street,
							'phone'			=> $phone,
							'pets'			=> $pets,
					);
		}
		$this->_render_page('search', $data);
	}
	
	public function advanced()
	{
		$name = null;
		$street = null;
		$phone = null;
		$client = null;
		$result = null;
		
		if ($this->input->post('submit') == "name") {
			$name = $this->input->post('name');
			$result = (!empty($name)) ? $this->owners->search_by_name($name) : false;
		}
		
		if ($this->input->post('submit') == "street") {
			$street = $this->input->post('street');
			$result = (!empty($street)) ? $this->owners->search_by_street($street) : false;
		}
		
		if ($this->input->post('submit') == "phone") {
			$phone = $this->input->post('phone');
			$result = (!empty($phone)) ? $this->owners->search_by_phone($phone) : false;
		}
		
		if ($this->input->post('submit') == "client") {
			$client = $this->input->post('client');
			$result = (!empty($client)) ? $this->owners->where(array("id" => $client))->get_all() : false;
		}
		
		if ($this->input->post('submit') == "chip") {
			$chip = $this->input->post('chip');
			$result = (!empty($chip)) ? $this->pets->with_breeds('fields:name')->with_owners()->where(array("chip" => $chip))->get() : false;
		}
		
		if ($this->input->post('submit') == "pet_name") {
			$pet_name = $this->input->post('pet_name');
			$result = (!empty($pet_name)) ? $this->pets
												->with_breeds('fields:name')
												->with_owners('fields:last_name, street, nr, city, last_bill')
												->like("name", $pet_name, "both")
												->where(array("death" => 0, "lost" => 0))->get_all() : false;
		}
		
		$data = array(
					"name" 		=> $name,
					"street" 	=> $street,
					"phone"		=> $phone,
					"client" 	=> $client,
					"chip" 		=> $chip,
					"pet_name" 	=> $pet_name,
					"result" 	=> $result,
				);
		$this->_render_page('search_adv', $data);
		
		/*
		$this->_render_page('owners_search', $data);
		$this->_render_page('pets_search_list', $data);
		$this->_render_page('pets_search', $data);
		*/
	}
}
