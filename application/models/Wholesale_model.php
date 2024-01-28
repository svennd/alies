<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Wholesale_model extends MY_Model
{
	public $table = 'wholesale';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('Wholesale_price_model', 'wh_price');
		$this->load->model('Wholesale_type_model', 'wh_type');

		$this->has_many['wholesale_prices'] = array(
			'foreign_model' => 'Wholesale_price_model',
			'foreign_table' => 'wholesale_price',
			'foreign_key' => 'art_nr',
			'local_key' => 'vendor_id'
		);
		$this->has_many['deliveries'] = array(
			'foreign_model' => 'Delivery_model',
			'foreign_table' => 'delivery',
			'foreign_key' => 'wholesale_id',
			'local_key' => 'id'
		);
		$this->has_one['product'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'wholesale',
			'local_key' => 'id'
		);
	}

	public function accept_price(int $id)
	{
		$accept_price = "
				UPDATE `wholesale` SET `last_bruto` = bruto, `last_bruto_date` = '" . date("Y-m-d") . "' WHERE `wholesale`.`id` = '" . $id ."';	
			";
		return $this->db->query($accept_price);
	}

	public function get_wh_id(string $wh_artnr)
	{
		$wh_info = $this->fields('id')->where(array("vendor_id" => $wh_artnr))->get();
		return (int) ($wh_info) ? $wh_info['id'] : false;
	}

	public function update_price(int $id, $new_bruto)
	{
		// check if required
		$wh_info = $this->fields('vendor_id, bruto')->get($id);

		// the price remains the same
		if ($wh_info && $wh_info['bruto'] == $new_bruto)
		{
			return false;
		}

		// update new bruto price for this product
		$this->update(array("bruto" => $new_bruto), $id);
	
		// store old bruto price
		$this->wh_price->insert(array("art_nr" => $wh_info['vendor_id'], "bruto" => $wh_info['bruto']));

		return true;
	}

	public function update_record($art_nr, $omschrijving, $bruto, $btw, $verk_pr_apotheek, $verdeler, $CNK, $VHB, $distr_id, $group)
	{
		// no buy data (product gone/out of stock/...)
		if ($bruto == '-') 
		{
			return true;
		}

		$result = $this->where(array('vendor_id' => $art_nr))->get();

		$bruto_format = str_replace(',', '.', $bruto);
		$verk_pr_apotheek = str_replace(',', '.', $verk_pr_apotheek);

		if ($group == null)
		{
			$group = "unknown";
		}

		$group_info = $this->wh_type->where(array("name" => $group))->get();
		$type_id = (!$group_info) ? $this->wh_type->insert(array("name" => $group)) : $group_info['id'];

		// the product already exists
		if($result)
		{
			$update = $this->where(array("vendor_id" => $art_nr))->update(array(
				"description" 	=> $omschrijving,
				"bruto" 		=> $bruto_format,
				"btw" 			=> $btw,
				"sell_price" 	=> $verk_pr_apotheek,
				"distributor" 	=> $verdeler,
				"CNK" 			=> $CNK,
				"VHB" 			=> $VHB,
				"distributor_id"=> $distr_id,
				"type" 			=> $type_id
			));

			if (!$update){
				echo "ERROR : issue updating product\n";
			}

			// only if price is different
			if ($bruto_format != $result['bruto'])
			{
				$this->wh_price->insert(array("art_nr" => $art_nr, "bruto" => $result['bruto']));
				return array("art_nr" => $art_nr, "description" => $omschrijving,  "new" => $bruto_format, "old" => $result['bruto']);
			}
		}
		# product not found, its new
		else
		{
			$this->insert(array(
							"vendor_id" 		=> $art_nr,
							"description" 		=> $omschrijving,
							"bruto" 			=> $bruto_format,
							"last_bruto"		=> $bruto_format, // since its new, the last bruto price is the same as the current
							"last_bruto_date" 	=> date("Y-m-d"),
							"btw" 				=> $btw,
							"sell_price"		=> $verk_pr_apotheek,
							"distributor" 		=> $verdeler,
							"CNK" 				=> $CNK,
							"VHB" 				=> $VHB,
							"distributor_id"	=> $distr_id,
							"type" 				=> $type_id
						));
		}
		return array();
	}
}
