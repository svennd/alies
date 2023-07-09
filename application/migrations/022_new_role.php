<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_role extends CI_Migration {

	protected $up_version = "022";
	protected $down_version = "021";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `owners` CHANGE `nr` `nr` VARCHAR(30) NOT NULL;";
		$sql[] = "INSERT INTO `groups` (`id`, `name`, `description`) VALUES (NULL, 'accounting', 'accounting');";
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