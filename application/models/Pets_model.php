<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Pets_model extends MY_Model
{
	public $table = 'pets';
	public $primary_key = 'id';
	
	public function __construct()
	{
		// enable soft deletes
		$this->soft_deletes = true;

		/*
			has_one
		*/
		$this->has_one['owners'] = array(
							'foreign_model' => 'Owners_model',
							'foreign_table' => 'owners',
							'foreign_key' => 'id',
							'local_key' => 'owner'
						);
						
		$this->has_one['breeds'] = array(
							'foreign_model' => 'Breeds_model',
							'foreign_table' => 'breeds',
							'foreign_key' => 'id',
							'local_key' => 'breed'
						);
						
		/*
			has_many
		*/
		$this->has_many['pets_weight'] = array(
							'foreign_model' => 'Pets_weight_model',
							'foreign_table' => 'Pets_weight',
							'foreign_key' => 'pets',
							'local_key' => 'id'
						);
						
		$this->has_many['vacs'] = array(
							'foreign_model' => 'Vaccine_pet_model',
							'foreign_table' => 'vaccine_pet',
							'foreign_key' => 'pet',
							'local_key' => 'id'
						);
						
		$this->has_many['tooths'] = array(
							'foreign_model' => 'Tooth_model',
							'foreign_table' => 'tooth',
							'foreign_key' => 'pet',
							'local_key' => 'id'
						);
		parent::__construct();
	}
	
	# used in search + search pet in lab
	public function search_by_name($query, int $limit = 250)
	{
		$query = $this->db->escape_like_str($query);
		$sql = "
			SELECT 
				pets.id as pet_id, pets.name, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				name LIKE '" . $this->db->escape_like_str($query) . "%' ESCAPE '!'
			AND
				death = 0
			ORDER BY
				owners.last_bill
			DESC
			LIMIT " . $limit . "
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_chip_ex($chip)
	{
		$sql = "
			SELECT 
				pets.name, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				chip LIKE '" . $this->db->escape_like_str($chip) . "%' ESCAPE '!'
			AND
				death = 0
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 5
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_id($id)
	{
		$sql = "
			SELECT 
				pets.name, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				pets.id = '" . (int) $id . "'
			AND
				death = 0
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 250
		";
		
		return $this->db->query($sql)->result_array();
	}
		
	public function get_per_type()
	{
		$pets_sql = "
			select 
				type,
				count(id) as amount
			from 
				pets
			where
				death = 0
			group by
				type			
		";
		return $this->db->query($pets_sql)->result_array();
	}
}
