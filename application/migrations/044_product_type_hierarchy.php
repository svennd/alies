<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_product_type_hierarchy extends CI_Migration {

	protected $up_version = "044";
	protected $down_version = "043";

	public function up()
	{
		$sql = array();
		$sql[] = "UPDATE `products_type` pt LEFT JOIN `products_type` parent ON parent.id = pt.root SET pt.root = NULL WHERE pt.root IS NOT NULL AND parent.id IS NULL;";
		$sql[] = "ALTER TABLE `products_type` MODIFY `root` TINYINT(3) UNSIGNED NULL DEFAULT NULL;";
		$sql[] = "ALTER TABLE `products_type` ADD INDEX `idx_products_type_root` (`root`);";
		$sql[] = "ALTER TABLE `products_type` ADD CONSTRAINT `fk_products_type_root` FOREIGN KEY (`root`) REFERENCES `products_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products_type` DROP FOREIGN KEY `fk_products_type_root`;";
		$sql[] = "ALTER TABLE `products_type` DROP INDEX `idx_products_type_root`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
