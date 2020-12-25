<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Install extends Frontend_Controller {

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# load librarys
		$this->load->helper('url');	
		$this->load->helper('file');
	}
	
	public function start()
	{
		if (is_file)
		{
			if(is_writable('application/config/config.php') && is_writable('application/config/database.php'))
			{
				
			}
		}
		$data = array(
						"writable" => (bool) (),
					);
		$this->_render_page("first", $data);
	}
	public function second()
	{
		$this->_render_page("second", array());
	}
	public function third()
	{
		// var_dump($this->input->post());
		
		$this->_render_page("third", array());
	}
	
	private function _render_page($page, $data)
	{
		$this->load->view('install/header', $data);
		$this->load->view('install/'. $page, $data);
		$this->load->view('install/footer', $data);
	}
}
