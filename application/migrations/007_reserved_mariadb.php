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
		// reduce the precision
		$sql[] = "alter table products change column `offset` `dead_volume` float(4,2);";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "alter table products change column `dead_volume` `offset` float(11,2);";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
