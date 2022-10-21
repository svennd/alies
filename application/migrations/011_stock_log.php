<?php
/*
	LOG EVERYTHING (temporary)
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_stock_log extends CI_Migration {

	protected $up_version = "011";
	protected $down_version = "010";


	public function up()
	{
		$sql[] = "
            CREATE TABLE `log_stock` (
                `id` INT NOT NULL AUTO_INCREMENT , 
                `user_id` TINYINT(4) NOT NULL , 
                `product` INT NOT NULL , 
                `product` VARCHAR(255) NOT NULL , 
                `volume` DECIMAL(10,2) NOT NULL , 
                `location` TINYINT(4) NOT NULL ,
                `msg` TEXT NOT NULL , 
                `level` TINYINT(3) NOT NULL , 
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
            PRIMARY KEY (`id`)) ENGINE = InnoDB;";
		$sql[] = "ALTER TABLE `log` ADD `location` TINYINT(4) NOT NULL AFTER `msg`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "DROP TABLE log_stock";
		$sql[] = "ALTER TABLE `log` DROP `location`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 
