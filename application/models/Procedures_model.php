<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Procedures_model extends MY_Model
{
	public $table = 'procedures';
	public $primary_key = 'id';
	public $delete_cache_on_save = true;
	
	public function __construct()
	{
		$this->soft_deletes = true;
		
		$this->has_one['booking_code'] = array(
							'foreign_model' => 'Booking_code_model',
							'foreign_table' => 'booking_codes',
							'foreign_key' => 'id',
							'local_key' => 'booking_code'
						);
		parent::__construct();
	}
}
