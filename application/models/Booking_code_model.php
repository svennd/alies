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


		/*
			has_many
		*/
		$this->has_many['products'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'booking_code',
			'local_key' => 'id'
		);

		$this->has_many['procedures'] = array(
			'foreign_model' => 'Procedures_model',
			'foreign_table' => 'procedures',
			'foreign_key' => 'booking_code',
			'local_key' => 'id'
		);

		parent::__construct();
	}
}
