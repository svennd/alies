<?php
/*
	Dummy migrations to set up the system
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Improve_import extends CI_Migration
{
	protected $up_version = "001";
	protected $down_version = "000";
	
	
	public function up()
	{
		return $this->up_version;
	}

	public function down()
	{
		return $this->down_version;
	}
}
