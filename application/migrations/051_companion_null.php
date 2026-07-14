<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_companion_null extends CI_Migration {

	protected $up_version = "051";
	protected $down_version = "050";

	public function up()
	{
		$this->db->query("UPDATE pets SET companion = NULL WHERE companion = 0;");
		return $this->up_version;
	}

	public function down()
	{
		return $this->down_version;
	}
}
