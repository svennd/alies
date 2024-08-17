<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Lab_model extends MY_Model
{
	public $table = 'lab';
	public $primary_key = 'id';

	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['pet'] = array(
							'foreign_model' => 'Pets_model',
							'foreign_table' => 'pets',
							'foreign_key' => 'id',
							'local_key' => 'pet'
						);
		parent::__construct();
	}

	/*
	* function: get_labs
	* get all lab results w/ pets & owners
	*/
	public function get_labs()
	{
		$this->db->select('
							lab.*, 
							pets.name as pet_name, pets.id as pet_id, pets.type as pet_type,
							owners.last_name as last_name, owners.id as owners_id
						');
		$this->db->join('pets', 'pets.id = lab.pet', 'left');
		$this->db->join('owners', 'owners.id = pets.owner', 'left');
		$this->db->order_by('lab.id', 'desc');
		return $this->db->get('lab')->result_array();
	}

	public function add_sample(int $lab_id, array $lab_info, string $source, int $pet_id = 0) 
	{
		$add_sample = "
		INSERT INTO 
			lab 
			(
				lab_id, 
				lab_date, lab_patient_id, lab_updated_at, lab_created_at, lab_comment,
				source, 
				updated_at, created_at
				" . ($pet_id ? ", pet" : "") . "
			)
		VALUES 
			(
				'". $lab_id ."',
				'". $lab_info['lab_date'] ."','". $lab_info['lab_patient_id'] ."','". $lab_info['lab_updated_at'] ."','". $lab_info['lab_created_at'] ."','". $lab_info['lab_comment'] ."',
				'". $source ."',
				'" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "'
				" . ($pet_id ? ", '". $pet_id ."'" : "") . "
			)
		ON DUPLICATE KEY UPDATE
			id = LAST_INSERT_ID(id),
			updated_at = '" . date('Y-m-d H:i:s') . "';
		";
		// run query
		$this->db->query($add_sample);
		
		return $this->db->insert_id();
	}

	public function get_unassigned()
	{
		$sql = "
			SELECT 
				count(id) as count
			FROM
				lab
			WHERE
				pet is NULL
			LIMIT 9
		";
		return ($this->db->query($sql)->result_array()[0]['count']);
	}

	/*
	* function: add_mslink_sample
	* adds a new sample to the database based on the lab_id
	*/
	public function add_mslink_sample(int $run_id, array $info, string $source, $pet_id)
	{
		// make sure we don't duplicate enter
		$check = $this->fields("lab_id")->where(array("lab_id" => $run_id))->get();
		if ($check)
		{
			return false;
		}

		# make sure updated_at is configured as given
		$this->timestamps = false;

		return $this->insert(array(
			"lab_id"		=> $run_id,
			"lab_date"		=> $info['lab_date'],
			"lab_patient_id"=> $pet_id,
			"lab_updated_at"=> $info['lab_updated_at'],
			"lab_created_at"=> $info['lab_created_at'],
			"lab_comment"	=> $info['lab_comment'],
			"source"		=> $source,
			"pet"			=> $pet_id,
			"updated_at"	=> $info['lab_updated_at'],
			"created_at"	=> $info['lab_created_at']
		));
	}

	/*
	* function: add_event
	* adds a new event to the database based on the lab_id
	*/
	public function add_event($lab_id, $pet_id, $anamnese)
	{
		$this->events->insert(array(
			"title" 	=> "lab:" . $lab_id,
			"pet"		=> $pet_id,
			"type"		=> LAB,
			"status"	=> STATUS_CLOSED, # might require status_history
			"payment" 	=> PAYMENT_PAID,
			"anamnese"	=> $anamnese,
			"location"	=> 0,
			"vet"		=> 0,
			"report"	=> REPORT_DONE
		));
	}
}