<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_companions extends CI_Migration {

	protected $up_version = "035";
	protected $down_version = "034";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` ADD `companion` INT(11) NULL DEFAULT NULL AFTER `hairtype`;";
		$sql[] = "DROP table price_track;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` DROP `companion`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}