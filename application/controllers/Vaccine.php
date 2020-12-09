<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vaccine extends Vet_Controller {
	
	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Vaccine_model', 'vacs_pet');
	}
		
	public function fiche($pet_id)
	{	
		$pet_info = $this->pets->with_owners('fields:first_name,last_name')->fields('id, type, name')->get($pet_id);

		$data = array(
					"pet_info" 	=> $pet_info,
					"pet_id" 	=> $pet_id,
					"vaccines" 	=> $this->vacs_pet
											->with_vet('fields: first_name')
											->with_product('fields: name, vaccin_freq')
											->with_location('fields: name')
											->where(array('pet' => $pet_id))
											->get_all()
				);
		;
		$this->_render_page('vaccine_overview', $data);
	}
	
}
