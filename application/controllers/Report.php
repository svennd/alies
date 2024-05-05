<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Report
class Report extends Vet_Controller
{

	// initialize
	public $events;

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

		$this->_render_page('reports/vet_overview', $data);
	}

}
