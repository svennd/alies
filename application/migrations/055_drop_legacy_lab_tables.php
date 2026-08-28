<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_drop_legacy_lab_tables extends CI_Migration
{
	protected $up_version = "055";
	protected $down_version = "054";

	public function up()
	{
		// Migration 047 copied these rows into lab_report/lab_results or retained
		// unmatched reports as self-contained JSON in lab_report_pending.
		$this->db->query("DROP TABLE IF EXISTS `lab_detail`;");
		$this->db->query("DROP TABLE IF EXISTS `lab`;");

		return $this->up_version;
	}

	public function down()
	{
		// A rollback can restore the retired schema, but not data discarded by up().
		$this->db->query("CREATE TABLE IF NOT EXISTS `lab` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`lab_id` INT(11) NOT NULL,
			`lab_date` DATE DEFAULT NULL,
			`lab_patient_id` INT(11) DEFAULT NULL,
			`pet` INT(11) DEFAULT NULL,
			`lab_updated_at` DATETIME DEFAULT NULL,
			`lab_created_at` DATETIME DEFAULT NULL,
			`lab_comment` TEXT NOT NULL,
			`source` VARCHAR(255) NOT NULL,
			`comment` TEXT NOT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`created_at` DATETIME DEFAULT NULL,
			`deleted_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `lab_id` (`lab_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `lab_detail` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`lab_id` INT(11) NOT NULL,
			`sample_id` INT(11) NOT NULL,
			`value` DECIMAL(10,2) NOT NULL,
			`string_value` VARCHAR(255) NOT NULL,
			`upper_limit` DECIMAL(10,2) NOT NULL,
			`lower_limit` DECIMAL(10,2) NOT NULL,
			`report` TINYINT(1) NOT NULL,
			`lab_code` INT(11) NOT NULL,
			`lab_code_text` VARCHAR(255) NOT NULL,
			`lab_updated_at` DATETIME DEFAULT NULL,
			`comment` TEXT NOT NULL,
			`unit` VARCHAR(255) NOT NULL,
			`created_at` DATETIME DEFAULT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `sample_id` (`sample_id`, `lab_code`),
			KEY `lab_id` (`lab_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

		return $this->down_version;
	}
}
