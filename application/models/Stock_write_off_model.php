<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_write_off_model extends MY_Model
{
    public $table = 'stock_write_off';
    public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['product'] = array(
				'foreign_model' => 'Products_model',
				'foreign_table' => 'products',
				'foreign_key' => 'id',
				'local_key' => 'product_id'
			);	
		$this->has_one['vet'] = array(
				'foreign_model' => 'Users_model',
				'foreign_table' => 'users',
				'foreign_key' => 'id',
				'local_key' => 'vet'
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