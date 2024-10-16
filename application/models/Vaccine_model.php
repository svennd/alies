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
	
	/*
	* function: view
	* get a summary of the vaccines for the pets fiche
	*/
	public function view($pet_id)
	{
		$sql = "
			SELECT 
				GROUP_CONCAT(event_id) as event_ids,
				MAX(vac.redo) as max_rappel,
				MAX(vac.created_at) as max_injection,
				MIN(no_rappel) as min_no_rappel,
				products.name
			FROM 
				" . $this->table . " as vac
			JOIN
				products
			ON
				products.id = vac.product_id
			WHERE 
				pet = " . (int) $pet_id . "
			AND
				vac.no_rappel = 0
			AND
				vac.redo >= vac.created_at
			group by 
				product_id
			order by 
				max_injection 
			desc
		";
		
		return ($this->db->query($sql)->result_array());
	}

	public function get_expiring_vaccines($date)
	{
		$sql = "
			SELECT 

				owners.id as owner_id,
				owners.first_name as first_name,
				owners.last_name as last_name,
				owners.street as owner_street,
				owners.nr as owner_nr,
				owners.city as owner_city,
				owners.province as province,
				owners.zip as zip,

				products.name as product_name,
				products.vaccin_disease as disease,

				vac.created_at as injection_date,
				vac.redo as redo_date,
				
				pets.name as pet_name, 
				pets.type as pet_type, 

				owners.mail as owner_mail,
				owners.last_bill as last_bill,
				owners.debts as debts,

				users.first_name as vet_name
				
			FROM 
			" . $this->table . " as vac
			
			JOIN
				products
			ON
				products.id = vac.product_id

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
			AND
				vac.no_rappel = 0
			AND
				owners.disabled = 0 -- not disabled
			ORDER BY vac.redo ASC
		";
		
		return ($this->db->query($sql)->result_array());
	}


	/*
	* function: martian
	* add a vaccine that was not done in our clinic
	*/
	public function martian(int $pet_id, array $data)
	{
		// force created_at to be injection time
		$this->timestamps = false;

		$this->insert(array(
			"pet" 			=> $pet_id,
			"product"		=> $data['product'],
			"redo" 			=> $data['redo'],
			"no_rappel" 	=> $data['no_rappel'],
			"created_at" 	=> $data['created_at'], // injection time
			"event_id"		=> 0, // imported
			"location" 		=> $data['location'],
			"vet" 			=> $this->user->id,
		));
	}
}
