<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Logs_model extends MY_Model
{
	public $table = 'log';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'user_id'
				);
		parent::__construct();
	}
	
	public function logger($user_id, $level, $event, $msg)
	{
		return $this->insert(array(
					"event" 	=> $event,
					"level" 	=> $level,
					"user_id" 	=> $user_id,
					"msg" 		=> $msg
				));
	}
}
