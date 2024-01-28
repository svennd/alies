<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cli extends CI_Controller {

    public $delivery;
    public $wholesale;

    public function __construct() {
        parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Delivery_model', 'delivery');
    }

	public function cli_delivery($filename)
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

	# truncate wholesale_price; truncate wholesale_type;truncate wholesale;
    public function cli_pricelist($filename)
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

    private function move_file(string $path, string $to) : bool {
        if(copy($path, $to)){
            unlink($path);
            return true;
        } else {
            return false;
        }
    }
}