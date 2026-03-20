<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	const WRITE_OFF_OWNER_ID = 1837;

	// define max volume of results to protect the server
	// empericly set
	const MAX_RESULTS = 5000;

	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Log_stock_model', 'log_stock');
		$this->load->model('Liquidate_model', 'liquidate');
		$this->load->model('Delivery_slip_model', 'delivery');
		$this->load->model('Register_in_model', 'regin');
		$this->load->model('Bills_model', 'bills');
		$this->load->model('Owners_model', 'owners');
	}
	
	/*
	* function: write_off
	* logbook for write offs
	*/
	public function write_off()
	{
		$logs = $this->liquidate
						->with_products('fields:name, unit_sell')
						->with_vet('fields:first_name')
						->with_location('fields:name')
						->with_bill('fields:id, invoice_id, invoice_date')
					->limit(self::MAX_RESULTS)
					->get_all();

		$data = array(
						"logs" 				=> $logs,
						"unbilled_count" 	=> $this->liquidate->count_unbilled(),
		);
		$this->_render_page('logs/stock_write_off', $data);
	}

	public function write_off_invoice()
	{
		$owner = $this->owners->get(self::WRITE_OFF_OWNER_ID);
		if (!$owner) {
			show_error("Write-off invoice client #" . self::WRITE_OFF_OWNER_ID . " was not found.");
		}

		$write_off_rows = $this->liquidate->get_unbilled();
		if (!$write_off_rows) {
			redirect('/logs/write_off', 'refresh');
		}

		$line_ids = array_map(function ($row) {
			return (int) $row['id'];
		}, $write_off_rows);

		$this->db->trans_start();

		$bill_id = $this->bills->insert(array(
			"owner_id"		=> self::WRITE_OFF_OWNER_ID,
			"vet"			=> $this->user->id,
			"location"		=> $this->_get_user_location(),
			"status"		=> BILL_PAID,
			"cash"			=> 0,
			"card"			=> 0,
			"transfer"		=> 0,
			"total_brut"	=> 0,
			"total_net"		=> 0,
			"BTW_0"			=> 0,
			"BTW_6"			=> 0,
			"BTW_21"		=> 0,
			"msg_invoice"	=> "Stock write-off invoice. All listed products were written off from stock.",
		));

		$this->bills->set_invoice_id((int) $bill_id, false);
		$this->liquidate->assign_bill($line_ids, (int) $bill_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->logs->logger(ERROR, "write_off_invoice", "failed to generate write-off invoice");
			redirect('/logs/write_off', 'refresh');
		}

		$this->logs->logger(INFO, "write_off_invoice", "bill_id:" . (int) $bill_id . " items:" . count($line_ids));
		redirect('/invoice/get_bill/' . (int) $bill_id, 'refresh');
	}
	
	/*
	* function: product
	* detailed transaction log for a product
	*/
	public function product(int $product_id)
	{
		$data = array(
			"logs" 		=> $this->log_stock
									->with_product('fields:name, unit_sell')
									->with_vet('fields:first_name')
									->with_locations('fields:name')
								->where(array('product' => $product_id))
								->get_all(),
		);
		$this->_render_page('logs/product', $data);
	}

	/*
	* function: nlog
	* common logbook for all actions in alies
	*/
	public function nlog()
	{
		$dt = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $dt->format('Y-m-d');
		$dt->modify('-3 day');
		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $dt->format('Y-m-d');

		$data = array(
						"search_to" 	=> $search_to,
						"search_from" 	=> $search_from,
						"logs" 			=> $this->logs
											->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
											->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
											->with_vet('fields:first_name')->order_by(array('id', 'desc'))
											->limit(self::MAX_RESULTS)
											->get_all(),
		);
		$this->_render_page('logs/global', $data);
	}
	
	/*
	* function: delivery
	* logbook for deliveries
	*/
	public function delivery(int $delivery = 0)
	{
		if($delivery)
		{
			$data = array(
				"delivery" => $this->delivery->with_location('fields: name')->with_vet('fields:first_name')->get($delivery),
				"products" => $this->regin->with_product('fields: name, sell_volume')->where(array('delivery_slip' => $delivery))->get_all(),
			);
			$this->_render_page('logs/delivery_detail', $data);
		}
		else
		{
			$data = array(
				"logs" 		=> $this->delivery->with_products('fields:name')->with_vet('fields:first_name')->get_all(),
			);
			$this->_render_page('logs/delivery', $data);
		}
	}

	/* usefull for debugin */
	public function software_version()
	{
		$changelog = (file_exists("CHANGELOG.md")) ? nl2br(file_get_contents("CHANGELOG.md")) : "No CHANGELOG.md file;";
		
		$data = array(
						"database_version"	=> $this->db->query("SELECT * FROM `migrations`")->result_array(),
						"changelog"			=> $changelog
		);
		$this->_render_page('logs/version', $data);
	}
}
