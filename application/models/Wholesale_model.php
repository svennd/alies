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

		$this->has_many['wholesale_prices'] = array(
			'foreign_model' => 'Wholesale_price_model',
			'foreign_table' => 'wholesale_price',
			'foreign_key' => 'art_nr',
			'local_key' => 'vendor_id'
		);
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

	public function update_record($art_nr, $omschrijving, $bruto, $btw, $verk_pr_apotheek, $verdeler, $CNK, $VHB)
	{
		// no buy data (product gone/out of stock/...)
		if ($bruto == '-') 
		{
			return true;
		}

		$result = $this->where(array('vendor_id' => $art_nr))->get();


		$bruto_format = str_replace(',', '.', $bruto);

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
				"VHB" 			=> $VHB
			));

			if (!$update){
				echo "bad line : " . implode(array(
					"description" 	=> $omschrijving,
					"bruto" 		=> $bruto_format,
					"btw" 			=> $btw,
					"sell_price" 	=> $verk_pr_apotheek,
					"distributor" 	=> $verdeler,
					"CNK" 			=> $CNK,
					"VHB" 			=> $VHB
				)) . "<br/>";
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
							"vendor_id" 	=> $art_nr,
							"description" 	=> $omschrijving,
							"bruto" 		=> $bruto_format,
							"btw" 			=> $btw,
							"sell_price"	=> $verk_pr_apotheek,
							"distributor" 	=> $verdeler,
							"CNK" 			=> $CNK,
							"VHB" 			=> $VHB
						));
		}
		return array();
	}
}
