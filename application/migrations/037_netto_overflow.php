<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_netto_overflow extends CI_Migration {

	protected $up_version = "037";
	protected $down_version = "036";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `wholesale` ADD `netto_overflow` DECIMAL(7,2) UNSIGNED NULL DEFAULT NULL AFTER `last_bruto_date`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `wholesale` DROP `netto_overflow`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}