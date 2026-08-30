<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Lab
class Lab extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');

        // new API models
        $this->load->model('LabReport_model', 'reports');
        $this->load->model('LabResult_model', 'lab_results');
        $this->load->model('LabPlots_model', 'lab_plots');
		$this->load->library('lab_result_presenter');
		$this->load->library('LabResultService');
		$this->load->library('lab_pending_preview');

		// library
		$this->load->library('pdf'); 

		// helper
		$this->load->helper('lab');
	}

	public function pending()
	{
		$pending_rows = array();
		foreach ($this->labPending->get_active() as $row) {
			$identifiers = json_decode((string) $row['identifiers'], true);
			$pending_rows[] = array(
				'id' => (int) $row['id'],
				'device' => $row['device'],
				'source' => $row['source'],
				'source_id' => $row['source_id'],
				'reason' => $row['reason'],
				'created_at' => $row['created_at'],
				'first_received_at' => $row['created_at'],
				'last_received_at' => $row['last_received_at'] ?: $row['created_at'],
				'identifiers' => is_array($identifiers) ? array_filter($identifiers, static function ($value) {
					return $value !== null && $value !== '';
				}) : array(),
			);
		}

		$this->_render_page('lab/pending', array(
			'pending_results' => $pending_rows,
			'pending_message' => $this->session->flashdata('pending_lab_message'),
			'pending_message_type' => $this->session->flashdata('pending_lab_message_type'),
		));
	}

	public function pending_detail(int $pending_id): void
	{
		$pending = $this->labPending->get_active_by_id($pending_id);
		if (!$pending) {
			show_404();
			return;
		}

		$identifiers = json_decode((string) $pending['identifiers'], true);
		$built = $this->lab_pending_preview->build($pending);
		$this->_render_page('lab/pending_detail', array(
			'pending' => $pending,
			'identifiers' => is_array($identifiers) ? array_filter($identifiers, static function ($value) {
				return $value !== null && $value !== '';
			}) : array(),
			'preview' => $built['preview'],
			'preview_warning' => $built['warning'],
			'raw_json' => $built['raw_json'],
			'pending_message' => $this->session->flashdata('pending_lab_message'),
			'pending_message_type' => $this->session->flashdata('pending_lab_message_type'),
		));
	}

	public function search_owners(): void
	{
		$term = trim((string) $this->input->get('term'));
		$results = array();
		if ($term !== '') {
			foreach ($this->owners->search_for_lab_assignment($term) as $owner) {
				$name = trim(($owner['first_name'] ?? '') . ' ' . ($owner['last_name'] ?? ''));
				$address = trim(($owner['street'] ?? '') . ' ' . ($owner['nr'] ?? '') . ' ' . ($owner['zip'] ?? ''));
				$results[] = array(
					'id' => (int) $owner['id'],
					'text' => $name . ' (#' . (int) $owner['id'] . ')' . ($address !== '' ? ' - ' . $address : ''),
				);
			}
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('results' => $results)));
	}

	public function search_pets(): void
	{
		$owner_id = (int) $this->input->get('owner_id');
		$term = trim((string) $this->input->get('term'));
		$results = array();
		if ($this->owners->is_assignable($owner_id)) {
			foreach ($this->pets->search_assignable_for_owner($owner_id, $term) as $pet) {
				$results[] = array(
					'id' => (int) $pet['id'],
					'text' => $pet['name'] . ' (#' . (int) $pet['id'] . ')',
				);
			}
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('results' => $results)));
	}

	public function recover_pending(int $pending_id): void
	{
		$this->require_pending_mutation();
		$owner_id = (int) $this->input->post('owner_id');
		$pet_id = (int) $this->input->post('pet_id');
		$result = $this->labresultservice->recover_pending($pending_id, $owner_id, $pet_id, (int) $this->user->id);

		if ($result['status'] === 'ok') {
			$this->logs->logger(
				INFO,
				'pending_lab_recovered',
				'pending_id: ' . $pending_id . ' | report_id: ' . (int) $result['report_id'] . ' | owner_id: ' . $owner_id . ' | pet_id: ' . $pet_id . ' | user_id: ' . (int) $this->user->id
			);
			$this->set_pending_feedback('success', $this->lang->line('lab_pending_recovered'));
			redirect('lab/detail/' . (int) $result['report_id']);
			return;
		} else {
			$this->set_pending_feedback('danger', $result['message']);
		}

		redirect('lab/pending_detail/' . $pending_id);
	}

	public function delete_pending(int $pending_id): void
	{
		$this->require_pending_mutation();
		$result = $this->labresultservice->soft_delete_pending($pending_id, (int) $this->user->id);

		if ($result['status'] === 'ok') {
			$this->logs->logger(
				INFO,
				'pending_lab_deleted',
				'pending_id: ' . $pending_id . ' | user_id: ' . (int) $this->user->id
			);
			$this->set_pending_feedback('success', $this->lang->line('lab_pending_deleted'));
		} else {
			$this->set_pending_feedback('danger', $result['message']);
		}

		redirect('lab/pending');
	}

	private function require_pending_mutation(): void
	{
		if ($this->input->method(true) !== 'POST') {
			show_error($this->lang->line('lab_pending_post_only'), 405);
			exit;
		}
	}

	private function set_pending_feedback(string $type, string $message): void
	{
		$this->session->set_flashdata('pending_lab_message_type', $type);
		$this->session->set_flashdata('pending_lab_message', $message);
	}

	public function index()
    {
		$dt = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $dt->format('Y-m-d');
		$dt->modify('-2 month');
		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $dt->format('Y-m-d');

    	$this->_render_page('lab/index', array(
			"data" => $this->reports->get_labs($search_from, $search_to),
			"search_from"	=> (isset($search_from)) ? $search_from : '',
			"search_to"		=> (isset($search_to)) ? $search_to : '',
		));
	}

	public function detail(int $lab_id)
	{
		$lab_info    = $this->reports->with_pet('fields: name, id')->get($lab_id);
		$pet_info    = isset($lab_info['pet']) ? $this->pets->with_breeds()->get($lab_info['pet']) : false;
		$lab_details = $this->lab_results->where(['report_id' => $lab_id])->get_all();
		$owner       = $pet_info ? $this->owners->where(['id' => $pet_info['owner']])->get() : false;
		$plots_raw 	 = $this->lab_plots->where(['report_id' => $lab_id])->get_all();

		$plots = [];
		if (is_array($plots_raw)) {
			foreach ($plots_raw as $p) {
				$plots[$p['type']] = json_decode($p['data'], true);
			}
		}

		$lab_details = $this->lab_result_presenter->normalize_many($lab_details);

		$data = [
			"lab_id"      => $lab_id,
			"lab_info"    => $lab_info,
			"pet_info"    => $pet_info,
			"lab_details" => $lab_details,
			"owner"       => $owner,
			"plots"       => $plots,
			"lab_message" => $this->session->flashdata('pending_lab_message'),
			"lab_message_type" => $this->session->flashdata('pending_lab_message_type'),
		];

		$this->_render_page('lab/detail', $data);
	}

	public function reassign(int $lab_id): void
	{
		$this->require_pending_mutation();
		$owner_id = (int) $this->input->post('owner_id');
		$pet_id = (int) $this->input->post('pet_id');
		$result = $this->labresultservice->reassign_report($lab_id, $owner_id, $pet_id);

		if ($result['status'] === 'ok') {
			$this->logs->logger(
				INFO,
				'lab_report_reassigned',
				'report_id: ' . $lab_id
				. ' | old_owner_id: ' . (int) $result['old_owner_id']
				. ' | old_pet_id: ' . (int) $result['old_pet_id']
				. ' | owner_id: ' . (int) $result['owner_id']
				. ' | pet_id: ' . (int) $result['pet_id']
				. ' | removed_event_links: ' . (int) $result['removed_event_links']
				. ' | user_id: ' . (int) $this->user->id
			);
			$this->set_pending_feedback('success', $this->lang->line('lab_reassigned'));
		} elseif ($result['status'] === 'noop') {
			$this->set_pending_feedback('info', $this->lang->line('lab_reassign_noop'));
		} else {
			$this->set_pending_feedback('danger', $result['message']);
		}

		redirect('lab/detail/' . $lab_id);
	}
	
	/*
	* function: print
	* create a printable pdf from the lab results
	*/
	public function print(int $lab_id)
    {
		$lab_info    = $this->reports->with_pet('fields: name, id')->get($lab_id);
		$pet_info    = isset($lab_info['pet']) ? $this->pets->with_breeds()->get($lab_info['pet']) : false;
		$lab_details = $this->lab_results->where(['report_id' => $lab_id])->get_all();
		$owner       = $pet_info ? $this->owners->where(['id' => $pet_info['owner']])->get() : false;
		$plots_raw 	 = $this->lab_plots->where(['report_id' => $lab_id])->get_all();

		$plots = [];
		$plots_base64 = [];
		if (is_array($plots_raw)) {
			foreach ($plots_raw as $p) {
				$plots[$p['type']] = json_decode($p['data'], true);
				$plots_base64[$p['type']] = $this->generateBase64Chart($plots[$p['type']], $p['type']);
			}
		}

		$lab_details = $this->lab_result_presenter->normalize_many($lab_details);

		$data = [
			"lab_info"    => $lab_info,
			"pet_info"    => $pet_info,
			"lab_details" => $lab_details,
			"owner"       => $owner,
			"plots"		 => $plots,
			"plots_base64" => $plots_base64
		];

		// $this->_render_page('lab/detail', $data);
		// test code
		// $this->load->view('lab/print', $data);

		// pdf code
		$template_data = $this->load->view('lab/print', $data, true);
		return $this->pdf->create($template_data, '-', PDF_STREAM, true);
		
    }

	/*
	* function: list_lab
	* list all lab results for a pet
	*/
	public function list_lab(int $pet_id)
	{
		$pet_info = $this->pets->with_owners('fields: id, last_name')->get($pet_id);

		if (!$pet_info) {
			redirect('/', 'refresh');
		}

		$this->_render_page('lab/list_lab', array(
			"pet_id" 		=> $pet_id,
			"pet_info" 		=> $pet_info,
			"lab_results" 	=> $this->reports->get_for_pet($pet_id)
		));
	}

	private function generateBase64Chart($data, $title = "") {
		// Convert the comma-separated string to an array of data points
		// $data = array_map('intval', explode(',', $dataString));
	
		// Create a blank image
		$width = 150;
		$height = 75;
		$image = imagecreatetruecolor($width, $height);
	
		// Allocate colors
		$bgColor = imagecolorallocate($image, 255, 255, 255); // White background
		$fillColor = imagecolorallocatealpha($image, 0, 0, 0, 100); // fill with transparency
		$textColor = imagecolorallocate($image, 0, 0, 0); // Black text

		// Fill the background
		imagefill($image, 0, 0, $bgColor);
	
		// Define chart parameters
		$numPoints = count($data);
		$pointWidth = $width / ($numPoints - 1);
		$maxDataValue = max($data);
	
		// Draw the line
		$prevX = 0;
		$prevY = $height - ($data[0] * ($height / $maxDataValue));
		$points = [];
	
		for ($i = 1; $i < $numPoints; $i++) {
			$x = $i * $pointWidth;
			$y = $height - ($data[$i] * ($height / $maxDataValue));
			$points[] = $x;
			$points[] = $y;
			$prevX = $x;
			$prevY = $y;
		}
	
		// Fill below the line
		$points[] = $width;
		$points[] = $height;
		imagefilledpolygon($image, $points, $fillColor);
	    
		if (!empty($title)) {
			$fontSize = 3; // Built-in GD font size
			$bbox = imagefontwidth($fontSize) * strlen($title);
			$x = 10;
			$y = 10; // Position from the top
	
			imagestring($image, $fontSize, $x, $y, $title, $textColor);
		}

		// Output the image as a Base64 string
		ob_start();
		imagepng($image);
		$imageData = ob_get_contents();
		ob_end_clean();
	
		imagedestroy($image);
	
		// Convert to Base64
		return 'data:image/png;base64,' . base64_encode($imageData);
	}
	
	public function delete(int $lab_id)
	{
		# check if admin 
		if (!$this->ion_auth->is_admin())
		{
			redirect('lab');
		}
		$this->reports->delete($lab_id);
		redirect('lab');
	}
}
