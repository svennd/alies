<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_events_lab extends CI_Migration {

	protected $up_version = "048";
	protected $down_version = "047";

	public function up()
	{
		if (!$this->db->field_exists('lab', 'events'))
		{
			$this->db->query("ALTER TABLE `events` ADD `lab` INT(11) NOT NULL DEFAULT 0 AFTER `report`;");
		}

		if (!$this->index_exists('events', 'lab'))
		{
			$this->db->query("ALTER TABLE `events` ADD KEY `lab` (`lab`);");
		}

		$this->db->query("DELETE FROM `events` WHERE `title` REGEXP '^lab:[0-9]+$';");

		return $this->up_version;
	}

	public function down()
	{
		if ($this->index_exists('events', 'lab'))
		{
			$this->db->query("ALTER TABLE `events` DROP INDEX `lab`;");
		}

		if ($this->db->field_exists('lab', 'events'))
		{
			$this->db->query("ALTER TABLE `events` DROP `lab`;");
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
