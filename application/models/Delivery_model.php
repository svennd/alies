<?php
if (! defined('BASEPATH')) { exit('No direct script access allowed'); }


/*
	don't think this is currently implemented
	but should be used for covetrus deliveries
	see controller/stock
*/
class Delivery_model extends MY_Model
{
	public $table = 'delivery';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Products_model', 'product');


		$this->has_one['wholesale'] = array(
			'foreign_model' => 'Wholesale_model',
			'foreign_table' => 'wholesale',
			'foreign_key' => 'id',
			'local_key' => 'wholesale_id'
		);
		
	}

	public function record(array $data)
	{
		// format pricing from xx,xx -> xx.xx
		$bruto_format = str_replace(',', '.', $data['bruto_price']);
		$netto_format = str_replace(',', '.', $data['netto_price']);

		// get id + update bruto price (if changed)
		$wh_id = $this->wholesale->get_wh_id($data['wholesale_artnr']);
		$updated_bruto = ($wh_id) ? $this->wholesale->update_price($wh_id, $bruto_format) : false;

		// get product id (if known)
		$product_id = ($wh_id) ? $this->product->get_product_id($wh_id) : 0;

		// add to delivery and make sure we get the id, so we can query it later
		$add_record = "
		INSERT INTO delivery
			(
				order_date, order_nr, my_ref,
				wholesale_artnr, wholesale_id, product_id,
				delivery_date, delivery_nr,
				bruto_price, netto_price,
				amount, lotnr, due_date,
				created_at
			)
		VALUES 
			(
				STR_TO_DATE('". $data['order_date'] ."', '%d/%m/%Y'), '" . (int) $data['order_nr'] . "', '" . $data['my_ref'] . "',
				'" . $data['wholesale_artnr'] . "', '" . $wh_id . "', '" . $product_id . "',
				STR_TO_DATE('". $data['delivery_date'] ."', '%d/%m/%Y'), '" . $data['delivery_nr'] . "',
				'" . $bruto_format . "', '" . $netto_format . "',
				'" . (int) $data['amount'] . "', '" . $data['lotnr'] . "', STR_TO_DATE('". $data['due_date'] ."', '%d/%m/%Y'),
				created_at = '" . date('Y-m-d H:i:s') . "'
				
			)
		ON DUPLICATE KEY UPDATE
			id = LAST_INSERT_ID(id),
			updated_at = '" . date('Y-m-d H:i:s') . "';
		";

		// run query
		$this->db->query($add_record);
		
		return array("updated_bruto_price" => $updated_bruto, "id" => $this->db->insert_id());
	}



}
