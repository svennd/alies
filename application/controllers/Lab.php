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
			"lab_info" => $this->lab->with_pet('fields: name, id')->get($lab_id),
			"lab_details" => $this->lab_line->where(array('lab_id' => $lab_id))->get_all(),
            "comment_update" => $comment_update
		));
		
    }

	# set the internal pet id
	private function add_lab_event(int $lab_id, int $pet_id)
	{
		$this->events->insert(array(
				"title" 	=> "lab:" . $lab_id,
				"pet"		=> $pet_id,
				"type"		=> LAB,
				"status"	=> STATUS_CLOSED, # might require status_history
				"payment" 	=> PAYMENT_PAID,
				"location"	=> $this->user->current_location,
				"vet"		=> $this->user->id,
				"report"	=> REPORT_DONE
		));
	}
}