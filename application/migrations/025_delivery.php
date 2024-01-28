<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_delivery extends CI_Migration {

	protected $up_version = "024";
	protected $down_version = "023";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `register_in` ADD `supplier` VARCHAR(255) NULL AFTER `lotnr`;";
		$sql[] = "ALTER TABLE `register_in` ADD INDEX(`delivery_slip`);";
		$sql[] = "ALTER TABLE `stock` ADD `supplier` VARCHAR(255) NULL AFTER `barcode`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}