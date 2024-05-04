<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Vet_Controller
class Vet_Controller extends MY_Controller
{
	public $user;
	public $locations;
	public $page_data;
	public $conf;

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

		$this->user = $this->ion_auth->user()->row(); // object
		$this->user->password = ""; // remove password from object
		
		$user_location_id = $this->ion_auth->get_user_location(); // int
		$alocations = $this->stock_location->fields('id, name, color')->get_all(); // array

		// make sure that id => array()
		$this->locations = array_combine(array_column($alocations, 'id'), $alocations);

		# this should never happen 
		# but in prod it does :(
		if ((int) $this->_get_user_location() != (int) $user_location_id) {
			$this->logs->logger(ERROR, "detected_diff_location", "db:" . $this->_get_user_location() . " session:" . $user_location_id);
		}

		# required on every page
		$this->page_data = array(
								"user" 						=> $this->user,
								"all_locations"				=> $this->locations,
								"clocation" 				=> $user_location_id,
								"location_changer" 			=> $this->_get_compass_locations($user_location_id, $this->locations),
								"mondal" 					=> (!$user_location_id) ? $this->_get_mondal($this->locations) : "",
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

		// just print the queries
		// foreach ($this->db->queries as $query) {
		// 	echo $query . PHP_EOL;
		// }
		// var_dump($this->get_user_location());
	}

	public function _get_compass_locations(int $location, array $all_locations) : string
	{
		if (!$location) { return ""; }
		return $this->load->view('blocks/location_changer', array(
						"clocation" 	=> $location,
						"all_locations" => $all_locations,
		), true);
	}

	public function _get_mondal(array $all_locations) : string
	{
		# get login cookie
		$this->load->helper('cookie');
		
		return $this->load->view('blocks/location', array(
				"location"			 => $all_locations,
				"suggest_location"	 => (get_cookie('alies_location')) ? get_cookie('alies_location') : -1,
			), true);
	}

	public function _render_page($page, $data = array())
	{
		$data = array_merge($data, $this->page_data);
		$this->load->view('header', $data);
		$this->load->view($page, $data);
		$this->load->view('footer', $data);
	}

	public function _get_user_location(): int
	{
		return $this->ion_auth->get_user_location();
	}

}
