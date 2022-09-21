<?php
/*
	change the lotnr to a varchar as its not always an int
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_float_to_decimal extends CI_Migration {

	protected $up_version = "009";
	protected $down_version = "008";


	public function up()
	{
		// add support for stock messages when input is done
		$sql[] = "ALTER TABLE `stock` CHANGE `in_price` `in_price` DECIMAL(10,2) NOT NULL;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 