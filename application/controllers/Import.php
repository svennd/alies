<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Delivery_model', 'delivery');
	}

	/*
	 * import data from covetrus (netorder)
	 *  covetrus allows to export its pricelist through :
	 *  - artikels -> prijslijst (download format, tekst/pipe or excel or tekst/tab)
	 */
	// public function import_covetrus()
	// {

	// 	// art. nr|omschrijving|bruto prijs|BTW|verk.pr.  apoth.|verdeler|CNK nummer|Registratienummer (VHB-nummer)
	// 	// 2033204|2LC1-N 30CAPS|68,7|6|74,90|HUMAAN-HUMAIN|2314813|

	// 	$count = false;
	// 	$list_with_changes = array();

	// 	if ($this->input->post('submit') && is_readable('data/stored/' . $this->input->post('file'))) 
    //     {
	// 		$handle = fopen('data/stored/' . $this->input->post('file'), 'r');

	// 		// header
	// 		fgetcsv($handle, 0, "|");
			
	// 		$count = 0;
	// 		for ($i = 0; $row = fgetcsv($handle, 0, "|"); ++$i)
	// 		{
	// 			list(
	// 				$art_nr,
	// 				$omschrijving,
	// 				$bruto_prijs,
	// 				$btw,
	// 				$verk_pr_apotheek,
	// 				$verdeler,
	// 				$CNK_nummer,
	// 				$VHB
	// 				) = $row;

	// 			$count++;
	// 			$info = $this->wholesale->update_record($art_nr, $omschrijving, $bruto_prijs, $btw, $verk_pr_apotheek, $verdeler, $CNK_nummer, $VHB);
	// 			if(isset($info['art_nr']))
	// 			{
	// 				$list_with_changes[] = $info;
	// 			}
	// 		}
	// 		fclose ($handle);
	// 	}

	// 	$this->_render_page('import/covetrus', array(
	// 				"processed" => $count, 
	// 				"changes" 	=> $list_with_changes,
	// 				"products" 	=> $this->wholesale->get_all(),
	// 			));
	// }
}
