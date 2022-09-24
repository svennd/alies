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
			# catch direct ID search here
			$last_name  = (is_numeric($query)) ? $this->owners->get_all((int)$query) : $this->owners->search_by_last_name($query);
			$first_name = $this->owners->search_by_first_name($query);
			$street		= $this->owners->search_by_street_ex($query);
			
			$possible_phone = false;
			for ($i = 0; $i < strlen($query); $i++) {
				if ( ctype_digit($query[$i]) ) {
					$possible_phone = true;
					break;
				}
			}
			$phone = ($possible_phone) ? $this->owners->search_by_phone_ex($query) : array();
			
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
							// 'name_street'	=> array_merge($last_name, $first_name, $street),
							'last_name'		=> $last_name,
							'first_name'	=> $first_name,
							'street'		=> $street,
							'phone'			=> $phone,
							'pets'			=> $pets,
					);
		}
		$this->_render_page('owners/search', $data);
	}
}
