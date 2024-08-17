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
		$this->load->model('Lab_model', 'lab');
		$this->load->model('Lab_detail_model', 'lab_line');

		// library
		$this->load->library('pdf'); 
	}

	public function index()
    {
    	$this->_render_page('lab/index', array(
			"data" => $this->lab->get_labs(),
		));
	}

    public function detail(int $lab_id)
    {
		# update comment
		$comment_update = false;
		if ($this->input->post('submit')) {
			$this->lab->
				update(
					array( "pet" => (int) $this->input->post('pet_id'), "comment" => $this->input->post('message')),
					$lab_id
				);
			$comment_update = true;
			$this->logs->logger(INFO, "lab_modify", " lab_id : " . $lab_id . " data:" . var_export($this->input->post(), true));
		}

		# check if we need to add event
		if ($this->input->post('pet_id') && !$this->input->post('no_event'))
		{
			$this->add_lab_event($lab_id, (int) $this->input->post('pet_id'));
		}


		$lab_info = $this->lab->with_pet('fields: name, id')->get($lab_id);
		$pet_info = (isset($lab_info['pet'])) ? $this->pets->with_breeds()->get($lab_info['pet']) : false;
		$lab_details = $this->lab_line->where(array('lab_id' => $lab_id))->get_all();

		$owner = ($pet_info) ? $this->owners->where(array('id' => $pet_info['owner']))->get() : false;

		$data = array(
			"lab_info" 			=> $lab_info,
			"pet_info" 			=> $pet_info,
			"lab_details" 		=> $lab_details,
			"owner" 			=> $owner,
            "comment_update" 	=> $comment_update
		);
		
		$this->_render_page('lab/detail', $data);
    }
	
	/*
	* function: print
	* create a printable pdf from the lab results
	*/
	public function print(int $lab_id)
    {
		$lab_info = $this->lab->get($lab_id);
		$pet_info = (isset($lab_info['pet'])) ? $this->pets->with_breeds()->get($lab_info['pet']) : false;
		$lab_details = $this->lab_line->where(array('lab_id' => $lab_id))->get_all();

		$owner = ($pet_info) ? $this->owners->where(array('id' => $pet_info['owner']))->get() : false;

		$data = array(
			"lab_info" 			=> $lab_info,
			"pet_info" 			=> $pet_info,
			"lab_details" 		=> $lab_details,
			"owner" 			=> $owner
		);

		if ($lab_info['source'] == "mslink - HEMATO")
		{
			list($wbc_plot, $rbc_plot, $thr_plot) = $this->get_static_plots($lab_details);
			$data["wbc_plot"] = $wbc_plot;
			$data["rbc_plot"] = $rbc_plot;
			$data["thr_plot"] = $thr_plot;
		}
		
		// test code
		// $this->load->view('lab/print', $data);

		// pdf code
		$template_data = $this->load->view('lab/print', $data, true);
		return $this->pdf->create($template_data, '-', PDF_STREAM, true);
		
    }

	# reset the lab link
	# in case vet made a booboo
	public function reset_lab_link(int $lab_id)
	{
		$lab_info = $this->lab->get($lab_id);

		# check if we have a pet
		if (isset($lab_info['pet']))
		{
			$this->lab->update(array("pet" => null), $lab_id);
			$this->events->where(array(
						"title" => "lab:" . $lab_id,
						"pet" 	=> $lab_info['pet']
						))
						->limit(1) // just to be sure
						->delete(); 

			$this->logs->logger(INFO, "lab_reset", " lab_id : " . $lab_id);
		}
		redirect('lab/detail/' . $lab_id);
	}

	# set the internal pet id
	private function add_lab_event(int $lab_id, int $pet_id)
	{
		# generate a report for the events
		$lines = $this->lab_line->where(array('lab_id' => $lab_id))->get_all();
		$anamnese = "Lab results\n\n";
		foreach ($lines as $line)
		{
			$anamnese .= $line['lab_code_text'] . " : " . (($line["value"] != 0 && strlen($line["string_value"]) <= 1) ? $line["string_value"] . $line["value"] : $line["string_value"]) . $line["unit"] . "\n";
		}
		$anamnese .= "\n";

		$this->lab->add_event($lab_id, $pet_id, $anamnese);
	}
	
	/*
	* function: get_static_plots
	* a bit of boiler plate code to generate the static plots
	*/
	private function get_static_plots(array $lab_details)
	{

		foreach($lab_details as $d):
			if ($d["lab_code"] == "1")
			{
				$WBC = substr($d["comment"], 4);
			}
			if ($d["lab_code"] == "2")
			{
				$RBC = substr($d["comment"], 4);
			}
			if ($d["lab_code"] == "3")
			{
				$THR = substr($d["comment"], 4);
			}
		endforeach;

		$wbc_plot = $this->generateBase64Chart($WBC, "WBC");
		$rbc_plot = $this->generateBase64Chart($RBC, "RBC");
		$thr_plot = $this->generateBase64Chart($THR, "THR");

		return array($wbc_plot, $rbc_plot, $thr_plot);
	}

	private function generateBase64Chart($dataString, $title = "") {
		// Convert the comma-separated string to an array of data points
		$data = array_map('intval', explode(',', $dataString));
	
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
	
}