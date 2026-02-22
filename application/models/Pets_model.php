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
	public function search_by_name($query, int $limit = 250, bool $dead_allowed = false)
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
				name LIKE '" . $query . "%' ESCAPE '!'
				" . ($dead_allowed ? "" : "AND pets.death = 0") . "
                AND pets.transfered = 0
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
                AND pets.transfered = 0
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
                AND pets.transfered = 0
			group by
				type			
		";
		return $this->db->query($pets_sql)->result_array();
	}

	// owners
	public function get_all_pets(int $owner)
	{
		return $this
                    ->with_breeds('field:name')
                    ->with_breeds2('field:name')
                    ->where(array("owner" => $owner, 'transfered' => 0))
                    ->order_by(array("birth, death"), "desc")
                    ->get_all();
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

    /*
    * function: transfer_pet
    * transfer a pet to a new owner
    * we have to create a hidden pet in order to make the 
    */
    public function transfer_pet(int $pet_id, int $new_owner_id)
    {
        # clone pet
        $this->clone_pet($pet_id, $new_owner_id);

        # set the old pet to hidden for old bills
        $this->where(array('id' => $pet_id))->update(array(
            'transfered'    => 1, 
            'note'          => '[transfer:send:' . $new_owner_id . ']',
            'chip'          => null, // remove chip
            'companion'     => null, // remove companion link
        ));
    }


    /*
    * function: duplicate_chips
    * find duplicate chips in pets - should not happen
    */
    public function duplicate_chips()
    {
        $sql = "
            SELECT 
                GROUP_CONCAT(pets.id ORDER BY last_bill DESC) AS pet_ids,
                COUNT(*) AS cnt,
                chip,
                max(owners.last_bill) AS max_last_bill,
                pets.*,
                owners.*
            FROM pets
            LEFT JOIN owners ON owners.id = pets.owner
            WHERE
                pets.transfered = 0
                AND pets.chip IS NOT NULL
                AND pets.chip <> ''
            GROUP BY 
                chip
            HAVING cnt > 1
            ORDER BY last_bill DESC;
            ;
        ";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    /*
    * function: does_pet_exist
    * check if pet exists by id - used in lab result ingestion
    */
    public function does_pet_exist(int $pet_id)
    {
        $pet = $this->where(array('id' => $pet_id))->get();

        if ($pet) {
            return $pet['id'];
        }

        return null;
    }

    /*
    * function: findByOwnerPhoneAndPet
    * find pets by owner phone and owner name + pet name
    * used in lab result ingestion
    */
    public function findByOwnerPhoneAndPet(string $phone, string $owner, string $pet)
    {
        $this->db->select('
                        pets.id        AS pet_id,
                        owners.id      AS owner_id,
                        pets.name      AS pet_name,
                        owners.last_name
                    ');
        $this->db->join('owners', 'owners.id = pets.owner');
        $this->db->group_start();
            $this->db
                    ->or_where('owners.telephone', $phone)
                    ->or_where('owners.mobile', $phone)
                    ->or_where('owners.phone2', $phone)
                    ->or_where('owners.phone3', $phone);
        $this->db->group_end();

        $this->db->where('owners.last_name', strtoupper($owner));
        $this->db->where('pets.name', strtoupper($pet));

        return $this->db->get('pets')->result();
    }
    
    /*
    * function: findByOwnerAndPet
    * find pets by owner name + pet name
    * used in lab result ingestion
    */
    public function findByOwnerAndPet(string $owner, string $pet)
    {
        $sql = "
            SELECT 
                pets.id        AS pet_id,
                owners.id      AS owner_id,
                pets.name      AS pet_name,
                owners.last_name
            FROM 
                pets
            JOIN
                owners
            ON
                owners.id = pets.owner
            WHERE
                owners.last_name = ?
            AND
                pets.name = ?
        ";
        $query = $this->db->query($sql, array(strtoupper($owner), strtoupper($pet)));
  
        return $query->result();
    }

    /*
    * function: findByChipNumber
    * find pets by chip number
    * used in lab result ingestion
    */
    public function findByChipNumber(string $chip)
    {
        return $this->where('chip', $chip)->get_all();
    }

    public function findByPetName(string $pet_name)
    {
        # more then 1 but limit it so we don't return too much
        return $this->where('name', $pet_name)->limit(3)->get_all();
    }

    private function clone_pet(int $pet_id, int $new_owner_id)
    {
        $pet = $this->get($pet_id);

        # remove id
        unset($pet['id']);

        # set new owner
        $pet['owner'] = $new_owner_id;
        $pet['note'] .= ' [transfer:owner:' . $pet['owner'] . ']'; // update note

        # insert
        $new_pet_id = $this->insert($pet);

        if (!$new_pet_id) {
            return false;
        }

        return $new_pet_id;
    }
}
