<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_type_model extends MY_Model
{
    public $table = 'products_type';
    public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_many['products'] = array(
					'foreign_model' => 'Products_model',
					'foreign_table' => 'products',
					'foreign_key' => 'type',
					'local_key' => 'id'
				);
		parent::__construct();
	}
}