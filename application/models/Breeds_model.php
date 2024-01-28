<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Breeds_model extends MY_Model
{
	public $table = 'breeds';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_many['pets'] = array(
			'foreign_model' => 'Pets_model',
			'foreign_table' => 'pets',
			'foreign_key' => 'breed',
			'local_key' => 'id'
		);
				
		parent::__construct();
	}
	
	# must be an exact match
	# otherwise common patterns generate WAAAY to much results
	public function search_by_name($query)
	{
		# ignore short strings
		if (strlen($query) < 4) { return array(); }

		$query = $this->db->escape_like_str($query);
		$sql = "
			SELECT 
				breeds.name as breed,
				pets.type as type,
				pets.name, owners.*
			FROM
				breeds
			LEFT JOIN
				pets
			ON
				pets.breed = breeds.id

			LEFT JOIN
				owners
			ON
				owners.id = pets.owner

			WHERE
				breeds.name LIKE '" . $this->db->escape_like_str($query) . "%' ESCAPE '!'
			AND
				pets.death = 0
			AND
				pets.lost = 0
			AND
				owners.last_bill != ''
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 50
		";
		
		return $this->db->query($sql)->result_array();
	}

	public function get_breed_stats(int $breed)
	{
		$sql = "select 
						last_weight, 
						gender, 
						(year(NOW()) - YEAR(birth)) as age 
				from 
					pets 
				where 
					breed = '" . $breed . "' 
				AND 
					death = 0 
				AND 
					lost = 0 
				AND
				(year(NOW()) - YEAR(birth)) < 20
				AND 
					last_weight != 0;";

		return $this->db->query($sql)->result_array();
	}
}
