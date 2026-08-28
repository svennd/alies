<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_lab_pending_lifecycle extends CI_Migration
{
	protected $up_version = "054";
	protected $down_version = "053";

	public function up()
	{
		$columns = array(
			'resolved_at' => "DATETIME NULL DEFAULT NULL AFTER `created_at`",
			'resolved_by' => "INT(11) NULL DEFAULT NULL AFTER `resolved_at`",
			'resolved_pet_id' => "INT(11) NULL DEFAULT NULL AFTER `resolved_by`",
			'report_id' => "INT(11) NULL DEFAULT NULL AFTER `resolved_pet_id`",
			'deleted_at' => "DATETIME NULL DEFAULT NULL AFTER `report_id`",
			'deleted_by' => "INT(11) NULL DEFAULT NULL AFTER `deleted_at`",
		);

		foreach ($columns as $name => $definition) {
			if (!$this->db->field_exists($name, 'lab_report_pending')) {
				$this->db->query("ALTER TABLE `lab_report_pending` ADD `{$name}` {$definition};");
			}
		}

		$indexes = $this->db->query("SHOW INDEX FROM `lab_report_pending` WHERE Key_name = 'pending_active'")->result_array();
		if (!$indexes) {
			$this->db->query("ALTER TABLE `lab_report_pending` ADD INDEX `pending_active` (`resolved_at`, `deleted_at`, `created_at`);");
		}

		return $this->up_version;
	}

	public function down()
	{
		$indexes = $this->db->query("SHOW INDEX FROM `lab_report_pending` WHERE Key_name = 'pending_active'")->result_array();
		if ($indexes) {
			$this->db->query("ALTER TABLE `lab_report_pending` DROP INDEX `pending_active`;");
		}

		foreach (array('deleted_by', 'deleted_at', 'report_id', 'resolved_pet_id', 'resolved_by', 'resolved_at') as $name) {
			if ($this->db->field_exists($name, 'lab_report_pending')) {
				$this->db->query("ALTER TABLE `lab_report_pending` DROP `{$name}`;");
			}
		}

		return $this->down_version;
	}
}
