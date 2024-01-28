<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_delivery extends CI_Migration {

	protected $up_version = "024";
	protected $down_version = "023";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `register_in` ADD `supplier` VARCHAR(255) NULL AFTER `lotnr`;";
		$sql[] = "ALTER TABLE `register_in` ADD INDEX(`delivery_slip`);";
		$sql[] = "ALTER TABLE `stock` ADD `supplier` VARCHAR(255) NULL AFTER `barcode`;";

		# reset 
		$sql[] = "DROP TABLE IF EXISTS `delivery`;";
		$sql[] = "CREATE TABLE `delivery` (
			`id` int(11) NOT NULL,
			`order_date` date NOT NULL,
			`order_nr` int(11) NOT NULL,
			`my_ref` varchar(255) NOT NULL,
			`wholesale_artnr` varchar(255) NOT NULL,
			`wholesale_id` int(11) DEFAULT NULL,
			`CNK` int(11) NOT NULL,
			`delivery_date` date NOT NULL,
			`delivery_nr` int(11) NOT NULL,
			`bruto_price` decimal(5,2) NOT NULL,
			`netto_price` decimal(5,2) NOT NULL,
			`amount` int(11) NOT NULL,
			`lotnr` varchar(255) NOT NULL,
			`due_date` date NOT NULL,
			`btw` smallint(6) NOT NULL,
			`billing` varchar(255) NOT NULL,
			`imported` tinyint(1) NOT NULL DEFAULT 0,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

		$sql[] = "ALTER TABLE `delivery` ADD PRIMARY KEY (`id`);";
		$sql[] = "ALTER TABLE `delivery`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

		$sql[] = "DROP TABLE IF EXISTS `wholesale`;";
		$sql[] = "CREATE TABLE `wholesale` (
					`id` int(11) NOT NULL,
					`vendor_id` varchar(25) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
					`description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci NOT NULL,
					`bruto` decimal(7,2) NOT NULL,
					`last_bruto` decimal(7,2) DEFAULT NULL,
					`last_bruto_date` date DEFAULT NULL,
					`btw` smallint(6) NOT NULL,
					`sell_price` decimal(7,2) NOT NULL,
					`distributor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
					`distributor_id` varchar(255) DEFAULT NULL,
					`CNK` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci NOT NULL,
					`VHB` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci DEFAULT NULL,
					`type` int(10) UNSIGNED NOT NULL,
					`ignore_change` tinyint(1) DEFAULT 0,
					`updated_at` datetime DEFAULT NULL,
					`created_at` datetime DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";


		$sql[] = "ALTER TABLE `wholesale` ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `vendor_id` (`vendor_id`),  ADD KEY `type` (`type`);";
		$sql[] = "ALTER TABLE `wholesale` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
		$sql[] = "CREATE TABLE `wholesale_type` (
			`id` int(11) UNSIGNED NOT NULL,
			`name` varchar(255) NOT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
		$sql[] = "ALTER TABLE `wholesale_type` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);";
		$sql[] = "ALTER TABLE `wholesale_type` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";
		

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