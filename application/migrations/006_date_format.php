<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_date_format extends CI_Migration {

	protected $up_version = "006";
	protected $down_version = "005";
	
	
	public function up()
	{
		$sql[] = "ALTER TABLE `users` ADD `user_date` varchar(9) NOT NULL DEFAULT 'd-m-Y' AFTER `phone`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `users` DROP `user_date`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
