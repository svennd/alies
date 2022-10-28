<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Register_in_model extends MY_Model
{
	public $table = 'register_in';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['delivery_slip'] = array(
						'foreign_model' => 'Delivery_slip_model',
						'foreign_table' => 'delivery_slip',
						'foreign_key' => 'id',
						'local_key' => 'delivery_slip'
					);
					
		$this->has_one['product'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'id',
			'local_key' => 'product'
		);
					
		parent::__construct();
	}
}
