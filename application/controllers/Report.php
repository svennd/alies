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

	public function search()
	{
		$query = $this->input->post('search_query');

		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-d", strtotime("-3 year")) : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-d") : $this->input->post('search_to');

		$anamnese 		= (bool)$this->input->post('anamnese');

		if ($query) 
		{
			$data = array(
				"reports" 		=> $this->events->search_event($query, $search_from, $search_to, $anamnese),
				"search_from" 	=> $search_from,
				"search_to" 	=> $search_to,
				"search_query" 	=> $query
			);
		} else 
		{
			$data = array(
				"search_from" 	=> $search_from,
				"search_to" 	=> $search_to,
				"search_query" 	=> false,
				"reports" 	=> false
			);
		}
		$this->_render_page('reports/search_event', $data);
	}

}