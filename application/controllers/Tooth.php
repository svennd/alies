<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tooth extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Tooth_model', 'toot');
		$this->load->model('Tooth_msg_model', 'toot_msg');
		$this->load->model('Pets_model', 'pets');
	}
	
	public function fiche($pet_id, $update_text = false)
	{
		// this could go in 1 query
		$pet_info = $this->pets->with_tooths()->with_owners()->fields('id, type, name')->get($pet_id);
		$messages = $this->toot_msg->where(array("pet" => $pet_id))->fields('msg')->order_by('id', 'DESC')->limit(1)->get();

		$data = array(
						"pet_info" 	=> $pet_info,
						"pet_id" 	=> $pet_id,
						"update_text" => $update_text,
						"tooth_msg" => $messages,
						"extra_header" => inject_trumbowyg('header'),
						"extra_footer" => inject_trumbowyg()
					);
		
		$this->_render_page('pets/tooth', $data);
	}
	
	public function history($pet_id)
	{
		
		$pet_info = $this->pets->with_tooths()->with_owners()->fields('id, type, name')->get($pet_id);
		$history = $this->toot_msg->with_vet('fields:first_name')->with_location('fields:name')->where(array("pet" => $pet_id))->order_by('id', 'DESC')->get_all();
		$data = array(
							"pet_info" 	=> $pet_info,
							"history" => $history
						);
		$this->_render_page('tooth_history', $data);
	}
	
	public function store($pet_id)
	{
		$this->toot_msg->insert(array(
									"pet" 		=> $pet_id,
									"vet" 		=> $this->user->id,
									"location" 	=> $this->_get_user_location(),
									"msg" 		=> $this->input->post('message')
								));
		redirect('/tooth/fiche/' . $pet_id . '/update_text', 'refresh');
	}
	
	public function update($pet_id)
	{
		$this->toot->update_tooth(
			$pet_id,
			$this->user->id,
			$this->input->post('tooth'),
			$this->input->post('color')
		);
		echo json_encode(array("tooth" => $this->input->post('tooth'), "color" => $this->input->post('color')));
	}
}
