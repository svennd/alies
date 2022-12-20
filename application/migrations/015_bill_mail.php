<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_bill_mail extends CI_Migration {

	protected $up_version = "015";
	protected $down_version = "014";


	public function up()
	{
		$sql[] = "ALTER TABLE `bills` ADD `mail` TINYINT(1) NOT NULL AFTER `msg`;";
		$sql[] = "CREATE TABLE `wholesale` (`id` INT NOT NULL AUTO_INCREMENT , `vendor_id` VARCHAR(25) NOT NULL , `description` VARCHAR(255) NOT NULL , `bruto` DECIMAL(5,2) NOT NULL , `btw` SMALLINT NOT NULL , `sell_price` DECIMAL(5,2) NOT NULL , `distributor` VARCHAR(255) NOT NULL , `CNK` VARCHAR(255) NOT NULL , `VHB` VARCHAR(255) NOT NULL , `updated_at` DATETIME NULL DEFAULT NULL , `created_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX `vendorid` (`vendor_id`)) ENGINE = InnoDB;";
		$sql[] = "CREATE TABLE `wholesale_price` (
			`art_nr` varchar(25) NOT NULL,
			`bruto` decimal(5,2) NOT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		  ALTER TABLE `wholesale_price`";

		$sql[] = "ADD KEY `art_nr` (`art_nr`);";
		$sql[] = "ALTER TABLE `products` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;";
		$sql[] = "ALTER TABLE `products` CHANGE `short_name` `short_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;";
		$sql[] = "ALTER TABLE `products` CHANGE `producer` `producer` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;";
		$sql[] = "ALTER TABLE `products` CHANGE `supplier` `supplier` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;";
		$sql[] = "ALTER TABLE `products` ADD `wholesale` INT(11) NOT NULL AFTER `vhbcode`, ADD INDEX (`wholesale`);";
	
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `bills` DROP `mail`;";
		$sql[] = "DROP TABLE wholesale`;";
		$sql[] = "DROP TABLE wholesale_price`;";
		$sql[] = "ALTER TABLE `products` DROP `wholesale`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
