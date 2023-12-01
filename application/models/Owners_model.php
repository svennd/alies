<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Owners_model extends MY_Model
{
	public $table = 'owners';
	public $primary_key = 'id';
	
	public function __construct()
	{
		// enable soft deletes
		$this->soft_deletes = true;
		
		$this->has_many['pets'] = array(
									'foreign_model' => 'Pets_model',
									'foreign_table' => 'pets',
									'foreign_key' => 'owner',
									'local_key' => 'id'
								);
		
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'initial_loc'
				);
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'initial_vet'
				);
		parent::__construct();
	}
	
	public function search_by_first_name($name)
	{
		$name = $this->db->escape_like_str($name);
		$sql = "
			SELECT 
				*
			FROM 
				owners
			WHERE
				first_name LIKE '" . $name . "%' ESCAPE '!'
			ORDER BY
				last_bill
			DESC
			LIMIT 250
		";
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_last_name($name)
	{
		$name = $this->db->escape_like_str($name);
		$sql = "
			SELECT 
				* 
			FROM 
				owners
			WHERE
				last_name LIKE '" . $name . "%' ESCAPE '!'
			ORDER BY
				last_bill
			DESC
			LIMIT 250
		";
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_street_ex($street)
	{
		$street = $this->db->escape_like_str($street);
		$sql = "
			SELECT 
				*
			FROM 
				owners
			WHERE
				street LIKE '%" . $this->db->escape_like_str($street) . "%' ESCAPE '!'
			ORDER BY
				last_bill
			DESC
			LIMIT 250
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_phone_ex($phone, int $limit = 250)
	{
		# in case its a false phone number
		if (!$phone) { return array(); }
		$phone = $this->db->escape_like_str($phone);
		$sql = "
			SELECT 
				*
			FROM 
				owners
			WHERE
				telephone LIKE '" . $phone . "%' ESCAPE '!'
				OR
				mobile LIKE '" . $phone . "%' ESCAPE '!'
				OR
				phone2 LIKE '" . $phone . "%' ESCAPE '!'
				OR
				phone3 LIKE '" . $phone . "%' ESCAPE '!'
			ORDER BY
				last_bill
			DESC
			LIMIT " . $limit . "
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	# needs improvement !!
	# probably 1 query should suffice
	public function search_by_name($name)
	{
		$result = array();
		
		$name = $this->db->escape_like_str($name);
		$sql = "
			SELECT 
				* 
			FROM 
				owners
			WHERE
				first_name LIKE '" . $name . "%' ESCAPE '!'
			OR
				last_name LIKE '" . $name . "%' ESCAPE '!'	
			ORDER BY
				last_bill
			DESC
		";
		
		$prime = $this->db->query($sql)->result_array();
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
					AND
						pets.lost = 0
					LIMIT
						0,2
				";
				$owners['pets'] = $this->db->query($pets_sql)->result_array();
				$result[] = $owners;
			}
		}
		return $result;
	}
	
	public function search_by_street($street)
	{
		$result = array();
		$sql = "
			SELECT 
				* 
			FROM 
				owners
			WHERE
				street LIKE '%" . $this->db->escape_like_str($street) . "%' ESCAPE '!'
				
			ORDER BY
				last_bill
			DESC
		";
		
		$prime = $this->db->query($sql)->result_array();
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
					AND
						pets.lost = 0
					LIMIT
						0,2
				";
				$owners['pets'] = $this->db->query($pets_sql)->result_array();
				$result[] = $owners;
			}
		}
		return $result;
	}
	
	public function get_per_city()
	{
		$get_per_city = "
			select 
				main_city,
				count(id) as amount
			from 
				owners
			group by
				main_city			
		";
		
		return $this->db->query($get_per_city)->result_array();
	}

	public function get_per_province()
	{
		$get_per_province = "
			select 
				province,
				count(id) as amount
			from 
				owners
			group by
				province			
		";
		
		return $this->db->query($get_per_province)->result_array();
	}
	
	public function last_bill_by_year_month_init_vet($year = 5)
	{
		$sql = "
			select 
				year(last_bill) as y,
				count(owners.id) as total,
				users.first_name as vet
			from 
				owners 
			join 
				users
			on 
				users.id = owners.initial_vet
			group by 
				year(last_bill), 
				initial_vet
			having 
				y > YEAR(CURDATE() - INTERVAL " . $year . " YEAR)
			and
				y <= YEAR(CURDATE())
			order by
				y asc
				";
			
		return $this->db->query($sql)->result_array();
	}
}
