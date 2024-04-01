<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_price_log extends CI_Migration {

	protected $up_version = "029";
	protected $down_version = "028";

	public function up()
	{
		$sql = array();
		$sql[] = "CREATE TABLE `price_log` (
			`id` int(11) NOT NULL,
			`product_id` mediumint(8) UNSIGNED NOT NULL,
			`log` text NOT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
		$sql[] = "ALTER TABLE `price_log` ADD PRIMARY KEY (`id`);";
		$sql[] = "ALTER TABLE `price_log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

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