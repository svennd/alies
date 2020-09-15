<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Products_model extends MY_Model
{
    public $table = 'products';
    public $primary_key = 'id';
	
	public function __construct()
	{
		$this->soft_deletes = true;
		$this->has_many['prices'] = array(
					'foreign_model' => 'Product_price_model',
					'foreign_table' => 'products_price',
					'foreign_key' => 'product_id',
					'local_key' => 'id'
				);
						
		$this->has_many['stock'] = array(
					'foreign_model' => 'Stock_model',
					'foreign_table' => 'stock',
					'foreign_key' => 'product_id',
					'local_key' => 'id'
				);
						
		$this->has_one['type'] = array(
							'foreign_model' => 'Product_type_model',
							'foreign_table' => 'products_type',
							'foreign_key' => 'id',
							'local_key' => 'type'
						);
						
		$this->has_one['booking_code'] = array(
							'foreign_model' => 'Booking_code_model',
							'foreign_table' => 'booking_codes',
							'foreign_key' => 'id',
							'local_key' => 'booking_code'
						);
		parent::__construct();
	}
}