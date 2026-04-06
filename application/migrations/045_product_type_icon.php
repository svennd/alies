<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_product_type_icon extends CI_Migration {

	protected $up_version = "045";
	protected $down_version = "044";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products_type` ADD `icon` VARCHAR(128) NULL DEFAULT NULL AFTER `name`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products_type` DROP `icon`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
