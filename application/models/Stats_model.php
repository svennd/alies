<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Stats_model extends MY_Model
{
	public $table = 'stats';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}
