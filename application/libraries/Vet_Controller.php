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
		$this->load->model('Logs_model', 'logs');
		$this->load->model('Config_model', 'settings');
		
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
		// $this->load->model('msg_messages_model', 'msg');
		$this->load->model('msg_participants_model', 'msg_participants');
		// $this->load->model('msg_state_model', 'msg_state');

		
		$this->user = $this->ion_auth->user()->row();
		$this->location = $this->stock_location->get_all();
		
		# required on every page
		$this->page_data = array(
								"messages" 			=> $this->msg_participants->get_messages($this->user->id),
								"user" 				=> $this->user,
								"location" 			=> $this->_get_compass_locations(),
								"current_location" 	=> $this->_get_current_location(),
								"mondal" 			=> ($this->_get_current_location() == "none") ? $this->_get_mondal() : "",
						);
						
		$this->load->model('Alerts_model', 'alerts');
		$this->is_backup_cron();
		
		$this->page_data['alerts'] = $this->alerts->limit(5)->get_all();
		
		// $sections = array(
		// 'config'  => TRUE,
		// 'queries' => TRUE,
		// 'query_toggle_count' => 250
		// );

		// $this->output->set_profiler_sections($sections);
		// $this->output->enable_profiler(TRUE);
	}
	
	
	# verify if there is a backup action in last 7 days;
	public function is_backup_cron()
	{
		$last_backup = date_create($this->conf['backup_count']['updated_at']);
		$diff = date_diff($last_backup, date_create("now"));
		if ($diff->format('%a') >= $this->conf['alert_last_backup']['value']) {
			# warn the user
			$this->alerts->insert(array(
										"level" 	=> WARN,
										"msg" 		=> "No backup for " . $this->conf['alert_last_backup']['value'] . " days!",
									));
			# since we warned the user
			# update the last backup (to not spam the admin)
			$this->settings->where(array("name" => "backup_count"))->update(array("name" => "backup_count"));
		}
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
		// var_dump($data);
		// var_dump($this->page_data);
		$data = array_merge($data, $this->page_data);
		// var_dump($data);
		$this->load->view('header', $data);
		$this->load->view($page, $data);
		$this->load->view('footer', $data);
	}

	public function _get_csv($name, $data)
	{
		$this->load->helper('download');
		
		$file = $name . '_' . date('y_m_d') . '.csv';

		$fp = fopen(APPPATH . 'cache/' . $file, 'w');
		foreach ($data as $line) {
			fputcsv($fp, $line, ';', '"');
		}
		fclose($fp);
		
		force_download(APPPATH . 'cache/' . $file, null);
	}
	
	public function _get_mondal()
	{
		return $this->load->view('mondal/location', array("location" => $this->location), true);
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
