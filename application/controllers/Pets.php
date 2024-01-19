<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pets extends Vet_Controller
{

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
	}

	public function index()
	{
		redirect('owners', 'refresh');
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
											"chip" 			=> str_replace('-', '', $this->input->post('chip')),
											"last_weight" 	=> $weight,
											"nr_vac_book" 	=> $this->input->post('vacbook'),
											"note" 			=> $this->input->post('msg'),
											"owner" 		=> $this->input->post('owner'),
											"location"		=> $this->user->current_location,
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
						"owner" => $this->owners->get($owner)
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
											"chip" 			=> $this->input->post('chip'),
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
			# add weight to history
			$this->pets_weight->insert(array(
									"pets" 		=> $pet_id,
									"weight" 	=> $this->input->post("weight")
								));

			redirect('/pets/fiche/' . (int)  $pet_id);
		}

		$pet_info = $this->pets->with_owners()->with_breeds('fields: name')->with_breeds2('fields: name')->get($pet_id);
		$data = array(
						"pet" => $pet_info,
						"owner" => $pet_info['owners']
					);

		$this->_render_page('pets/profile', $data);
	}

	public function fiche(int $pet_id)
	{
		$pet_info = $this->pets->with_breeds('fields: name')->with_breeds2('fields: name')->with_pets_weight()->get($pet_id);

		$pet_history = $this->
									events->
									with_products('fields:events_products.volume, unit_sell, name')->
									with_procedures('fields:events_procedures.volume, name')->
									with_vet('fields:first_name')->
									with_vet_1_sup('fields:first_name')->
									with_vet_2_sup('fields:first_name')->
									with_location('fields:name')->
									where(
										array(
												"pet" 			=> $pet_id,
												"no_history" 	=> 0
												))->
									order_by('created_at', 'DESC')->
									get_all();

		$other_pets = $this->pets->other_pets($pet_info['owner'], $pet_id);

		$data = array(
			"pet"				=> $pet_info,
			"owner" 			=> $this->owners->get($pet_info['owner']),
			"pet_history"		=> $pet_history,
			"vaccines" 			=> $this->vacs_pet->view($pet_id),
			"other_pets"		=> $other_pets,
		);

		$this->_render_page('pets/fiche', $data);
	}

	public function export($pet_id)
	{
		$pet_info = $this->pets->with_breeds('fields: name')->with_breeds2('fields: name')->get($pet_id);
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
										"type !=" 		=> 1,
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

			$html = $this->load->view('export_pdf', $data, true);

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
		$this->pets->update(array("owner" => $new_owner), $pet_id);
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
}