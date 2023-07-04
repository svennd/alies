<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_product_page extends CI_Migration {

	protected $up_version = "021";
	protected $down_version = "020";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products` ADD `backorder` TINYINT(1) NOT NULL AFTER `discontinued`;";
		$sql[] = "ALTER TABLE `products_type` ADD `root` TINYINT NULL AFTER `name`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products` DROP `backorder`;";
		$sql[] = "ALTER TABLE `products_type` DROP `root`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}