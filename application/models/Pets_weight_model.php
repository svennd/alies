<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Pets_weight_model extends MY_Model
{
	public $table = 'pets_weight';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}
