<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Voeding_advies extends CI_Migration
{
	protected $up_version = "003";
	protected $down_version = "002";
	
	
	public function up()
	{
		$sql[] = "ALTER TABLE `pets` ADD `nutritional_advice` TEXT NOT NULL AFTER `note`;";
		foreach ($sql as $q) {
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `pets` DROP `nutritional_advice`";
		foreach ($sql as $q) {
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
