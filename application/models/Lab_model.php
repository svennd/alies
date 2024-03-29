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

	public function add_sample(int $lab_id, array $lab_info, string $source) 
	{
		$add_sample = "
		INSERT INTO 
			lab 
			(
				lab_id, 
				lab_date, lab_patient_id, lab_updated_at, lab_created_at, lab_comment,
				source, 
				updated_at, created_at
			)
		VALUES 
			(
				'". $lab_id ."',
				'". $lab_info['lab_date'] ."','". $lab_info['lab_patient_id'] ."','". $lab_info['lab_updated_at'] ."','". $lab_info['lab_created_at'] ."','". $lab_info['lab_comment'] ."',
				'". $source ."',
				'" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "'
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
}
