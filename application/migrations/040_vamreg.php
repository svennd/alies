<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_vamreg extends CI_Migration {

	protected $up_version = "040";
	protected $down_version = "039";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products` ADD `cnk` VARCHAR(20) NULL AFTER `vhbcode`;";
		$sql[] = "ALTER TABLE `products` ADD `is_antibiotic` TINYINT(1) NOT NULL DEFAULT 0, ADD INDEX idx_is_antibiotic (is_antibiotic);";
		$sql[] = "ALTER TABLE `products` ADD `default_indication` ENUM('DIGEST','EYE','LOCO','MAST','NERVE','PERI_OP','RESP','DERMA','SYST','URO_GEN','NONE') NULL DEFAULT NULL AFTER `is_antibiotic`;";
		$sql[] = "ALTER TABLE `products` ADD `ab_unit` ENUM('PACKS','PIECE','PRESTATION','TUBE','G','ML') NULL DEFAULT NULL AFTER `default_indication`;";
		$sql[] = "ALTER TABLE `users` ADD `order_nr` VARCHAR(6) NOT NULL AFTER `vsens`;";
		$sql[] = "ALTER TABLE `products` ADD `ab_unit_volume` DECIMAL(10,2) NOT NULL AFTER `ab_unit`;";
		$sql[] = "ALTER TABLE `products` CHANGE `ab_unit_volume` `ab_unit_volume` DECIMAL(10,2) NULL;";
		$sql[] = "ALTER TABLE `lab_report` ADD `deleted_at` DATETIME NULL AFTER `updated_at`;";
		$sql[] = "ALTER TABLE `products` ADD `cti_e` VARCHAR(20) NULL AFTER `cnk`;";
		
        # index
        $sql[] = "ALTER TABLE vamreg_index ADD COLUMN cti varchar(50) NOT NULL AFTER id, ADD UNIQUE KEY uniq_cti (cti);";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `products` DROP COLUMN `cnk`;";
        $sql[] = "ALTER TABLE products DROP COLUMN is_antibiotic;";
        $sql[] = "ALTER TABLE products DROP COLUMN default_indication;";
		$sql[] = "ALTER TABLE products DROP COLUMN ab_unit;";
		$sql[] = "ALTER TABLE products DROP COLUMN ab_unit_volume;";
		$sql[] = "ALTER TABLE `lab_report` DROP COLUMN `deleted_at`;";
		$sql[] = "ALTER TABLE `products` DROP COLUMN `cti_e`;";
		

		# vet nr
		$sql[] = "ALTER TABLE `users` DROP COLUMN `order_nr`;";
		
		# index
		$sql[] = "ALTER TABLE vamreg_index DROP COLUMN cti;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}