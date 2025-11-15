<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Usage extends CI_Migration {

	protected $up_version = "039";
	protected $down_version = "038";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE products ADD COLUMN usage_count INT UNSIGNED NOT NULL DEFAULT 0;";
		$sql[] = "ALTER TABLE `pets` ADD `transfered` TINYINT NOT NULL DEFAULT '0' AFTER `init_vet`;";
		$sql[] = "ALTER TABLE `pets` ADD INDEX(`transfered`);";
		$sql[] = "ALTER TABLE `delivery` CHANGE `billing` `billing` VARCHAR(255) NULL;"; # already in prod
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE products DROP COLUMN usage_count;";
		$sql[] = "ALTER TABLE pets DROP COLUMN transfered;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}