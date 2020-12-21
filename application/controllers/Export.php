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

		$Customers = $domtree->createElement("Customers");
		$Customers = $xmlRoot->appendChild($Customers);
		

		foreach ($clients as $client) {
			$cust = $domtree->createElement("Customer");
			$cust = $Customers->appendChild($cust);
			
			$cust->appendChild($domtree->createElement('Prime', $client['id']));
			// $cust->appendChild($domtree->createElement('Name', htmlspecialchars($client['first_name'] . ' ' . $client['last_name'])));
			$cust->appendChild($domtree->createElement('Name', htmlspecialchars($client['last_name'])));
			$cust->appendChild($domtree->createElement('Country', 'BE'));
			$cust->appendChild($domtree->createElement('Street', $client['street']));
			$cust->appendChild($domtree->createElement('HouseNumber', $client['nr']));
			$cust->appendChild($domtree->createElement('ZipCode', $client['zip']));
			$cust->appendChild($domtree->createElement('City', $client['city']));
			$cust->appendChild($domtree->createElement('Language', $client['language']));
			$cust->appendChild($domtree->createElement('CurrencyCode', 'EUR'));
			$cust->appendChild($domtree->createElement('VATCode', '1'));
			if ($client['btw_nr']) {
				$cust->appendChild($domtree->createElement('VATStatus', '1'));
			} else {
				$cust->appendChild($domtree->createElement('VATStatus', '0'));
			}
			if ($client['btw_nr']) {
				$cust->appendChild($domtree->createElement('VATNumber', $client['btw_nr']));
			}
			if ($client['telephone']) {
				$cust->appendChild($domtree->createElement('Phone', $client['telephone']));
			}
			if ($client['mobile']) {
				$cust->appendChild($domtree->createElement('GSM', $client['mobile']));
			}
			if ($client['mail']) {
				$cust->appendChild($domtree->createElement('Email', $client['mail']));
			}
			if ($client['msg']) {
				$cust->appendChild($domtree->createElement('FreeField1', $client['msg']));
			}
			$cust->appendChild($domtree->createElement('Status', '1'));
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
			exit;
		}
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'Windows-1252');

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
			
			$Sale->appendChild($domtree->createElement('Customer_Prime', $factuur['owner_id']));
			$Sale->appendChild($domtree->createElement('DocType', 10));
			$Sale->appendChild($domtree->createElement('DocNumber', date("Y") . substr(str_pad($factuur['id'], 5, "0", STR_PAD_LEFT), 0, 5)));
			$Sale->appendChild($domtree->createElement('DocDate', $dt->format('d/m/Y')));
			$Sale->appendChild($domtree->createElement('Amount', $this->amount($factuur['amount'])));
			$Sale->appendChild($domtree->createElement('VATAmount', $this->amount($total_btw)));
			$Sale->appendChild($domtree->createElement('Status', 0));
			
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
			
			$detail->appendChild($domtree->createElement('Account', 400000)); // 400000 == booking of full amount
			$detail->appendChild($domtree->createElement('Amount', $this->amount($factuur['amount'])));
			$detail->appendChild($domtree->createElement('DebCre', 1));
			$detail->appendChild($domtree->createElement('Ventil', 0)); // for full amount this must be 0
				
			$detail = $domtree->createElement("Detail");
			$detail = $Details->appendChild($detail);
			
			$detail->appendChild($domtree->createElement('Account', 451000)); // 451000 == btw to pay
			$detail->appendChild($domtree->createElement('Amount', $this->amount($total_btw)));
			$detail->appendChild($domtree->createElement('DebCre', -1));
			$detail->appendChild($domtree->createElement('Ventil', 11)); // btw
			
			# booking codes
			foreach ($event_booking	as $booking => $tally) {
				$current_booking_code = $this->booking->fields('code, btw')->get($booking);

				$detail = $domtree->createElement("Detail");
				$detail = $Details->appendChild($detail);
			
				if ($current_booking_code['btw'] == 0) {
					$current_btw = 1;
				} elseif ($current_booking_code['btw'] == 6) {
					$current_btw = 2;
				} elseif ($current_booking_code['btw'] == 12) {
					$current_btw = 3;
				} elseif ($current_booking_code['btw'] == 21) {
					$current_btw = 4;
				}
					
				$detail->appendChild($domtree->createElement('Account', $current_booking_code['code']));
				$detail->appendChild($domtree->createElement('Amount', $this->amount($tally)));
				$detail->appendChild($domtree->createElement('DebCre', -1));
				$detail->appendChild($domtree->createElement('Ventil', $current_btw));
			}
		}
		/* get the xml printed */
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}
	
	private function amount($value)
	{
		return number_format($value, 2, ',', '');
	}
}
