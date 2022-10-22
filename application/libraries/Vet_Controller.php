<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vet_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		# check if install is fine
		if (empty($this->config->item('install'))) {
			redirect('install/start', 'refresh');
		}

		# check if they are logged in
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}

		# check if they are part of the vet group
		if (!$this->ion_auth->in_group('vet') && !$this->ion_auth->in_group('admin')) {
			# this needs 404
			# if not we can get in a redirect loop
			show_error("Not part of the correct group;");
			// redirect('/');
		}

		$this->load->model('Stock_location_model', 'stock_location');
		$this->load->model('Config_model', 'settings');
		$this->load->model('Events_model', 'events');

		// $conf = $this->settings->set_cache('all_config')->get_all();
		$conf = $this->settings->get_all();
		if ($conf) {
			foreach ($conf as $c) {
				$this->conf[$c['name']] = array(
												"value" 		=> $c['value'],
												"updated_at" 	=> $c['updated_at'],
												"created_at" 	=> $c['created_at']
											);
			}
		} else {
			$this->conf = array();
		}

		$this->user = $this->ion_auth->user()->row();
		$this->location = $this->stock_location->get_all();

		# required on every page
		$this->page_data = array(
								"user" 						=> $this->user,
								"location" 					=> $this->_get_compass_locations(),
								"current_location" 			=> $this->_get_current_location(),
								"mondal" 					=> ($this->_get_current_location() == "none") ? $this->_get_mondal() : "",
						);

		$this->page_data['report_count'] = $this->events
																	->where(array(
																						'vet' 			=> $this->user->id,
																						'no_history' 	=> 0,
																						'report' 		=> 1
																					))
																	->where('updated_at > DATE_ADD(NOW(), INTERVAL -3 DAY)', null, null, false, false, true)
																	->count_rows();

		// $sections = array(
		// 	'config'  => TRUE,
		// 	'queries' => TRUE,
		// 	'query_toggle_count' => 250
		// );
		
		// $this->output->set_profiler_sections($sections);
		// $this->output->enable_profiler(TRUE);

		$this->lang->load('vet', 'dutch');
		// $this->lang->load('vet', 'english');
	}

	public function _get_compass_locations()
	{
		$current = $this->_get_current_location();

		$compass = '
		<div class="dropdown show">
			<a class="btn ' . (($current == 'none') ? "btn-outline-danger" : "btn-outline-success") . ' btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="far fa-compass"></i> '. $current .'
			</a>
			<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
		';
		foreach ($this->location as $location) {
			$compass .= '<a class="dropdown-item" href="' . base_url() . '/welcome/change_location/' . $location['id'] . '">'. $location['name'] .'</a>';
		}
		$compass .= "</div></div>";
		return $compass;
	}

	public function _render_page($page, $data = array())
	{
		$data = array_merge($data, $this->page_data);
		$this->load->view('header', $data);
		$this->load->view($page, $data);
		$this->load->view('footer', $data);
	}

	public function _get_mondal()
	{

		# get login cookie
		$this->load->helper('cookie');
		
		return $this->load->view('mondal/location', array(
				"location"			 => $this->location,
				"suggest_location"	 => (get_cookie('alies_location')) ? get_cookie('alies_location') : -1,
			), true);
	}

	private function _get_current_location()
	{
		$current = ($this->user->current_location != 0) ?
			$this->stock_location->get($this->user->current_location)['name']
			:
			"none";
		return $current;
	}
}
