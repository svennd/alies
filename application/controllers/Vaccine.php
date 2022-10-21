<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vaccine extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Vaccine_model', 'vacs');

		# helpers
		$this->load->helper('file_download');
	}
	
	public function index(int $month = 1, $export = false)
	{
		# get first day of the month
		$date = new DateTime('first day of this month');

		# increase or decreate month by settings
		$date->modify($month . 'month');

		$data = array(
				"month" => $date->format('F'),
				"year" => $date->format('Y'),
				"month_int"	=> $month,
				"expiring_vacs" => $this->vacs->get_expiring_vaccines($date->format('Y-m-d H:i:s'))
		);
		if ($export)
		{
			$csv = $this->load->view('vaccine/export', $data, true);
			array_to_csv_download($csv, "vaccines_" . $date->format('M_Y') . ".csv");

			// PII so, keep atleast a log
            $this->logs->logger(INFO, "downloaded_vaccine_list", "month : " . $month);
		}
		else
		{
			$this->_render_page('vaccine/overview', $data);
		}
	}

	public function fiche($pet_id)
	{
		$pet_info = $this->pets->with_owners('fields:first_name,last_name')->fields('id, type, name')->get($pet_id);

		$data = array(
					"pet_info" 	=> $pet_info,
					"pet_id" 	=> $pet_id,
					"vaccines" 	=> $this->vacs
											->with_vet('fields: first_name')
											->with_product('fields: name, vaccin_freq')
											->with_location('fields: name')
											->where(array('pet' => $pet_id))
											->get_all()
				);
		;
		$this->_render_page('vaccine/pet_fiche', $data);
	}
}
