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
	
	public function date_lookup($search_from, $search_to)
	{
		$sql = "
				select 
					ri.volume, ri.eol, ri.in_price, ri.lotnr,
					products.name, products.buy_price, products.btw_buy, products.btw_sell,
					wholesale.bruto, wholesale.sell_price
				from 
					register_in as ri
				JOIN
					products 
				ON
					products.id = ri.product
				LEFT JOIN
					wholesale
				ON
					wholesale.id = products.wholesale
				WHERE
					ri.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
				AND
					ri.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			";
		return $this->db->query($sql)->result_array();
	}
}
