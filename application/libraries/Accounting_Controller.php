<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounting_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		# check if they are logged in
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}

		# check if they are part of the vet group
		if (!$this->ion_auth->in_group('admin') && !$this->ion_auth->in_group('accounting')) {
			# this needs 404
			# if not we can get in a redirect loop
			show_error("Not part of the correct group;");
			// redirect('/');
		}


		$this->user = $this->ion_auth->user()->row();

		# required on every page
		$this->page_data = array(
								"user" 						=> $this->user
						);

		$this->lang->load('vet', 'dutch');
		$this->lang->load('admin', 'dutch');
	}

	public function _render_page($page, $data = array())
	{
		$data = array_merge($data, $this->page_data);
		$this->load->view('header', $data);
		$this->load->view($page, $data);
		$this->load->view('footer', $data);
	}
}
