<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Vaccine_model extends MY_Model
{
	public $table = 'vaccine_pet';
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
				
		$this->has_one['vet'] = array(
						'foreign_model' => 'Users_model',
						'foreign_table' => 'users',
						'foreign_key' => 'id',
						'local_key' => 'vet'
					);
					
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'location'
				);
				
		$this->has_one['product'] = array(
						'foreign_model' => 'Products_model',
						'foreign_table' => 'products',
						'foreign_key' => 'id',
						'local_key' => 'product_id'
				);
		/*
			pivot
		*/
		$this->has_many_pivot['owners'] = array(
						'foreign_model'		=> 'Owners_model',
						'pivot_table'		=> 'pets',
						'local_key'			=> 'pet',
						'pivot_local_key' 	=> 'id',
						'pivot_foreign_key' => 'owner',
						'foreign_key' 		=> 'id',
						'get_relate'		=> false
		);
		
		parent::__construct();
	}
	
	public function view($pet_id)
	{
		$sql = "
			SELECT 
				GROUP_CONCAT(event_id) as event_ids,
				MAX(redo) as max_redo,
				products.name
			FROM 
				" . $this->table . " 
			JOIN
				products
			ON
				products.id = vaccine_pet.product_id
			WHERE 
				pet = " . (int) $pet_id . "
			group by 
				product_id
			order by 
				max_redo 
			asc
		";
		
		return ($this->db->query($sql)->result_array());
	}

	public function get_expiring_vaccines($date)
	{
		$sql = "
			SELECT 
				MIN(vac.redo) as redo_date,

				GROUP_CONCAT(DISTINCT products.name) as product_name,
				
				GROUP_CONCAT(DISTINCT pets.name) as pet_name, 

				owners.id as owner_id,
				owners.first_name as first_name, 
				owners.last_name as last_name, 
				owners.street as owner_street, 
				owners.nr as owner_nr, 
				owners.city as owner_city, 
				owners.mail as owner_mail, 
				owners.last_bill as last_bill,

				stock.name as location,

				users.first_name as vet_name
			FROM 
			" . $this->table . " as vac
			
			JOIN
				products
			ON
				products.id = vac.product_id

			JOIN
				stock_location as stock
			ON
				stock.id = vac.location

			JOIN
				pets
			ON
				pets.id = vac.pet
				
			JOIN
				owners
			ON
				owners.id = pets.owner
			JOIN
				users
			ON
				users.id = vac.vet

			WHERE
				vac.redo <= LAST_DAY('" . $date . "') 
			AND 
				vac.redo >= DATE_FORMAT('" . $date . "', '%Y-%m-01')
			AND
				pets.death = 0
			AND
				pets.lost = 0
			GROUP BY
				owners.id

			ORDER BY vac.redo ASC
		";
		
		return ($this->db->query($sql)->result_array());
	}
}
