<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Search extends CI_Migration {

	protected $up_version = "038";
	protected $down_version = "037";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE events ADD FULLTEXT(title, anamnese);";
		$sql[] = "ALTER TABLE events ADD FULLTEXT(anamnese);";
		$sql[] = "ALTER TABLE events ADD FULLTEXT(title);";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE events DROP INDEX anamnese;";
		$sql[] = "ALTER TABLE events DROP INDEX title;";
		$sql[] = "ALTER TABLE events DROP INDEX title_anamnese;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}