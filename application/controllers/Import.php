<?php
defined('BASEPATH') OR exit('No direct script access allowed');

# imports don't have to be rerun ...

class Import extends Frontend_Controller {

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# load librarys
		$this->load->helper('url');	
		$this->load->helper('date');	
		
		
		$this->load->model('Procedures_model', 'proc');
		$this->load->model('Booking_code_model', 'booking');
		
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Pets_weight_model', 'weight');
		$this->load->model('Breeds_model', 'breeds');
		
		$this->load->model('Owners_model', 'owners');
		
		$this->load->model('Products_model', 'products');
		$this->load->model('Events_model', 'events');
		$this->load->model('Bills_model', 'bills');
		
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Product_type_model', 'product_type');
		$this->load->model('Product_price_model', 'price');
		
		$this->load->model('Zipcodes_model', 'zipcode');
	}

	public function init_config()
	{
		$this->load->model('Config_model', 'conf');
		$this->conf->insert(array(
								"name" 		=> "backup_count",
								"value" 	=> 0, # count is used for time in updated_at
								));
		$this->conf->where(array("name" => "backup_count"))->update(array("value" => 0));
		$this->conf->insert(array(
								"name" 		=> "alert_last_backup",
								"value" 	=> 7, # in days since last backup
								));
	}
	
	// import zipcodes (belgium)
	public function import_zipcodes()
	{
		# reset
		# truncate zipcodes;
		
		$handle = fopen("import/zipcodes.csv", "r");
		$header = fgetcsv($handle, 0, ";");
		
		$count = 0;
		
		for ($i = 0; $row = fgetcsv($handle, 0, ";"); ++$i) 
		{
			// var_dump($row);
			list(
				$postcode,
				$city,
				$part,
				$main_city,
				$provincie
				) = $row;
				
				
			$this->zipcode->insert(array(
						"zip" 		=> trim($postcode),
						"city" 		=> trim($city),
						"main_city"	=> trim($main_city),
						"province"	=> trim($provincie),
				));
			$count++;

		}
		fclose ($handle);
		echo "zipcodes : " . $count . "<br>";
	}
	
	public function import_procedures()
	{
		# FICHE/PRESTART.DBF
		# truncate procedures;
		
		$handle = fopen("import/19_07_20/export/PRESTART.csv", "r");
		$header = fgetcsv($handle, 0, ",", '"');
		
		$procedure_booking = $this->booking->insert(array('category' => 'PROCEDURE', 'code' => '700721', 'btw' => '21'));
			
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			list(
					$NAAM,
					$VERKOOPEENH,
					$TYPE,
					$PRIJS
				) = $row;
				
			$this->proc->insert(array(
						"name" 			=> $NAAM,
						"booking_code" 	=> $procedure_booking,
						"price" 		=> $PRIJS
						));
		}
		echo "done";
	}
	
	public function client_import()
	{
		# reset db :
		# truncate owners;
		
		/*
		KLANTEN.DBF
		*/

		$handle = fopen("import/12_10_20/KLANTEN.csv", "r");
		$header = fgetcsv($handle, 0, ",", '"');
		/*
		array (size=23)
			  0 => string 'NAAM,C,25' (length=9)
			  1 => string 'VOORNAAM,C,15' (length=13)
			  2 => string 'STRAAT,C,30' (length=11)
			  3 => string 'HUISNR,C,10' (length=11)
			  4 => string 'POSTNR,C,4' (length=10)
			  5 => string 'WOONPL,C,20' (length=11)
			  6 => string 'TEL1,C,14' (length=9)
			  7 => string 'TEL2,C,14' (length=9)
			  8 => string 'TEL3,C,14' (length=9)
			  9 => string 'BEROEP,C,25' (length=11)
			  10 => string 'NRKLANT,C,6' (length=11)
			  11 => string 'CODEKLANT,C,15' (length=14)
			  12 => string 'SCHULD,L' (length=8)
			  13 => string 'ARTS,C,3' (length=8)
			  14 => string 'FOUTADR,L' (length=9)
			  15 => string 'MAILING,L' (length=9)
			  16 => string 'SOUNDEX,C,5' (length=11)
			  17 => string 'BTWNR,C,14' (length=10)
			  18 => string 'FAX,C,14' (length=8)
			  19 => string 'TAALKODE,C,1' (length=12)
			  20 => string 'LANDBOUW,L' (length=10)
			  21 => string 'VERVALD,N,2,0' (length=13)
			  22 => string 'IDPRAK,C,1' (length=10)
		*/
		
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			list(
					$naam,
					$voornaam,
					$straat,
					$huisnr,
					$postnr,
					$city,
					$tel1,
					$tel2,
					$tel3,
					$beroep, # ignore
					$nrklant, # take this as id
					$CODEKLANT, # ignore
					$SCHULD, # needs processing (TRUE / FALSE) --> 1 / 0
					$ARTS, # needs users_id
					$FOUTADR,
					$MAILING, # contact y/n
					$SOUNDEX, # ignore
					$BTWNR,
					$FAX,
					$TAALKODE,
					$LANDBOUW,
					$VERVALD,
					$IDPRAK
				) = $row;
				
				$debts = 0;
				if (!empty($SCHULD))
				{
					if($SCHULD == "TRUE")
					{
						$debts = 1;
					}
				}
				$contact = 0;
				if (!empty($MAILING))
				{
					if($MAILING == "TRUE")
					{
						$contact = 1;
					}
				}
				
				if ($BTWNR == "NO")
				{
					$btw = "";
				}
				else
				{
					$btw = $BTWNR;
				}
				
				if ($TAALKODE == "N")
				{
					$language = 1;
				}
				else
				{
					$language = 2;
				}
				
			$praktijk = $this->get_pract($IDPRAK);
			$vet_id = $this->get_vet($ARTS);
			$result = $this->zipcode->where(array('zip' => $postnr))->get();
		
			$this->db->insert('owners', array(
						"id" 			=> $nrklant,
						"first_name" 	=> $voornaam,
						"last_name" 	=> $naam,
						"street"		=> $straat,
						"nr" 			=> $huisnr,
						"zip" 			=> $postnr,
						"city" 			=> $city,
						"main_city"		=> ($result) ? $result['main_city'] : "",
						"province"		=> ($result) ? $result['province'] : "",
						"telephone" 		=> $tel1,
						"mobile"	 		=> $tel2,
						"phone2"			=> $tel3,
						"debts"				=> $debts,
						"btw_nr"			=> $btw,
						"contact"		=> $contact,
						"debts"			=> $debts,
						"language"		=> $language,
						"initial_loc"	=> $praktijk,
						"initial_vet"	=> $vet_id,
					));
								
			if ($i % 100 == 0)
			{
				echo $i. "<br/>";
			}
		}
		echo "done";
	}
	
	public function import_pets()
	{
		/*
			PATIENT.DBF
		*/
		# reset
		# truncate pets; truncate pets_weight; truncate breeds;
		
		
		$error = 0;
		
		# import breeds
		$breed_unknown_id = $this->breeds->insert(array("name" => "unknown"));
		
		$known_breeds = fopen("import/breeds_known.txt", "r");
		while(! feof($known_breeds))  {
			$result = fgets($known_breeds);
			$known_breeds_array[trim($result)] = true;
		}
		fclose($known_breeds);
		echo "read from file known breeds " . count($known_breeds_array) . "<br/>";
		
		foreach (array_keys($known_breeds_array) as $breed)
		{
			$ras = ucfirst(strtolower(trim($breed)));
			# for case issues
			if (!$this->breeds->where(array("name" => $ras))->get())
			{
				$this->breeds->insert(array("name" => $ras));
			}
		}
		
		/*
		  0 => string 'NRKLANT,C,6' (length=11)
		  1 => string 'NAAMDR,C,20' (length=11)
		  2 => string 'DRSOORT,C,1' (length=11)
		  3 => string 'CODENR,C,6' (length=10)
		  4 => string 'RAS,C,25' (length=8)
		  5 => string 'SEX,C,2' (length=7)
		  6 => string 'KLEUR,C,20' (length=10)
		  7 => string 'GB_DATUM,C,8' (length=12)
		  8 => string 'GEWICHT,C,15' (length=12)
		  9 => string 'DOOD,L' (length=6)
		  10 => string 'WEG,L' (length=5)
		  11 => string 'ARTS,C,3' (length=8)
		  12 => string 'IDNUM,C,20' (length=10)
		  13 => string 'TATTOO,C,20' (length=11) --> num vac bookje
		  14 => string 'NUMVACC,C,8' (length=11)
		  15 => string 'IDPRAK,C,1' (length=10)
		*/
		
		$handle = fopen("import/12_10_20/PATIENT.csv", "r");
		$header = fgetcsv($handle, 0, ",", '"');
		// var_dump($header);
		
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			$msg = ""; # used for message about import
			list(
					$NRKLANT,
					$NAAMDR,
					$DRSOORT,
					$CODENR, # ID ?
					$RAS, # to unify
					$SEX,
					$KLEUR,
					$GB_DATUM,
					$GEWICHT,
					$DOOD, 
					$WEG, 
					$ARTS,
					$IDNUM,
					$TATTOO, # abused for numvacc
					$NUMVACC, # broken due to short
					$IDPRAK
				) = $row;
				
			// $dier_soort = $DRSOORT;
			$weight = (float)$GEWICHT;
					
			$death = ($DOOD == "TRUE") ? 1 : 0;
			$lost = ($WEG == "TRUE") ? 1 : 0;
			
			$praktijk = $this->get_pract($IDPRAK);
			
			$ras_format = ucfirst(strtolower(trim($RAS)));
			$breed = $this->breeds->where(array("name" => $ras_format))->get();
			
			if (empty($ras_format))
			{
				$breed_id = $breed_unknown_id;
			}
			elseif (!$breed)
			{
				// $breed_id = $this->breeds->insert(array("name" => $ras_format));
				$breed_id = $breed_unknown_id;
				$msg .= "import breed : " . $ras_format . "";
			}
			else
			{
				$breed_id = $breed['id'];
			}
			
			# errors
			if(empty($NRKLANT)) {$error++; continue;}
			
			$date = null;
			if (substr_count($GB_DATUM,"/") == 2) 
			{
				$date_class = date_create_from_format('d/m/y', $GB_DATUM);
				// var_dump($GB_DATUM . ' ==> '  . date("Y/m/d", $date->getTimestamp()));
				
				if ($date_class)
				{
					$date = date_format($date_class, "Y/m/d");
				}
			}
			
			# if we have no idea when the birth was
			# we expect the animal to be dead.
			if ($date == null)
			{
				$death = 1;
				$date = "1978/01/01"; # guessy
			}
			
			/*
			0 dog
			1 cat
			2 horse
			3 bird
			4 other
			*/
			switch ($DRSOORT)
			{
				case "2": $type = 0; break; # dog
				case "6": $type = 1; break; # cat
				case "0": $type = 2; break; # horse
				case "5": $type = 3; break; # bird
				default : # other
					$type = 4;
			}
						
			switch ($SEX)
			{
				case "M": $gender = 0; break; # m
				case "V": $gender = 1; break; # f
				case "MN": $gender = 2; break; # mn
				case "VN": $gender = 3; break; # fn
				default : # other
					$gender = 4;
			}
			
			$exist = $this->pets->get((int)$CODENR);
			
			if (!$exist)
			{
				$new_pet_id = $this->db->insert('pets', array(
								"id" 		=> (int)$CODENR,
								"owner" 	=> $NRKLANT,
								"name" 		=> $NAAMDR,
								"type" 		=> $type,
								"breed" 	=> $breed_id,
								"gender" 	=> $gender,
								"color" 	=> $KLEUR,
								"birth" 	=> $date, # might fail
								"last_weight" => (float) $weight,
								"lost" 		=> $lost,
								"death" 	=> $death,
								"chip" 		=> $IDNUM,
								"nr_vac_book" => $TATTOO,
								"location" 	=> $praktijk,
								"note" 		=> $msg
								));
			} else {
				$msg .= "import error : id duplicate :" . $CODENR . "\n";
				$new_pet_id = $this->pets->insert(array(
						"owner" => $NRKLANT,
						"name" => $NAAMDR,
						"type" => $type,
						"breed" => $breed_id,
						"gender" => $gender,
						"color" => $KLEUR,
						"birth" => $date, # might fail
						"last_weight" => (float) $weight,
						"lost" => $lost,
						"death" => $death,
						"chip" => $IDNUM,
						"nr_vac_book" => $TATTOO,
						"location" => $praktijk,
						"note" => $msg
						));
				echo "duplicate : $CODENR --> $new_pet_id";
			}
			
			# if a decent weigh enter it
			if ($weight != 0)
			{
				$this->weight->insert(array(
								"pets" => $new_pet_id,
								"weight" => $weight,
				));
			}
			if ($i % 100 == 0)
			{
				echo $i. "<br/>";
			}
		}
		echo "done";
		echo "errors : " . $error;
	}
	
	public function import_events()
	{
		# reset
		# truncate events
		
		/*
			DATA/ziekten*.dbf
		*/
		$this->load->model('Events_model', 'events');
		$this->load->model('Pets_model', 'pets');
		
		// $handle = fopen("import/19_07_20/export/data_ziekten.csv", "r");
		// $handle = fopen("import/19_07_20/export/data_ziekten_2.csv", "r");
		$handle = fopen("import/19_07_20/export/data_ziekten_3.csv", "r");
		// $header = fgetcsv($handle, 0, ",", '"');
		$vets = array();
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			list(
					$CODENR,
					$DATUM,
					$ANAMNESE,
					$CODEDIAG,
					$ANALYSE,
					$CODEPREST,
					$DATUMLAB,
					$ARTS,
					$ARTS2
				) = $row;
			
		
			# format title from /(string)/(empty)/(empty)
			# to /string/string or string
			$title_temp = explode("/", $CODEDIAG);
			$title = "";
			foreach ($title_temp as $t)
			{
				$t = trim(preg_replace('/\s+/', '', $t));
				if(!empty($t))
				{
					$title .= $t . "/";
				}
			}
			$title = substr($title,0,-1);
				
				// var_dump($CODEDIAG);
				// var_dump($title);

			# try to import the date
			# date stored as 20070326
			$date_class = date_create_from_format('Ymd', $DATUM);

			if ($date_class)
			{
				$date = date_format($date_class, "Y-m-d H:i:s");
			}
			
			$pet_id = $this->pets->fields('id')->get((int)$CODENR);

			if (!$pet_id)
			{
				echo "can't find : " . $CODENR . " <br/>";
				var_dump($pet_id);
				var_dump($row);
			}
			else
			{
				$vet_id = $this->get_vet($ARTS);
				$vet_id_2 = $this->get_vet($ARTS2);
				$vets[$ARTS] = 1;
				$vets[$ARTS2] = 1;
				
				// $vet_id = 1;
				$this->db->insert('events', array(
							"title" 		=> $title,
							"anamnese" 		=> $ANAMNESE, // optimiaze : gzcompress($string, 1) 
							"pet" 			=> (int)$CODENR,
							"status" 		=> STATUS_HISTORY, # no way to determ
							"payment"		=> EVENT_PAYMENT_IMPORT, # no way to determ
							"vet"			=> $vet_id,
							"vet_support_1"	=> $vet_id_2,
							"location"		=> 1,
							"created_at"	=> $date
					));
			}
			if ($i % 100 == 0)
			{
				echo $i. "<br/>";
			}
		}
		
		echo "done; vets : ";
		var_dump($vets);
		// print_r($this->db->last_query());    
		// $this->output->enable_profiler(TRUE);    

	}

	
	/*
		import facturen

	*/
	public function import_facturen()
	{
		# reset
		# truncate bills;
		
		$handle = fopen("import/18_08_20/FACTUUR.csv", "r");
		$header = fgetcsv($handle, 0, ",", '"');
		$count = 0;
		
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			list(
				$NRKLANT,
				$ARTS,
				$BTWNR,
				$DATUM,
				$VERVALDAT,
				$NRFAC,
				$ERELOON,
				$BTWERE,
				$VERKERE,
				$VERPL,
				$BTWVERPL,
				$VERKVERPL,
				$KOMMENTAAR,
				$TYPEBTW,
				$TAALCODE,
				$BETAALD,
				$TOTAAL,
				$BASIS1, $BASIS2,
				$BASIS3, $BASIS4,
				$SBTW1,	$SBTW2,
				$SBTW3,	$SBTW4,
				$NRBEHANDEL,
				$AFSLUIT,
				$PRINT,
				$TYPE,
				$KORTING1,	$KORTING2,
				$KORTING3,	$KORTING4,
				$GROEP,
				$DATFACT,
				$IDPRAK,
				$CODEVERK,
				) = $row;
				
				$praktijk = $this->get_pract ($IDPRAK);
				$vet_id = $this->get_vet($ARTS);

			$date_class = date_create_from_format('m/d/Y', $DATUM);
			$year_exp = date_format($date_class, "Y");
			
			if ($date_class)
			{
				$date = date_format($date_class, "Y-m-d H:i:s");
				$date_short = date_format($date_class, "Y-m-d");
			}
			
			if ($year_exp > 2017)
			{
				$this->db->insert('bills', array(
							"owner_id" 		=> trim($NRKLANT),
							"vet" 			=> $vet_id,
							"location" 		=> $praktijk,
							"amount" 		=> (float) $TOTAAL,
							"card" 			=> (float) $TOTAAL,
							"cash" 			=> 0,
							"status" 		=> 3,
							"created_at"	 => $date
					));
				$count++;
			}
			if ($date_class)
			{
				# not sure how to do it quick in my_model
				$sql = "UPDATE `owners` SET `last_bill` = '" . $date_short . "' WHERE (`last_bill` < '" . $date_short . "' OR `last_bill` IS NULL) AND `id` = '" . trim($NRKLANT) . "';";
				$this->db->query($sql);
			}
		}
		fclose ($handle);
		echo "facturen imported : " . $count . "<br>";
	}
	
	public function import_products()
	{
		# reset db : 
		# truncate products_price; truncate products; truncate stock; truncate products_type;
		# truncate booking_codes;

		
		## 1 = waasmunster
		$location = 1;
		##
		
		# products type
		#
			$spuiten_id = $this->product_type->insert(array('name' => 'Spuiten'));
			$medicat_id = $this->product_type->insert(array('name' => 'Medicatie'));
			$voeding = $this->product_type->insert(array('name' => 'Voeding & Materiaal'));
			$OK_id = $this->product_type->insert(array('name' => 'O.K.'));
			$other_id = $this->product_type->insert(array('name' => 'Other'));
		#
		# BOOKING CODE
		# 
			$afgeleverd_6 = $this->booking->insert(array('category' => 'AFGELEVERD', 'code' => '700606', 'btw' => '6'));
			$afgeleverd_21 = $this->booking->insert(array('category' => 'AFGELEVERD', 'code' => '700621', 'btw' => '21'));
			$verbruik_6 = $this->booking->insert(array('category' => 'VERBRUIK', 'code' => '700506', 'btw' => '6'));
			$verbruik_21 = $this->booking->insert(array('category' => 'VERBRUIK', 'code' => '700521', 'btw' => '21'));
		
		$this->load->library('barcode');
		
		$known_products = fopen("import/product_known.txt", "r");
		while(! feof($known_products))  {
			$result = fgets($known_products);
			$known_products_array[trim($result)] = true;
		}
		fclose($known_products);
		echo "read from file importable products " . count($known_products_array) . "<br/>";
		
		// $handle = fopen("import/19_07_20/export/PRODUKT.csv", "r");
		$handle = fopen("import/PRODUKT_11_10_2020.csv", "r");
		$header = fgetcsv($handle, 0, ",", '"');
		// var_dump($header);
		
		$timestamp = (time() - 1575158400) - 100;
		$total_products = 0;
		for ($i = 0; $row = fgetcsv($handle, 0, ",", '"'); ++$i) 
		{
			list(
					$name,
					$afkorting,
					$barcode_input,
					$lever,
					$aankoopeendheid,
					$aankoop_unit,
					$verkoopeenheid,
					$verkoop_unit,
					$aankoop_prijs,
					$drempel,
					$btw_aankoop,
					$verk1,
					$verk2,
					$verk3,
					$verk4,
					$verk5,
					$verk_pr1,
					$verk_pr2,
					$verk_pr3,
					$verk_pr4,
					$verk_pr5,
					$quant, // sum of quant (not always correct)
					$verval,
					$lot1,
					$lot2,
					$lot3,
					$gelduur,
					$quant1,
					$quant2,
					$quant3,
					$btw_verkoop,
					$comment,
					$producent,
					$lot4,
					$lot5,
					$quant4,
					$quant5,
					$wachttijd,
					$vervald1,
					$vervald2,
					$vervald3,
					$vervald4,
					$vervald5
				) = $row;
		
		# filters
		# if nothing is in stock
		# unlikely we still use it
		if ($quant == 0 && $quant1 == 0 && $quant2 == 0 && $quant3 == 0 && $quant4 == 0 && $quant5 == 0)
		{
			continue;
		}
		
		// if (!isset($known_products_array[$name]))
		// {
			// echo "considered deleted : ". $name . "<br/>";
			// continue;
		// }
		
		if ($btw_aankoop == 6 && $btw_verkoop == 21)
		{
			$booking_code_determ = $verbruik_21;
			$type = $spuiten_id;
		}
		elseif ($btw_aankoop == 6 && $btw_verkoop == 6)
		{
			$booking_code_determ = $verbruik_6;
			$type = $medicat_id;
		}
		elseif ($btw_aankoop == 21 && $btw_verkoop == 6)
		{
			$booking_code_determ = $afgeleverd_6;
			$type = $other_id;
		}
		elseif ($btw_aankoop == 21 && $btw_verkoop == 21)
		{
			$booking_code_determ = $afgeleverd_21;
			$type = $voeding;
		}
		else
		{
			$booking_code_determ = 0;
			$type = 0;
		}
		
		$usable_product = ($vervald1 == 0 && $vervald2 == 0 && $vervald3 == 0 && $vervald4 == 0 && $vervald5 == 0) ? false : true;
		
			$product_id = $this->products->insert(array(
					"name" => $name,
					"short_name" => $afkorting,
					"producer" => $producent,
					"supplier" => $lever,
					"buy_volume" => $aankoopeendheid,
					"buy_price" => $aankoop_prijs,
					"unit_buy" => $aankoop_unit,
					"sell_volume" => $verkoopeenheid,
					"unit_sell" => $verkoop_unit,
					"input_barcode" => $barcode_input,
					"btw_buy" => $btw_aankoop,
					"btw_sell" => $btw_verkoop,
					"limit_stock" => $drempel,
					"booking_code" => $booking_code_determ,
					"delay" => $wachttijd,
					"sellable" => ($usable_product) ? 1 : 0,
					"type" => ($usable_product) ? $type : $OK_id,
				));
			$total_products++;
			
			# add price structure
			$this->price->insert(array(
								"product_id" 	=> $product_id,
								"volume"		=> (float) $verk1,
								"price"			=> (float) $verk_pr1
							));
							
			# check if price is different for larger volume
			# if not, don't add it;
			# check for internal use products (sell price = 0)
			if ($verk_pr1 != $verk_pr2 && $verk_pr1 > 0)
			{
				$this->price->insert(array(
									"product_id" 	=> $product_id,
									"volume"		=> (float) $verk2,
									"price"			=> (float) $verk_pr2
								));
				
				if ($verk_pr2 != $verk_pr3)
				{
					$this->price->insert(array(
										"product_id" 	=> $product_id,
										"volume"		=> (float) $verk3,
										"price"			=> (float) $verk_pr3
									));
									
					if ($verk_pr3 != $verk_pr4)
					{
						$this->price->insert(array(
											"product_id" 	=> $product_id,
											"volume"		=> (float) $verk4,
											"price"			=> (float) $verk_pr4
											));
						if ($verk_pr4 != $verk_pr5)
						{
							$this->price->insert(array(
												"product_id" 	=> $product_id,
												"volume"		=> (float) $verk5,
												"price"			=> (float) $verk_pr5
												));
						}
					}
				}
			}
			
			if ($quant1 != 0)
			{

				$barcode = base_convert($timestamp, 10, 36);
				$this->barcode->generate($barcode);
				$this->stock->insert(array(
						"product_id" 	=> $product_id,
						"eol"			=> (!empty($vervald1)) ? date("Y/m/d", strtotime($vervald1)) : '',
						"location"		=> 1,
						"in_price"		=> $aankoop_prijs,
						"lotnr"			=> $lot1,
						"volume"		=> $quant1,
						"barcode"		=> $barcode						
					));
					
				# barcode generation
				$timestamp++;
			}
			
			if ($quant2 != 0)
			{
				$barcode = base_convert($timestamp, 10, 36);
				$this->barcode->generate($barcode);
				$this->stock->insert(array(
						"product_id" 	=> $product_id,
						"eol"			=> (!empty($vervald2)) ? date("Y/m/d", strtotime($vervald2)) : '',
						"location"		=> 1,
						"in_price"		=> $aankoop_prijs,
						"lotnr"			=> $lot2,
						"volume"		=> $quant2,
						"barcode"		=> $barcode						
					));
				# barcode generation
				$timestamp++;
			}
			
			if ($quant3 != 0)
			{
				
				$barcode = base_convert($timestamp, 10, 36);
				$this->barcode->generate($barcode);
				$this->stock->insert(array(
						"product_id" 	=> $product_id,
						"eol"			=> (!empty($vervald3)) ? date("Y/m/d", strtotime($vervald3)) : '',
						"location"		=> 1,
						"in_price"		=> $aankoop_prijs,
						"lotnr"			=> $lot3,
						"volume"		=> $quant3,
						"barcode"		=> $barcode						
					));
				# barcode generation
				$timestamp++;
			}
			
			if ($quant4 != 0)
			{
				$barcode = base_convert($timestamp, 10, 36);
				$this->barcode->generate($barcode);
				$this->stock->insert(array(
						"product_id" 	=> $product_id,
						"eol"			=> (!empty($vervald4)) ? date("Y/m/d", strtotime($vervald4)) : '',
						"location"		=> 1,
						"in_price"		=> $aankoop_prijs,
						"lotnr"			=> $lot4,
						"volume"		=> $quant4,
						"barcode"		=> $barcode						
					));
				# barcode generation
				$timestamp++;
			}
			
			if ($quant5 != 0)
			{
				$barcode = base_convert($timestamp, 10, 36);
				$this->barcode->generate($barcode);
				$this->stock->insert(array(
						"product_id" 	=> $product_id,
						"eol"			=> (!empty($vervald5)) ? date("Y/m/d", strtotime($vervald5)) : '',
						"location"		=> 1,
						"in_price"		=> $aankoop_prijs,
						"lotnr"			=> $lot5,
						"volume"		=> $quant5,
						"volume"		=> $quant5,
						"barcode"		=> $barcode						
					));
				# barcode generation
				$timestamp++;
			}
			
			if ($i % 100 == 0)
			{
				echo $i. "<br/>";
			}
		}
		echo "done<br/>";
		echo "total products :" . $total_products . "<br/>";
	}
	
	/*
		Helper functions
	*/
	private function get_vet($ARTS)
	{
	/*  
  'KK' => int 1  
  'ABO' => int 1  
  'ADB' => int 1
  '???' => int 1
  '19A' => int 1
  'KAB' => int 1
  'KDB' => int 1
  'KMO' => int 1
	*/

		if ($ARTS == "MJA" || $ARTS == "MAJ" || $ARTS == "mJA" || $ARTS == "mja")
		{
			$vet_id = 5;
		}
		elseif ($ARTS == "LVE" || $ARTS == "LV")
		{
			$vet_id = 6;
		}
		elseif ($ARTS == "AJA" || $ARTS == "aja" || $ARTS == "aj")
		{
			$vet_id = 4;
		}
		elseif ($ARTS == "CVH" || $ARTS == "cvh")
		{
			$vet_id = 7;
		}
		elseif ($ARTS == "KB" || $ARTS == "KBO" || $ARTS == "KK")
		{
			$vet_id = 12;
		}
		elseif ($ARTS == "OPE")
		{
			$vet_id = 13;
		}
		else
		{
			$vet_id = 1;
		}
		return $vet_id;
	}
	
	private function get_pract($IDPRAK)
	{
			switch (strtolower($IDPRAK))
			{
				case "w" :
					$praktijk = 1;
					break;
				case "s" :
					$praktijk = 2;
					break;
				case "a" :
					$praktijk = 3;
					break;
				case "n" :
					$praktijk = 4;
					break;
				default :
					$praktijk = 1;
			}
		return $praktijk;
	}
}