<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_color extends CI_Migration {

	protected $up_version = "026";
	protected $down_version = "027";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `stock_location` ADD `color` VARCHAR(7) NOT NULL AFTER `name`;";

		# yes this is bad
		$sql[] = "UPDATE `stock_location` SET `color` = '#153b31' WHERE `id` = 1;";
		$sql[] = "UPDATE `stock_location` SET `color` = '#f36a62' WHERE `id` = 2;";
		$sql[] = "UPDATE `stock_location` SET `color` = '#00ffea' WHERE `id` = 3;";
		$sql[] = "UPDATE `stock_location` SET `color` = '#f7ae6a' WHERE `id` = 4;";

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