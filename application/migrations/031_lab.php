<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_lab extends CI_Migration {

	protected $up_version = "031";
	protected $down_version = "030";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `lab_detail` CHANGE `comment` `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}