<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Sticky
class Sticky extends Vet_Controller
{

	// initialize
	public $input, $ion_auth, $sticky;

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
				"location"	=> $this->_get_user_location(),
				"user_id"	=> $this->user->id, 
				"private"	=> (int) $this->input->post('private'),
			));
		}
	}

	public function delete(int $id)
	{
		if ($this->ion_auth->in_group('admin')) 
		{
			$this->sticky->delete($id);
		}
		else
		{
			$this->sticky->where(array("user_id" => $this->user->id))->delete($id);
		}
		redirect('sticky');
	}
}