<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Sticky_model extends MY_Model
{
	public $table = 'sticky';
	public $primary_key = 'id';

	public function __construct()
	{
		$this->soft_deletes = true;
		$this->has_one['location'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'location'
		);
		$this->has_one['vet'] = array(
			'foreign_model' => 'Users_model',
			'foreign_table' => 'users',
			'foreign_key' => 'id',
			'local_key' => 'user_id'
		);
			parent::__construct();
	}
}
