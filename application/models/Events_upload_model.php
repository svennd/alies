<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Events_upload_model extends MY_Model
{
    public $table = 'events_upload';
    public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}