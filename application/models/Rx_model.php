<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Rx_model extends MY_Model
{
	public $table = 'rx';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['pet'] = array(
					'foreign_model' => 'Pets_model',
					'foreign_table' => 'pets',
					'foreign_key' => 'id',
					'local_key' => 'pet_id'
				);
		parent::__construct();
	}

	public function get_images(int $pet_id)
	{
		$sql = "
			SELECT 
				group_concat(path) as images,
				group_concat(DISTINCT studydate) as study_date, 
				group_concat(description) as description, 
				group_concat(bodypart) as bodypart, 
				group_concat(studydescription) as study_description, 
				group_concat(series) as series
			FROM `rx` 
			WHERE
				pet_id = '" . $pet_id ."' 
			GROUP BY 
				studydate 
			ORDER BY 
				studydate 
			DESC;";

		return $this->db->query($sql)->result_array();
	}
}
