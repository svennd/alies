<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Wholesale_price_model extends MY_Model
{
	public $table = 'wholesale_price';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}
