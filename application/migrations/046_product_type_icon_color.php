<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_product_type_icon_color extends CI_Migration {

	protected $up_version = "046";
	protected $down_version = "045";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products_type` ADD `icon_color` CHAR(7) NULL DEFAULT NULL AFTER `icon`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products_type` DROP `icon_color`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
