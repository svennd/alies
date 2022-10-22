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
	}

	# show bills of last 30 days;
	public function index()
	{
		// TODO VERIFY THIS
		// the html is not safe!
		// $search_from = (strtotime('-14 days') > strtotime($this->input->post('search_from'))) ? strtotime('-14 days') : $this->input->post('search_from');

		$today = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $today->format('Y-m-d');

		$today->modify('-1 day');

		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $today->format('Y-m-d');

		// ->where('created_at > DATE_ADD(NOW(), INTERVAL -14 DAY)', null, null, false, false, true)
		$bill_overview = $this->bills
			->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
			->with_location('fields:name')
			->with_vet('fields:first_name')
			->with_owner('fields:last_name')
			->limit(100)
			->get_all();

		$data = array(
			"bills" 		=> $bill_overview,
			"search_from"	=> (isset($search_from)) ? $search_from : '',
			"search_to"		=> (isset($search_to)) ? $search_to : '',
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
	public function get_bill($bill_id, $pdf = false)
	{
		$bill = $this->bills->get($bill_id);
		$owner_id = $bill['owner_id'];
		$bill_status = $bill['status'];

		$bill_total_tally = array();

		# init
		$pet_id_array = array();
		$print_bill = array();
		$event_info = array();

		# get all pets
		$pets = $this->get_pets($owner_id, $bill);

		foreach ($pets as $pet) {
			# for easy access
			$pet_id = $pet['id'];

			# get all events for this pet that have an open payment
			# this could be multiple (consult + op) for example
			$pet_events = $this->events
									->where("pet", "=", $pet['id'])
										->group_start()
											->where("payment", "=", NO_BILL)
											->where("payment", "=", $bill_id, true)
										->group_end()
									->fields("id, location, payment, created_at, updated_at")
									->get_all();

			# no event for this pet, skip all togheter
			if (!$pet_events) {
				continue;
			}

			# create array if there is going to be
			# events linked to this animal
			$print_bill[$pet_id] = array();

			# should generally only be 1 event
			# but in case of open events
			# could be more
			foreach ($pet_events as $event) {
				#
				$event_id = $event['id'];

				# get the calculated bill
				# for all procedures and products for this pet
				$event_bill = $this->events->get_products_and_procedures($event_id);

				# update event if its not part of this bill yet
				# only if creating a new bill, else we would add events to a closed invoice
				if ($event['payment'] != $bill_id && $bill_status != PAYMENT_PAID) {
					# update event so that we know on what bill it was placed (and its no longer NO_BILL)
					$this->events->update(array("payment" => $bill_id), $event_id);
				}

				# add to the total
				foreach ($event_bill['tally'] as $btw => $total) {
					$bill_total_tally[$btw] = (isset($bill_total_tally[$btw])) ? $bill_total_tally[$btw] + $total : $total;
				}

				# printable
				$print_bill[$pet_id][$event_id] = $event_bill;

				# a list with event description
				$event_info[$pet_id][$event_id] = $event;
			}

			# for making a printable bill
			$pet_id_array[$pet_id] = $pet;
		}

		# calculate the full bill
		$bill_total = 0.0;
		foreach ($bill_total_tally as $btw => $total) {
			// var_dump($bill_total_tally);
			$bill_total += $total * (1 + ($btw/100));
		}

		# check if the calculated price is equal to the price in the database
		# if not, something got added or this is the initial call, and we need to update it
		$this->check_for_updates_in_the_bill($bill_id, $bill['status'], $bill_total, $bill['amount']);

		$data = array(
					"owner" 		=> $this->owners->get($owner_id),
					"pets" 			=> $pet_id_array,
					"print_bill"	=> $print_bill,
					"bill_total_tally" => $bill_total_tally,
					"bill_id"		=> $bill_id,
					"open_bills"	=> $this->get_open_or_unpaid_bills($owner_id, $bill_id),
					"event_info"	=> $event_info,
					"location_i"	=> $this->location,
					"bill"			=> $this->bills->get($bill_id) // can't remove for race condition on calculation
				);

		if ($pdf)
		{
			$this->generate_pdf_bill($bill_id, $data);
		}
		$this->_render_page('bill_report', $data);
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
	public function bill_pay($bill_id)
	{
		$bill = $this->bills->get($bill_id);

		# only allow payment processing
		# if not yet payed or payment is open
		if ($this->input->post('submit') == 1 && ($bill['status'] == PAYMENT_UNPAID || $bill['status'] == PAYMENT_OPEN || $bill['status'] == PAYMENT_PARTIALLY)) {

			# do this first if something below fails.
			# set all the events linked to this bill to closed so we can't add anything anymore
			$this->events->where(array('payment' => $bill_id))->update(array("status" => STATUS_CLOSED));

			# card and cash
			$card_value = round((float) $this->input->post('card_value'), 2);
			$cash_value = round((float) $this->input->post('cash_value'), 2);

			# update the bill
			$total_payed = ((float)$cash_value+(float)$card_value) - (float)$bill['amount'];
			$status = ($total_payed < 0.001 && $total_payed > -0.001) ? PAYMENT_PAID : PAYMENT_PARTIALLY;

			$this->bills->update(array("status" => $status, "card" => $card_value, "cash" => $cash_value, "msg" => $this->input->post('msg')), $bill_id);

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

	# on bill_unpay we need to send this in the background
	public function store_bill_msg(int $bill_id)
	{
		$this->bills->update(array("msg" => $this->input->post('msg')), $bill_id);
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
	private function generate_pdf_bill($bill_id, $data)
	{
		# load library (html->pdf)
		$this->load->library('pdf'); 
	
		# race condition 
		$bill = $this->bills->get($bill_id);

		# generate the pdf based		
		$this->pdf->create(
			$this->load->view('bill_report_print', $data, true), 
			"bill_" . get_bill_id($bill_id, $bill['created_at']), 
			true
		);
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

	// get pets for billing
	private function get_pets(int $owner_id, $bill)
	{
		$pets = $this->pets->where(array("owner" => $owner_id))->fields(array('id', 'name', 'chip'))->get_all();

		# no pets on this owner
		if (!$pets) {
			$this->_render_page('bill_invalid', array('bill' => $bill));
			return 0;
		}
		return $pets;
	}
}
