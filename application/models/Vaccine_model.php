<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
				vaccine_pet 
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
}