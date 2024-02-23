<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_stock_value extends CI_Migration {

	protected $up_version = "028";
	protected $down_version = "027";

	public function up()
	{
		$sql = array();
		$sql[] = "CREATE TABLE `stock_value` (
			`id` int(11) NOT NULL,
			`value` decimal(10,2) NOT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
		$sql[] = "ALTER TABLE `stock_value`ADD PRIMARY KEY (`id`);";
		$sql[] = "ALTER TABLE `stock_value` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

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