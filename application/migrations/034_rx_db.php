<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rx_db extends CI_Migration {

	protected $up_version = "034";
	protected $down_version = "033";

	public function up()
	{
		$sql = array();
		$sql[] = "CREATE TABLE IF NOT EXISTS `rx` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`path` varchar(255) NOT NULL,
					`pet_id` int(11) NOT NULL,
					`studydate` date NOT NULL,
					`description` varchar(255) NOT NULL,
					`bodypart` varchar(255) NOT NULL,
					`client` varchar(255) DEFAULT NULL,
					`petname` varchar(255) DEFAULT NULL,
					`gender` tinyint(3) UNSIGNED DEFAULT NULL,
					`petbirthdate` date DEFAULT NULL,
					`studydescription` varchar(255) DEFAULT NULL,
					`series` int(11) DEFAULT NULL,
					`updated_at` datetime DEFAULT NULL,
					`created_at` datetime DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `path` (`path`),
					KEY `pet_id` (`pet_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
					";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "DROP TABLE `rx`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}