<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_lotnr extends CI_Migration {

	protected $up_version = "002";
	protected $down_version = "001";
	
	
	public function up()
	{
		$sql[] = "ALTER TABLE `stock` CHANGE `lotnr` `lotnr` VARCHAR(255) NOT NULL;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `stock` CHANGE `lotnr` `lotnr` INT(11) NOT NULL;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
