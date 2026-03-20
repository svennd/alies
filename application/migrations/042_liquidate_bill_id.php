<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_liquidate_bill_id extends CI_Migration {

	protected $up_version = "042";
	protected $down_version = "041";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `liquidate` ADD `bill_id` INT(11) NULL DEFAULT NULL AFTER `stock_id`;";
		$sql[] = "ALTER TABLE `liquidate` ADD INDEX `bill_id` (`bill_id`);";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `liquidate` DROP INDEX `bill_id`;";
		$sql[] = "ALTER TABLE `liquidate` DROP `bill_id`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
