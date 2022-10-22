<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_delete_writeoff extends CI_Migration {

	protected $up_version = "012";
	protected $down_version = "011";


	public function up()
	{
		$sql[] = "DROP TABLE `stock_write_off`";
		$sql[] = "DROP TABLE `alerts`";
		$sql[] = "ALTER TABLE `events` ADD INDEX(`vet`);";
		$sql[] = "ALTER TABLE `bills` ADD `msg` TEXT NOT NULL AFTER `status`;";

		$sql[] = "ALTER TABLE `events_products` ADD INDEX(`event_id`);";
		$sql[] = "ALTER TABLE `events_products` ADD INDEX(`product_id`);";

		$sql[] = "ALTER TABLE `events_procedures` ADD INDEX(`procedures_id`);";
		$sql[] = "ALTER TABLE `events_procedures` ADD INDEX(`event_id`);";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "CREATE TABLE `stock_write_off` (`id` INT NOT NULL ) ENGINE = InnoDB;";
		$sql[] = "CREATE TABLE `alerts` (`id` INT NOT NULL ) ENGINE = InnoDB;";

		$sql[] = "ALTER TABLE `bills` DROP `msg`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
