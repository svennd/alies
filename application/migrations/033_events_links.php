<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_events_links extends CI_Migration {

	protected $up_version = "033";
	protected $down_version = "032";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` DROP `rx_count`;";

		$sql[] = "CREATE TABLE `events_links` (
					`id` int(11) UNSIGNED NOT NULL,
					`event_id` int(11) UNSIGNED NOT NULL,
					`type` tinyint(3) UNSIGNED NOT NULL,
					`value` int(11) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "ALTER TABLE `events_links` ADD PRIMARY KEY (`id`), ADD KEY `event_id` (`event_id`), ADD KEY `type` (`type`);";
		$sql[] = "ALTER TABLE `events_links` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;";

		# Add deleted_at to lab
		$sql[] = "ALTER TABLE `lab` ADD `deleted_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;";
		
		# add vaccine disease name to products (for vaccine export)
		$sql[] = "ALTER TABLE `products` ADD `vaccin_disease` VARCHAR(255) NOT NULL AFTER `vaccin_freq`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `pets` ADD `rx_count` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `medication`;";
		$sql[] = "DROP TABLE `events_links`;";

		$sql[] = "ALTER TABLE `producs` DROP `vaccin_disease`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}