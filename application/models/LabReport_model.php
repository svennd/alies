<?php


// application/models/LabReport_model.php
class LabReport_model extends MY_Model {

    public $table = 'lab_report';
	public $primary_key = 'id';
	public $soft_delete = true;

	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['pet'] = array(
							'foreign_model' => 'Pets_model',
							'foreign_table' => 'pets',
							'foreign_key' => 'id',
							'local_key' => 'pet_id'
						);
		parent::__construct();
		$this->load->library('lab_source_identity');
	}

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        
        return $this->db->insert_id();
    }

	# search by (device + source_id) or (source + source_id)
    public function findBySource($device, $source, $source_id, bool $for_update = false)
    {
		$candidates = $this->lab_source_identity->candidates($device, $source, $source_id);
		if (!$candidates) {
            return null;
        }

		foreach ($candidates as $identity) {
			if ($for_update) {
				$existing = $this->db->query(
					'SELECT * FROM `lab_report` WHERE `' . $identity['kind'] . '` = ? AND `source_id` = ? LIMIT 1 FOR UPDATE',
					array($identity['authority'], $identity['source_id'])
				)->row();
			} else {
				$existing = $this->db
					->where($identity['kind'], $identity['authority'])
					->where('source_id', $identity['source_id'])
					->get($this->table)
					->row();
			}
			if ($existing) {
				return $existing;
			}
		}

        return null;
    }

	public function claimSource(array $data): int
	{
		$data['device'] = $this->lab_source_identity->normalize_value($data['device'] ?? null);
		$data['source'] = $this->lab_source_identity->normalize_value($data['source'] ?? null);
		$data['source_id'] = $this->lab_source_identity->normalize_value($data['source_id'] ?? null);
		if (!$this->lab_source_identity->candidates(
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'] ?? null
		)) {
			return (int) $this->create($data);
		}

		$sql = 'INSERT INTO `lab_report` '
			. '(`pet_id`, `device`, `source`, `source_id`, `sample_date`, `software_version`, `metadata`, `created_at`) '
			. 'VALUES (?, ?, ?, ?, ?, ?, ?, ?) '
			. 'ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)';
		$this->db->query($sql, array(
			$data['pet_id'],
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'],
			$data['sample_date'] ?? null,
			$data['software_version'] ?? null,
			$data['metadata'] ?? null,
			$data['created_at'] ?? date('Y-m-d H:i:s'),
		));

		return (int) $this->db->insert_id();
	}
    
    public function touch($report_id)
    {
        $this->db
            ->where('id', $report_id)
            ->update($this->table, ['updated_at' => date('Y-m-d H:i:s')]);
    }


	/*
	* function: get_labs
	* get all lab results w/ pets & owners
	*/
	public function get_labs($search_from = null, $search_to = null)
	{
		$this->db->select('
							lab_report.*, 
							pets.name as pet_name, pets.id as pet_id, pets.type as pet_type,
							owners.last_name as last_name, owners.id as owners_id
						');
		$this->db->join('pets', 'pets.id = lab_report.pet_id', 'left');
		$this->db->join('owners', 'owners.id = pets.owner', 'left');
		$this->db->where("date(lab_report.created_at) >=", $search_from);
		$this->db->where("date(lab_report.created_at) <=", $search_to);
		// $this->db->where('lab_report.deleted_at', NULL);
		$this->db->order_by('lab_report.id', 'desc');
		return $this->db->get('lab_report')->result_array();
	}

	public function get_for_pet(int $pet_id)
	{
		$this->db->select('lab_report.*');
		$this->db->where('lab_report.pet_id', $pet_id);
		$this->db->order_by('lab_report.sample_date', 'desc');
		$this->db->order_by('lab_report.id', 'desc');

		return $this->db->get('lab_report')->result_array();
	}

	public function count_for_pet(int $pet_id): int
	{
		return (int) $this->db
			->where('pet_id', $pet_id)
			->count_all_results($this->table);
	}

	public function has_for_pet(int $pet_id): bool
	{
		return $this->count_for_pet($pet_id) > 0;
	}


}
