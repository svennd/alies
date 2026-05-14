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
	}

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        
        return $this->db->insert_id();
    }

	# search by (device + source_id) or (source + source_id)
    public function findBySource($device, $source, $source_id)
    {
		if ($source_id === null) {
            return null;
        }

        if ($device !== null) {
            $existing = $this->db
                ->where('device', $device)
                ->where('source_id', $source_id)
                ->get($this->table)
                ->row();

            if ($existing) {
                return $existing;
            }
        }

        if ($source !== null) {
            return $this->db
                ->where('source', $source)
                ->where('source_id', $source_id)
                ->get($this->table)
                ->row();
        }

        return null;
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


}
