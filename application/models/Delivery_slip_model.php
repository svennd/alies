<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Delivery_slip_model extends MY_Model
{
	public $table = 'delivery_slip';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['vet'] = array(
			'foreign_model' => 'Users_model',
			'foreign_table' => 'users',
			'foreign_key' => 'id',
			'local_key' => 'vet'
		);
		$this->has_one['location'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'location'
		);
		/*
			pivot
		*/
		$this->has_many_pivot['products'] = array(
			'foreign_model'		=> 'Products_model',
			'pivot_table'		=> 'register_in',
			'local_key'			=> 'id',
			'pivot_local_key' 	=> 'delivery_slip',
			'pivot_foreign_key' => 'product',
			'foreign_key' 		=> 'id',
			'get_relate'		=> false
		);
					
		parent::__construct();
	}
}
