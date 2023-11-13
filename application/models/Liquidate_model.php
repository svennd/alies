<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Liquidate_model extends MY_Model
{
	public $table = 'liquidate';
	public $primary_key = 'id';
	
	public function __construct()
	{

		$this->has_one['vet'] = array(
			'foreign_model' => 'Users_model',
			'foreign_table' => 'users',
			'foreign_key' => 'id',
			'local_key' => 'user'
		);
		$this->has_one['location'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'location'
		);

		$this->has_one['products'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'id',
			'local_key' => 'product_id'
		);

		parent::__construct();
	}
}
