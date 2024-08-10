<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_liquidate extends CI_Migration {

	protected $up_version = "030";
	protected $down_version = "029";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `liquidate` ADD `product_name` VARCHAR(255) NULL DEFAULT NULL AFTER `product_id`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `liquidate` DROP `product_name`;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}