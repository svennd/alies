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
		$this->load->model('Breeds_model', 'breeds');
		$this->load->model('Events_model', 'events');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Zipcodes_model', 'zipcode');
	}
	
	public function index()
	{
		$query = $this->input->get('search_query');

		# bad query
		if (!$query) { redirect('/'); }

		$first_name = $this->owners->search_by_first_name($query);
		$street		= $this->owners->search_by_street_ex($query);
		$breeds		= $this->breeds->search_by_name($query);
		$phone 		= $this->owners->search_by_phone_ex($query);
		
		# if its numeric it might be a chip, client_id or pet_id
		if (is_numeric($query))
		{
			$pets		= (strlen($query) >= 10) ? $this->pets->search_by_chip_ex($query) : $this->pets->search_by_id($query);				
			$last_name 	= $this->owners->get_all((int)$query);
		}
		else
		{
			$pets		= $this->pets->search_by_name($query);
			$last_name	= $this->owners->search_by_last_name($query);
		}
		
		$data = array(
						'query' 		=> htmlspecialchars($query),
						'last_name'		=> ($last_name) ? $last_name : array(),
						'first_name'	=> $first_name,
						'street'		=> $street,
						'phone'			=> $phone,
						'pets'			=> $pets,
						'breeds'		=> $breeds
				);
		$this->_render_page('owners/search', $data);
	}
}
