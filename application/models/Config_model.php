<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Config_model extends MY_Model
{
	public $table = 'config';
	public $primary_key = 'id';
	public $delete_cache_on_save = true;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function store(string $name, string $value)
	{
		$sql = "
			INSERT INTO 
				config 
				(name, value, created_at)
			VALUES 
				('". $name ."','". base64_encode($value) . "', '" . date('Y-m-d H:i:s') . "')
			ON DUPLICATE KEY UPDATE
				value = '" . base64_encode($value) . "', 
				updated_at = '" . date('Y-m-d H:i:s') . "';		
		";
		return $this->db->query($sql);
	}

	// not used yet (we load everything at bootstrap time)
	public function get_value(string $name)
	{
		$sql = "
			SELECT 
				value
			FROM 
				config 
			WHERE 
				name = '" . $name . "'
			LIMIT 1;
		";
		$result = $this->db->query($sql)->row_array();
		return base64_decode($result['value']);
	}
}
