<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Export extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Events_model', 'events');
		$this->load->model('Booking_code_model', 'booking');
	}
	
	public function clients($days = false)
	{
		# owners
		if (!$days) {
			$clients = $this->owners->get_all();
		} else {
			$clients = $this->owners
							->group_start()
								->where('created_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
								->where('created_at < NOW()', null, null, false, false, true)
							->group_end()
							->or_group_start()
								->where('updated_at > DATE_ADD(NOW(), INTERVAL -' .  $days. ' DAY)', null, null, false, false, true)
								->where('updated_at < NOW()', null, null, false, false, true)
							->group_end()
							->get_all();
		}
	
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'Windows-1252');

		/* create the root element of the xml tree */
		$xmlRoot = $this->MPlus_headers($domtree);
		
		/* set template */
		$Customers = $domtree->createElement("Customers");
		$Customers = $xmlRoot->appendChild($Customers);
		
		foreach ($clients as $client) {
			/* set item */
			$cust = $domtree->createElement("Customer");
			$cust = $Customers->appendChild($cust);
			
			$this->append_child_element($cust, $domtree, 
				array(
							'Prime' 		=> $client['id'],
							'Name' 			=> htmlspecialchars($client['last_name']),
							'Country'		=> 'BE',
							'Street' 		=> $client['street'],
							'HouseNumber' 	=> $client['nr'],
							'ZipCode' 		=> $client['zip'],
							'City' 			=> $client['city'],
							'Language' 		=> $client['language'],
							'CurrencyCode' 	=> 'EUR',
							'VATCode' 		=> '1',
							'VATStatus'		=> (($client['btw_nr']) ? '1' : '0' ),
							'VATNumber'		=> (($client['btw_nr']) ? $client['btw_nr'] : ''),
							'Phone'			=> (($client['telephone']) ? $client['telephone'] : ''),
							'GSM'			=> (($client['mobile']) ? $client['mobile'] : ''),
							'Email'			=> (($client['mail']) ? $client['mail'] : ''),
							'FreeField1'	=> (($client['msg']) ? $client['msg'] : ''),
							'Status'		=> '1', // status : 0 : new, 1 : already known, 2 : known but editted 
							
				));
		}
		
		/* get the xml printed */
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}
	
	public function facturen($search_from, $search_to)
	{
		$bill_overview = $this->bills
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->order_by('created_at', 'asc')
			->get_all();
		
		if (!$bill_overview) {
			echo "No valid result to be shown.";
			return false;
		}
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'Windows-1252');

		/* create the root element of the xml tree */
		$xmlRoot = $this->MPlus_headers($domtree);

		/* set template */
		$Sales = $domtree->createElement("Sales");
		$Sales = $xmlRoot->appendChild($Sales);
		
		foreach ($bill_overview as $factuur) {
			/*
				required :
					- customer_prime 	: customer id
					- doctype			: 10 = factuur 30 = creditnota
					- docnumber			: factuurnr : Y + (padding)(int)
					- amount			:
					- status			: (0 : niet geimporteerd, 1 : geimporteerd, 2 : gewijzigt geimporteerd)
						details
						- acount		: centralisatierekening / grootboekrekening
						- amount		:
						- debcre		: 1 = D -1 = C
						- ventil		:  1 = 0%, 2 = 6%, 3 = 12%, 4 = 21%

				marc :
					- accountingperiod	: Boekhoudperiode
					- VATMonth			: Btw-periode
					- Year_Alfa 		: Jaar

			*/
			$bill_id = $factuur['id'];
		
			/* query events */
			$events = $this->events
				->where(array("payment" => $bill_id))
				->fields('id, pet, payment')
				->get_all();
			
			/* if events fail don't even book this */
			if (!$events) {
				continue;
			}

			$event_tally = array();
			$event_booking = array();
		
			foreach ($events as $e) {
				$event_bill = $this->events->get_products_and_procedures($e['id']);
			
				if (count($event_bill['tally']) == 0) {
					continue;
				}
			
				foreach ($event_bill['tally'] as $btw => $value) {
					$event_tally[$btw] =  (isset($event_tally[$btw])) ? $event_tally[$btw] + $value : $value;
				}
				foreach ($event_bill['booking'] as $booking => $value) {
					$event_booking[$booking] = (isset($event_booking[$booking])) ? $event_booking[$booking] + $value: $value;
				}
			}
			
			/* set item */
			$Sale = $domtree->createElement("Sale");
			$Sale = $Sales->appendChild($Sale);
		
			/*
				meta data about the bill

				required :
					- Customer_Prime : client id
					- DocType : 10 factuur, 30 creditnota
					- DocNumber : factuurnr
					- Amount : , for decimal eg. 11,11
					- Status : 0 not imported, 1 imported, 2 imported but changed

				optional and used here :
					- DocDate : DD/MM/YYYY
					- VATAmount

				optional :
					- Journal_Prime (int)
					- AccountingPeriod (int)
					- VATMonth (YYYYMM) (?)
					- Year_Alfa (YYYY)
					- CurrencyCode EUR
					- DueDate : DD/MM/YYYY
					- OurRef
					- YourRef
					- Discount
					- Ventil (?)

			*/
			$dt = DateTime::createFromFormat('Y-m-d H:i:s', $factuur['created_at']);
			
			# btw account
			$total_btw = (float) 0.0;
			foreach ($event_tally as $btw => $tally) {
				$total_btw += round(($btw/100)*$tally, 2);
			}
			
			$this->append_child_element($Sale, $domtree, 
				array(
							'Customer_Prime'	=> $factuur['owner_id'],
							'DocType' 			=> '10',
							'DocNumber'			=> date("Y") . substr(str_pad($factuur['id'], 5, "0", STR_PAD_LEFT), 0, 5),
							'DocDate' 			=> $dt->format('d/m/Y'),
							'Amount' 			=> $this->amount($factuur['amount']),
							'VATAmount' 		=> $this->amount($total_btw),
							'Status' 			=> '0',
							
				));
			
			/*
				a line for everything on the bill

				required :
					- account
					- amount
					- DebCre : 1 (for money received) -1 (for money spend)
					- Ventil : ventil code : 0 : no ventil, 1 : 0%, 2 = 6%, 4 = 21%, 11 = btw

				optional :
					- ref
			*/
			
			// group details
			$Details = $domtree->createElement("Details");
			$Details = $Sale->appendChild($Details);
			
			// element detail : full amount
			$detail = $domtree->createElement("Detail");
			$detail = $Details->appendChild($detail);
			
			$this->append_child_element($detail, $domtree, 
				array(
							'Account'			=> 400000, // 400000 == booking of full amount
							'Amount' 			=> $this->amount($factuur['amount']),
							'DebCre'			=> 1,
							'Ventil' 			=> 0, // for full amount this must be 0
							
				));
				
			$detail = $domtree->createElement("Detail");
			$detail = $Details->appendChild($detail);
			
			$this->append_child_element($detail, $domtree, 
				array(
							'Account'			=> 451000, // 451000 == btw to pay
							'Amount' 			=> $this->amount($total_btw),
							'DebCre'			=> -1,
							'Ventil' 			=> 11, // btw
							
				));
			
			# booking codes
			foreach ($event_booking	as $booking => $tally) {
				$current_booking_code = $this->booking->fields('code, btw')->get($booking);

				$detail = $domtree->createElement("Detail");
				$detail = $Details->appendChild($detail);
				
				$this->append_child_element($detail, $domtree, 
					array(
								'Account'			=> $current_booking_code['code'],
								'Amount' 			=> $this->amount($tally),
								'DebCre'			=> -1,
								'Ventil' 			=> $this->get_btw_id($current_booking_code['btw']), // btw
								
					));
			}
		}
		/* get the xml printed */
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}
	
	/*
		btw codes in Belgium
	*/
	private function get_btw_id($btw)
	{
		if ($btw == 0) {
			return 1;
		} elseif ($btw == 6) {
			return 2;
		} elseif ($btw == 12) {
			return 3;
		} elseif ($btw == 21) {
			return 4;
		}
		
		return -1;
	}
	
	/*
		loop through the data and put it in childs
	*/
	private function append_child_element($father, $domtree, $data)
	{
		foreach ($data as $key => $value)
		{
			// if no value skip it
			if (empty($value)) { 
				continue; 
			}
			$father->appendChild($domtree->createElement($key, $value));
		}
	}
	
	/*
		change number formatting
	*/
	private function amount($value)
	{
		return number_format($value, 2, ',', '');
	}
	
	/*
		headers specific to Expert/M bookkeeping
	*/
	private function MPlus_headers($domtree)
	{
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("ImportExpMPlus");
		
		/* attributes */
		$schema_xsi = $domtree->createAttribute('xmlns:xsi');
		$schema_xsi->value = 'http://www.w3.org/2001/XMLSchema-instance';
			
		$schema_xsd = $domtree->createAttribute('xmlns:xsd');
		$schema_xsd->value = 'http://www.w3.org/2001/XMLSchemae';
		
		$xmlRoot->appendChild($schema_xsi);
		$xmlRoot->appendChild($schema_xsd);
		
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		return $xmlRoot;
	}
}
