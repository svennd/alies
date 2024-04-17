<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounting extends Admin_Controller
{
	public $eproc, $eprod, $bills, $stock, $book, $events, $logs;

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Events_procedures_model', 'eproc');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Booking_code_model', 'book');
	}

	/**
	 *  function: Dashboard
	 *  
	 * - monthly earnings
	 * - yearly earnings
	 * - client contacts per year / per month ?
	 * - distribution products/procedures (earnings)
	 * - earnings overview per month (/vs last year?)
	 */
	public function dashboard(int $month = 0)
	{
		$date = new DateTime('first day of this month');

		// increase or decrease months
		$date->modify($month . 'month');

		$last_year = clone $date;
		$last_year->modify('-1 year');

		$last_month = clone $date;
		$last_month->modify('-1 month');

		$data = array(
			"month"						=> $month,
			"current_date"				=> clone $date,
			"monthly_earnings"			=> $this->bills->get_monthly_earning(clone $date),
			"client_contacts" 			=> $this->events->get_contacts(clone $date),
			"client_contacts_lm" 		=> $this->events->get_contacts(clone $last_month),
			"client_contacts_year"		=> $this->events->get_contacts_year(clone $date),
			"client_contacts_year_ly" 	=> $this->events->get_contacts_year(clone $last_year),
			"distribution_proc_prod" 	=> $this->get_prod_proc_distribution(clone $date),
			"yearly_earnings" 		 	=> $this->bills->get_yearly_earnings(clone $date),
			"yearly_earnings_ly" 		=> $this->bills->get_yearly_earnings(clone $last_year),
			"yearly_by_month"		 	=> $this->bills->get_yearly_earnings_by_month(clone $date),
			"yearly_by_month_last_year"	=> $this->bills->get_yearly_earnings_by_month($last_year),
			"stock_error_count"			=> $this->stock->where(array("state" => STOCK_ERROR))->count_rows(),
			"logs" 						=> $this->logs
												->with_vet('fields:first_name')
												->with_location('fields:name')
												->where("LEVEL", "<=", WARN)
												->limit(25)
												->order_by("created_at", "DESC")
												->get_all(),
		);
		
		$this->_render_page('accounting/dashboard', $data);
	}

	// function: get_prod_proc_distribution
	// query both products & procedures
	private function get_prod_proc_distribution(datetime $date): array
	{
		$products = $this->eprod->get_monthly_earning($date);
		$procedures = $this->eproc->get_monthly_earning($date);
		return array( 
			"products" 		=> $products,
			"procedures" 	=> $procedures,
			"total" 		=> round($products['total']+$procedures, 2) // the error here is unpaid/partially paid bills
		);
	}
}
