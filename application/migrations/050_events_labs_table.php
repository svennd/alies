<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_events_labs_table extends CI_Migration {

	protected $up_version = "050";
	protected $down_version = "049";

	public function up()
	{
		if (!$this->db->table_exists('events_labs'))
		{
			$this->db->query("
				CREATE TABLE `events_labs` (
					`event_id` int(11) UNSIGNED NOT NULL,
					`lab_id` int(11) NOT NULL,
					PRIMARY KEY (`event_id`, `lab_id`),
					KEY `lab_id` (`lab_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
			");
		}

		if ($this->db->field_exists('lab', 'events'))
		{
			$this->db->query("
				INSERT IGNORE INTO `events_labs` (`event_id`, `lab_id`)
				SELECT `id`, `lab`
				FROM `events`
				WHERE `lab` > 0;
			");

			if ($this->index_exists('events', 'lab'))
			{
				$this->db->query("ALTER TABLE `events` DROP INDEX `lab`;");
			}

			$this->db->query("ALTER TABLE `events` DROP `lab`;");
		}

		return $this->up_version;
	}

	public function down()
	{
		if (!$this->db->field_exists('lab', 'events'))
		{
			$this->db->query("ALTER TABLE `events` ADD `lab` INT(11) NOT NULL DEFAULT 0 AFTER `report`;");
		}

		if (!$this->index_exists('events', 'lab'))
		{
			$this->db->query("ALTER TABLE `events` ADD KEY `lab` (`lab`);");
		}

		if ($this->db->table_exists('events_labs'))
		{
			$this->db->query("
				UPDATE `events`
				JOIN (
					SELECT `event_id`, MIN(`lab_id`) AS `lab_id`
					FROM `events_labs`
					GROUP BY `event_id`
				) `labs`
					ON `labs`.`event_id` = `events`.`id`
				SET `events`.`lab` = `labs`.`lab_id`;
			");

			$this->db->query("DROP TABLE `events_labs`;");
		}

		return $this->down_version;
	}

	private function index_exists(string $table, string $key_name): bool
	{
		$query = $this->db->query(
			"SHOW INDEX FROM `" . $this->db->escape_str($table) . "` WHERE `Key_name` = " . $this->db->escape($key_name)
		);

		return $query->num_rows() > 0;
	}
}
