<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Log_stock_model extends MY_Model
{
	public $table = 'log_stock';
	public $primary_key = 'id';

	public function __construct()
	{
		$this->has_one['product'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'id',
			'local_key' => 'product'
		);
		$this->has_one['vet'] = array(
				'foreign_model' => 'Users_model',
				'foreign_table' => 'users',
				'foreign_key' => 'id',
				'local_key' => 'user_id'
			);
		$this->has_one['locations'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'location'
		);
		parent::__construct();
	}
}
