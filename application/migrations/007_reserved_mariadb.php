<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Reserved_mariadb extends CI_Migration {

	protected $up_version = "007";
	protected $down_version = "006";


	public function up()
	{
		// add support for stock messages when input is done
		$sql[] = "CREATE TABLE `stock_input` (
				  `id` int(11) NOT NULL,
				  `user` int(11) NOT NULL,
				  `location` int(11) NOT NULL,
				  `msg` text NOT NULL,
				  `updated_at` datetime NOT NULL,
				  `created_at` datetime NOT NULL
				) ENGINE=InnoDB;
		";
		$sql[] = "ALTER TABLE `stock_input` ADD PRIMARY KEY (`id`);";
		$sql[] = "ALTER TABLE `stock_input` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

		// reduce the precision remove the keyword offset
		$sql[] = "alter table products change column `offset` `dead_volume` float(4,2);";

		// add support for reporting function on events
		$sql[] = "ALTER TABLE `events` ADD `report` TINYINT(1) NOT NULL DEFAULT '0' AFTER `vet_support_2`;";


		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "alter table products change column `dead_volume` `offset` float(11,2);";
		$sql[] = "ALTER TABLE `events` DROP `report`;";
		$sql[] = "drop table stock_input;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
