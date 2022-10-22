<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Logs_model extends MY_Model
{
	public $table = 'log';
	public $primary_key = 'id';
	public $min_log_level = DEBUG; # DEBUG - INFO - WARN - ERROR - FATAL
	
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
	
	public function logger($level, $event, $msg)
	{
		return ($level > $this->min_log_level) ? 
			true : 
			$this->insert(array(
					"event" 	=> $event,
					"level" 	=> $level,
					"user_id" 	=> $this->user->id,
					"msg" 		=> $msg,
					"location"	=> $this->user->current_location,
				));
	}

	public function stock($level, $event, int $product, $volume, $location = false)
	{

		return ($level > $this->min_log_level) ? 
			true : 
			$this->db->insert('log_stock', array(
					"level" 	=> $level,
					"event" 	=> $event,
					"product" 	=> $product,
					"volume" 	=> $volume,
					"user_id" 	=> $this->user->id,
					"location"	=> ($location) ? $location : $this->user->current_location,
				));
	}
}
