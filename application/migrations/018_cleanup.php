<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_cleanup extends CI_Migration {

	protected $up_version = "018";
	protected $down_version = "017";


	public function up()
	{
		  $sql[] = "DROP TABLE `msg_messages`";
		  $sql[] = "DROP TABLE `msg_participants`";
		  $sql[] = "DROP TABLE `msg_state`";
		  $sql[] = "ALTER TABLE `products` ADD `discontinued` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sellable`;";
		  $sql[] = "ALTER TABLE `products` ADD `wholesale_name` VARCHAR(255) NOT NULL AFTER `name`;";

		  # no rappel binary
		  $sql[] = "ALTER TABLE `vaccine_pet` ADD `no_rappel` TINYINT(1) NOT NULL AFTER `redo`;`";


		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	// irreverisble :) 
	// public function down()
	// {
	// 	return ($r) ? $this->down_version : false;
	// }
}