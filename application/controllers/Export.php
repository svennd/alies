<?php
defined('BASEPATH') or exit('No direct script access allowed');

# export xml based on Kluwers Expert/M :
# 		https://123support.wolterskluwer.be/files/images/pdf/manual/xml_import_nl.pdf
#
class Export extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Events_model', 'events');
		$this->load->model('Booking_code_model', 'booking');
	}


	/*
		generic export function for owners
		following import_export_alies.docx guidelines
	 */
	public function owners($days = false)
	{
		$clients = $this->get_owners($days);

		if (!$clients) { return $this->empty_xml('owners'); }

		$domtree = new DOMDocument('1.0');

		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");

		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);

		// owners
		$owners = $domtree->createElement("owners");
		$owners = $xmlRoot->appendChild($owners);

		foreach ($clients as $client) {
			/* set item */
			$cust = $domtree->createElement("owner");
			$cust = $owners->appendChild($cust);
			$this->append_child_element($cust, $domtree,
				array(
							'id' 			=> $client['id'],
							'first_name' 	=> htmlspecialchars($client['first_name']),
							'last_name' 	=> htmlspecialchars($client['last_name']),
							'telephone' 	=> $client['telephone'],
							'mobile' 		=> $client['mobile'],
							'phone2' 		=> $client['phone2'],
							'phone3' 		=> $client['phone3'],
							'street' 		=> $client['street'],
							'nr'		 	=> $client['nr'],
							'zip' 			=> $client['zip'],
							'city' 			=> $client['city'],
							'mail' 			=> $client['mail'],
							'msg' 			=> $client['msg'],
							'btw_nr'		=> $client['btw_nr'],
							'invoice_addr'	=> $client['invoice_addr'],
							'invoice_contact' => $client['invoice_contact'],
							'invoice_tel'	=> $client['invoice_tel'],
							'debts'			=> $client['debts'], 
							'low_budget'	=> $client['low_budget'], 
							'contact'		=> $client['contact'], 
							'last_bill'		=> $client['last_bill'], 

				));
		}
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}

	/*
		generic export function for pets
		following import_export_alies.docx guidelines
	 */
	public function pets($days = false)
	{
		$pets = $this->get_pets($days);
		
		if (!$pets) { return $this->empty_xml('pets'); }

		$domtree = new DOMDocument('1.0');

		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");

		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);

		// owners
		$petsx = $domtree->createElement("pets");
		$petsx = $xmlRoot->appendChild($petsx);

		foreach ($pets as $pet) {
			/* set item */
			$cust = $domtree->createElement("pet");
			$cust = $petsx->appendChild($cust);
			$this->append_child_element($cust, $domtree,
				array(
							'id' 			=> $pet['id'],
							'type'			=> $pet['type'],
							'name'			=> $pet['name'],
							'birth' 		=> $pet['birth'],
							'death' 		=> $pet['death'],
							'death_date' 	=> $pet['death_date'],
							'breed'		 	=> $pet['breed'],
							'gender' 		=> $pet['gender'],
							'color' 		=> $pet['color'],
							'last_weight' 	=> $pet['last_weight'],
							'lost' 			=> $pet['lost'],
							'chip'			=> $pet['chip'],
							'nr_vac_book'	=> $pet['nr_vac_book'],
							'note' 			=> $pet['note'],
							'nutritional_advice' => $pet['nutritional_advice'],
							'owner'			=> $pet['owner'], 
							'location'		=> $pet['location'], 
							'init_vet'		=> $pet['init_vet']
				));
		}
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}
	
	public function clients($days = false, int $status = 0)
	{
		# owners
		$clients = $this->get_owners($days);

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
							'Language' 		=> 1, // we have a field $client['language'] but its not implemented 
												  // Kluwer has : 1: NL, 2: FR, 3: EN, 4: DE
							'CurrencyCode' 	=> 'EUR',
							'VATCode' 		=> 1,
							'VATStatus'		=> (($client['btw_nr']) ? 1 : 0 ),
							'VATNumber'		=> (($client['btw_nr']) ? trim($client['btw_nr'], "BE") : ''),
							'Phone'			=> (($client['telephone']) ? $client['telephone'] : ''),
							'GSM'			=> (($client['mobile']) ? $client['mobile'] : ''),
							'Email'			=> (($client['mail']) ? $client['mail'] : ''),
							'FreeField1'	=> (($client['msg']) ? $client['msg'] : ''),
							'Status'		=> $status, // status : 0 : new, 1 : already known, 2 : known but editted

				));
		}
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}

	public function facturen($search_from, $search_to, int $status = 0)
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
					$event_tally[$btw] = (isset($event_tally[$btw])) ? $event_tally[$btw] + $value : $value;
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
							'Status' 			=> $status,

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
								// 1:0%, 2:6%, 3:12%, 4:21%

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
			if (empty($value) && $value != "0") {
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

	/*
		something is wrong (false) so return a empty file
	*/
	private function empty_xml($type = "")
	{
		Header('Content-type: text/xml');
		echo "<?xml version='1.0'?><xml><" . $type . "></" . $type . "></xml>";
	}

	/*
		return a list of owners
	*/
	private function get_owners($days = false)
	{
		# owners
		if (!$days) {
			return $this->owners->get_all();
		}

		return $this->owners
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

	/*
		return a list of pets
	*/
	private function get_pets($days = false)
	{
		# owners
		if (!$days) {
			return $this->pets->get_all();
		}

		return $this->pets
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
}
