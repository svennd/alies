<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_death_date extends CI_Migration {

	protected $up_version = "004";
	protected $down_version = "003";
	
	
	public function up()
	{
		$sql[] = "ALTER TABLE `users` ADD `remember_selector` varchar(255) NULL DEFAULT NULL;";
		$sql[] = "ALTER TABLE `users` ADD `forgotten_password_selector` varchar(255) NULL DEFAULT NULL;";
		$sql[] = "ALTER TABLE `pets` ADD `death_date` DATE NULL DEFAULT NULL AFTER `death`;";
		
		$sql[] = "ALTER TABLE `owners` ADD INDEX(`street`);";
		$sql[] = "ALTER TABLE `owners` ADD INDEX(`first_name`);";
		$sql[] = "ALTER TABLE `owners` ADD INDEX(`last_name`);";
		
		$sql[] = "ALTER TABLE `stock` ADD INDEX `gsl_lookup` (`eol`, `location`, `lotnr`);";
		
		$sql[] = "ALTER TABLE `products` CHANGE `input_barcode` `input_barcode` VARCHAR(255) NULL;";
		$sql[] = "ALTER TABLE `products` ADD UNIQUE `barcode` (`input_barcode`);";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `users` DROP `forgotten_password_selector`;";
		$sql[] = "ALTER TABLE `users` DROP `remember_selector`;";
		
		$sql[] = "ALTER TABLE `pets` DROP `death_date`;";
		
		$sql[] = "ALTER TABLE `owners` DROP INDEX `first_name`;";
		$sql[] = "ALTER TABLE `owners` DROP INDEX `last_name`;";
		$sql[] = "ALTER TABLE `owners` DROP INDEX `street`;";
		
		$sql[] = "ALTER TABLE `stock` DROP INDEX `gsl_lookup`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
