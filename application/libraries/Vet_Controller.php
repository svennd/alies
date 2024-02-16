<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vet_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		# check if they are logged in
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}

		# check if they are part of the vet group
		if (!$this->ion_auth->in_group('vet') && !$this->ion_auth->in_group('admin') && !$this->ion_auth->in_group('accounting')) {
			# this needs 404
			# if not we can get in a redirect loop
			show_error("Not part of the correct group;");
		}

		$this->load->model('Stock_location_model', 'stock_location');
		$this->load->model('Config_model', 'settings');
		$this->load->model('Events_model', 'events');
		$this->load->model('Sticky_model', 'sticky');
		$this->load->model('Lab_model', 'lab');

		$this->lang->load('vet', 'dutch');
		$this->lang->load('admin', 'dutch');
		
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

		$current_location = $this->_get_current_location();
		$current_location_name = $current_location ? $current_location['name'] : "none";
		
		# required on every page
		$this->page_data = array(
								"user" 						=> $this->user,
								"location" 					=> $this->_get_compass_locations($current_location),
								"current_location" 			=> $current_location_name,
								"mondal" 					=> (!$current_location) ? $this->_get_mondal() : "",
								"cnt_sticky"				=> $this->sticky->count_rows(),
								"report_count"				=> $this->events->get_open_reports($this->user->id),
								"lab_count"					=> $this->lab->get_unassigned(),
						);

		// $sections = array(
		// 	'config'  => TRUE,
		// 	'queries' => TRUE,
		// 	'query_toggle_count' => 500
		// );
		
		// $this->output->set_profiler_sections($sections);
		// $this->output->enable_profiler(TRUE);
	}

	public function _get_compass_locations($location) : string
	{
		if (!$location)
		{
			return "";
		}
		
		$compass = '
		<div class="dropdown show">
			<a class=" dropdown-toggle btn btn-outline-success btn-sm" style="color:#1cc88a;background-color:rgba(0,0,0,0);" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="fa-solid fa-fw fa-location-dot" style="color:' . $location['color'] . '"></i> '. $location['name'] .'
			</a>
			<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
		';
		foreach ($this->location as $location) {
			$compass .= '<a class="dropdown-item" href="' . base_url('welcome/change_location/' . $location['id']). '">
							<i class="fa-solid fa-fw fa-location-dot" style="color:' . $location['color'] . '"></i> '. $location['name'] .'
				</a>';
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
		return ($this->user->current_location != 0) ? $this->stock_location->get($this->user->current_location) : false;
	}
}
