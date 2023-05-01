<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sticky extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Sticky_model', 'sticky');
	}

	public function index()
    {
    	$this->_render_page('sticky/index', array(
				"data" => $this->sticky->with_vet('fields:first_name')->with_location('fields:name')->get_all()));
	}

	public function add()
	{
		if ($this->input->post('content'))
		{
			$this->sticky->insert(array(
				"note"		=> htmlspecialchars($this->input->post('content')),
				"location"	=> $this->user->current_location,
				"user_id"	=> $this->user->id, 
				"private"	=> (int) $this->input->post('private'),
			));
		}
	}

	public function delete(int $id)
	{
		$this->sticky->where(array("user_id" => $this->user->id))->delete($id);
		redirect('sticky');
	}
}