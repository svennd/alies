<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_pet_update extends CI_Migration {

	protected $up_version = "023";
	protected $down_version = "022";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` ADD `hairtype` VARCHAR(255) NOT NULL AFTER `nr_vac_book`;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` DROP `hairtype`;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}