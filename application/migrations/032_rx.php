<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rx extends CI_Migration {

	protected $up_version = "032";
	protected $down_version = "031";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` ADD `rx_count` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `medication`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sq[] = "ALTER TABLE `pets` DROP `rx_count`;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}