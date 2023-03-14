<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_invoice_id extends CI_Migration {

	protected $up_version = "017";
	protected $down_version = "016";


	public function up()
	{
		  $sql[] = "
				ALTER TABLE `bills` ADD `transfer` DECIMAL(10,2) NOT NULL AFTER `card`, 
						ADD `invoice_id` INT(11) NULL DEFAULT NULL AFTER `transfer`;
		  ";
		  $sql[] = "
		  		ALTER TABLE `bills` 
					CHANGE `cash` `cash` DECIMAL(10,2) NULL, 
					CHANGE `card` `card` DECIMAL(10,2) NULL;
		  ";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `bills` DROP `transfer`, DROP `invoice_id`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}