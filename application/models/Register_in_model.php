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
			'foreign_key' 	=> 'id',
			'local_key' 	=> 'product'
		);
					
		parent::__construct();
	}

	/*
	 * Get all register_in records
	*/
	public function get_delivery($delivery_date)
	{
		$sql = "
				select 
					ri.volume, ri.eol, ri.in_price, ri.lotnr,
					products.name,
					deliv.regdate
				from 
					register_in as ri
				JOIN
					products 
				ON
					products.id = ri.product
				JOIN
					delivery_slip as deliv
				ON
					deliv.id = ri.delivery_slip
				WHERE
					deliv.regdate = '" . $delivery_date . "'
			";
		return $this->db->query($sql)->result_array();
	}

	public function date_lookup($search_from, $search_to)
	{
		$sql = "
				select 
					ri.volume, ri.eol, ri.in_price, ri.lotnr, ri.supplier,
					products.name, products.unit_buy, products.buy_price, products.btw_buy, products.btw_sell, products.vhbcode, products.supplier as null_supplier,
					wholesale.bruto,
					deliv.regdate
				from 
					register_in as ri
				JOIN
					products 
				ON
					products.id = ri.product
				JOIN
					delivery_slip as deliv
				ON
					deliv.id = ri.delivery_slip
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

	public function get_last_net_brut(int $product_id)
	{
		$sql = "
				select 
					in_price, cat_price, regdate
				from 
					register_in
				JOIN
					delivery_slip
				ON
					delivery_slip.id = register_in.delivery_slip
				WHERE
					product = " . $product_id . "
				ORDER BY
					regdate DESC
				LIMIT 1
			";
		$data = $this->db->query($sql)->row_array();

		
		return ($data) ? array(
			((!is_null($data['in_price'])) ? $data['in_price'] : 0),
			((!is_null($data['cat_price'])) ? $data['cat_price'] : 0),
			((!is_null($data['regdate'])) ? user_format_date($data['regdate'], "d-m-Y") : 0)
		) : array(0, 0, 0);
	}
}
