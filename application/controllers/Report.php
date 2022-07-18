<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Users_model', 'users');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Products_model', 'product');
		$this->load->model('Events_model', 'events');
	}

	public function index()
	{
		$data = array(
			"reports" => $this->events
							->with_pet('fields:id, type, name')
							->with_location('fields:name')
							->fields('id, title, pet, status, payment, location, report, updated_at')
							->where(array(
											'vet' 		=> $this->user->id,
											'no_history' => 0
										))
							->where('updated_at > DATE_ADD(NOW(), INTERVAL -3 DAY)', null, null, false, false, true)
							->order_by('created_at', 'DESC')->get_all(),
							);

		$this->_render_page('vet/report', $data);
	}

}
