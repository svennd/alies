<?php
defined('BASEPATH') or exit('No direct script access allowed');

# export xml based on Kluwers Expert/M :
# 		https://123support.wolterskluwer.be/files/images/pdf/manual/xml_import_nl.pdf
#
// Class: Export
class Export extends Admin_Controller
{

	// initialize
	public $bills, $owners, $pets, $events, $booking;

	// ci specific
	public $input;

	# probably needs to be a configuration or something
	private string $invoice_storage_path = "data/stored/.invoices/";

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

		// merging pdf
		$this->load->library('pdf'); 
	}


	/*
	* function: index
	* list of income w/ export function
	* (nowhere linked)
	*/
	public function index()
	{
        
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-01") : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-t") : $this->input->post('search_to');

		# bills -> in pdf & kluwer xml
		$bills = $this->bills->get_yearly_earnings_by_date($search_from, $search_to);
		$checks = $this->bills
			->where('invoice_id IS NULL', null, null, false, false, true)
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->count_rows();

		# clients
		# pets ?

		$data = array(
			"checks" 		=> $checks,
			"bills" 		=> ($bills) ? $bills[0] : false,
			"search_from"	=> $search_from,
			"search_to"		=> $search_to,
		);

		$this->_render_page('export/index', $data);
	}

	/*
	* function: owners
	* generic export function for owners
	* following import_export_alies.docx guidelines
	*/
	public function owners($search_from, $search_to)
	{
		$clients = $this->get_owners($search_from, $search_to);

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
	* function: pdf
	* export invoices into a single pdf
	*/
	public function pdf($search_from, $search_to)
	{
		$bill_overview = $this->bills
			->fields("id, invoice_id, invoice_date")
			->where('invoice_id IS NOT NULL', null, null, false, false, true)
			->where('invoice_date > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('invoice_date < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->order_by('invoice_date', 'asc')
			->get_all();

		$warning = false;
		# check if pdf already exists
		foreach ($bill_overview as $bill)
		{
			$invoice_date = $bill['invoice_date'];
			$time 		= strtotime($invoice_date);
			$filename 	= "bill_" . get_invoice_id($bill['invoice_id'], $invoice_date, $this->conf['invoice_prefix']['value']) . '.pdf';
			$path 		= $this->invoice_storage_path . date('Y', $time) . '/' . date('W', $time) . '/';

			if(!file_exists($path . $filename))
			{
				echo "warning : <a href='" . base_url('invoice/get_bill/' . $bill["id"]) . "'>" . $filename . "</a> not found, click this link to generate and try again.<br />";
				$warning = true;
			}
			$list[] = $path . $filename;
		}

		if ($warning)
		{
			exit;
		}
		$filename = $this->pdf->merge_pdf($this->invoice_storage_path . "list" . $search_from . "_" . $search_to . ".pdf", $list);

		// open this new file
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($filename));
		@readfile($filename);
		exit;
	}

	/*
	* function: csv
	* export invoices into a single csv
	*/
	public function csv($search_from, $search_to)
	{
		$bill_overview = $this->bills
			->with_owner('fields:id, last_name')
			->with_vet('fields:id, first_name')
			->with_location('fields:id, name')
			->where('invoice_id IS NOT NULL', null, null, false, false, true)
			->where('invoice_date > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('invoice_date < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->order_by('invoice_date', 'asc')
			->get_all();

		echo "invoice_id,invoice_date,total_brut,total_net,BTW_0,BTW_6,BTW_21,cash,card,transfer,modified,owner_id,owner_last_name,vet_first_name,location_name\n";
		foreach($bill_overview as $bill)
		{
			echo get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']) . ",";
			echo $bill['invoice_date'] . ",";
			echo $bill['total_brut'] . ",";
			echo $bill['total_net'] . ",";
			echo $bill['BTW_0'] . ",";
			echo $bill['BTW_6'] . ",";
			echo $bill['BTW_21'] . ",";
			echo $bill['cash'] . ",";
			echo $bill['card'] . ",";
			echo $bill['transfer'] . ",";
			echo $bill['modified'] . ",";
			echo $bill['owner']['id'] . ",";
			echo $bill['owner']['last_name'] . ",";
			echo $bill['vet']['first_name'] . ",";
			echo $bill['location']['name'];
			echo "\n";
		}
	}

	/*
	* function: pets
	* generic export function for pets
	* following import_export_alies.docx guidelines
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
	
	/*
	* function: clients
	*/
	private function clients($domtree, $xmlRoot, $search_from, $search_to)
	{
		# owners
		$clients = $this->get_owners($search_from, $search_to);

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
							'Status'		=> 0, // status : 0 : new, 1 : already known, 2 : known but editted

				));
		}
		// Header('Content-type: text/xml');
		// echo $domtree->saveXML();
		return $domtree;
	}

	/*
	* function: facturen
	* export invoices into a single xml tree
	*/
	private function facturen($domtree, $xmlRoot,$search_from, $search_to): DOMDocument
	{
		$bill_overview = $this->bills
			->where('invoice_id IS NOT NULL', null, null, false, false, true)
			->where('invoice_date > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('invoice_date < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->order_by('invoice_date', 'asc')
			->get_all();

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
						(details)
						- acount		: centralisatierekening / grootboekrekening
						- amount		:
						- debcre		: 1 = D -1 = C
						- ventil		:  1 = 0%, 2 = 6%, 3 = 12%, 4 = 21%

				marc :
					- accountingperiod	: Boekhoudperiode
					- VATMonth			: Btw-periode
					- Year_Alfa 		: Jaar

			*/

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
					- OurRef : bill_id

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
			$dt = DateTime::createFromFormat('Y-m-d H:i:s', $factuur['invoice_date']);
			$total_btw = $factuur['total_brut'] - $factuur['total_net'];

			$this->append_child_element($Sale, $domtree,
				array(
							'Customer_Prime'	=> $factuur['owner_id'],
							'DocType' 			=> '10',
							'DocNumber'			=> get_invoice_id($factuur['invoice_id'], $factuur['invoice_date'], $this->conf['invoice_prefix']['value']),
							'DocDate' 			=> $dt->format('d/m/Y'),
							'Amount' 			=> $this->amount($factuur['total_brut']),
							'VATAmount' 		=> $this->amount($total_btw),
							'OurRef'			=> $factuur['id'],
							'Status' 			=> 0,

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

			// reqyured since 13.03.210.01
			$this->append_child_element($detail, $domtree,
				array(
							'Account'			=> 400000, // 400000 == booking of full amount
							'Amount' 			=> $this->amount($factuur['total_brut']),
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
			$this->generate_booking_xml($domtree, $Details, $factuur['id']);
		}
		/* get the xml printed */
		return $domtree;
	}

	/*
	* function: kluwer
	* export clients, incoices into a single xml file valid for Kluwers Expert/M
	* booking codes could be added here btw
	*/
	public function kluwer($search_from, $search_to)
	{
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'Windows-1252');

		/* create the root element of the xml tree */
		$xmlRoot = $this->MPlus_headers($domtree);

		$domtree = $this->clients($domtree, $xmlRoot, $search_from, $search_to);
		$domtree = $this->facturen($domtree, $xmlRoot, $search_from, $search_to);
		
		// var_dump($domtree);
		Header('Content-type: text/xml');
		echo $domtree->saveXML();
	}
	
	/*
	* function: generate_booking_xml
	* generate booking codes
	*/
	private function generate_booking_xml($domtree, $Details, int $bill_id)
	{

		/* query events */
		$events = $this->events
			->where(array("payment" => $bill_id))
			->fields('id, pet, payment')
			->get_all();
		
		# transform to array with only id's
		$events_list = array_map(function($item) { return (int)$item['id']; }, $events);
				
		$products = ($this->events->get_booking_export($events_list, PRODUCT));
		foreach ($products as $book)
		{
			$detail = $domtree->createElement("Detail");
			$detail = $Details->appendChild($detail);

			$this->append_child_element($detail, $domtree,
				array(
							'Account'			=> $book['code'],
							'Amount' 			=> $this->amount($book['total_net']),
							'DebCre'			=> -1,
							'Ventil' 			=> $this->get_btw_id($book['btw']), // btw
							// 1:0%, 2:6%, 3:12%, 4:21%

				));
		}

		$procedures = ($this->events->get_booking_export($events_list, PROCEDURE));
		foreach ($procedures as $book)
		{
			$detail = $domtree->createElement("Detail");
			$detail = $Details->appendChild($detail);

			$this->append_child_element($detail, $domtree,
				array(
							'Account'			=> $book['code'],
							'Amount' 			=> $this->amount($book['total_net']),
							'DebCre'			=> -1,
							'Ventil' 			=> $this->get_btw_id($book['btw']), // btw
							// 1:0%, 2:6%, 3:12%, 4:21%

				));
		}
		return $domtree;
	}

	/*
	* function: get_btw_id
	* btw codes in Belgium
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
	* function: append_child_element
	* loop through the data and put it in childs
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
	* function: amount
	* change number formatting
	*/
	private function amount($value)
	{
		return number_format($value, 2, ',', '');
	}

	/*
	* function: MPlus_headers
	* headers specific to Expert/M bookkeeping
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
	* function: empty_xml
	* something is wrong (false) so return a empty file
	*/
	private function empty_xml($type = "")
	{
		Header('Content-type: text/xml');
		echo "<?xml version='1.0'?><xml><" . $type . "></" . $type . "></xml>";
	}

	/*
	* function: get_owners
	* return a list of owners
	*/
	private function get_owners($search_from, $search_to)
	{
		return $this->owners
						->group_start()
							->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
							->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->group_end()
						->or_group_start()
							->where('updated_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
							->where('updated_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
						->group_end()
						->get_all();
	}

	/*
	* function: get_pets
	* return a list of pets
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
