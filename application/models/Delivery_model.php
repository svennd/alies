<?php
if (! defined('BASEPATH')) { exit('No direct script access allowed'); }
/*
* used in cli to store incoming deliveries
*/
class Delivery_model extends MY_Model
{
	public $table = 'delivery';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Products_model', 'product');


		$this->has_one['wholesale'] = array(
			'foreign_model' => 'Wholesale_model',
			'foreign_table' => 'wholesale',
			'foreign_key' => 'id',
			'local_key' => 'wholesale_id'
		);
		
	}
}
