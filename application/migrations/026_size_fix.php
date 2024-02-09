<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_size_fix extends CI_Migration {

	protected $up_version = "025";
	protected $down_version = "026";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `tooth` CHANGE `tooth` `tooth` SMALLINT UNSIGNED NOT NULL;";
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