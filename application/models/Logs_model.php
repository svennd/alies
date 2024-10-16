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
		parent::__construct();

		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'user_id'
				);
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'location'
				);
	}
	
	public function logger(int $level, $event, $msg, int $user = SYSTEM, int $location = SYSTEM)
	{
		# using the CI3 file logger
		log_message('error', $msg);

		return ($level > $this->min_log_level) ? 
			true : 
			$this->insert(array(
					"event" 	=> $event,
					"level" 	=> $level,
					"msg" 		=> $msg,
					"user_id"	=> $this->session->userdata('user_id') ? $this->session->userdata('user_id') : SYSTEM,
					"location"	=> $this->session->userdata('location') ? $this->session->userdata('location') : SYSTEM
				));
	}

	public function stock($level, $event, int $product, $volume, $location = false)
	{
		# using the CI3 file logger
		log_message('error', $event);

		return ($level > $this->min_log_level) ? 
			true : 
			$this->db->insert('log_stock', array(
					"level" 	=> $level,
					"event" 	=> $event,
					"product" 	=> $product,
					"volume" 	=> $volume,
					"user_id" 	=> $this->user->id,
					"location"	=> ($location) ? $location : $this->session->userdata('location'),
				));
	}
}
