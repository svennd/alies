<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Events_model', 'events');
		$this->load->model('Events_products_model', 'events_products');

		# helpers
		$this->load->helper('generate_bill_id_helper');

		# load library (html->pdf)
		$this->load->library('pdf'); 
	}

	# show bills of last 30 days for admin and 7 days for vets;
	public function index()
	{
		$dt = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $dt->format('Y-m-d');
		
		# restrict normal vets to 250 (todo: make config variable)
		$search_limit = 250;
		# set default lookback to 7 days for vets
		$dt->modify('-7 day');

		if ($this->ion_auth->in_group("admin"))
		{
			# set default lookback to ~1month
			$dt->modify('-23 day');
			# set restriction for admin loose
			$search_limit = 5000;
		}  

		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $dt->format('Y-m-d');

		$bill_overview = $this->bills
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->with_location('fields:name')
			->with_vet('fields:first_name')
			->with_owner('fields:last_name,id as user_id,low_budget,debts,btw_nr')
			->limit($search_limit)
			->order_by('created_at', 'desc')
			->get_all();

		# max search for vets ; 30 days
		$dt->modify('-23 day');
		
		$data = array(
			"bills" 		=> $bill_overview,
			"search_from"	=> (isset($search_from)) ? $search_from : '',
			"search_to"		=> (isset($search_to)) ? $search_to : '',
			"max_search_from" => ($this->ion_auth->in_group("admin")) ? '2000-01-01' : date_format($dt, 'Y-m-d'),

		);
		$this->_render_page('bills/bill_overview', $data);
	}

	# generate a bill if thre is no open for this owner
	# once a bill is "unpaid/incomplete" products are removed from stock
	# and the assigned vet is "locked"
	public function bill(int $owner_id, int $event_id)
	{
		# before we create a new bill check if there is already an unpaid bill
		$check_bill = $this->bills
					->group_start()
						->where("status", "=", PAYMENT_UNPAID)
						->where("status", "=", PAYMENT_OPEN, true)
					->group_end()
					->where(array("owner_id" => $owner_id))
					->get_all();

		if ($check_bill)
		{
			redirect('/invoice/get_bill/' . $check_bill[0]['id'] . '?old', 'refresh');
		}

		// create new bill
		$bill_id = $this->bills->insert(array(
				"owner_id" 		=> $owner_id,
				"vet" 			=> $this->user->id,
				"location" 		=> $this->user->current_location,
				"status" 		=> PAYMENT_OPEN,
			));

		// set the event to this payment
		$this->events->update(array("payment" => $bill_id), $event_id);

		// update clients last bill
		$this->owners->update(array("last_bill" => date_format(date_create(), "Y-m-d")), $owner_id);

		// make this traceable
		$this->logs->logger(DEBUG, "generate_bill", "bill_id: " . $bill_id);

		redirect('/invoice/get_bill/' . $bill_id, 'refresh');
	}

	# generate a bill for a owner
	# 	-> get all pets
	# 	--> for each pet there could be multiple events on different locations
	#		--> for each event there are 1) proc's and 2) prod's
	# 	-> for the full bill create a line and add the events to bill_events
	#		so we don't create 2 bills for 1 (or more) event
	# report : 0 (html), 1 (pdf), 2 (email)
	public function get_bill(int $bill_id, int $report = 0)
	{
		$bill_model = $this->bills->get_bill_details($bill_id);
		if (!$bill_model)
		{
			$this->_render_page('bill_invalid', array('bill' => $this->bills->get($bill_id)));
			return 0;
		}

		list($print_bill, $bill_total_tally, $event_info, $pet_id_array, $bill_total) = $bill_model;


		$bill = $this->bills->get($bill_id);
		
		# check if the calculated price is equal to the price in the database
		# if not, something got added or this is the initial call, and we need to update it
		$this->check_for_updates_in_the_bill($bill_id, $bill['status'], $bill_total, $bill['amount']);

		$data = array(
					"owner" 		=> $this->owners->get($bill['owner_id']),
					"pets" 			=> $pet_id_array,
					"print_bill"	=> $print_bill,
					"bill_total_tally" => $bill_total_tally,
					"bill_id"		=> $bill_id,
					"open_bills"	=> $this->get_open_or_unpaid_bills($bill['owner_id'], $bill_id),
					"event_info"	=> $event_info,
					"due_date_days" => (isset($this->conf['due_date'])) ? (int) base64_decode($this->conf['due_date']['value']) : 30,
					"bill"			=> $this->bills->with_location('fields:name')->get($bill_id)// can't remove for race condition on calculation
				);


		if ($report == 1)
		{
			$this->generate_pdf_bill($bill_id, $data);
		}
		elseif($report == 2)
		{
			return $this->generate_pdf_mail($bill_id, $data);
		}
		else
		{
			# note : we can only generate one PDF at a time :( 
			# check & see if we can generate a finale bill
			# in pdf to store
			$this->generate_final_bill($data);
		}
		$this->_render_page('bills/report', $data);
	}

	# in case the client does not pay
	public function bill_unpay($bill_id)
	{
		# make this traceable
		$this->logs->logger(INFO, "bill_unpay", "bill_id: " . $bill_id);

		$bill = $this->bills->get($bill_id);

		if ($bill['status'] != PAYMENT_PARTIALLY) {
			$this->remove_from_stock($bill_id);
		}

		# set status
		$this->bills->update(array("status" => PAYMENT_PARTIALLY), $bill_id);

		# set all the events linked to this bill to closed so we can't add anything anymore
		$this->events->where(array('payment' => $bill_id))->update(array("status" => STATUS_CLOSED));

		redirect('/invoice/get_bill/' . $bill_id, 'refresh');
	}

	# remove products from stock
	# set bill amount to payed part
	public function bill_pay(int $bill_id)
	{
		$bill = $this->bills->get($bill_id);

		# only allow payment processing
		# if not yet payed or payment is open
		if ($this->input->post('submit') == 1 && ($bill['status'] == PAYMENT_UNPAID || $bill['status'] == PAYMENT_OPEN || $bill['status'] == PAYMENT_PARTIALLY)) {
			
			# default
			$is_modified = false;

			# do this first if something below fails.
			# set all the events linked to this bill to closed so we can't add anything anymore
			$this->events->where(array('payment' => $bill_id))->update(array("status" => STATUS_CLOSED));

			# card and cash
			$card_value = round((float) $this->input->post('card_value'), 2);
			$cash_value = round((float) $this->input->post('cash_value'), 2);

			# update the bill
			$total_payed = ((float)$cash_value+(float)$card_value) - (float)$bill['amount'];
			$status = ($total_payed < 0.001 && $total_payed > -0.001) ? PAYMENT_PAID : PAYMENT_PARTIALLY;

			# check if bill was modified
			# only run once since its "expensive"
			if ($status == PAYMENT_PAID) { $is_modified = $this->bills->is_bill_modified($bill_id); }

			$this->bills->update(array("status" => $status, "card" => $card_value, "cash" => $cash_value, "msg" => $this->input->post('msg'), "msg_invoice" => $this->input->post('msg_invoice')), $bill_id);

			# generate an invoice id, these HAVE to be +1 everytime. 
			# every new year we start at 1 again
			$this->bills->set_invoice_id($bill_id, $is_modified);
			
			# remove products from stock
			# only do this when the payment is not yet processed once before
			# if it was open->partial->done this could be ran twice, generating another stock reduction
			if ($bill['status'] != PAYMENT_PARTIALLY) {
				$this->remove_from_stock($bill_id);
			} else {
				# make this traceable - payment is done only partially
				$this->logs->logger(INFO, "bill_pay_incomplete", "bill_id: " . $bill_id . " current_state:" . $bill['status'] );
			}

		}
		redirect('/invoice/get_bill/' . $bill_id, 'refresh');
	}

	public function make_invoice_id(int $bill_id)
	{
		$is_modified = $this->bills->is_bill_modified($bill_id);
		$this->bills->set_invoice_id($bill_id, $is_modified);
		redirect('/invoice/get_bill/' . $bill_id, 'refresh');
	}

	# on bill_unpay we need to send this in the background
	public function store_bill_msg(int $bill_id)
	{
		$this->bills->update(array("msg" => $this->input->post('msg'), "msg_invoice" => $this->input->post('msg_invoice')), $bill_id);
	}

	# check if the calculated price is equal to the price in the database
	# if not, something got added, and we need to update it
	# called in get_bill()
	private function check_for_updates_in_the_bill(int $bill_id, int $bill_status, float $bill_total, $bill_amount)
	{
		# partial is tricky can remove products if not enough money
		# but then we need to recalculate if there was enough payed
		if (in_array($bill_status, array(PAYMENT_OPEN, PAYMENT_UNPAID, PAYMENT_PARTIALLY))) {

			# update the bill in case something changed
			# hack for float comparison
			# bill_amount can be NULL
			if (is_null($bill_amount) || round($bill_total, 2) != (float) $bill_amount) {
				$this->bills->update(array("status" => PAYMENT_UNPAID, "amount" => round($bill_total, 2)), $bill_id);
			}
		}
	}

	# select all events with payment = $bill_id
	# reduce stock based on the items used in the events;
	private function remove_from_stock($bill_id)
	{
		$events_from_bill = $this->events->where(array("payment" => $bill_id))->fields('id, location')->get_all();
		$product_count = 0;
		foreach ($events_from_bill as $event) {
			$product_list = $this->events_products->where(array("event_id" => $event['id']))->fields('product_id, volume, barcode')->get_all();

			if (!$product_list) {
				continue;
			}
			foreach ($product_list as $prod) {
				$this->stock->reduce_stock(
					$prod['product_id'],
					$prod['volume'],
					$event['location'],
					$prod['barcode']
				);
				$product_count++;
			}
		}

		$this->logs->logger(DEBUG, "drop_from_stock", "bill_id: " . $bill_id) . " products : ". $product_count;
		return true;
	}

	# logic for generating a pdf
	private function generate_pdf_bill(int $bill_id, $data)
	{		
		# merge pagedate
		$data = array_merge($data, $this->page_data);

		# generate the pdf based		
		$this->pdf->create(
			$this->load->view('bills/report_print', $data, true),
			(!$data['bill']['invoice_id']) ? "check_" . get_bill_id($bill_id) : "bill_" . get_invoice_id($data['bill']['invoice_id'], $data['bill']['created_at']), 
			false // download
		);
	}

	// generate a bill that has been payed in full
	private function generate_final_bill(array $data)
	{
		$bill = $data['bill'];

		// check if its a check or a bill
		if ($bill['invoice_id'])
		{
			$date = strtotime($bill['created_at']);
			$full_path = 'data/stored/.bills/' . date('Y', $date) . '/' . date('m', $date);

			if (!file_exists($full_path)) {
				mkdir($full_path, 0700, true);
			}
			$this->pdf->create_file(
				$this->load->view('bills/report_print', $data, true), 
				$full_path . '/bill_' . get_invoice_id($bill['invoice_id'], $bill['created_at'])
			);
			return true;
		}

		// can't make the bill
		return false;
	}

	private function generate_pdf_mail(int $bill_id, $data) 
	{
		$this->load->library('mail'); 

		# generate the pdf based
		$full_path = 'data/stored/rekening_' . (!$data['bill']['invoice_id']) ? "check_" . get_bill_id($bill_id) : "bill_" . get_invoice_id($data['bill']['invoice_id'], $data['bill']['created_at']);

		$file = $this->pdf->create_file(
			$this->load->view('bills/report_print', $data, true), 
			$full_path
			);

		$this->mail->attach_file($file);
		$this->mail->send("svenn.dhert@gmail.com", "Bedankt voor uw bezoek aan De Dommel DAP", "Geachte Svenn,\nBedankt voor uw bezoek aan DAP de dommel.\nUw kunt uw rekening in bijlage bij deze e-mail vinden.", false);

		$this->bills->update(array("mail" => 1), $bill_id);
		
		echo json_encode(array("result" => true));
	}

	# get open_or_unpaid bills other then the one we are creating
	private function get_open_or_unpaid_bills(int $owner_id, int $bill_id)
	{
		return $this->bills
				->group_start()
					->where("status", "=", PAYMENT_UNPAID)
					->where("status", "=", PAYMENT_OPEN, true)
					->where("status", "=", PAYMENT_PARTIALLY, true)
					->where("status", "=", PAYMENT_NON_COLLECTABLE, true)
				->group_end()
					->where("id", "!=", $bill_id)
				->where(array("owner_id" => $owner_id))
				->get_all();;
	}
}
