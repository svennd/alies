<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Help extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
	}
	
	/* function: admin
	* show admin help page
	*/
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
	
	/*
	* function: vet
	* show vet help page
	*/
	public function vet()
	{
		# load the page
		$this->_render_page('help/vet');
	}
}
