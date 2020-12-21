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
	
	public function index()
	{
		$data = array();
		
		// $this->_render_page('tooth_test', $data);
		// $this->_render_page('tooth_canine_upper', $data);
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
						"extra_header" =>
							'<link href="'. base_url() .'assets/css/trumbowyg.min.css" rel="stylesheet">'
							,
						"extra_footer" =>
							'<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>' .
							'<script src="'. base_url() .'assets/js/trumbowyg.min.js"></script>' .
							'<script src="'. base_url() .'assets/js/plugins/cleanpaste/trumbowyg.cleanpaste.min.js"></script>' .
							'<script src="'. base_url() .'assets/js/plugins/fontsize/trumbowyg.fontsize.min.js"></script>' .
							'<script src="'. base_url() .'assets/js/plugins/template/trumbowyg.template.min.js"></script>'
					);
		
		$this->_render_page('tooth', $data);
	}
	
	public function history($pet_id)
	{
		$history = $this->toot_msg->with_vet('fields:first_name')->with_location('fields:name')->where(array("pet" => $pet_id))->order_by('id', 'DESC')->get_all();
		$data = array(
							"history" => $history
						);
		$this->_render_page('tooth_history', $data);
	}
	
	public function store($pet_id)
	{
		$this->toot_msg->insert(array(
									"pet" 		=> $pet_id,
									"vet" 		=> $this->user->id,
									"location" 	=> $this->user->current_location,
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
