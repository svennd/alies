<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_search_config extends CI_Migration {

	protected $up_version = "005";
	protected $down_version = "004";
	
	
	public function up()
	{
		$sql[] = "ALTER TABLE `users` ADD `search_config` TINYINT(1) NOT NULL DEFAULT '0' AFTER `phone`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `users` DROP `search_config`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
