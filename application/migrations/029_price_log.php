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

		# increase size for storing values
		# it was 5,2 which is way too small
		$sql[] = "ALTER TABLE `lab_detail` CHANGE `value` `value` DECIMAL(10,2) NOT NULL, CHANGE `upper_limit` `upper_limit` DECIMAL(10,2) NOT NULL, CHANGE `lower_limit` `lower_limit` DECIMAL(10,2) NOT NULL;";
		
		# set updated_at to null
		# since it can never be updated
		$sql[] = "ALTER TABLE `register_in` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;";
		$sql[] = "update register_in set updated_at = null;";

		# add primary key to log_stock
		$sql[] = "ALTER TABLE `log_stock` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);";

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