<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Users_model extends MY_Model
{
	public $table = 'users';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_many_pivot['groups'] = array(
		    'foreign_model'		=> 'Groups_model',
		    'pivot_table' 		=> 'users_groups',
		    'local_key'			=> 'id',
		    'pivot_local_key' 	=> 'user_id',
		    'pivot_foreign_key'	=> 'group_id',
		    'foreign_key' 		=> 'id',
		    'get_relate'		=> true 
		);
			
		parent::__construct();
	}
	
	public function get_active_vets()
	{
		// hard coded 3 =(
		$sql = "SELECT 
					users.id, first_name, last_name, last_login, image
				FROM
					users
				LEFT JOIN 
					users_groups 
				ON 
					users.id = users_groups.user_id
				WHERE
					users_groups.group_id = 3
				AND
					users.active = 1
			";
					
		return $this->db->query($sql)->result_array();
	}
}
