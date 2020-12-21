<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Tooth_model extends MY_Model
{
	public $table = 'tooth';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}

	public function update_tooth($pet_id, $vet, $tooth, $tooth_status)
	{
		$update_tooth = "
		INSERT INTO 
			tooth 
			(pet, vet, tooth, tooth_status, created_at)
		VALUES 
			('". $pet_id ."','". $vet ."','". $tooth ."','". $tooth_status ."','" . date('Y-m-d H:i:s') . "')
		ON DUPLICATE KEY UPDATE
			tooth_status = '" . $tooth_status . "', 
			updated_at = '" . date('Y-m-d H:i:s') . "';		
		";
		return $this->db->query($update_tooth);
	}
}
