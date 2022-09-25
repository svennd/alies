<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stock_limit_model extends MY_Model
{
	public $table = 'stock_limit';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['products'] = array(
					'foreign_model' => 'Products_model',
					'foreign_table' => 'products',
					'foreign_key' => 'id',
					'local_key' => 'product_id'
				);
		$this->has_one['stock_locations'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'stock'
		);
		parent::__construct();
	}
}
