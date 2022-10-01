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
		$where = array('no_history' => 0);
		if (!($this->ion_auth->in_group("admin")))
		{
			$where['vet'] = $this->user->id;
		}
		$data = array(
			"reports" => $this->events
							->with_pet('fields:id, type, name')
							->with_location('fields:name')
							->with_vet('fields:first_name')
							->fields('id, title, pet, status, payment, location, report, updated_at')
							->where($where)
							->where('updated_at > DATE_ADD(NOW(), INTERVAL -7 DAY)', null, null, false, false, true)
							->order_by('created_at', 'DESC')->get_all(),
							);

		$this->_render_page('vet/report', $data);
	}

}
