<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alerts_model extends MY_Model
{
    public $table = 'alerts';
    public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}