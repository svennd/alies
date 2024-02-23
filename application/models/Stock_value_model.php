<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stock_value_model extends MY_Model
{
	public $table = 'stock_value';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	public function record_value()
	{
		$sql = "INSERT INTO stock_value (value, created_at)
					SELECT ROUND(SUM(stock.in_price / (products.buy_volume / stock.volume)), 2) as current_stock_value, NOW() as created_at
					FROM stock
					JOIN products ON products.id = stock.product_id
					WHERE stock.state = " . STOCK_IN_USE . " 
					AND (
						stock.eol >= CURRENT_DATE 
						OR stock.eol IS NULL 
						OR stock.eol = '0000-00-00'
					);
					";
		return $this->db->query($sql);
	}
}
