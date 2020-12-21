<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Users_model', 'users');
		
		$this->load->library('migration');
	}
	
	public function index()
	{
		# upgrade if available
		# if success = version
		# if none = true
		# if error = false
		$version = $this->migration->latest();
		if ($version === false) {
			show_error($this->migration->error_string());
		}
		$data = array("locations" => $this->location, "update_to_version" => $version);
		
		$this->_render_page('welcome_message', $data);
	}

	public function change_location($id)
	{
		$this->users->update(array("current_location" => $id), $this->user->id);
		redirect('/', 'refresh');
	}
}
