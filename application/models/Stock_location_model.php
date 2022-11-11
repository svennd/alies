<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stock_location_model extends MY_Model
{
	public $table = 'stock_location';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();

		$this->soft_deletes = true;
	}
}
