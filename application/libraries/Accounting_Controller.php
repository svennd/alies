<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Accounting
class Accounting_Controller extends Vet_Controller
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
	}
}
