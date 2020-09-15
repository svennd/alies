<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Config_model extends MY_Model
{
    public $table = 'config';
    public $primary_key = 'id';
    public $delete_cache_on_save = TRUE;
	
	public function __construct()
	{
		parent::__construct();
	}
}