<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lab extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Lab_model', 'lab');
		$this->load->model('Lab_detail_model', 'lab_line');
	}

	public function index()
    {
    	$this->_render_page('lab/index', array(
			"data" => $this->lab->with_pet('fields: name,id')->get_all(),
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

    	$this->_render_page('lab/detail', array(
			"lab_info" 			=> $this->lab->with_pet('fields: name, id')->get($lab_id),
			"lab_details" 		=> $this->lab_line->where(array('lab_id' => $lab_id))->get_all(),
            "comment_update" 	=> $comment_update
		));
		
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

		$this->events->insert(array(
				"title" 	=> "lab:" . $lab_id,
				"pet"		=> $pet_id,
				"type"		=> LAB,
				"status"	=> STATUS_CLOSED, # might require status_history
				"payment" 	=> PAYMENT_PAID,
				"anamnese"	=> $anamnese,
				"location"	=> $this->_get_user_location(),
				"vet"		=> $this->user->id,
				"report"	=> REPORT_DONE
		));
	}
}