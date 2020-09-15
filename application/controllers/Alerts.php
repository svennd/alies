<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alerts extends Admin_Controller {

	# constructor
	public function __construct()
	{
		parent::__construct();		
	}
	
	public function index()
	{
		$data = array(
							"alerts" => $this->alerts->get_all()
						);
		
		$this->_render_page('alerts_list', $data);
	}
}
