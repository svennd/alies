<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_price_model extends MY_Model
{
    public $table = 'products_price';
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