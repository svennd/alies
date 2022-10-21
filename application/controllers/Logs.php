<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Log_stock_model', 'log_stock');
	}

	public function index()
	{
		$data = array();
		$this->_render_page('logs/index', $data);
	}
	
	public function write_off()
	{
		
		$data = array(
						"logs" 		=> $this->log_stock
												->with_product('fields:name, unit_sell')
												->with_vet('fields:first_name')
												->with_locations('fields:name')
											->where(array('event' => 'writeoff'))
											->get_all(),
		);
		$this->_render_page('logs/stock_write_off', $data);
	}
	
	public function product(int $product_id)
	{
		$data = array(
			"logs" 		=> $this->log_stock
									->with_product('fields:name, unit_sell')
									->with_vet('fields:first_name')
									->with_locations('fields:name')
								->where(array('product' => $product_id))
								->get_all(),
		);
		$this->_render_page('logs/product', $data);
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
