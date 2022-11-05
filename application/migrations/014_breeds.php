<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_breeds extends CI_Migration {

	protected $up_version = "014";
	protected $down_version = "013";


	public function up()
	{
		$sql[] = "ALTER TABLE `breeds` ADD INDEX(`type`);";
		$sql[] = "ALTER TABLE `breeds` CHANGE `male_min_weight` `male_min_weight` DECIMAL(5,2) NOT NULL, CHANGE `male_max_weight` `male_max_weight` DECIMAL(5,2) NOT NULL, CHANGE `female_min_weight` `female_min_weight` DECIMAL(5,2) NOT NULL, CHANGE `female_max_weight` `female_max_weight` DECIMAL(5,2) NOT NULL;";
		$sql[] = "ALTER TABLE `bills` ADD `deleted_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;";
		$sql[] = "ALTER TABLE `users` ADD `vsens` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sidebar`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `breeds` DROP INDEX `type`;";
		$sql[] = "ALTER TABLE `users` DROP `vsens`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
