<?php
// used to store emails for offline mutation database

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tooth_msg_model extends MY_Model
{
    public $table = 'tooth_msg';
    public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['vet'] = array(
						'foreign_model' => 'Users_model',
						'foreign_table' => 'users',
						'foreign_key' => 'id',
						'local_key' => 'vet'
					);
		$this->has_one['location'] = array(
						'foreign_model' => 'Stock_location_model',
						'foreign_table' => 'stock_location',
						'foreign_key' => 'id',
						'local_key' => 'location'
					);
					
		parent::__construct();
	}
}