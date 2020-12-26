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
	
	public function search_by_chip($chip)
	{
		$sql = "
			SELECT 
				* 
			FROM 
				pets
			WHERE
				chip = '". $chip ."'	
		";
		
		$prime = $this->db->query($sql)->result_array();
		$result = array();
		if ($prime) {
			foreach ($prime as $owners) {
				$pets_sql = "
					SELECT
					*
					FROM 
						pets
					WHERE
						pets.owner = " . $owners['id'] . "
					AND
						pets.death = 0
					LIMIT
						0,2
				";
				$owners['pets'] = $this->db->query($pets_sql)->result_array();
				$result[] = $owners;
			}
		}
		return $result;
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
