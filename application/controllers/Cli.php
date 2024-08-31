<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Class: Cli
class Cli extends Frontend_Controller 
{
	// initialize
	public $settings, $stock, $logs, $log_stock, $wholesale, $delivery, $lab, $lab_line, $pets, $stock_value, $events, $pricetrack;

	// ci specific
	public $input;
	public $conf = array();
	
	// data import constants
	const MSLINK_DATA_OFFSET = 18;

    public function __construct() {
        parent::__construct();

		# only accept cli here
		if (!is_cli()) { show_error('Direct access is not allowed'); }

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Delivery_model', 'delivery');
		$this->load->model('Lab_model', 'lab');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Lab_detail_model', 'lab_line');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Stock_value_model', 'stock_value');
		$this->load->model('Events_model', 'events');
		$this->load->model('Config_model', 'settings');
		$this->load->model('Log_stock_model', 'log_stock');
		$this->load->model('Products_model', 'products');
		$this->load->model('Price_track_model', 'pricetrack');

        $conf = $this->settings->get_all();
		if ($conf) {
			foreach ($conf as $c) {
				$this->conf[$c['name']] = base64_decode($c['value']);
			}
		}
    }

	/*
	* function: index
	* show the available functions
	*/
	public function index()
	{
		echo "Welcome to alies, cli\n";
		echo "functions :\n";
		echo "  - medilab : fetch medilab samples\n";
		echo "  - delivery [filename] : import delivery file (covetrus)\n";
		echo "  - pricelist [filename] : import pricelist file (covetrus)\n";
		echo "  - stock_clean : attempt to fix stock issues\n";
		echo "  - autoclose : auto close events\n";
		echo "  - prune : prune old logs\n";
		echo "  - auto_death : auto death pets\n";
	}

	/*
	* function: daily
	* daily cron job
	*/
	public function daily()
	{
		# todo: for removed products remove limits
	
		# run the cleanup crew
		$this->autoclose();
		$this->prune();
		$this->auto_death();
		$this->stock_clean();
		$this->stock_value->record_value();
	}

	/*
	* function: prune
	* prune old logs
	*/
	
	public function prune()
	{
		if (!$this->conf['pruning']){ return; }

		# prune global logs
		$p_global_logs = (isset($this->conf['prune_global_log'])) ? (int) $this->conf['prune_global_log'] : false;
		if ($p_global_logs)
		{
			$this->logs->where('created_at < DATE_SUB(NOW(), INTERVAL ' . $p_global_logs . ' DAY)', null, null, false, false, true)->delete();
			$r = $this->db->affected_rows();
			if ($r >= 1)
			{
				$this->logs->logger(INFO, "prune_global_log", "deleted: " . $r);
			}
		}

		# prune empty history stock lines
		$p_stock = (isset($this->conf['prune_stock'])) ? (int) $this->conf['prune_stock'] : false;
		if ($p_stock)
		{
			$this->stock->where(array("state" => STOCK_HISTORY))->where('created_at < DATE_SUB(NOW(), INTERVAL ' . $p_stock . ' YEAR)', null, null, false, false, true)->delete();
			$r = $this->db->affected_rows();
			if ($r >= 1)
			{
				$this->logs->logger(INFO, "prune_stock", "deleted: " . $r);
			}
		}

		# prune stock detail changes log
		$p_log_stock = (isset($this->conf['prune_stock_log'])) ? (int) $this->conf['prune_stock_log'] : false;
		if ($p_log_stock)
		{
			$this->log_stock->where('created_at < DATE_SUB(NOW(), INTERVAL ' . $p_log_stock . ' DAY)', null, null, false, false, true)->delete();
			$r = $this->db->affected_rows();
			if ($r >= 1)
			{
				$this->logs->logger(INFO, "prune_stock_log", "deleted: " . $r);
			}
		}

	}

	/*
	* function: auto_death
	* auto death pets
	*/
	public function auto_death()
	{
		if (!$this->conf['autdeath']){ return; }

		foreach(array(DOG, CAT, HORSE, BIRD, RABBIT, OTHER) as $type)
		{
			$auto_death_year = (isset($this->conf['auto_dead_' . $type])) ? (int) $this->conf['auto_dead_' . $type] : 0;

			// only execute if > 0
			if ($auto_death_year)
			{
				$killed = $this->pets->auto_death($type, $auto_death_year);
				if ($killed)
				{
					$this->logs->logger(INFO, "auto_death", "killed: " . $killed. " of type: " . $type);
				}
			}
		}
		/*
			// add missing death_dates
			UPDATE pets
			SET `death_date` = LEAST(
								CASE 
									WHEN type IN (0, 1, 3, 5) THEN DATE_ADD(birth, INTERVAL 25 YEAR)
									WHEN type = 2 THEN DATE_ADD(birth, INTERVAL 15 YEAR)
									WHEN type = 4 THEN DATE_ADD(birth, INTERVAL 10 YEAR)
									ELSE NULL
								END,
								DATE_SUB(CURDATE(), INTERVAL 14 DAY)
							)
			WHERE death_date IS NULL AND death = 1;
		*/
	}

	/*
	* function: medilab
    * cron function for samples from online.medilab.be
    */
    public function medilab($redirect = false, int $days = 14)
    {
        // static
        $url = "https://" . $this->conf['medilab_user'] . ":". $this->conf['medilab_pasw'] . "@online.medilab.be/dokter/";

        $test_id_to_names = array(
            // hematologie - guessed
            "11015" => "Hemoglobine",
            "11050" => "Hematocriet",
            "11052" => "Rode bloedcellen",
            "11060" => "MCV",
            "11061" => "MCH",
            "11062" => "MCHC",
            "11080" => "Witte bloedcellen",
            
            // formule
            "11101" => "Lymfocyten",
            "11103" => "Staafkernigen",
            "11105" => "Segmentkernigen",
            "11107" => "Eosinofielen",
            "11109" => "Monocyten",
            "11111" => "Basofielen",
            "11113" => "Abnormale vormen",
            "11126" => "Totale Lymfo s (ALC)",
            "11127" => "Totale Neutr.staven",
            "11128" => "Tot.neutrof.segm.(ANC)",
            "11129" => "Totale monocyten",
            "11134" => "Reticulocyten",
            "11135" => "Trombocyten",
            "11131" => "Totale eosinofielen",

            "12340" => "Ureum",
            "12345" => "Creatinine",
            "12385" => "IJzer",
            "12405" => "Totaal Eiwit",
            "12407" => "Albumine",
            "12657" => "Cholesterol",
            "12730" => "Bilirubine totaal",
            "12732" => "Bilirubine direct",
            "12734" => "Bilirubine indirect",

            "12930" => "Amylase",
            "12980" => "Natrium",
            "12982" => "Kalium",
            "13005" => "Calcium",
            "13010" => "Fosfor",

            // hemostase
            "11511" => "Protrombinetijd",
            "11530" => "Part.trombopl.tijd",
            "11540" => "Fibrinogeen",

            // urine
            "15741" => "Cortisol urine (random)",
            "15742" => "Cortisol creatinine ratio",
            "16226" => "Creatinine (random)", // double w 16290
            "16428" => "Eiwit creatinine ratio",

            // screening
            "16477" => "Glucose",
            "16478" => "Eiwit",
            "16479" => "Ketonen",
            "16480" => "pH",
            "16483" => "Densiteit (S.G.)",
            "16649" => "Bilirubine", // REPORT : N ???
            "16486" => "Bilirubine", // REPORT : J
            "16492" => "Urobiline", // based on req. form
            "16655" => "Kultuur",
            "16656" => "Identificatie",
            "16657" => "Antibiogram (urine)",
            
            // Microscopie/flow cytometrie
            "16645" => "Leukocyten",
            "16646" => "Erythrocyten",
            "16647" => "Plaveiselepitheel",
            "16648" => "Kleine ronde cellen",
            "16650" => "Gisten",
            "16651" => "Hyaliene cylinders", // guessed
            "16652" => "Pathologische cylinders", // guessed
            "16653" => "Kristallen",

            // FARMACA-TOXICOLOGIE-SPOORELEMENTEN
            "14705" => "Fenobarbital",

            // biochemie
            "12210" => "Glucose nuchter",
            "12211" => "Fructosamine",
            "12260" => "Insuline",
            "12776" => "Gamma GT",
            "12780" => "Galzuren",

            // ANATOMOPATHOLOGIE
            "17809" => "oorsprong biopt",
            "17810" => "Histologie",

            // IMMUNOLOGIE
            "11852" => "As Leishmania",
            "11856" => "Anaplasma",
            "11857" => "Ehrlichia",
            "11858" => "Borrelia (Lyme)",
            "11859" => "Dirofilaria (hartworm)",

            // 
            "12750" => "Ammoniak (ext)",
            "12760" => "GOT (AST)",
            "12773" => "GPT (ALT)",
            "12361" => "SDMA",
            "16290" => "Creatinine (random)", // double w 16226
            "12805" => "Alkalische fosfatasen",
            "12807" => "Alkalische fosfatasen 65",

            // serum indices
            "15000" => "Hemolyse",
            "15001" => "Lipemie",
            "15002" => "Icterie",

            // faeces
            "16712" => "Parvovirus in faeces",
            "16704" => "Bloed in faeces",
            "16713" => "Giardia screening",
            "16807" => "Macroscopisch aspect",
            "16810" => "Kultuur",
            "16819" => "Campylobacter",
            "16825" => "Parasieten",
            "16830" => "Antibiogram",

            // vertering
            "16715" => "zetmeel",
            "16718" => "spierweefsel",
            "16721" => "vetten",

            // Niersteen
            "17874" => "Niersteen analyse",

            // extra? 
            "18070" => "Bijkomende bepaling",

            // Vogels - but also dog/cat
            "13519" => "DGGR Lipase",
            
            // hormonology
            "13931" => "TSH",
            "14130" => "Oestradiol",
            "14141" => "Progesteron",
        );

        // request
        $request = "stalen.json?days=" . $days;
        $json_response = $this->req_curl_json($url . $request);
        $stalen = json_decode($json_response, true);

        foreach($stalen as $staal)
        {
            $internal_id = $this->lab->add_sample(
                    $staal['id'],
                    array(
                            "lab_date"          => $staal['datum_opname'], 
                            "lab_patient_id"    => $staal['patient_id'], 
                            "lab_updated_at"    => $staal['updated_at'], 
                            "lab_created_at"    => $staal['tijd_opname'], 
                            "lab_comment"       => $staal['extra_gegevens']
                        ),
                    "medilab"
            );

            $detail_response = $this->req_curl_json($url . "staal/" . $staal['id'] . ".json");
            $staal = json_decode($detail_response, true);

            if ($staal["resultaten"])
            {
                foreach($staal["resultaten"] as $line)
                {
                    $this->lab_line->add_line(
                                                $internal_id,
                                                $staal['id'], // lab_id
                                                array(
                                                    'resultaat'       => $line['resultaat'],  
                                                    'sp_resultaat'    => (!empty($line['sp_resultaat']) ? $line['sp_resultaat'] : $line['tek_resultaat']),
                                                    'bovenlimiet'     => $line['bovenlimiet'],  
                                                    'onderlimiet'     => $line['onderlimiet'],  
                                                    'rapport'         => ($line['rapport'] == "J") ? 1 : 0,  
                                                    'eenheid'         => str_replace("Âµ", "µ", $line['eenheid']),  
                                                    'updated_at'      => $line['updated_at'],  
                                                    'tabulatie_code'  => $line['tabulatie_code'],  
                                                    'lab_code_text'   => (isset($test_id_to_names[$line["tabulatie_code"]]) ? $test_id_to_names[$line["tabulatie_code"]] : '---'),  
                                                    'commentaar'      => $line['commentaar'], 
                                                )
                                    );
                }
            }
    
        }
        if($redirect)
        {
            redirect('lab', 'refresh');
        }
        else
        {
            echo date("m.d.y H:i") . " " . count($stalen) . " samples!\n";
        }
    }

	/*
	* function: mslink
	* import data from mslink devices (ms4s2, IkeMS, ImMScan)
	*/
	public function mslink()
	{
		$path = "data/stored/lab/";
		$files = glob($path . "*.txt");
		
		foreach($files as $file)
		{
			$line = 0;
			$this->process_mslink_lab_file($file, $path);
		}
	}

	/*
	* function: process_mslink_lab_file
	* process a single mslink lab file
	*/
	private function process_mslink_lab_file($file, $path)
	{
		// static lab_code
		$lab_code = array(
			"WBC" => 1, 
			"RBC" => 2, 
			"THR" => 3
		);

		$data = explode(";", file_get_contents($file));

		// get the first line
		list(
			$pet_type, // dog, cat, Control, Dog, Cat, CHAT
			$system, // BIOCH HEMATO IMMUNO
			$start_analyse, 
			$end_analyse, 
			$client_name,  // name, id, phone
			$device, // iKEMS, MS4S2, IMMSCAN
			$pet_name, // name or id
			$pet_id,  // name or id or client
			$unk,
			$unk2,
			$empty,
			$empty,
			$run_id,
			) = $data;

		// determine what is most likely the pet_id
		$pet_id = (is_numeric($pet_id)) ? $pet_id : (is_numeric($pet_name) ? $pet_name : (is_numeric($client_name) ? $client_name : NULL));
		
		$start 	= DateTime::createFromFormat('d/m/Y H:i:s:v', $start_analyse)->format('Y-m-d H:i:s');
		$end 	= DateTime::createFromFormat('d/m/Y H:i:s:v', $end_analyse)->format('Y-m-d H:i:s');

		
		$internal_id = $this->lab->add_mslink_sample(
				$run_id,
				array(
						"lab_date"          => $start,
						"lab_updated_at"    => $start, 
						"lab_created_at"    => $end, 
						"lab_comment"       => $system . " - " . $device
					),
				"mslink - " . $system,
				$pet_id
		);

		// check for duplicate
		if ($internal_id == false)
		{
			echo "ERROR : ". $file . " duplicate detected\n";
			// move the file
			if(!$this->move_file($file, $path . 'failed/' . basename($file)))
			{
				echo "ERROR : issue moving file\n";
			}
			return;
		}

		if ($system == "HEMATO")
		{
			// extract the plots
			$hemato_plot = array_slice($data, 13, 3);
			foreach ($hemato_plot as $plot)
			{
				$name = explode(",", $plot)[0];

				$this->lab_line->insert(array(
					"lab_id" 			=> $internal_id,
					"sample_id" 		=> $internal_id,
					"lab_code"		 	=> $lab_code[$name],
					"lab_code_text" 	=> $name,
					"comment"			=> $plot,
					"updated_at" 		=> $start,
					"created_at" 		=> $end
				));
			}
		}

		// extract the values
		/*
		*	the data is always in fields of 6
		*/
		$anamnese = "Lab results:\n";
		// extract the values
		for ($i = self::MSLINK_DATA_OFFSET; $i < count($data); $i++) {
			list(
					$lab_name, 		// test name
					$value, 		// test value
					$lab_unit, 		// unit 
					$lab_result_symbol,  // > or < (sometimes)
					$lab_result, 		//  + or - or 0
					$low_high
					) = array_slice($data, $i, 6);
			
			// if the name is not empty
			if ($lab_name == "") { break; }
			if ($lab_name == "Err1") { continue; }
			if ($lab_name == "Err2") { continue; }
			if ($lab_name == "Err3") { continue; }
			$i += 5;

			# data prep
			if ($low_high != "")
			{
				$low = trim(explode("-", $low_high)[0]);
				$high = trim(explode("-", $low_high)[1]);
			} else {
				$low = "";
				$high = "";
			}

			$this->lab_line->insert(array(
				"lab_id" 			=> $internal_id,
				"sample_id" 		=> $internal_id,
				"value" 			=> trim($value),
				"upper_limit" 		=> $high,
				"lower_limit" 		=> $low,
				"lab_code"		 	=> hexdec(substr(md5($lab_name), 0, 4)), // need some sort of an idea
				"lab_code_text" 	=> $lab_name,
				"unit"				=> $lab_unit,
				"updated_at" 		=> $start,
				"created_at" 		=> $end
			));
			$anamnese .= $lab_name . " : " . $value . " " . $lab_unit . "\n";

		}
		
		if ($pet_id && $pet_id != 0)
		{
			$this->lab->add_event($internal_id, $pet_id, $anamnese);
		}
		// move the file
		if(!$this->move_file($file, $path . 'processed/' . basename($file)))
		{
			echo "ERROR : issue moving file\n";
		}
	}

	/*
	* function: delivery
	* import delivery file from covetrus
	*/
	public function delivery($filename)
	{
        $path = "data/stored/delivery/";
        $file = $path . $filename;

		# check if the file exists
		if(!is_readable($file) && !is_file($file))
		{
			echo "File not found : " . $file . "\n";
			return;
		}
    
		# open the file and read line by line
		$handle = fopen($file, 'r');
		$line = 0;
		# header 
		fgetcsv($handle, 0, "|");

		while (($row = fgetcsv($handle, 0, "|")) !== FALSE) 
		{
			# get the data
			list(
				$order_date,
				$order_nr,
				$my_ref,
				$wholesale_artnr, // could be number but we don't trust this, so we link to wholesale_id
				$wholesale_art_name,
				$CNK_nummer,
				$delivery_date,
				$delivery_nr,
				$bruto_price,
				$netto_price,
				$verk_pr_apotheek, // ignored
				$btw,
				$amount,
				$lotnr,
				$due_date,
				$billing
				) = $row;
				
                $x = $this->wholesale->fields('id')->where(array("vendor_id" => $wholesale_artnr))->get();
				$id = ($x) ? $x['id'] : null;

                # dates
                $dt_order_date = DateTime::createFromFormat('j/m/Y', $order_date);
                $dt_delivery_date = DateTime::createFromFormat('j/m/Y', $delivery_date);
                $dt_due_date = DateTime::createFromFormat('j/m/Y', $due_date);
				
				$netto_price_format = str_replace(',', '.', $netto_price);
				$bruto_price_format = str_replace(',', '.', $bruto_price);
				
				# if this is a known product
				# check for price changes
				# note: bruto is checked daily by pricelist
				if ($id)
				{
					$this->check_for_netto_price_change($netto_price_format, $id);
				}

				$this->delivery->insert(array(
					"order_date" 			=> ($dt_order_date) ? $dt_order_date->format('Y-m-d') : "",
					"order_nr" 				=> $order_nr,
					"my_ref" 				=> $my_ref,
					"wholesale_artnr" 		=> $wholesale_artnr,
					"wholesale_id"			=> $id,
					"CNK"        			=> $CNK_nummer,
					"delivery_date" 		=> ($dt_delivery_date) ? $dt_delivery_date->format('Y-m-d') : "",
					"delivery_nr" 			=> $delivery_nr,
					"bruto_price" 			=> $bruto_price_format,
					"netto_price" 			=> $netto_price_format,
					"amount" 				=> $amount,
					"lotnr" 				=> $lotnr,
					"due_date" 				=> ($dt_due_date) ? $dt_due_date->format('Y-m-d') : "",
					"btw" 					=> $btw,
					"billing"				=> $billing
				));
                $line++;
		}
        fclose($handle);
        if(!$this->move_file($file, $path . 'processed/' . $filename))
        {
            echo "ERROR : issue moving file\n";
        }
        echo "lines : " . $line . "\n";
	}

	/*
	* function: pricelist
	* import pricelist file from covetrus
	*/
    public function pricelist(string $filename)
    {
        $path = "data/stored/pricelist/";
        $file = $path . $filename;

		# check if the file exists
		if(!is_readable($file) && !is_file($file))
		{
			echo "File not found : " . $file . "\n";
			return;
		}

        # open the file and read line by line
		$handle = fopen($file, 'r');
		$line = 0;

		# check if expected header
		if (fgetcsv($handle, 0, "|") != "art. nr|omschrijving|bruto prijs|BTW|verk.pr.  apoth.|verdeler|CNK nummer|Registratienummer (VHB-nummer)|Artikelnr. verdeler|Hoofdgroep|Creatiedatum") 
		{
			// echo "Bad header\n";
			// return;
		}
		
		while (($row = fgetcsv($handle, 0, "|")) !== FALSE) 
		{
			# get the data
			list(
                $art_nr,
                $omschrijving,
                $bruto_prijs,
                $btw,
                $verk_pr_apotheek,
                $verdeler,
                $CNK_nummer,
                $VHB,
				$distr_id,
				$group,
				$created // ignored
				) = $row;
                
                $this->wholesale->update_record($art_nr, $omschrijving, $bruto_prijs, $btw, $verk_pr_apotheek, $verdeler, $CNK_nummer, $VHB, $distr_id, $group);

				if($line % 100 == 0) { echo $line . "\n"; usleep(500000); }
                $line++;
		}
        fclose($handle);
        if(!$this->move_file($file, $path . 'processed/' . $filename))
        {
            echo "ERROR : issue moving file\n";
        }
        echo "lines : " . $line . "\n";
    }

	/*
	* function: check_for_changes_in_price
	* check for changes in wholesale bruto price
	*/
	public function check_for_changes_in_price()
	{
		$price_changes = $this->wholesale->get_price_diff();
		foreach ($price_changes as $change)
		{
			// each price change from wholesale can trigger
			// a price warning to the user
			$updated = $this->pricetrack
				->update(
					array(
						"product_id" 		=> $this->products->get_id_by_wholesale($change['id']), // int or nul
						"original_price" 	=> $change['last_bruto'],
						"new_price" 		=> $change['bruto'],
						"source" 			=> "wholesale_pricelist",
						"ack_user"			=> 0,
						"applied"			=> 0
					),
					array("wholesale_id" => $change['id'])
				);

			if (!$updated)
			{
				$this->pricetrack->insert(array(
						"product_id" 		=> $this->products->get_id_by_wholesale($change['id']), // int or null
						"wholesale_id" 		=> $change['id'], // wholesaleid
						"original_price" 	=> $change['last_bruto'],
						"new_price" 		=> $change['bruto'],
						"source" 			=> "wholesale_pricelist",
				));
			}

			// also "accept" this change since we warned the user
			$this->wholesale->accept_price($change['id']);
		}
	}

	/*
	* function: stock_clean
	* attempt to fix stock issues
	*/
	public function stock_clean()
	{
		$r = $this->stock->where(array('state' => STOCK_IN_USE, 'volume' => '0.0'))->update(array("state" => STOCK_HISTORY));

		# make this traceable
		if ($r >= 1)
		{
			$this->logs->logger(WARN, "stock_clean", "archived: " . $r);
		}
	}

	/*
	* function: autoclose
	* auto close events
	*/
	public function autoclose() {
		// var
		if ($this->conf['autoclose'] == ""){ return; }

		$autodisable = (isset($this->conf['autodisable'])) ? (bool) $this->conf['autodisable'] : false;
		$autoclose_days = (isset($this->conf['autoclose'])) ? (int) $this->conf['autoclose'] : 14;
		$affected = 0;

		# special case
		if($autoclose_days == 0) { return; }

		# attempt to hide empty or not filled in
		# events that are not finished
		if($autodisable)
		{
			$sql = "
				UPDATE `events`
				SET 
					`report` = " . REPORT_DONE . ",
					`no_history` = 1
				WHERE 
					`created_at` < (NOW() - INTERVAL " . $autoclose_days . " DAY) 
				AND 
					`report` != " . REPORT_DONE . "
				AND
					`no_history` != 1
				AND
				( 
					`anamnese` = ''
					OR
					`anamnese` = '" . $this->conf['autotemplate'] . "'
				)
					;
			 ";
			 # run query
			 $this->db->query($sql);
	 
			 # check if we got hits
			 $affected += $this->db->affected_rows();
		}

		# autoclose events that are not finished
		$sql = "
		UPDATE `events`
			SET 
				`report` = " . REPORT_DONE . ",
				`anamnese` = CONCAT(`anamnese`, ' [Auto-Closed]')
			WHERE 
				`created_at` < (NOW() - INTERVAL " . $autoclose_days . " DAY) 
			AND 
				`report` != " . REPORT_DONE . "
			AND
				`no_history` != 1
				;
		 ";

		# run query
		$this->db->query($sql);

		# check if we got hits
		$affected += $this->db->affected_rows();

		if ($affected > 0)
		{
			$this->logs->logger(INFO, "autoclose", "closed " . $affected . " events");
		}
		else
		{
			$this->logs->logger(DEBUG, "ran_autoclose", "no affected");
		}
	}

	/*
	* function: check_for_netto_price_change
	*
	*/
	private function check_for_netto_price_change($netto_price_format, int $id)
	{
		// get the last netto price
		$last_price = $this->delivery
				->fields('netto_price')
				->where(array("wholesale_id" => $id))
				->order_by('id', 'DESC')
				->get();
		
		// no last price
		if (!$last_price)
		{
			return;
		}

		// probably want to do this in one go
		// and restrict based on config values
		// so that 0.01% increase/decrease isn't a trigger
		// but 1% is if the value of the product is larger (eg +0.03c on 3€ is not a trigger, but +0.03c on 0.3€ is a trigger)
		if (abs($last_price['netto_price']-$netto_price_format) > 0.01)
		{
			// insert or update ?
			$updated = $this->pricetrack
				->update(
					array(
						"product_id" 		=> $this->products->get_id_by_wholesale($id), // int or null
						"new_price"			=> $netto_price_format,
						"original_price" 	=> $last_price['netto_price'],
						"source" 			=> "wholesale_delivery",
						"ack_user"			=> 0,
						"applied"			=> 0
					),
					array("wholesale_id" => $id)
				);
			if (!$updated)
			{
				$this->pricetrack->insert(array(
					"product_id" 		=> $this->products->get_id_by_wholesale($id), // int or null
					"wholesale_id" 		=> $id, // wholesaleid
					"original_price" 	=> $last_price['netto_price'],
					"new_price" 		=> $netto_price_format,
					"source" 			=> 'wholesale_delivery',
				));
			}
		}
	}

	/*
	*	function: move_file
	*	move a file from one location to another
	*/
    private function move_file(string $path, string $to): bool {
        if(copy($path, $to)){
            unlink($path);
            return true;
        } else {
            return false;
        }
    }
	
	/*
	*	function: req_curl_json
	*	wrapper around some curl setup
	* may require a specific php extension : php-curl
	*/
	private function req_curl_json(string $url): string
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