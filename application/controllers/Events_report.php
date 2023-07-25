<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Events_report extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Products_model', 'products');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Events_upload_model', 'events_upload');
		$this->load->model('Events_procedures_model', 'eproc');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Booking_code_model', 'booking');
		$this->load->model('Vaccine_model', 'vaccine');
		$this->load->model('Bills_model', 'bills');
	}

	# in case its only medication pickup or food
	# and this isn't relevant for medical history purpose
	public function disable_history($event_id)
	{
		$this->events->update(array('no_history' => 1), $event_id);
		redirect('events/event/' . $event_id);
	}

	# the reverse
	public function enable_history($event_id)
	{
		$this->events->update(array('no_history' => 0), $event_id);
		redirect('events/event/' . $event_id);
	}

	public function set_type(int $event_id, int $type)
	{
		$this->events->update(array('type' => $type), $event_id);
		redirect('events/event/' . $event_id);	
	}

	public function update_report($event_id)
	{
		if ($this->events->get_status($event_id) == STATUS_HISTORY) {
			echo "cannot change due to status : status_history";
			return false;
		}

		if ($this->input->post('submit') != 'report' && $this->input->post('submit') != 'finished_report') { echo "no post data"; return false; }

		# log this
		$this->logs->logger(DEBUG, "update_report", "report_id: " . $event_id);
		
		// var_dump($this->input->post());

		# loop all posted extra vets
		$extra_vets = $this->input->post('sup_vet');
		$vet_1 = false;
		$vet_2 = false;
		if ($extra_vets)
		{
			$vet_1 = $extra_vets['0'];
			$vet_2 = (isset($extra_vets['1'])) ? $extra_vets['1'] : false;
		}
		// var_dump($vet_1,$vet_2);
		# event update
		$this->events->update(
			array(
					"title" 					=> $this->input->post('title'),
					"anamnese" 					=> $this->input->post('anamnese'),
					// "type" 						=> (int) $this->input->post('type'),
					"vet_support_1"				=> $vet_1,
					"vet_support_2" 			=> $vet_2,
					"report"					=> ($this->input->post('submit') == 'finished_report') ? REPORT_DONE : REPORT_OPEN,
					),
			$event_id
		);

		# report is finished, redirect to pet overview
		if ($this->input->post('submit') == 'finished_report') {
			redirect('/pets/fiche/' . $this->input->post('pet_id'));
		}
		redirect('/events/event/' . $event_id . '/report');

	}

	# auto save function
	public function anamnese($event_id)
	{
		$title = $this->input->post('title');
		$anamnese = $this->input->post('anamnese');
		
		return (!empty($anamnese)) ? 
			$this->events->where(array("id" => $event_id))->where("report", "!=", REPORT_DONE)->update(array("anamnese" => $anamnese, "title" => $title)) 
			:
			0;
	}
}