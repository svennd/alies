<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Events_model', 'events');
	}

	public function index()
	{
		$data = array(
			"reports" => $this->events->get_current_events((bool)$this->ion_auth->in_group("admin"))
		);

		$this->_render_page('vet/report', $data);
	}

}
