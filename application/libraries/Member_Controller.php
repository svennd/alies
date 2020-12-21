<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Member_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->library(array('ion_auth'));
		
		# check if they are logged in
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}
		
		# check if they are part of the customer group
		if (!$this->ion_auth->in_group('members')) {
			redirect('/');
		}
	}
}
