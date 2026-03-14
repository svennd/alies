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
	
	// data paths - should be in config somewhere
	private const RX_DIR = "data/stored/rx/";
	private const PRICELIST_DIR = "data/stored/pricelist/";
	private const DELIVERY_DIR = "data/stored/delivery/";
	private const MONTHLY_PRODUCT_USAGE_DIR = "data/stored/reports/monthly_product_usage/";
	
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
		$this->load->model('Log_stock_model', 'log_stock');
		$this->load->model('Products_model', 'products');
		$this->load->model('Rx_model', 'rx');

		$this->load->model('Vamreg_index_model', 'vamreg_index');
		$this->load->model('Vamreg_in_buffer_model', 'vamreg_in_buffer');

		$this->load->model('Config_model', 'settings');
        $conf = $this->settings->get_all();
		if ($conf) {
			foreach ($conf as $c) {
				$this->conf[$c['name']] = base64_decode($c['value']);
			}
		}

		# helpers
		$this->load->helper('file');
    }

	/*
	* function: index
	* show the available functions
	*/
	public function index()
	{
		echo "Welcome to alies, cli\n";
		echo "functions :\n";
		echo "  - delivery [filename] : import delivery file (covetrus)\n";
		echo "  - pricelist [filename] : import pricelist file (covetrus)\n";
		echo "  - stock_clean : attempt to fix stock issues\n";
		echo "  - autoclose : auto close events\n";
		echo "  - prune : prune old logs\n";
		echo "  - auto_death : auto death pets\n";
		echo "  - import_rx : scan files for new rx images\n";
		echo "  - recalculate_usage : recalculate product usage\n";
		echo "  - monthly_product_usage_dump [YYYY-MM] [force=0|1] : create immutable monthly product usage csv\n";
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
		$this->recalculate_usage();

		# check if we need to run the monthly product usage dump
		$this->monthly_product_usage_dump();

		# cleanup api rate limits
		$this->cleanup_rate_limits();

		# pull new data from vamreg + set ab status for products
		$this->vamreg_update();
	}

	/*
	* function: monthly_product_usage_dump
	* create immutable monthly product usage csv
	* note : this is a protection
	*/
	public function monthly_product_usage_dump($month = null, $force = 0)
	{
		$force = ((int) $force) === 1;
		$headers = [];

		if ($month) {
			$period = DateTimeImmutable::createFromFormat('Y-m-d', $month . '-01');
			if (!$period || $period->format('Y-m') !== $month) {
				echo "Invalid month, expected YYYY-MM\n";
				return false;
			}
		} else {
			if (!$force && date('d') !== '01') {
				echo "Skip monthly product usage dump: today is not the first of the month.\n";
				return false;
			}

			$period = new DateTimeImmutable('first day of last month');
		}

		$search_from = $period->format('Y-m-01');
		$search_to = $period->format('Y-m-t');
		$snapshot_month = $period->format('Y-m');
		$generated_at = date('Y-m-d H:i:s');

		$target_dir = SELF::MONTHLY_PRODUCT_USAGE_DIR;
		$filename = 'monthly_product_usage_' . $snapshot_month . '.csv';
		$file = $target_dir . $filename;

		if (is_file($file) && !$force) {
			echo "Monthly product usage dump already exists: " . $file . "\n";
			return $file;
		}

		if (!is_dir($target_dir) && !mkdir($target_dir, 0700, true) && !is_dir($target_dir)) {
			echo "Unable to create monthly product usage directory: " . $target_dir . "\n";
			return false;
		}

		$rows = $this->events->register_out_snapshot($search_from, $search_to, $snapshot_month, $generated_at);
		$headers = !empty($rows) ? array_keys($rows[0]) : [];

		$handle = fopen($file, 'w');
		if ($handle === false) {
			echo "Unable to write monthly product usage dump: " . $file . "\n";
			return false;
		}

		fputcsv($handle, $headers);
		foreach ($rows as $row) {
			$line = array();
			foreach ($headers as $header) {
				$value = isset($row[$header]) ? $row[$header] : '';
				if (is_string($value)) {
					$value = preg_replace("/[\r\n]+/", ' ', $value);
				}
				$line[] = $value;
			}
			fputcsv($handle, $line);
		}
		fclose($handle);

		$msg = 'created ' . $filename . ' with ' . count($rows) . ' rows for ' . $snapshot_month;
		echo "Monthly product usage dump " . $msg . "\n";
		$this->logs->logger(INFO, 'monthly_product_usage_dump', $msg);

		return $file;
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
    cleanup api rate limits
    */
    public function cleanup_rate_limits()
    {
        $this->load->model('ApiRate_model');
        $this->ApiRate_model->cleanup();
    }

	/*
	* function: delivery
	* import delivery file from covetrus
	*/
	public function delivery($filename)
	{
        /* normalize header names */
        $map = [
            'Besteldatum'        => 'order_date',
            'Bestelbonnr'        => 'order_nr',
            'Mijn Referentie'    => 'my_ref',
            'Art. nr.'           => 'wholesale_artnr',
            'Omschrijving'       => 'wholesale_art_name',
            'CNK nummer'         => 'CNK_nummer',
            'Leveringsdatum'     => 'delivery_date',
            'Leveringsbon nummer'=> 'delivery_nr',
            'bruto prijs'        => 'bruto_price',
            'netto prijs'        => 'netto_price',
            'BTW'                => 'btw',
            'aantal'             => 'amount',
            'Lotnummer'          => 'lotnr',
            'Vervaldatum'        => 'due_date',
            'Facturatie'         => 'billing',
        ];

        $file = SELF::DELIVERY_DIR . $filename;

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
		$header = fgetcsv($handle, 0, "|");
        /* build index */
        $idx = [];
        foreach ($header as $i => $h) {
            if (isset($map[$h])) {
                $idx[$map[$h]] = $i;
            }
        }

		while (($row = fgetcsv($handle, 0, "|")) !== false) {

        $order_date      = isset($idx['order_date'])      ? ($row[$idx['order_date']] ?? null) : null;
        $order_nr        = isset($idx['order_nr'])        ? ($row[$idx['order_nr']] ?? null) : null;
        $my_ref          = isset($idx['my_ref'])          ? ($row[$idx['my_ref']] ?? null) : null;
        $wholesale_artnr = isset($idx['wholesale_artnr']) ? ($row[$idx['wholesale_artnr']] ?? null) : null;
        $CNK_nummer      = isset($idx['CNK_nummer'])      ? ($row[$idx['CNK_nummer']] ?? null) : null;
        $delivery_date   = isset($idx['delivery_date'])   ? ($row[$idx['delivery_date']] ?? null) : null;
        $delivery_nr     = isset($idx['delivery_nr'])     ? ($row[$idx['delivery_nr']] ?? null) : null;
        $bruto_price     = isset($idx['bruto_price'])     ? ($row[$idx['bruto_price']] ?? null) : null;
        $bruto_price     = isset($idx['bruto_price'])     ? (str_replace(',', '.', $row[$idx['bruto_price']]) ?? null) : null;
        $netto_price     = isset($idx['netto_price'])     ? (str_replace(',', '.', $row[$idx['netto_price']]) ?? null) : null;
        $btw             = isset($idx['btw'])             ? ($row[$idx['btw']] ?? null) : null;
        $amount          = isset($idx['amount'])          ? ($row[$idx['amount']] ?? null) : null;
        $lotnr           = isset($idx['lotnr'])           ? ($row[$idx['lotnr']] ?? null) : null;
        $due_date        = isset($idx['due_date'])        ? ($row[$idx['due_date']] ?? null) : null;
        $billing         = isset($idx['billing'])         ? ($row[$idx['billing']] ?? null) : null;


            $x = $this->wholesale->fields('id')->where(array("vendor_id" => $wholesale_artnr))->get();
            $id = ($x) ? $x['id'] : null;

            # dates
            $dt_order_date = DateTime::createFromFormat('j/m/Y', $order_date);
            $dt_delivery_date = DateTime::createFromFormat('j/m/Y', $delivery_date);
            $dt_due_date = DateTime::createFromFormat('j/m/Y', $due_date);
                        
            $this->delivery->insert(array(
                "order_date" 			=> ($dt_order_date) ? $dt_order_date->format('Y-m-d') : "",
                "order_nr" 				=> $order_nr,
                "my_ref" 				=> $my_ref,
                "wholesale_artnr" 		=> $wholesale_artnr,
                "wholesale_id"			=> $id,
                "CNK"					=> $CNK_nummer,
                "delivery_date" 		=> ($dt_delivery_date) ? $dt_delivery_date->format('Y-m-d') : "",
                "delivery_nr" 			=> $delivery_nr,
                "bruto_price" 			=> $bruto_price,
                "netto_price" 			=> $netto_price,
                "amount" 				=> $amount,
                "lotnr" 				=> $lotnr,
                "due_date" 				=> ($dt_due_date) ? $dt_due_date->format('Y-m-d') : "",
                "btw" 					=> $btw,
                "billing"				=> $billing
            ));

            # check if this product is vamreg required
            $hit = $this->vamreg_index->where(array("cnk" => $CNK_nummer))->get();

            # it is in the list
            if ($hit)
            {
                // echo "FOUND A product for Vamreg CNK: " . $CNK_nummer . " id: " . $id . "\n";
                
                // add to buffer
                $this->vamreg_in_buffer->insert(array(
                    "cnk"                       => $CNK_nummer,
                    "wholesale_id"              => $id,
                    "in_quantity_pack_count"    => $amount,
                    "delivery"                  => ($dt_delivery_date) ? $dt_delivery_date->format('Y-m-d') : "",
                    "product_type"              => "BE", # assumption
                    "provider_type"             => "DIST_BE", # assumption
                    "status"                    => "DRAFT",
                ));
            }

            # in some weird cases
            # eg: when a tax is added but not shown in bruto_price
            if ($netto_price > $bruto_price && $id && !$netto_price)
            {
                $this->wholesale->update(array("netto_overflow" => $netto_price), $id);
                $this->logs->logger(WARN, "delivery", "netto overflow: " . $netto_price . " > " . $bruto_price . " for id: " . $id);
            }
            $line++;
		}
        fclose($handle);
        if(!move_file($file, SELF::DELIVERY_DIR . 'processed/' . $filename))
        {
            echo "ERROR : issue moving file\n";
        }
        echo "in ". $filename . " lines : " . $line . "\n";

		$this->logs->logger(INFO, "import_delivery", "file: " . $filename . " lines: " . $line);
	}
    

	/*
	* function: pricelist
	* import pricelist file from covetrus
	*/
    public function pricelist(string $filename)
    {
        $file = SELF::PRICELIST_DIR . $filename;

		# check if the file exists
		if(!is_readable($file) && !is_file($file))
		{
			echo "File not found : " . $file . "\n";
			return;
		}

        # open the file and read line by line
		$handle = fopen($file, 'r');
		$line = 0;

		# pop header
		fgetcsv($handle, 0, "|");
		
		while (($row = fgetcsv($handle, 0, "|")) !== FALSE) 
		{
			# get the data
			list(
                $wholesale_artnr,
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
                
                $this->wholesale->update_record(
					$wholesale_artnr, 
					$omschrijving, 
					$bruto_prijs, 
					$btw, 
					$verk_pr_apotheek, 
					$verdeler, 
					$CNK_nummer, 
					$VHB, 
					$distr_id, 
					$group
				);

				if($line % 100 == 0) { echo $line . "\n"; usleep(500000); }
                $line++;
		}
        fclose($handle);
        if(!move_file($file, SELF::PRICELIST_DIR . 'processed/' . $filename))
        {
			$this->logs->logger(ERROR, "import_pricelist", "issue moving file");
        }
        echo "lines : " . $line . "\n";
		$this->logs->logger(INFO, "import_pricelist", "lines: " . $line);
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

		/*
		* will clean up products that have been removed, but still have stock
		* this would result in errors
		*/
		$removed_products = $this->stock->remove_dead_products_from_stock();
		
		# remove_dead_products_from_stock already logs this
		# but this gives back the amount of removed products
		if ($removed_products >= 1)
		{
			$this->logs->logger(WARN, "remove_dead_products_from_stock", "archived: " . $removed_products);
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
	* function: recalculate_usage
	* recalculate usage for all products
	* this is used to update the usage of products based on the stock history
	*/
	public function recalculate_usage()
	{
		// recalculate usage for all products
		$affected = $this->stock->recalculate_usage();

		// log this
		$this->logs->logger(INFO, "recalculate_usage", "recalculated usage for all products (" . $affected . " affected)");
	}

    public function vamreg_update()
    {

		$this->load->library('vamreg_sync');
		$sync_status = $this->vamreg_sync->sync_medicinal_products(
			$this->conf['vamreg_api_key'],
			$this->conf['vamreg_push']
		);
        $this->logs->logger(INFO, "vamreg", "synchronized medicinal products from Vamreg : " . ($sync_status ? "success" : "failure"));

        $affected = $this->vamreg_index->set_ab_status_on_product();

        // log this
        $this->logs->logger(INFO, "vamreg", "set AB status for buffer items (" . $affected . " affected)");
    }

	/*
	* function: import_rx
	* scan files for new rx images
	*/
	public function import_rx($do_full_scan = false) {
	
		if ($do_full_scan)
		{

			$dirs = glob(SELF::RX_DIR . "/*", GLOB_ONLYDIR);
		}
		else
		{
			# only scan for last and this month (multiple dates still possible)
			$pattern = SELF::RX_DIR . "{" . date("Y-m", strtotime("-1 month")) . "," . date("Y-m") . "}*";
			$dirs = glob($pattern, GLOB_ONLYDIR|GLOB_BRACE);
			
		}

		foreach ($dirs as $dir)
		{
			# iterate over files
			$files = glob($dir . "/*.jpg");
			foreach ($files as $file)
			{
				# get the file info
				list($path, $basename, $ext, $name) = array_values(pathinfo($file));
				
				# check if file is already in the db
				$exists = $this->rx->where(array("path" => basename($path) . '/' . $name))->get();
				if ($exists) { continue; }

				# get the meta data
				if (is_file($path . '/' . $name. '.txt')) {
					
					$meta_content = trim(file_get_contents($path . '/' . $name . '.txt'));
					foreach (explode("\n", $meta_content) as $line)
					{
						list($key, $value) = explode(":", $line);
						if ($key == "PatientName")
						{
							$patient_pet = explode("^", $value);
							if (count($patient_pet) != 2)
							{
								$meta['client'] = trim($value);
								$meta['petname'] = "";
							}
							else
							{
								$meta['client'] = trim($patient_pet[0]);
								$meta['petname'] = trim($patient_pet[1]);
							}
						}
						elseif ($key == "PatientSex")
						{
							$meta['gender'] = (trim($value) == "F") ? FEMALE : MALE;
						}
						else
						{
							$meta[$key] = trim($value);
						}
					}

					# insert into database
					$this->rx->insert(array(
											"path" 			=> basename($path) . '/' . $name,
											"pet_id"		=> $meta['PatientID'],

											"studydate"		=> $meta['StudyDate'],
											"description"	=> $meta['SeriesDescription'],
											"bodypart"		=> $meta['BodyPartExamined'],
											"client"		=> $meta['client'],
											"petname" 		=> $meta['petname'],
											"gender" 		=> $meta['gender'],
											"petbirthdate" 	=> $meta['PatientBirthDate'],
											"studydescription"	=> $meta['StudyDescription'],
											"series"		=> $meta['SeriesNumber']
					));

				}
			}
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