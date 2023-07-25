<?php
defined('BASEPATH') OR exit('No direct script access allowed');

# not executed yet 
# needs expanding
class Migration_sql_comment extends CI_Migration {

	protected $up_version = "00";
	protected $down_version = "00";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `bills` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `modified` `modified` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'yes/no value indication if price was manually modified', CHANGE `status` `status` INT(11) NOT NULL COMMENT 'PAYMENT_* constant values';";
		$sql[] = "INSERT INTO `config` (`id`, `name`, `value`, `updated_at`, `created_at`) VALUES (NULL, 'autoclose', 'MTQ=', '2023-07-11 10:01:10', '2023-07-11 10:01:10');";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}