<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_price_dump extends CI_Migration {

	protected $up_version = "036";
	protected $down_version = "035";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `register_in` ADD `cat_price` DECIMAL(10,2) NULL COMMENT 'catalogus price manual' AFTER `in_price`;";
		$sql[] = "ALTER TABLE `stock` ADD `cat_price` DECIMAL(10,2) NULL COMMENT 'cat price manual' AFTER `in_price`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `register_in` DROP `cat_price`;";
		$sql[] = "ALTER TABLE `stock` DROP `cat_price`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}