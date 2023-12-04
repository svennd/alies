<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Payment_model extends MY_Model
{
	public $table = 'payment';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->soft_deletes = true;
		parent::__construct();
	}
}