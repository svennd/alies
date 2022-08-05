<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_comment_upload extends CI_Migration {

	protected $up_version = "008";
	protected $down_version = "007";


	public function up()
	{
		// add support for stock messages when input is done
		$sql[] = "ALTER TABLE `events_upload` ADD `comment` TEXT NOT NULL AFTER `mime`;";
		$sql[] = "ALTER TABLE `pets` ADD `deleted_at` DATETIME NULL AFTER `updated_at`;";
		$sql[] = "ALTER TABLE `owners` ADD `deleted_at` DATETIME NULL AFTER `updated_at`;";

		# improving footprint
		$sql[] = "ALTER TABLE `pets` CHANGE `gender` `gender` TINYINT(1) NOT NULL;";
		$sql[] = "ALTER TABLE `pets` CHANGE `lost` `lost` TINYINT(1) NOT NULL;";
		$sql[] = "ALTER TABLE `pets` CHANGE `type` `type` TINYINT(1) NOT NULL;";
		$sql[] = "ALTER TABLE `pets` CHANGE `death` `death` TINYINT(1) NOT NULL;";
		$sql[] = "ALTER TABLE `owners` 
						CHANGE `debts` `debts` TINYINT(1) NULL DEFAULT NULL, 
						CHANGE `low_budget` `low_budget` TINYINT(1) NOT NULL, 
						CHANGE `language` `language` TINYINT(1) NOT NULL, 
						CHANGE `contact` `contact` TINYINT(1) NOT NULL;";
		$sql[] = "ALTER TABLE `products` 
						CHANGE `btw_buy` `btw_buy` TINYINT NOT NULL, 
						CHANGE `btw_sell` `btw_sell` TINYINT NOT NULL, 
						CHANGE `booking_code` `booking_code` TINYINT NOT NULL, 
						CHANGE `sellable` `sellable` TINYINT(1) NOT NULL, 
						CHANGE `vaccin` `vaccin` TINYINT(1) NOT NULL DEFAULT '0';";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `events_upload` DROP `comment`;";
		$sql[] = "ALTER TABLE `pets` DROP `deleted_at`;";
		$sql[] = "ALTER TABLE `owners` DROP `deleted_at`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 