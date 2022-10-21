<?php
/*
	LOG EVERYTHING (temporary)
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_stock_log extends CI_Migration {

	protected $up_version = "011";
	protected $down_version = "010";


	public function up()
	{
		$sql[] = "
		CREATE TABLE `log_stock` (
			`id` int(11) NOT NULL,
			`user_id` tinyint(4) NOT NULL,
			`product` int(11) NOT NULL,
			`event` varchar(255) NOT NULL,
			`volume` decimal(10,2) NOT NULL,
			`location` tinyint(4) NOT NULL,
			`level` tinyint(3) NOT NULL,
			`updated_at` datetime NOT NULL DEFAULT current_timestamp(),
			`created_at` datetime NOT NULL DEFAULT current_timestamp()
		  ) ENGINE=InnoDB;";
		$sql[] = "ALTER TABLE `log` ADD `location` TINYINT(4) NOT NULL AFTER `msg`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "DROP TABLE log_stock";
		$sql[] = "ALTER TABLE `log` DROP `location`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
