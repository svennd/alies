<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_settings extends CI_Migration {

	protected $up_version = "016";
	protected $down_version = "015";


	public function up()
	{
		$sql[] = "ALTER TABLE `config` DROP INDEX `name`;";
		$sql[] = "ALTER TABLE `config` ADD UNIQUE(`name`);";
		$sql[] = "
		CREATE TABLE `lab` (
			`id` int(11) NOT NULL,
			`lab_id` int(11) NOT NULL,
			`lab_date` date DEFAULT NULL,
			`lab_patient_id` int(11) DEFAULT NULL,
			`lab_updated_at` datetime DEFAULT NULL,
			`lab_created_at` datetime DEFAULT NULL,
			`lab_comment` text NOT NULL,
			`source` varchar(255) NOT NULL,
			`comment` text NOT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		  ";
		$sql[] = "ALTER TABLE `lab`	ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `lab_id` (`lab_id`);";
		$sql[] = "ALTER TABLE `lab`	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
		$sql[] = "
		CREATE TABLE `lab_detail` (
			`id` int(11) NOT NULL,
			`lab_id` int(11) NOT NULL,
			`sample_id` int(11) NOT NULL,
			`value` decimal(5,2) NOT NULL,
			`string_value` varchar(255) NOT NULL,
			`upper_limit` decimal(5,2) NOT NULL,
			`lower_limit` decimal(5,2) NOT NULL,
			`report` tinyint(1) NOT NULL,
			`lab_code` int(11) NOT NULL,
			`lab_code_text` varchar(255) NOT NULL,
			`lab_updated_at` datetime DEFAULT NULL,
			`comment` varchar(255) NOT NULL,
			`unit` varchar(255) NOT NULL,
			`created_at` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
		  ";
		  $sql[] = "ALTER TABLE `lab_detail` ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `sample_id` (`sample_id`,`lab_code`),  ADD KEY `lab_id` (`lab_id`);";
		  $sql[] = "ALTER TABLE `lab_detail` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "DROP TABLE lab`;";
		$sql[] = "DROP TABLE lab_detail`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}