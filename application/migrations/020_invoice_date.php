<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Invoice_date extends CI_Migration {

	protected $up_version = "020";
	protected $down_version = "019";


	public function up()
	{
		  $sql[] = "ALTER TABLE `bills` ADD `invoice_date` DATETIME NULL DEFAULT NULL AFTER `invoice_id`;";
		  $sql[] = "ALTER TABLE `bills` ADD `msg_invoice` TEXT NOT NULL AFTER `msg`;";
		  $sql[] = "ALTER TABLE `bills` ADD `modified` TINYINT(1) NOT NULL DEFAULT '0' AFTER `invoice_date`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `bills`
					DROP `invoice_date`,
					DROP `modified`,
					DROP `msg_invoice`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}