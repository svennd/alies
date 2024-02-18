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
						
		$this->has_one['breeds2'] = array(
							'foreign_model' => 'Breeds_model',
							'foreign_table' => 'breeds',
							'foreign_key' => 'id',
							'local_key' => 'breed2'
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
				pets.id as pet_id, pets.name, pets.type as type, owners.*
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
				pets.name, pets.type as type, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				chip LIKE '" . $this->db->escape_like_str($chip) . "%' ESCAPE '!'
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 5
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_id(int $id)
	{
		$sql = "
			SELECT 
				pets.name, pets.type as type, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				pets.id = '" . $id . "'
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 5
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

	// owners
	public function get_all_pets(int $owner)
	{
		return $this->with_breeds('field:name')->with_breeds2('field:name')->where(array("owner" => $owner))->order_by(array("birth, death"), "desc")->get_all();
	}

	/*
		generate a list of pets of the owner
		that isn't the current_pet
	*/
	public function other_pets(int $owner, int $pet_id, int $limit = 5)
	{
		return $this->where(array('owner' => $owner, 'death' => 0, 'lost' => 0))->where('id !=', $pet_id)->fields('id, type, name')->limit($limit)->get_all();
	}


	/*
		cli cron job
	*/
	public function auto_death(int $type, int $years)
	{
		$sql = "
			UPDATE pets
			SET death = 1,
				death_date = DATE_SUB(CURDATE(), INTERVAL 14 DAY), -- killed withouth a trace :)
				note = CONCAT(note, ' [auto-death]')
			WHERE 
				death = 0
			AND
				type = " . $type . "
			AND TIMESTAMPDIFF(YEAR, birth, CURDATE()) > " . $years . "
			AND id NOT IN (
				SELECT DISTINCT pet
				FROM events
				WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
			);
		";
		$this->db->query($sql);
		return $this->db->affected_rows();
	}
}
