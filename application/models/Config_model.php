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
		$update_tooth = "
			INSERT INTO 
				config 
				(name, value, created_at)
			VALUES 
				('". $name ."','". $value . "', '" . date('Y-m-d H:i:s') . "')
			ON DUPLICATE KEY UPDATE
				value = '" . $value . "', 
				updated_at = '" . date('Y-m-d H:i:s') . "';		
		";
		return $this->db->query($update_tooth);
	}
}
