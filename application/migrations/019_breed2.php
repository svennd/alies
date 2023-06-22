<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_breed2 extends CI_Migration {

	protected $up_version = "018";
	protected $down_version = "017";


	public function up()
	{
		  $sql[] = "ALTER TABLE `pets` ADD `breed2` INT(11) NULL AFTER `breed`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `pets` DROP `breed2`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}