<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stats extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('Stats_model', 'stats');
	}
	

	public function index()
	{
		/* input */
		$search_from 	= (is_null($this->input->post('search_from'))) ? date("Y-m-01") : $this->input->post('search_from');
		$search_to 		= (is_null($this->input->post('search_to'))) ? date("Y-m-t") : $this->input->post('search_to');
		$result 		= false;
		$stat_info 		= false;
		$sql 			= "";	

		if ($this->input->post('query')) {
			$stat_info = $this->stats->get((int) $this->input->post('query'));
			$sql = sprintf($stat_info['query'], $search_from, $search_to);
			$result = $this->db->query($sql)->result_array();
		}
				
		$data = array(
						"search_from" 	=> $search_from,
						"search_to" 	=> $search_to,
						"result" 		=> $result,
						"stat_info" 	=> $stat_info,
						"stats" 		=> $this->stats->get_all(),
						"sql"			=> $sql
					);

		$this->_render_page('stats/index', $data);
	}

	/*
	* function: update
	* update the stats query
	*/
	public function update(int $id)
	{
		if ($this->input->post('submit')) {
			$this->stats->update(array("query" => $this->input->post('query')), $id);
			redirect('stats');
		}

		$stat = $this->stats->get($id);
		$data = array(
						"stat" => $stat
					);

		$this->_render_page('stats/update', $data);
	}
}
