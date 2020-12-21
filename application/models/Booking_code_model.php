<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Booking_code_model extends MY_Model
{
	public $table = 'booking_codes';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->soft_deletes = true;
		parent::__construct();
	}
}
