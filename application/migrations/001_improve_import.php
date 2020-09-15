<?php
/*
	Dummy migrations to set up the system
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Improve_import extends CI_Migration {

	protected $up_version = "001";
	protected $down_version = "000";
	
	$r = true;
	
	public function up()
	{
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		return ($r) ? $this->down_version : false;
	}
}