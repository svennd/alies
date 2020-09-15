<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends Vet_Controller {
	
	public function __construct(){
		
		parent::__construct();
		
		# check if they are part of the vet group
		if (!$this->ion_auth->in_group('admin'))
		{
			redirect('/');
		}
	}	
}