<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Log_stock_model', 'log_stock');
		$this->load->model('Liquidate_model', 'liquidate');
		$this->load->model('Delivery_slip_model', 'delivery');
		$this->load->model('Register_in_model', 'regin');
	}

	public function index()
	{
		$data = array();
		$this->_render_page('logs/index', $data);
	}
	
	public function write_off()
	{
		
		$data = array(
						"logs" 		=> $this->liquidate
												->with_products('fields:name, unit_sell')
												->with_vet('fields:first_name')
												->with_location('fields:name')
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
		$dt = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $dt->format('Y-m-d');
		$dt->modify('-3 day');
		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $dt->format('Y-m-d');

		$data = array(
						"search_to" 	=> $search_to,
						"search_from" 	=> $search_from,
						"logs" 			=> $this->nlog
											->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
											->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
											->with_vet('fields:first_name')->order_by(array('id', 'desc'))->get_all(),
		);
		$this->_render_page('logs/global', $data);
	}
	
	public function delivery(int $delivery = 0)
	{
		if($delivery)
		{
			$data = array(
				"delivery" => $this->delivery->with_location('fields: name')->with_vet('fields:first_name')->get($delivery),
				"products" => $this->regin->with_product('fields: name, sell_volume')->where(array('delivery_slip' => $delivery))->get_all(),
			);
			$this->_render_page('logs/delivery_detail', $data);
		}
		else
		{
			$data = array(
				"logs" 		=> $this->delivery->with_products('fields:name')->with_vet('fields:first_name')->get_all(),
			);
			$this->_render_page('logs/delivery', $data);
		}
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
