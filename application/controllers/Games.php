<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Games extends Vet_Controller {

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Stock_model', 'stock');
	}
	

	public function index()
	{	
		$data = array();
		$this->_render_page('game_index', $data);
	}
	
	
	# a stock game
	public function stock()
	{
		$result = $this->stock
			->fields('id, product_id, verify, lotnr, eol, volume, barcode, volume')
			->where(array(
							"location" 		=> $this->user->current_location
							))
			->with_products('fields:name,unit_sell, short_name')
			->limit(3)
			->order_by('verify', 'ASC')
			->get_all();
		
		$data = array(
						"result" => $result[rand(0,2)]
					);
		$this->_render_page('game_stock', $data);
	}

}
