<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_pet_avatar extends CI_Migration
{
	protected $up_version = "052";
	protected $down_version = "051";

	public function up()
	{
		if (!$this->db->field_exists('avatar', 'pets')) {
			$this->db->query("ALTER TABLE `pets` ADD `avatar` VARCHAR(255) NULL DEFAULT NULL AFTER `transfered`;");
		}

		return $this->up_version;
	}

	public function down()
	{
		if ($this->db->field_exists('avatar', 'pets')) {
			$this->db->query("ALTER TABLE `pets` DROP `avatar`;");
		}

		return $this->down_version;
	}
}
