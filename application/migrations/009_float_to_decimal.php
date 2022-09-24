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
		$sql[] = "ALTER TABLE `stock` 
			CHANGE `in_price` `in_price` DECIMAL(10,2) NOT NULL,
			CHANGE `product_id` `product_id` MEDIUMINT UNSIGNED NOT NULL, 
			CHANGE `volume` `volume` DECIMAL(10,2) NOT NULL, 
			CHANGE `state` `state` TINYINT UNSIGNED NOT NULL;
		";

		// bills
		$sql[] = "ALTER TABLE `bills` 
			CHANGE `amount` `amount` DECIMAL(10,2) NULL DEFAULT NULL, 
			CHANGE `cash` `cash` DECIMAL(10,2) NULL DEFAULT NULL, 
			CHANGE `card` `card` DECIMAL(10,2) NULL DEFAULT NULL;";

		// booking codes
		$sql[] = "ALTER TABLE `booking_codes` CHANGE `btw` `btw` TINYINT NOT NULL;";

		// locations
		$sql[] = "ALTER TABLE `stock_location` CHANGE `id` `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT;";

		// local limits
		$sql[] = "ALTER TABLE `stock_limit` 
			CHANGE `stock` `stock` TINYINT UNSIGNED NOT NULL,
			CHANGE `product_id` `product_id` MEDIUMINT UNSIGNED NOT NULL,
			CHANGE `volume` `volume` SMALLINT UNSIGNED NOT NULL;";

		// events
		$sql[] = "ALTER TABLE `events` 
			CHANGE `vet` `vet` TINYINT NOT NULL, 
			CHANGE `location` `location` TINYINT NOT NULL,
			CHANGE `status` `status` TINYINT NOT NULL,
			CHANGE `type` `type` TINYINT NOT NULL,
			CHANGE `vet_support_1` `vet_support_1` TINYINT NOT NULL, 
			CHANGE `vet_support_2` `vet_support_2` TINYINT NOT NULL, 
			CHANGE `no_history` `no_history` TINYINT(1) NOT NULL
			;";

		// events_procedures
		$sql[] = "ALTER TABLE `events_procedures` 
			CHANGE `procedures_id` `procedures_id` SMALLINT NOT NULL, 
			CHANGE `amount` `amount` TINYINT NOT NULL,
			CHANGE `net_price` `net_price` DECIMAL(10,2) NOT NULL, 
			CHANGE `price` `price` DECIMAL(10,4) NOT NULL, 
			CHANGE `btw` `btw` TINYINT NOT NULL, 
			CHANGE `booking` `booking` TINYINT(11) NOT NULL, 
			CHANGE `calc_net_price` `calc_net_price` DECIMAL(10,2) NOT NULL;
		";

		$sql[] = "ALTER TABLE `events_products` 
			CHANGE `product_id` `product_id` MEDIUMINT NOT NULL, 
			CHANGE `volume` `volume` DECIMAL(10,2) NOT NULL, 
			CHANGE `net_price` `net_price` DECIMAL(10,2) NOT NULL, 
			CHANGE `price` `price` DECIMAL(10,4) NOT NULL, 
			CHANGE `btw` `btw` TINYINT NOT NULL, 
			CHANGE `booking` `booking` TINYINT NOT NULL, 
			CHANGE `calc_net_price` `calc_net_price` DECIMAL(10,2) NOT NULL;
		";

		$sql[] = "ALTER TABLE `events_upload` 
			CHANGE `user` `user` TINYINT NOT NULL, 
			CHANGE `location` `location` TINYINT NOT NULL;
		";
		$sql[] = "ALTER TABLE `products_type` 
			CHANGE `id` `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT;
		";

		$sql[] = "ALTER TABLE `log` 
			CHANGE `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
			CHANGE `user_id` `user_id` TINYINT UNSIGNED NOT NULL, 
			CHANGE `level` `level` TINYINT UNSIGNED NOT NULL;
		";

		$sql[] = "ALTER TABLE `owners` 
			CHANGE `telephone` `telephone` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, 
			CHANGE `mobile` `mobile` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, 
			CHANGE `phone3` `phone3` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
			CHANGE `phone2` `phone2` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
		";

		$sql[] = "ALTER TABLE `pets` CHANGE `last_weight` `last_weight` DECIMAL(6,2) NOT NULL;";
		$sql[] = "ALTER TABLE `pets_weight` CHANGE `weight` `weight` DECIMAL(6,2) NOT NULL;";
		$sql[] = "ALTER TABLE `procedures` 
			CHANGE `price` `price` DECIMAL(11,2) NOT NULL, 
			CHANGE `booking_code` `booking_code` TINYINT UNSIGNED NOT NULL;";

		$sql[] = "ALTER TABLE `products` 
			CHANGE `type` `type` TINYINT UNSIGNED NOT NULL, 
			CHANGE `dead_volume` `dead_volume` DECIMAL(4,2) NULL DEFAULT NULL, 
			CHANGE `buy_volume` `buy_volume` SMALLINT NOT NULL, 
			CHANGE `sell_volume` `sell_volume` DECIMAL(10,2) NOT NULL, 
			CHANGE `buy_price` `buy_price` DECIMAL(10,2) NOT NULL, 
			CHANGE `input_barcode` `input_barcode` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, 
			CHANGE `limit_stock` `limit_stock` SMALLINT UNSIGNED NOT NULL, 
			CHANGE `vaccin_freq` `vaccin_freq` SMALLINT UNSIGNED NOT NULL;";

		$sql[] = "ALTER TABLE `products_price` 
			CHANGE `product_id` `product_id` MEDIUMINT UNSIGNED NOT NULL, 
			CHANGE `volume` `volume` DECIMAL(5,2) NOT NULL, 
			CHANGE `price` `price` DECIMAL(5,2) NOT NULL;";

		$sql[] = "ALTER TABLE `stock_write_off` 
			CHANGE `product_id` `product_id` MEDIUMINT UNSIGNED NOT NULL, 
			CHANGE `volume` `volume` DECIMAL(5,2) NOT NULL, 
			CHANGE `location` `location` TINYINT UNSIGNED NOT NULL, 
			CHANGE `vet` `vet` TINYINT UNSIGNED NOT NULL;";
		
		$sql[] = "ALTER TABLE `tooth` 
			CHANGE `vet` `vet` SMALLINT UNSIGNED NOT NULL, 
			CHANGE `tooth` `tooth` TINYINT UNSIGNED NOT NULL;";

		$sql[] = "ALTER TABLE `tooth_msg` 
			CHANGE `vet` `vet` TINYINT UNSIGNED NOT NULL, 
			CHANGE `location` `location` TINYINT UNSIGNED NOT NULL;
			";

		$sql[] = "ALTER TABLE `vaccine_pet` 
			CHANGE `location` `location` TINYINT UNSIGNED NOT NULL, 
			CHANGE `vet` `vet` TINYINT UNSIGNED NOT NULL;";

		$sql[] = "ALTER TABLE `users` CHANGE `current_location` `current_location` TINYINT UNSIGNED NOT NULL;";

		# add date for when catalogus is updated
		$sql[] = "ALTER TABLE `products` ADD `buy_price_date` DATE NULL DEFAULT NULL AFTER `buy_price`;";
		# vhb code 
		$sql[] = "ALTER TABLE `products` ADD `vhbcode` VARCHAR(255) NOT NULL AFTER `vaccin_freq`;";
		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->up_version : false;
	}

	public function down()
	{
		$sql[] = "ALTER TABLE `products`
						DROP `buy_price_date`,
						DROP `vhbcode`;";

		foreach ($sql as $q)
		{
			$r = $this->db->query($q);
		}
		return ($r) ? $this->down_version : false;
	}
}
 