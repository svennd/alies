<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Price_log_model extends MY_Model
{
	public $table = 'price_log';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['products'] = array(
							'foreign_model' => 'Products_model',
							'foreign_table' => 'product',
							'foreign_key' => 'id',
							'local_key' => 'product_id'
						);
		parent::__construct();
	}
}
