<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Msg_messages_model extends MY_Model
{
	public $table = 'msg_messages';
	public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}
