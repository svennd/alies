<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
	}

	/*
	 * import data from covetrus (netorder)
	 *  covetrus allows to export its pricelist through :
	 *  - artikels -> prijslijst (download format, tekst/pipe or excel or tekst/tab)
	 */
	public function import_covetrus()
	{

		// art. nr|omschrijving|bruto prijs|BTW|verk.pr.  apoth.|verdeler|CNK nummer|Registratienummer (VHB-nummer)
		// 2033204|2LC1-N 30CAPS|68,7|6|74,90|HUMAAN-HUMAIN|2314813|

		$count = false;
		$list_with_changes = array();

		if ($this->input->post('submit') && is_readable('data/stored/' . $this->input->post('file'))) 
        {
			$handle = fopen('data/stored/' . $this->input->post('file'), 'r');

			$header = fgetcsv($handle, 0, "|");
			
			$count = 0;
			for ($i = 0; $row = fgetcsv($handle, 0, "|"); ++$i)
			{
				list(
					$art_nr,
					$omschrijving,
					$bruto_prijs,
					$btw,
					$verk_pr_apotheek,
					$verdeler,
					$CNK_nummer,
					$VHB
					) = $row;

				$count++;
				$info = $this->wholesale->update_record($art_nr, $omschrijving, $bruto_prijs, $btw, $verk_pr_apotheek, $verdeler, $CNK_nummer, $VHB);
				if(isset($info['art_nr']))
				{
					$list_with_changes[] = $info;
				}
			}
			fclose ($handle);
		}

		$this->_render_page('import/covetrus', array(
					"processed" => $count, 
					"changes" => $list_with_changes,
					"products" => $this->wholesale->get_all(),
				));
	}

	/*
		this function will import _ALL_ lab results
		unlike the cron that will only get some results

		from dokter/stalen?days=$ you get : 
			- datum_opname (date : YYYY-MM-DD)
			- extra_gegevens (string) // empty
			- hoofddokter_id (int)
			- id (int)
			- patient_id (int)
			- tijd_opname (datetime : YYYY-MM_DDTHH:MM:SSZ) // wrong
			- updated_at (datetime : YYYY-MM_DDTHH:MM:SSZ)

		from dokter/staal/$id
			- datum_opname
			- extra_gegevens
			- hoofddokter_id
			- id
			- patient_id
			- tijd_opname
			- updated_at
			- resultaten (array)
				- bovenlimiet (float)
				- commentaar
				- decimalen
				- eenheid
				- ingegeven (string)
				- labo_id
				- lengte
				- onderlimiet
				- rapport
				- resultaat
				- sp_resultaat
				- staal_id
				- svorig
				- table
				- tabulatie_code
				- tek_resultaat
				- test_id
				- updated_at
	*/
	public function import_medilab()
	{
		$username = "";
		$password = ""; 

		$url = "https://" . $username . ":". $password . "@online.medilab.be/dokter/tests";
		$json_response = $this->req_curl_json($url);
		var_dump($json_response);
		$response = json_decode($json_response, true);

		var_dump($response);
		// $days = 30;
		// $url = "https://" . $username . ":". $password . "@online.medilab.be/dokter/stalen?days=14";
		// $url = "https://" . $username . ":". $password . "@online.medilab.be/dokter/stalen?days=" . $days;
		// $json_response = $this->req_curl_json($url);
		// $response = json_decode($json_response, true);
		// $i = 1;

		// $data = array();
		// foreach ($response as $r)
		// {
		// 	// echo $i . "\t" . $r["datum_opname"] . "\t" 
		// 	// 	. $r["hoofddokter_id"] . "\t"
		// 	// 	. $r["id"] . "\t"
		// 	// 	. $r["patient_id"] . "\t"
		// 	// 	. $r["updated_at"] . "\n"
		// 	// 	;
		// 	$i++;

		// 	$url_staal = "https://" . $username . ":". $password . "@online.medilab.be/dokter/staal/" . $r["id"];
		// 	$details = $this->req_curl_json($url_staal);
		// 	$lines = json_decode($details, true);
		// 	$data[] = $lines['resultaten'];
		// }
		// $this->_render_page('import/medilab', array(
		// 	"data" => $data, 
		// ));
	}

	private function req_curl_json(string $url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		$json_response = curl_exec($curl);
		curl_close($curl);

		return $json_response;
	}
}
