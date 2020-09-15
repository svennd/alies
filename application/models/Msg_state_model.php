<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Msg_state_model extends MY_Model
{
    public $table = 'msg_state';
    public $primary_key = 'msg_id';
	
	public function __construct()
	{						
		parent::__construct();
	}
}