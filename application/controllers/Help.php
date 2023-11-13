<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Help extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
	}
	
	public function admin()
	{
		# only accessible by admin users
		if (!$this->ion_auth->in_group("admin"))
		{
			redirect('vet/dashboard');
		}

		# load the page
		$this->_render_page('help/admin');
	}
	
	public function vet()
	{
		# load the page
		$this->_render_page('help/vet');
	}
}
