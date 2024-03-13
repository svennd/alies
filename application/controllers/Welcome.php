<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Users_model', 'users');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Products_model', 'product');
		$this->load->model('Events_model', 'events');
		$this->load->model('Stock_limit_model', 'stock_limit');

		$this->load->library('migration');

		$this->load->helper('online');
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
		
		$data = array(
							"update_to_version" 		=> $version,
							"vets" 						=> $this->users->get_active_vets()
							);

		$this->_render_page('welcome_message', $data);
	}

	public function change_location(int $id): void
	{
		// sql & session
		$this->users->update(array("current_location" => $id), $this->user->id);
		$this->ion_auth->set_user_location($id);
		
		redirect('/', 'refresh');
	}
}
