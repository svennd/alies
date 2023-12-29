<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends Vet_Controller
{

	private string $bill_storage_path = "data/stored/.bills/";
	private string $invoice_storage_path = "data/stored/.invoices/";

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Payment_model', 'payment');
		$this->load->model('Events_model', 'events');
		$this->load->model('Events_products_model', 'events_products');

		# helpers
		$this->load->helper('generate_bill_id_helper');

		# load library (html->pdf)
		$this->load->library('pdf'); 
		$this->load->library('qr'); 
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

	# generate a bill
	public function bill(int $owner_id)
	{
		// init
		$bill_status = BILL_DRAFT;

		// create new bill
		// unless there is a bill with status BILL_PENDING on this owner
		$open_bill = $this->bills->where(array("status" => BILL_PENDING, "owner_id" => $owner_id))->get();
		if ($open_bill)
		{
			$bill_id = $open_bill['id'];
			$bill_status = $open_bill['status'];
		}
		else
		{
			$bill_id = $this->bills->insert(array(
				"owner_id" 		=> $owner_id,
				"vet" 			=> $this->user->id,
				"location" 		=> $this->user->current_location,
				"status" 		=> $bill_status, // will be upgraded to BILL_PENDING when calculated
			));

			// update clients last bill
			$this->owners->update(array("last_bill" => date_format(date_create(), "Y-m-d")), $owner_id);

			// make this traceable
			$this->logs->logger(DEBUG, "generate_bill", "bill_id: " . $bill_id);
		}

		redirect('/invoice/get_bill/' . $bill_id, 'refresh');
	}

	# generate bill screen
	public function get_bill(int $bill_id, int $report = 0)
	{
		$bill = $this->bills->with_location('fields:name')->get($bill_id);

		// set all events that have no payment linked from this owner to this new bill
		if (in_array($bill['status'], array(BILL_DRAFT, BILL_PENDING, BILL_UNPAID))) {
			$this->events->set_open_events_to_bills($bill['owner_id'], $bill_id);

			// generate the bill
			// this does the calculations sets status to BILL_PENDING
			list($total_net, $total_brut, $btw_details) = $this->bills->calculate_bill($bill_id, $bill['status']);

			// this has to be below calculate_bill
			$bill_info = $this->bills->with_location('fields:name')->get($bill_id);
		}
		// it was already calculated before
		else
		{
			list($total_net, $total_brut, $btw_details) = array(
																$bill['total_net'],
																$bill['total_brut'],
																	array(
																		"0" => array("over" => $bill['BTW_0'], "calculated" => 0),
																		"6" => array("over" => $bill['BTW_6'], "calculated" => number_format($bill['BTW_6']*0.06, 2)),
																		"21" => array("over" => $bill['BTW_21'], "calculated" => number_format($bill['BTW_21']*0.21, 2))
																	));
			$bill_info = $bill;
		}

		// the view data
		$data = array(
			// queries printable info
			// this could perhaps be done in calculate_bill
			// since the queries are very similar *todo* ?
			"print" 		=> $this->bills->get_details($bill_id, $bill_info['owner_id']),
			"total_net" 	=> $total_net,
			"total_brut"	=> $total_brut,
			"btw_details" 	=> $btw_details,
			"owner" 		=> $this->owners->get($bill_info['owner_id']),
			"open_bills"	=> $this->bills->get_open_bills($bill_info['owner_id'], $bill_id),
			"due_date_days" => (isset($this->conf['due_date'])) ? (int) base64_decode($this->conf['due_date']['value']) : 30,
			"bill"			=> $bill_info
		);

		// if the bill is paid or there is an invoice_id defined (= legal document) generate a pdf
		if (in_array($bill_info['status'], array(BILL_PAID)) || $bill_info['invoice_id'])
		{
			$this->generate_pdf($data, PDF_FILE);
		}

		if ($report == 1) 
		{
			$struct = generate_struct_message($bill_info['owner_id'], $bill_info['id'], base64_decode($this->conf['struct_config']['value']));;
			$data['struct'] = $struct;
			$data['qr'] = $this->qr->create($total_brut, $struct);
			$data['BIC'] = base64_decode($this->conf['bic']['value']);
			$data['IBAN'] = base64_decode($this->conf['iban']['value']);
			$data['name_owner'] = base64_decode($this->conf['nameiban']['value']);

			$this->generate_pdf($data, PDF_STREAM);
		}
		elseif ($report == 2)
		{
			$this->mail_bill($data);
		}
		else
		{
			$this->_render_page('bills/report', $data);
		}
	}

	# in case the client does not pay
	public function bill_unpay($bill_id)
	{
		# make this traceable
		$this->logs->logger(INFO, "bill_unpay", "bill_id: " . $bill_id);

		$bill = $this->bills->get($bill_id);

		if ($bill['status'] != BILL_INCOMPLETE) {
			$this->remove_from_stock($bill_id);
		}

		# set status
		$this->bills->update(array("status" => BILL_INCOMPLETE), $bill_id);

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
		if ($this->input->post('submit') == 1 && (in_array($bill['status'], array(BILL_PENDING, BILL_UNPAID, BILL_INCOMPLETE)))) {
			
			# default
			$is_modified = false;

			# do this first if something below fails.
			# set all the events linked to this bill to closed so we can't add anything anymore
			$this->events->where(array('payment' => $bill_id))->update(array("status" => STATUS_CLOSED));

			# card and cash
			$card_value = round((float) $this->input->post('card_value'), 2);
			$cash_value = round((float) $this->input->post('cash_value'), 2);
			$transfer_value = round((float) $this->input->post('transfer_value'), 2);

			# store the payment
			$this->process_payment($card_value, $cash_value, $transfer_value, $bill_id);

			# update the bill
			$total_payed = ($cash_value + $card_value + $transfer_value) - (float)$bill['total_brut'];
			$status = ($total_payed < 0.001 && $total_payed > -0.001) ? BILL_PAID : BILL_INCOMPLETE;

			# check if bill was modified
			# only run once since its "expensive"
			if ($status == BILL_PAID) { $is_modified = $this->bills->is_bill_modified($bill_id); }

			$this->bills->update(array(
								"status" 	=> $status, 
								"card" 		=> $card_value, 
								"cash" 		=> $cash_value, 
								"transfer" 	=> $transfer_value, 
								"msg" 		=> $this->input->post('msg'), 
								"msg_invoice" => $this->input->post('msg_invoice'),
								"modified" 	=> $is_modified
							), $bill_id);

			# generate an invoice id, these HAVE to be +1 everytime. 
			# every new year we start at 1 again
			$this->bills->set_invoice_id($bill_id);
			
			# remove products from stock
			# only do this when the payment is not yet processed once before
			# if it was open->partial->done this could be ran twice, generating another stock reduction
			if ($bill['status'] != BILL_INCOMPLETE) {
				$this->remove_from_stock($bill_id);

				# make this traceable - payment is done
				$this->logs->logger(DEBUG, "bill_pay_complete", "bill_id: " . $bill_id);
			} else {
				# make this traceable - payment is done only partially
				$this->logs->logger(DEBUG, "bill_pay_incomplete", "bill_id: " . $bill_id . " current_state:" . $bill['status'] );
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


	# select all events with payment = $bill_id
	# reduce stock based on the items used in the events;
	private function remove_from_stock(int $bill_id)
	{
		$product_list = $this->events->all_bill_products($bill_id);
		$product_count = 0;

		foreach ($product_list as $product) {
			if ($product['stock_id'])
			{
				$this->stock->reduce($product['stock_id'], $product['product_id'], $product['volume']);
			}
			else
			{
				# this can happen when there is no stock at all for this product
				# and so stock_id is null, this means we don't know where it was and our best guess
				# is the event_location, but current_location is most likely fine too
				$this->stock->fallback_reduce($product['product_id'], $product['volume'], $this->user->current_location, 'NO_STOCK');
			}
			$product_count++;
		}

		$this->logs->logger(DEBUG, "drop_from_stock", "bill_id: " . $bill_id . " products : ". $product_count);
		return true;
	}
	
	private function generate_pdf(array $data, int $mode = PDF_STREAM)
	{
		# generate template data
		$template_data = $this->load->view('bills/report_print', $data, true);
		
		# invoice
		if ($data['bill']['invoice_id'])
		{
			$invoice_date = $data['bill']['invoice_date'];
			$time 		= strtotime($invoice_date);
			$filename 	= "bill_" . get_invoice_id($data['bill']['invoice_id'], $invoice_date, $this->conf['invoice_prefix']['value']);
			$path 		= $this->invoice_storage_path . date('Y', $time) . '/' . date('W', $time);
		}
		# bill
		else
		{
			$created_at = $data['bill']['created_at'];
			$time 		= strtotime($created_at);
			$filename 	= "check_" . get_bill_id($data['bill']['id']);
			$path 		= $this->invoice_storage_path . date('Y', $time) . '/' . date('W', $time);
		}

		# if path doesn't exist, create it
		if (!file_exists($path)) {
			mkdir($path, 0700, true);
		}

		# don't make duplicates in case of file
		if (file_exists($path . '/' . $filename . '.pdf') && $mode == PDF_FILE) {
			return $path . '/' . $filename . '.pdf';
		}

		# generate the pdf based
		return $this->pdf->create($template_data, $path . '/' . $filename, $mode);
	}

	private function mail_bill(array $data) 
	{
		$this->load->library('mail'); 
		$file = $this->generate_pdf($data, PDF_FILE);

		$this->mail->attach_file($file);
		// $this->mail->send($data['owner']['mail'], base64_decode($this->conf['emailtitle']['value']), base64_decode($this->conf['emailcontent']['value']), false);
		$this->mail->send("svennson@gmail.com", base64_decode($this->conf['emailtitle']['value']), base64_decode($this->conf['emailcontent']['value']), false);

		$this->bills->update(array("mail" => 1), $bill_id);
		
		echo json_encode(array("result" => true));
	}

	# extra security, keep log of payment processing
	private function process_payment(float $card_value, float $cash_value, float $transfer_value, int $bill_id)
	{
		# store the payment
		$this->payment->insert(array(
			"card" 		=> $card_value,
			"cash" 		=> $cash_value,
			"transfer" 	=> $transfer_value,
			"bill_id" 	=> $bill_id,
			"vet" 		=> $this->user->id,
			"location" 	=> $this->user->current_location
		));
	}

}
