<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pets extends Vet_Controller
{

	public $d_pet_type 		= array(
									"0" => array("dog", "#f2a10d", "dog", DOG),
									"1" => array("cat", "#005248", "cat", CAT),
									"2" => array("horse", "#402E32", "horse", HORSE),
									"3" => array("bird", "#FFB087", "dove", BIRD),
									"5" => array("rabbit", "#AD4CF4", "paw", RABBIT),
									"4" => array("other", "#DFE0DF", "ghost", OTHER)
							);
	public $d_gender_type 	= array(
									"0" => array("Male", "#4c6ef5", "mars", MALE),
									"2" => array("Male neutered", "#000", "mars", MALE_NEUTERED),
									"1" => array("Female", "#f783ac", "venus", FEMALE),
									"3" => array("Female neutered", "#000", "venus", FEMALE_NEUTERED),
									"4" => array("Other", "#6cce23", "genderless", OTHER),
							);

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Pets_weight_model', 'pets_weight');
		$this->load->model('Breeds_model', 'breeds');
		$this->load->model('Events_model', 'events');
		$this->load->model('Vaccine_model', 'vacs_pet');
		$this->load->model('Rx_model', 'rx');
		$this->load->model('LabReport_model', 'lab_reports');
		$this->load->library('Pet_avatar');
		$this->load->library('Pet_avatar_manager');
	}

	public function index()
	{
		redirect('owners', 'refresh');
	}

	/*
	* function: push_weight
	* push a new weight value from owner page overview
	*/
	public function push_weight(int $pet_id)
	{
		if ($this->input->post("weight") > 0) {
			$this->pets_weight->insert(array(
												"pets" 		=> $pet_id,
												"weight" 	=> $this->input->post("weight")
											));
			$this->pets->update(
				array(
										"last_weight" => $this->input->post("weight")),
				$pet_id
			);
		}
	}

	# input new weight on weight page
	public function add_weight(int $pet_id)
	{
		if ($this->input->post('submit') && $this->input->post("weight") > 0) {
			$this->pets_weight->insert(array(
												"pets" => $pet_id,
												"weight" => $this->input->post("weight")
											));
			$this->pets->update(
				array(
										"last_weight" => $this->input->post("weight")),
				$pet_id
			);
		}
		redirect('/pets/history_weight/' . $pet_id, 'refresh');
	}

	# delete weight from weight page
	public function del_weight(int $weight_id, int $pet_id)
	{
		$this->pets_weight->delete($weight_id);
		redirect('/pets/history_weight/' . $pet_id, 'refresh');
	}

	public function history_weight(int $pet_id)
	{
		$data = array(
						"pets"				=> $this->pets->with_owners('fields:last_name, id')->fields('name, id')->where(array("id" => $pet_id))->get(),
						"weight_history" 	=> $this->pets_weight->where(array("pets" => $pet_id))->order_by("created_at", "asc")->get_all(),
					);

		$this->_render_page('pets/weight_history', $data);
	}

	public function add(int $owner)
	{
		if ($this->input->post('submit')) {
			if (
					is_null($this->input->post('type')) || 
					is_null($this->input->post('gender')) || 
					empty($this->input->post('name')))
			{
				redirect('/pets/add/' . $owner);
			}

			$weight = $this->input->post('weight');
			$pet_id = $this->pets->insert(array(
											"type" 			=> (int) $this->input->post('type'),
											"name" 			=> $this->input->post('name'),
											"gender" 		=> $this->input->post('gender'),
											"birth" 		=> $this->input->post('birth'),
											"breed" 		=> $this->input->post('breed'),
											"breed2" 		=> $this->input->post('breed2'),
											"color" 		=> $this->input->post('color'),
											"hairtype" 		=> $this->input->post('hairtype'),
											"companion" 	=> empty($this->input->post('companion')) ? null : $this->input->post('companion'),
											"chip" 			=> str_replace('-', '', $this->input->post('chip')),
											"last_weight" 	=> $weight,
											"nr_vac_book" 	=> $this->input->post('vacbook'),
											"note" 			=> $this->input->post('msg'),
											"owner" 		=> $this->input->post('owner'),
											"location"		=> $this->_get_user_location(),
											"init_vet"		=> $this->user->id
										));
			# if it failed
			if (!$pet_id) {
				redirect('/owners/detail/' . (int) $owner); 
			}

			# add weight to history
			if ($weight)
			{
				$this->pets_weight->insert(array(
										"pets" => $pet_id,
										"weight" => $this->input->post("weight")
									));
			}
			$this->logs->logger(INFO, "add_pet", "Added pet " . $this->input->post('name') . " (". $pet_id . ")");

			redirect('/pets/fiche/' . (int) $pet_id);
		
		}

		$data = array(
						"pet_type" 		=> $this->d_pet_type,
						"gender_type" 	=> $this->d_gender_type,
						"owner" 		=> $this->owners->get($owner)
					);

		$this->_render_page('pets/add', $data);
	}

	public function delete(int $pet_id)
	{
		# check if user is admin
		if (!$this->ion_auth->in_group("admin")) { redirect('/'); }

		$pet_info = $this->pets->get($pet_id);

		# delete the pet
		$this->pets->delete($pet_id);

		# log it
		$this->logs->logger(INFO, "delete_pet", "Deleted pet " . $pet_info['name'] . " (#" . $pet_id . ")");

		# send admin to overview
		redirect('/owners/detail/' . (int) $pet_info['owner']);
	
	}

	public function edit($pet_id)
	{
		if ($this->input->post('submit')) {

			# if breed isn't defined check current_breed
			$breed = (!$this->input->post('breed')) ? $this->input->post('current_breed') : $this->input->post('breed');

			# second breed options :
			#	- no second given, but there is one in current_breed2 ==> current_breed2
			#	- second set to -1 ==> NULL
			#	- second given ==> int
			$breed2_input = $this->input->post('breed2');
			$breed2 = ($breed2_input) ? 
									(($breed2_input == -1) ? NULL: (int) $breed2_input) 
									: 
									$this->input->post('current_breed2');

			$this->pets->update(
				array(	
											"type" 			=> (int) $this->input->post('type'),
											"name" 			=> $this->input->post('name'),
											"gender" 		=> (!empty($this->input->post('gender_custom')) ? $this->input->post('gender_custom') : $this->input->post('gender')),
											"birth" 		=> $this->input->post('birth'),
											"breed" 		=> $breed,
											"breed2" 		=> $breed2,
											"color" 		=> $this->input->post('color'),
											"hairtype" 		=> $this->input->post('hairtype'),
											"companion"		=> empty($this->input->post('companion')) ? null : $this->input->post('companion'),
											"chip" 			=> str_replace('-', '', $this->input->post('chip')),
											"last_weight" 	=> $this->input->post('weight'),
											"nr_vac_book" 	=> $this->input->post('vacbook'),
											"nutritional_advice" => $this->input->post('nutritional_advice'),
											"medication" => $this->input->post('medication'),
											"note" 			=> $this->input->post('msg'),
											"lost" 			=> (is_null($this->input->post('lost'))) ? 0 : $this->input->post('lost'),
											"death" 		=> (is_null($this->input->post('dead'))) ? 0 : $this->input->post('dead'),
											"death_date" 	=> (is_null($this->input->post('dead'))) ? NULL : date('Y-m-d'), // this is problematic if they modify the pet after death
										),
				$pet_id
			);

			# add weight to history if it's new and not zero
			$weight = $this->pets_weight->fields('weight')->where(array('pets' => $pet_id))->order_by('created_at', 'DESC')->limit(1)->get();
			$prev_weight = (isset($weight['weight'])) ? $weight['weight'] : 0;
			$weight = $this->input->post('weight');

			if ($weight && $weight > 0 && $prev_weight != $weight) {
				$this->pets_weight->insert(array(
										"pets" => $pet_id,
										"weight" => $weight
									));
									
			}

			redirect('/pets/fiche/' . (int)  $pet_id);
		}

		$pet_info = $this->pets->with_owners()->with_breeds('fields: name')->with_breeds2('fields: name')->get($pet_id);
		$data = array(
						"pet_type" 		=> $this->d_pet_type,
						"gender_type" 	=> $this->d_gender_type,
						"pet" 			=> $pet_info,
						"owner" 		=> $pet_info['owners']
					);

		$this->_render_page('pets/profile', $data);
	}

	public function fiche(int $pet_id)
	{
		$pet_info = $this->pets->with_breeds('fields: name')->with_breeds2('fields: name')->with_pets_weight()->get($pet_id);
		if (!$pet_info) {
			show_404();
			return;
		}
		$other_pets = $this->pets->other_pets($pet_info['owner'], $pet_id);
		$pet_avatar_path = !empty($pet_info['avatar']) ? $this->pet_avatar->path($pet_info['avatar']) : false;

		$data = array(
			"pet"				=> $pet_info,
			"owner" 			=> $this->owners->get($pet_info['owner']),
			"pet_history"		=> $this->events->get_pet_history($pet_id),
			"vaccines" 			=> $this->vacs_pet->view($pet_id),
			"other_pets"		=> $other_pets,
			"pet_has_rx"		=> $this->rx->has_images_for_pet($pet_id),
			"pet_has_lab"		=> $this->lab_reports->has_for_pet($pet_id),
			"pet_avatar_available" => $pet_avatar_path !== false && is_file($pet_avatar_path) && is_readable($pet_avatar_path),
			"pet_avatar_message" => $this->session->flashdata('pet_avatar_message'),
			"pet_avatar_message_type" => $this->session->flashdata('pet_avatar_message_type'),
			"extra_header" => '<link href="' . base_url('assets/css/croppie.css') . '" rel="stylesheet">',
			"extra_footer" => '<script src="' . base_url('assets/js/croppie.min.js') . '"></script>'
				. '<script src="' . javascript('assets/js/pet-avatar.js') . '"></script>',
		);

		$this->_render_page('pets/fiche', $data);
	}

	public function save_avatar(int $pet_id)
	{
		$this->require_avatar_post();
		$source = isset($_FILES['pet_avatar_source']) ? $_FILES['pet_avatar_source'] : array();
		$result = $this->pet_avatar_manager->save(
			$pet_id,
			$source,
			(string) $this->input->post('pet_avatar_crop', false)
		);

		if ($result['status'] === 'unknown') {
			$this->set_avatar_feedback('danger', 'pet_avatar_unknown_pet');
			redirect('/', 'refresh');
			return;
		}
		if ($result['status'] === 'invalid') {
			$this->set_avatar_feedback('danger', $this->avatar_error_language_key($result['error']));
			redirect('/pets/fiche/' . $pet_id, 'refresh');
			return;
		}
		if ($result['status'] !== 'success') {
			$this->set_avatar_feedback('danger', 'pet_avatar_storage_error');
			redirect('/pets/fiche/' . $pet_id, 'refresh');
			return;
		}

		$this->set_avatar_feedback('success', $result['message_key']);
		redirect('/pets/fiche/' . $pet_id, 'refresh');
	}

	public function remove_avatar(int $pet_id)
	{
		$this->require_avatar_post();
		$result = $this->pet_avatar_manager->remove($pet_id);
		if ($result['status'] === 'unknown') {
			$this->set_avatar_feedback('danger', 'pet_avatar_unknown_pet');
			redirect('/', 'refresh');
			return;
		}
		if ($result['status'] !== 'success') {
			$this->set_avatar_feedback('danger', 'pet_avatar_storage_error');
			redirect('/pets/fiche/' . $pet_id, 'refresh');
			return;
		}

		$this->set_avatar_feedback('success', $result['message_key']);
		redirect('/pets/fiche/' . $pet_id, 'refresh');
	}

	public function avatar_file(int $pet_id)
	{
		$pet = $this->pets->fields('avatar')->get($pet_id);
		if (!$pet || empty($pet['avatar'])) {
			show_404();
			return;
		}

		$path = $this->pet_avatar->path($pet['avatar']);
		if ($path === false || !is_file($path) || !is_readable($path)) {
			show_404();
			return;
		}

		$this->output
			->set_content_type('image/jpeg')
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: private, no-cache, must-revalidate')
			->set_header('Content-Length: ' . filesize($path))
			->set_output(file_get_contents($path));
	}

	private function require_avatar_post()
	{
		if ($this->input->method(true) !== 'POST') {
			show_error($this->lang->line('pet_avatar_post_only'), 405);
			exit;
		}
	}

	private function set_avatar_feedback(string $type, string $language_key)
	{
		$this->session->set_flashdata('pet_avatar_message_type', $type);
		$this->session->set_flashdata('pet_avatar_message', $this->lang->line($language_key));
	}

	private function avatar_error_language_key(string $error): string
	{
		$keys = array(
			'size' => 'pet_avatar_too_large',
			'type' => 'pet_avatar_invalid_type',
			'dimensions' => 'pet_avatar_invalid_dimensions',
			'crop' => 'pet_avatar_invalid_crop',
			'storage' => 'pet_avatar_storage_error',
			'processing' => 'pet_avatar_processing_error',
			'invalid' => 'pet_avatar_invalid_image',
		);

		return isset($keys[$error]) ? $keys[$error] : 'pet_avatar_invalid_image';
	}

	public function export(int $pet_id)
	{
		$pet_info = $this->pets->with_breeds('fields: name')->with_breeds2('fields: name')->get($pet_id);

		# bad link
		if (!$pet_info) {
			redirect('/', 'refresh');
		}
		
		$pet_history = $this->
							events->
							with_products('fields:events_products.volume, unit_sell, name')->
							with_procedures('fields:events_procedures.volume, name')->
							with_vet('fields:first_name, last_name')->
							with_location('fields:name')->
							where(
								array(
										"pet" 			=> $pet_id,
										"no_history" 	=> 0,
										))->
							order_by('created_at', 'DESC')->
							get_all();

		$data = array(
			"pet_info"		=> $pet_info,
			"owner" 		=> $this->owners->get($pet_info['owner']),
			"pet_history"	=> $pet_history,
			"vaccines" 		=> $this->vacs_pet->where(array('pet' => $pet_id))->get_all()
		);

		# submit generate pdf
		if ($this->input->post('submit')) {
			$data['history_to_take'] = $this->input->post('history_to_take');
			$this->load->library('pdf');

			$filename = "export_" . $pet_id . "_".  date("m.d.y");

			$html = $this->load->view('export/pdf_pet_history', $data, true);

			# content, filename, provide as download
			$this->pdf->create($html, $filename, true);
		}

		$this->_render_page('pets/export', $data);
	}

	public function change_owner($pet_id, $new_owner = false)
	{
		$name = null;
		$street = null;
		$client = null;
		$result = null;

		if ($this->input->post('submit') == "name") {
			$name = $this->input->post('name');
			$result = (!empty($name)) ? $this->owners->search_by_name($name) : false;
		}
		
		if ($this->input->post('submit') == "client") {
			$client = $this->input->post('client');
			$result = (!empty($client)) ? $this->owners->where(array("id" => $client))->get_all() : false;
		}

		$new_owner_q = ($new_owner) ? $this->owners->get($new_owner) : false;

		$pet_info = $this->pets->get($pet_id);
		$data = array(
			"pet"			=> $pet_info,
			"name"			=> $name,
			"client"		=> $client,
			"result"		=> $result,
			"owner" 		=> $this->owners->get($pet_info['owner']),
			"new_owner"		=> $new_owner_q
		);

		$this->_render_page('pets/change_owner', $data);
	}

	public function change_owner_complete($pet_id, $new_owner)
	{
        // clone the pet and clear "identification" of old one
        $this->pets->transfer_pet($pet_id, $new_owner);

        $this->logs->logger(INFO, "transfer_pet", "pet " . $pet_id . " (new owner:". $new_owner . ")");
		redirect('owners/detail/' . $new_owner, 'refresh');
	}

	# used in lab lookups if we don't know who it is for
	# best to use the ID, but the name can be done also
	# the results of name lookup is limited to the last 5
	# ordered by last_bill of the client
	public function get_pet_name()
	{
		# if nothing given don't present random options.
		$query = $this->input->get("term");
		if (!$query)
		{
			echo json_encode(array("results" => array()));
			return;
		}

		$pets = array();
		if (is_numeric($query))
		{
			# search by id
			$name = $this->pets->with_owners()->get($query);
			$pets[] = array(
				"id" 	=> $name['id'], 
				"text" 	=> $name['name'] . "(#". $name['id'] . ") - " . $name['owners']['last_name']
			);
		}
		else
		{
			# search by string
			$names = $this->pets->search_by_name(strtolower($query), 5);
			foreach ($names as $name)
			{
				$pets[] = array(
						"id" 	=> $name['pet_id'], 
						"text" 	=> $name['name'] . "(#". $name['pet_id'] . ") - " . $name['last_name']
					);
			}
		}
		echo json_encode(array("results" => $pets));
	}

    /*
    * function: check_chip
    * check if a chip is already in the system, but ignore current_pet
    */
    public function check_chip(string $current_pet, string $chip_code)
    {
        $pet = $this->pets
                    ->fields('id, name, chip')
                    ->with_owners('fields:id, last_name')
                    ->where(array('chip' => $chip_code))
                    ->where('id !=', $current_pet)
                    ->get();

        $response = array('status' => '404');
        if ($pet) {
            $response = array(
                'status'    => '200',
                'pet_id'    => $pet['id'],
                'pet_name'  => $pet['name'],
                'owner_id'  => $pet['owners']['id'],
                'owner_name'=> $pet['owners']['last_name']
            );
        }
        
        echo json_encode($response);
    }

}
