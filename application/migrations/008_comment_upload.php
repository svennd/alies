<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_comment_upload extends CI_Migration {

	protected $up_version = "008";
	protected $down_version = "007";


	public function up()
	{
		// add support for stock messages when input is done
		$sql[] = "ALTER TABLE `events_upload` ADD `comment` TEXT NOT NULL AFTER `mime`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `events_upload` DROP `comment`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
