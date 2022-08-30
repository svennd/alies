<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data = array();
		$this->_render_page('logs/index', $data);
	}
	
	public function write_off()
	{
		$this->load->model('Stock_write_off_model', 'stock_write_off_log');
		
		$data = array(
						"logs" 		=> $this->stock_write_off_log
												->with_product('fields:name, unit_sell')
												->with_vet('fields:first_name')
												->with_locations('fields:name')
											->get_all(),
		);
		$this->_render_page('logs/stock_write_off', $data);
	}
	
	public function nlog()
	{
		$this->load->model('Logs_model', 'nlog');
		
		$data = array(
						"logs" 		=> $this->nlog->with_vet('fields:first_name')->order_by(array('id', 'desc'))->get_all(),
		);
		$this->_render_page('logs/global', $data);
	}
	
	/* usefull for debugin */
	public function software_version()
	{
		$changelog = (file_exists("CHANGELOG.md")) ? nl2br(file_get_contents("CHANGELOG.md")) : "No CHANGELOG.md file;";
		
		$data = array(
						"database_version"	=> $this->db->query("SELECT * FROM `migrations`")->result_array(),
						"changelog"			=> $changelog
		);
		$this->_render_page('logs/version', $data);
	}
}
