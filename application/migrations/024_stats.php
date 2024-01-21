<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_stats extends CI_Migration {

	protected $up_version = "024";
	protected $down_version = "023";

	public function up()
	{
		$sql = array();
		$sql[] = "ALTER TABLE `bills` CHANGE `total_net` `total_net` DECIMAL(10,2) NULL DEFAULT NULL;";
		$sql[] = "ALTER TABLE `bills` CHANGE `total_brut` `total_brut` DECIMAL(10,2) NULL DEFAULT NULL;";
		$sql[] = "CREATE TABLE `stats` (`id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(255) NOT NULL , `query` TEXT NOT NULL ,`help` text NOT NULL, `updated_at` DATETIME NULL , `created_at` DATETIME NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
		$sql[] = "INSERT INTO `stats` (`id`, `title`, `query`, `help`, `updated_at`, `created_at`) VALUES
		(1, 'revenue over period', 'SELECT\n    sum(total_brut) as total_bruto, sum(total_net) as total_netto, sum(	BTW_6) as total_6,sum(BTW_21) as total_21,\ncount(*) as total_bills\nFROM\n    bills\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')', '', NULL, NULL),
		(2, 'revenue over period per month', 'SELECT MONTH(invoice_date) as mnt,\n    MONTHNAME(invoice_date) as month, \n sum(total_brut) as total_bruto, sum(total_net) as total_netto, sum(	BTW_6) as total_6,sum(BTW_21) as total_21,\ncount(*) as total_bills\nFROM\n    bills\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n    MONTH(invoice_date)\nORDER BY\n MONTH(invoice_date)', '', NULL, NULL),
		(3, 'revenue per vet', 'SELECT\n    users.first_name, sum(total_brut) as total_brut, sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as total_operations\nFROM\n    bills\nJOIN\n    users\n    ON\n        bills.vet = users.id\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\nvet', 'Operations are based on the event type', NULL, NULL),
		(4, 'revenue per vet per month', 'SELECT\n    users.first_name, MONTHNAME(invoice_date) as month, sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as total_operations\nFROM\n    bills\nJOIN\n    users\n    ON\n        bills.vet = users.id\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n   MONTH(invoice_date), vet', 'Operations are based on the event type', NULL, NULL),
		(5, 'revenue per location', 'SELECT\n    stock_location.name as location, sum(total_brut) as total_brut, sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as operations\nFROM\n    bills\nJOIN\n    stock_location\n    ON\n        bills.location = stock_location.id\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n    bills.location\n', '', NULL, NULL),
		(6, 'operations for initial_location', 'SELECT\n    stock_location.name as stock_location, sum(total_brut) as total_brut, sum(total_net) as total_net\nFROM\n    bills\nJOIN\n    events\n    ON\n        events.payment = bills.id\nJOIN\n    owners\n    ON\n        bills.owner_id = owners.id\nJOIN\n    stock_location\n    ON\n        owners.initial_loc = stock_location.id\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (bills.status = \'4\' OR bills.status = \'7\')\nAND\n    events.type = 1\nGROUP BY\n    owners.initial_loc', '', NULL, NULL),
		(7, 'operations for initial_vet', '\nSELECT\n    users.first_name as vet, sum(total_brut), sum(total_net) as total_net\nFROM\n    bills\nJOIN\n    events\n    ON\n        events.payment = bills.id\nJOIN\n    owners\n    ON\n        bills.owner_id = owners.id\nJOIN\n    users\n    ON\n        owners.initial_vet = users.id\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (bills.status = \'4\' OR bills.status = \'7\')\nAND\n    events.type = 1\nGROUP BY\n    owners.initial_vet', '', NULL, NULL),
		(8, 'revenue/hour', 'SELECT\n   HOUR(bills.created_at) as hour,\n    sum(total_brut) as total_brut,\n    sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as operations\nFROM\n    bills\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n    HOUR(bills.created_at)\nORDER BY\n    HOUR(bills.created_at)\n', '', NULL, NULL),
		(9, 'revenue/hour for a week', 'SELECT\n    DAYNAME(bills.created_at) as day,\n    HOUR(bills.created_at) as hour,\n    sum(total_brut) as total_brut,\n    sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as operations\nFROM\n    bills\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n    DAYNAME(bills.created_at), HOUR(bills.created_at)\nORDER BY\n   DATE_FORMAT(bills.created_at, \'%%w\'), HOUR(bills.created_at)', '', NULL, NULL),
		(10, 'revenue/hour for a week per location', 'SELECT\n    DAYNAME(bills.created_at) as day,\n    HOUR(bills.created_at) as hour,\n    stock_location.name as location,\n    sum(total_net) as total_net,\n    sum(\n        CASE WHEN EXISTS\n            (SELECT 1 FROM events WHERE events.payment = bills.id AND events.type = 1 LIMIT 1)\n        THEN total_net\n        ELSE 0\n        END\n    ) as operations\nFROM\n    bills\nLEFT JOIN\n    stock_location ON stock_location.id = bills.location\nWHERE\n    DATE(invoice_date) >= STR_TO_DATE(\'%s\', \'%%Y-%%m-%%d\')\nAND\n    DATE(invoice_date) <= LAST_DAY(\'%s\')\nAND\n    (status = \'4\' OR status = \'7\')\nGROUP BY\n    DAYNAME(bills.created_at), HOUR(bills.created_at), stock_location.name\nORDER BY\n   stock_location.name, DATE_FORMAT(bills.created_at, \'%%w\'), HOUR(bills.created_at)', '', NULL, NULL);
		";
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