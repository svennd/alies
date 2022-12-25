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
			"data" => $this->lab->get_all(),
		));
	}


    public function detail(int $lab_id)
    {
		# update comment
		$comment_update = false;
		if ($this->input->post('submit')) {
			$this->lab
					->where(array(
									"id" 	=> (int) $lab_id
							))
					->update(array(
									"comment" => $this->input->post('message'),
							));
			$comment_update = true;
		}

    	$this->_render_page('lab/detail', array(
			"lab_info" => $this->lab->get($lab_id),
			"lab_details" => $this->lab_line->where(array('lab_id' => $lab_id))->get_all(),
            "comment_update" => $comment_update
		));
    }
}