<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Price_track_model extends MY_Model
{
	public $table = 'price_track';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}
