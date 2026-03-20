<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_vamreg extends CI_Migration {

	protected $up_version = "040";
	protected $down_version = "039";

	public function up()
	{
		$sql = array();

		$sql[] = "CREATE TABLE `vamreg_index` (
				`id` int(10) UNSIGNED NOT NULL,
				`cti` varchar(50) NOT NULL,
				`cnk` varchar(20) NOT NULL,
				`ppnNl` varchar(255) NOT NULL,
				`packSize` varchar(150) DEFAULT NULL,
				`susage` enum('HUMAN','VETERINARY') NOT NULL,
				`maName` varchar(255) DEFAULT NULL,
				`maNumber` varchar(50) DEFAULT NULL,
				`mahName` varchar(255) DEFAULT NULL,
				`updated_at` datetime DEFAULT NULL,
				`created_at` datetime NOT NULL DEFAULT current_timestamp()
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";

		$sql[] = "CREATE TABLE `vamreg_in_buffer` (
				`id` bigint(20) UNSIGNED NOT NULL,
				`cnk` varchar(20) NOT NULL,
				`wholesale_id` int(11) DEFAULT NULL,
				`in_quantity_pack_count` int(11) NOT NULL,
				`delivery` date NOT NULL,
				`product_type` enum('BE','FOREIGN','MAGISTRAL') NOT NULL,
				`provider_type` enum('DIST_BE','DIST_EU','DIST_NOT_EU','PHARMACY_BE','PHARMACY_EU','VET_DEPOT') NOT NULL,
				`status` enum('DRAFT','VALIDATED','SENT','ERROR','INVALID') NOT NULL DEFAULT 'DRAFT',
				`api_declaration_id` char(24) DEFAULT NULL,
				`api_error` text DEFAULT NULL,
				`sent_at` datetime DEFAULT NULL,
				`updated_at` datetime DEFAULT NULL,
				`created_at` datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		
		$sql[] = "CREATE TABLE `vamreg_out_buffer` (
				`id` bigint(20) UNSIGNED NOT NULL,
				`event` int(11) NOT NULL,
				`event_line` int(11) NOT NULL,
				`cnk` varchar(20) DEFAULT NULL,
				`out_quantity_type` enum('PACKS','UNITS') NOT NULL,
				`out_quantity_pack_count` decimal(10,2) DEFAULT NULL,
				`out_quantity_unit_count` decimal(10,2) DEFAULT NULL,
				`out_quantity_unit` enum('G','ML','PRESTATION','PIECE','TUBE') DEFAULT NULL,
				`out_date` date NOT NULL,
				`product_type` enum('BE','FOREIGN','MAGISTRAL') NOT NULL,
				`target_species` enum('CAT','DOG','HORSE','OTHER_NON_FOOD') NOT NULL,
				`indication` enum('DIGEST','EYE','LOCO','MAST','NERVE','PERI_OP','RESP','DERMA','SYST','URO_GEN') DEFAULT NULL,
				`vet` smallint(5) UNSIGNED NOT NULL,
				`status` enum('DRAFT','VALIDATED','SENT','ERROR','INVALID') NOT NULL DEFAULT 'DRAFT',
				`api_declaration_id` char(24) DEFAULT NULL,
				`api_error` text DEFAULT NULL,
				`sent_at` datetime DEFAULT NULL,
				`updated_at` datetime DEFAULT NULL,
				`created_at` datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;";
		$sql[] = "ALTER TABLE `vamreg_index`  ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `cti` (`cti`),  ADD KEY `cnk` (`cnk`);";
		$sql[] = "ALTER TABLE `vamreg_in_buffer`  ADD PRIMARY KEY (`id`),  ADD KEY `idx_cnk` (`cnk`),  ADD KEY `idx_status` (`status`),  ADD KEY `idx_date_time` (`delivery`),  ADD KEY `wholesale_id` (`wholesale_id`);";
		$sql[] = "ALTER TABLE `vamreg_out_buffer` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `event_line` (`event_line`), ADD KEY `cnk` (`cnk`),  ADD KEY `event` (`event`);";
		$sql[] = "ALTER TABLE `vamreg_index` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;";
		$sql[] = "ALTER TABLE `vamreg_in_buffer` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;";
		$sql[] = "ALTER TABLE `vamreg_out_buffer` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;";

		$sql[] = "ALTER TABLE `products` ADD `cnk` VARCHAR(20) NULL AFTER `vhbcode`;";
		$sql[] = "ALTER TABLE `products` ADD `is_antibiotic` TINYINT(1) NOT NULL DEFAULT 0, ADD INDEX idx_is_antibiotic (is_antibiotic);";
		$sql[] = "ALTER TABLE `products` ADD `default_indication` ENUM('DIGEST','EYE','LOCO','MAST','NERVE','PERI_OP','RESP','DERMA','SYST','URO_GEN','NONE') NULL DEFAULT NULL AFTER `is_antibiotic`;";
		$sql[] = "ALTER TABLE `products` ADD `ab_unit` ENUM('PACKS','PIECE','PRESTATION','TUBE','G','ML') NULL DEFAULT NULL AFTER `default_indication`;";
		$sql[] = "ALTER TABLE `users` ADD `order_nr` VARCHAR(6) NOT NULL AFTER `vsens`;";
		$sql[] = "ALTER TABLE `products` ADD `ab_unit_volume` DECIMAL(10,2) NOT NULL AFTER `ab_unit`;";
		$sql[] = "ALTER TABLE `products` CHANGE `ab_unit_volume` `ab_unit_volume` DECIMAL(10,2) NULL;";
		$sql[] = "ALTER TABLE `products` ADD `cti_e` VARCHAR(20) NULL AFTER `cnk`;";
		
		$sql[] = "ALTER TABLE `delivery` CHANGE `netto_price` `netto_price` DECIMAL(5,2) NULL;";

		# add the cnk based off the wholesale link we already have
		$sql[] = "UPDATE products p JOIN wholesale w ON w.id = p.wholesale SET p.cnk = w.CNK WHERE w.CNK IS NOT NULL AND w.CNK <> '' AND (p.cnk IS NULL OR p.cnk = '');";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}

		# truncate mistaken data
		$this->db->query("TRUNCATE TABLE delivery;");

		# move all files from data/stored/delivery/processed to data/stored/delivery/
		$processedDir = FCPATH . 'data/stored/delivery/processed/';
		$files = glob($processedDir . '*.txt');
		foreach ($files as $file) {
			if (is_file($file)) {
				@rename($file, FCPATH . 'data/stored/delivery/' . basename($file));
			}
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
		$sql[] = "ALTER TABLE `products` DROP COLUMN `cti_e`;";
		
		# vet nr
		$sql[] = "ALTER TABLE `users` DROP COLUMN `order_nr`;";
		
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
