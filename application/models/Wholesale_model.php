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
