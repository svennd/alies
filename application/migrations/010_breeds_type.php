<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_breeds_type extends CI_Migration {

	protected $up_version = "010";
	protected $down_version = "009";


	public function up()
	{
		// add support for stock messages when input is done
		$sql[] = "ALTER TABLE `breeds` ADD `type` TINYINT NULL DEFAULT '-1' AFTER `name`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `breeds` DROP `type`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 