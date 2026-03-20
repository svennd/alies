<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_api_lab extends CI_Migration {

	protected $up_version = "043";
	protected $down_version = "042";

	public function up()
	{
		$sql = array();
		$sql[] = "CREATE TABLE IF NOT EXISTS `api_keys` (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`key_hash` CHAR(64) NOT NULL,
			`device` VARCHAR(64) NOT NULL,
			`active` TINYINT(1) NOT NULL,
			`rate_limit` SMALLINT(6) NOT NULL DEFAULT 60,
			`last_used_at` DATETIME DEFAULT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`created_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `key_hash` (`key_hash`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `api_rate_limit` (
			`api_key_id` INT(10) UNSIGNED NOT NULL,
			`minute` CHAR(12) NOT NULL,
			`count` SMALLINT(1) NOT NULL DEFAULT 1,
			PRIMARY KEY (`api_key_id`, `minute`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `lab_plots` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`report_id` INT(11) NOT NULL,
			`type` VARCHAR(64) NOT NULL,
			`data` TEXT NOT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`created_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `report_id` (`report_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `lab_report` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`pet_id` INT(11) NOT NULL,
			`device` VARCHAR(64) DEFAULT NULL,
			`source` VARCHAR(64) DEFAULT NULL,
			`source_id` VARCHAR(64) DEFAULT NULL,
			`software_version` VARCHAR(255) DEFAULT NULL,
			`metadata` TEXT DEFAULT NULL,
			`sample_date` DATETIME DEFAULT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`deleted_at` DATETIME DEFAULT NULL,
			`created_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `source` (`source_id`, `source`) USING BTREE,
			UNIQUE KEY `device` (`device`, `source_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `lab_report_pending` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`device` VARCHAR(255) DEFAULT NULL,
			`source` VARCHAR(64) DEFAULT NULL,
			`source_id` VARCHAR(64) DEFAULT NULL,
			`raw_payload` TEXT DEFAULT NULL,
			`identifiers` TEXT DEFAULT NULL,
			`reason` VARCHAR(64) DEFAULT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`created_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `lab_results` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`report_id` INT(11) NOT NULL,
			`code` VARCHAR(64) NOT NULL,
			`value_num` DECIMAL(10,2) DEFAULT NULL,
			`value_text` TEXT DEFAULT NULL,
			`unit` VARCHAR(32) DEFAULT NULL,
			`ref_min` DECIMAL(10,2) DEFAULT NULL,
			`ref_max` DECIMAL(10,2) DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";

		$sql[] = "ALTER TABLE IF EXISTS `lab_report` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `updated_at`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "DROP TABLE IF EXISTS `lab_results`;";
		$sql[] = "DROP TABLE IF EXISTS `lab_report_pending`;";
		$sql[] = "DROP TABLE IF EXISTS `lab_plots`;";
		$sql[] = "DROP TABLE IF EXISTS `lab_report`;";
		$sql[] = "DROP TABLE IF EXISTS `api_rate_limit`;";
		$sql[] = "DROP TABLE IF EXISTS `api_keys`;";
		$sql[] = "ALTER TABLE IF EXISTS `lab_report` DROP COLUMN IF EXISTS `deleted_at`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
