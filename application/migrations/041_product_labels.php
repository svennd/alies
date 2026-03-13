<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_product_labels extends CI_Migration {

	protected $up_version = "041";
	protected $down_version = "040";

	public function up()
	{
		$sql = array();
		$sql[] = "CREATE TABLE `products_label` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(100) NOT NULL,
			`created_at` DATETIME NULL DEFAULT NULL,
			`updated_at` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uniq_products_label_name` (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "CREATE TABLE `products_product_label` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`product_id` INT(11) NOT NULL,
			`label_id` INT(11) NOT NULL,
			`created_at` DATETIME NULL DEFAULT NULL,
			`updated_at` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uniq_product_label` (`product_id`, `label_id`),
			KEY `idx_product_label_product` (`product_id`),
			KEY `idx_product_label_label` (`label_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "INSERT INTO `products_label` (`name`, `created_at`, `updated_at`) VALUES
			('antibiotica', NOW(), NOW()),
			('oog', NOW(), NOW()),
			('oor', NOW(), NOW()),
			('blaas', NOW(), NOW()),
			('cardio', NOW(), NOW());";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "DROP TABLE IF EXISTS `products_product_label`;";
		$sql[] = "DROP TABLE IF EXISTS `products_label`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
