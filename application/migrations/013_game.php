<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_game extends CI_Migration {

	protected $up_version = "013";
	protected $down_version = "012";


	public function up()
	{
		$sql[] = "CREATE TABLE `badges` (
			`id` int(11) NOT NULL,
			`title` varchar(255) NOT NULL,
			`x` int(11) NOT NULL,
			`y` int(11) NOT NULL,
			`description` text NOT NULL,
			`value` int(11) NOT NULL,
			`level` int(11) NOT NULL,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `delivery_slip` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`vet` int(11) NOT NULL,
			`note` text NOT NULL,
			`regdate` date NOT NULL,
			`location` int(11) NOT NULL,
			`updated_at` datetime NOT NULL,
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `register_in` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`product` int(11) NOT NULL,
			`volume` decimal(10,2) NOT NULL,
			`eol` date NOT NULL,
			`in_price` decimal(10,2) NOT NULL,
			`lotnr` varchar(255) NOT NULL,
			`delivery_slip` int(11) NOT NULL,
			`updated_at` datetime NOT NULL,
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "DROP TABLE `badges`";
		$sql[] = "DROP TABLE `delivery_slip`";
		$sql[] = "DROP TABLE `register_in`";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
