<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vaccine extends Vet_Controller
{
	
	# constructor
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Vaccine_model', 'vacs');

		# helpers
		$this->load->helper('file_download');
	}
	
	public function index(int $month = 1)
	{
		# safety check
		if (abs($month) >= 3 && !$this->ion_auth->in_group("admin"))
		{
            $this->logs->logger(WARN, "boundary_vaccine_attack", "month : " . $month);
			redirect('/');
		}

		$date = $this->get_month($month);

		$data = array(
				"month" 		=> $date->format('F'),
				"year" 			=> $date->format('Y'),
				"month_int"		=> $month,
				"expiring_vacs" => $this->vacs->get_expiring_vaccines($date->format('Y-m-d H:i:s'))
		);
		
		$this->_render_page('vaccine/overview', $data);
	}

    /*
    * function: export_vaccine
    * export vaccination card of a pet
    */
    public function export_vaccine(int $pet_id)
	{
		$pet_info = $this->pets->with_breeds('fields: name')->with_breeds2('fields: name')->get($pet_id);

		# bad link
		if (!$pet_info) {
			redirect('/', 'refresh');
		}

		$data = array(
			"pet_info"		=> $pet_info,
			"owner" 		=> $this->owners->get($pet_info['owner']),
			"vaccines" 		=> $this->vacs
											->with_vet('fields: first_name, last_name')
											->with_product('fields: name, vaccin_freq')
											->with_location('fields: name')
											->where(array('pet' => $pet_id))
											->get_all()
		);

        $this->load->library('pdf');

        $filename = "export_" . $pet_id . "_vaccines_".  date("m.d.y");

        $html = $this->load->view('export/pdf_pet_vaccination', $data, true);
        
        # content, filename, provide as download
        $this->pdf->create($html, $filename, true);
	}

	/*
	* function: export - internal use
	* export a filtered list if required
	*/
	public function export(int $month = 1)
	{
		# safety check
		if (abs($month) >= 3 && !$this->ion_auth->in_group("admin"))
		{
			$this->logs->logger(WARN, "boundary_vaccine_download_attack", "month : " . $month);
			redirect('/');
		}
		
		$date = $this->get_month($month);

		$data = array(
						"month" 		=> $date->format('F'),
						"year" 			=> $date->format('Y'),
						"month_int"		=> $month,
						"excluded_products" => $this->vacs->get_expiring_vaccines_product_list($date->format('Y-m-d H:i:s'))	
		);

		if ($this->input->post('submit'))
		{
			// do download
			$exclusion = array_filter(array_map('intval', (array) $this->input->post('excluded_products')));
			$data['expiring_vacs'] = $this->vacs->get_expiring_vaccines($date->format('Y-m-d H:i:s'), $exclusion);
			$data['date_format'] = $this->input->post('date_format');

			$csv = $this->load->view('vaccine/export', $data, true);
			
			array_to_csv_download($csv, "vaccines_" . $date->format('M_Y') . ".csv");

			// PII so, keep atleast a log
            $this->logs->logger(INFO, "downloaded_vaccine_list", "month : " . $month);
		}
		else
		{
			$this->_render_page('vaccine/export_process', $data);
		}
	}
	
	public function fiche(int $pet_id)
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

	# show vaccine details
	public function detail(int $vaccine_id)
	{
		$data = array(
			'vac' => $this->vacs->with_product('fields:id,name')->with_pet('fields:id, name, type')->get($vaccine_id),
		);

		$this->_render_page('vaccine/detail', $data);
	}

	# change vaccine details
	public function update(int $vaccine_id, int $pet_id)
	{
		$this->vacs->update(array(
								"no_rappel" => is_null($this->input->post('no_rappel')) ? 1 : 0,
								"redo" 		=> $this->input->post('date_redo')
								), $vaccine_id);

		$this->logs->logger(DEBUG, "change_vaccine", "vaccine_id ".  $vaccine_id);
	
		redirect('vaccine/fiche/'. $pet_id);
	}

	/*
	* function: remove
	* remove a vaccine from a pet
	*/
	public function remove(int $vaccine_id, int $pet_id)
	{
		$this->vacs->delete($vaccine_id);
		$this->logs->logger(INFO, "remove_vaccine", "pet_id ". $pet_id . "vaccine_id ".  $vaccine_id);
		redirect('vaccine/fiche/'. $pet_id);
	}

	/*
	* function: add_martian_vaccine
	* add documentation of a vaccine that was not done in our clinic
	*/
	public function add_martian_vaccine(int $pet_id)
	{

		if ($this->input->post('submit')) {
			$this->vacs->martian(
				$pet_id,
				array(
				"product"		=> $this->input->post('vaccine'),
				"redo" 			=> $this->input->post('date_redo'),
				"no_rappel" 	=> is_null($this->input->post('no_rappel')) ? 1 : 0,
				"created_at" 	=> $this->input->post('created_at'),
				"location" 		=> $this->_get_user_location(),
			));

			$this->logs->logger(INFO, "add_martian_vaccine", "pet_id ". $pet_id);
			redirect('vaccine/fiche/'. $pet_id);
		}

		$data = array(
			"pet_id" 	=> $pet_id,
			"pet" 		=> $this->pets->fields('id, name')->get($pet_id),
		);

		$this->_render_page('vaccine/add', $data);
	}


	/*
	*	function: get_month
	*	get the current month (small helper)
	*/
	private function get_month(int $month): DateTime
	{
		# get first day of the month
		$date = new DateTime('first day of this month');

		# increase or decreate month by settings
		$date->modify($month . 'month');

		return $date;
	}
}
