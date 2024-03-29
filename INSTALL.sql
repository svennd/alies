-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 18, 2023 at 08:59 AM
-- Server version: 10.11.2-MariaDB-1
-- PHP Version: 8.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alies`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `description` text NOT NULL,
  `value` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `vet` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `cash` decimal(10,2) DEFAULT NULL,
  `card` decimal(10,2) DEFAULT NULL,
  `transfer` decimal(10,2) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `msg` text NOT NULL,
  `mail` tinyint(1) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_codes`
--

CREATE TABLE `booking_codes` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `btw` tinyint(4) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `type` tinyint(4) DEFAULT -1,
  `freq` int(10) UNSIGNED NOT NULL,
  `male_min_weight` decimal(5,2) NOT NULL,
  `male_max_weight` decimal(5,2) NOT NULL,
  `female_min_weight` decimal(5,2) NOT NULL,
  `female_max_weight` decimal(5,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_nr` int(11) NOT NULL,
  `my_ref` varchar(255) NOT NULL,
  `wholesale_artnr` varchar(255) NOT NULL,
  `wholesale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `delivery_date` date NOT NULL,
  `delivery_nr` int(11) NOT NULL,
  `bruto_price` decimal(5,2) NOT NULL,
  `netto_price` decimal(5,2) NOT NULL,
  `amount` int(11) NOT NULL,
  `lotnr` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_slip`
--

CREATE TABLE `delivery_slip` (
  `id` int(11) NOT NULL,
  `vet` int(11) NOT NULL,
  `note` text NOT NULL,
  `regdate` date NOT NULL,
  `location` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `anamnese` text NOT NULL,
  `pet` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `payment` int(11) NOT NULL,
  `location` tinyint(4) NOT NULL,
  `vet` tinyint(4) NOT NULL,
  `vet_support_1` tinyint(4) NOT NULL,
  `vet_support_2` tinyint(4) NOT NULL,
  `report` tinyint(1) NOT NULL DEFAULT 0,
  `no_history` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_procedures`
--

CREATE TABLE `events_procedures` (
  `id` int(11) NOT NULL,
  `procedures_id` smallint(6) NOT NULL,
  `event_id` int(11) NOT NULL,
  `amount` tinyint(4) NOT NULL,
  `net_price` decimal(10,2) NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `btw` tinyint(4) NOT NULL,
  `booking` tinyint(11) NOT NULL,
  `calc_net_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_products`
--

CREATE TABLE `events_products` (
  `id` int(11) NOT NULL,
  `product_id` mediumint(9) NOT NULL,
  `event_id` int(11) NOT NULL,
  `volume` decimal(10,2) NOT NULL,
  `net_price` decimal(10,2) NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `btw` tinyint(4) NOT NULL,
  `booking` tinyint(4) NOT NULL,
  `calc_net_price` decimal(10,2) NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_upload`
--

CREATE TABLE `events_upload` (
  `id` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `user` tinyint(4) NOT NULL,
  `location` tinyint(4) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'General User'),
(3, 'vet', 'Veterinarian');

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

CREATE TABLE `lab` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `lab_date` date DEFAULT NULL,
  `lab_patient_id` int(11) DEFAULT NULL,
  `pet` int(11) DEFAULT NULL,
  `lab_updated_at` datetime DEFAULT NULL,
  `lab_created_at` datetime DEFAULT NULL,
  `lab_comment` text NOT NULL,
  `source` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_detail`
--

CREATE TABLE `lab_detail` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `value` decimal(5,2) NOT NULL,
  `string_value` varchar(255) NOT NULL,
  `upper_limit` decimal(5,2) NOT NULL,
  `lower_limit` decimal(5,2) NOT NULL,
  `report` tinyint(1) NOT NULL,
  `lab_code` int(11) NOT NULL,
  `lab_code_text` varchar(255) NOT NULL,
  `lab_updated_at` datetime DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` tinyint(3) UNSIGNED NOT NULL,
  `event` varchar(255) NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL,
  `msg` text NOT NULL,
  `location` tinyint(4) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_stock`
--

CREATE TABLE `log_stock` (
  `id` int(11) NOT NULL,
  `user_id` tinyint(4) NOT NULL,
  `product` int(11) NOT NULL,
  `event` varchar(255) NOT NULL,
  `volume` decimal(10,2) NOT NULL,
  `location` tinyint(4) NOT NULL,
  `level` tinyint(3) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`version`) VALUES
(17);

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `phone3` varchar(50) NOT NULL,
  `phone2` varchar(50) NOT NULL,
  `street` varchar(255) NOT NULL,
  `nr` varchar(10) NOT NULL,
  `city` varchar(255) NOT NULL,
  `main_city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `zip` varchar(6) NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `msg` text NOT NULL,
  `btw_nr` varchar(255) DEFAULT NULL,
  `invoice_addr` text DEFAULT NULL,
  `invoice_contact` varchar(255) DEFAULT NULL,
  `invoice_tel` varchar(255) DEFAULT NULL,
  `debts` tinyint(1) DEFAULT NULL,
  `low_budget` tinyint(1) NOT NULL,
  `language` tinyint(1) NOT NULL,
  `contact` tinyint(1) NOT NULL,
  `last_bill` date DEFAULT NULL,
  `initial_vet` int(11) NOT NULL,
  `initial_loc` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `birth` date DEFAULT NULL,
  `death` tinyint(1) NOT NULL,
  `death_date` date DEFAULT NULL,
  `breed` int(11) NOT NULL DEFAULT 0,
  `gender` tinyint(1) NOT NULL,
  `color` varchar(255) NOT NULL,
  `last_weight` decimal(6,2) NOT NULL,
  `lost` tinyint(1) NOT NULL,
  `chip` varchar(30) NOT NULL,
  `nr_vac_book` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `nutritional_advice` text NOT NULL,
  `owner` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `init_vet` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets_weight`
--

CREATE TABLE `pets_weight` (
  `id` int(11) NOT NULL,
  `pets` int(11) NOT NULL,
  `weight` decimal(6,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procedures`
--

CREATE TABLE `procedures` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `booking_code` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `short_name` varchar(255) NOT NULL,
  `producer` varchar(255) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `posologie` text NOT NULL,
  `toedieningsweg` varchar(50) NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `dead_volume` decimal(4,2) DEFAULT NULL,
  `buy_volume` smallint(6) NOT NULL,
  `sell_volume` decimal(10,2) NOT NULL,
  `buy_price` decimal(10,2) NOT NULL,
  `buy_price_date` date DEFAULT NULL,
  `unit_buy` varchar(10) NOT NULL,
  `unit_sell` varchar(10) NOT NULL,
  `input_barcode` varchar(20) DEFAULT NULL,
  `btw_buy` tinyint(4) NOT NULL,
  `btw_sell` tinyint(4) NOT NULL,
  `booking_code` tinyint(4) NOT NULL,
  `delay` int(11) NOT NULL,
  `comment` text NOT NULL,
  `sellable` tinyint(1) NOT NULL,
  `limit_stock` smallint(5) UNSIGNED NOT NULL,
  `vaccin` tinyint(1) NOT NULL DEFAULT 0,
  `vaccin_freq` smallint(5) UNSIGNED NOT NULL,
  `vhbcode` varchar(255) NOT NULL,
  `wholesale` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_price`
--

CREATE TABLE `products_price` (
  `id` int(11) NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `volume` decimal(5,2) NOT NULL,
  `price` decimal(5,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_type`
--

CREATE TABLE `products_type` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `register_in`
--

CREATE TABLE `register_in` (
  `id` int(11) NOT NULL,
  `product` int(11) NOT NULL,
  `volume` decimal(10,2) NOT NULL,
  `eol` date NOT NULL,
  `in_price` decimal(10,2) NOT NULL,
  `lotnr` varchar(255) NOT NULL,
  `delivery_slip` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sticky`
--

CREATE TABLE `sticky` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `note` text NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `eol` date DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `in_price` decimal(10,2) NOT NULL,
  `lotnr` varchar(255) NOT NULL,
  `volume` decimal(10,2) NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `verify` datetime DEFAULT NULL,
  `state` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_input`
--

CREATE TABLE `stock_input` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `msg` text NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_limit`
--

CREATE TABLE `stock_limit` (
  `id` int(11) NOT NULL,
  `stock` tinyint(3) UNSIGNED NOT NULL,
  `product_id` mediumint(8) UNSIGNED NOT NULL,
  `volume` smallint(5) UNSIGNED NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_location`
--

CREATE TABLE `stock_location` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tooth`
--

CREATE TABLE `tooth` (
  `id` int(11) NOT NULL,
  `pet` int(11) NOT NULL,
  `vet` smallint(5) UNSIGNED NOT NULL,
  `tooth` tinyint(3) UNSIGNED NOT NULL,
  `tooth_status` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tooth_msg`
--

CREATE TABLE `tooth_msg` (
  `id` int(11) NOT NULL,
  `pet` int(11) NOT NULL,
  `vet` tinyint(3) UNSIGNED NOT NULL,
  `location` tinyint(3) UNSIGNED NOT NULL,
  `msg` text NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `forgotten_password_time` int(10) UNSIGNED DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(10) UNSIGNED NOT NULL,
  `last_login` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(3) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_date` varchar(9) NOT NULL DEFAULT 'd-m-Y',
  `search_config` tinyint(1) NOT NULL DEFAULT 0,
  `current_location` tinyint(3) UNSIGNED NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `sidebar` varchar(15) NOT NULL,
  `vsens` tinyint(1) NOT NULL DEFAULT 0,
  `activation_selector` varchar(255) DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE `users_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `vaccine_pet`
--

CREATE TABLE `vaccine_pet` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_line` int(11) NOT NULL,
  `pet` int(11) NOT NULL,
  `redo` date NOT NULL,
  `location` tinyint(3) UNSIGNED NOT NULL,
  `vet` tinyint(3) UNSIGNED NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale`
--

CREATE TABLE `wholesale` (
  `id` int(11) NOT NULL,
  `vendor_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci NOT NULL,
  `bruto` decimal(7,2) NOT NULL,
  `btw` smallint(6) NOT NULL,
  `sell_price` decimal(7,2) NOT NULL,
  `distributor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `CNK` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci NOT NULL,
  `VHB` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_520_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_price`
--

CREATE TABLE `wholesale_price` (
  `id` int(11) NOT NULL,
  `art_nr` varchar(25) NOT NULL,
  `bruto` decimal(7,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zipcodes`
--

CREATE TABLE `zipcodes` (
  `id` int(11) NOT NULL,
  `zip` varchar(16) NOT NULL,
  `city` varchar(255) NOT NULL,
  `main_city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `zipcodes`
--

INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `updated_at`, `created_at`) VALUES
(1, '2970', '\'S Gravenwezel', 'SCHILDE', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(2, '3700', '\'S Herenelderen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(3, '9420', 'Aaigem', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(4, '8511', 'Aalbeke', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(5, '3800', 'Aalst', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(6, '8700', 'Aarsele', 'TIELT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(7, '8211', 'Aartrijke', 'ZEDELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(8, '4557', 'Abe', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(9, '4280', 'Abolens', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(10, '3930', 'Achel', 'HAMONT-ACHEL', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(11, '5590', 'Ach?ne', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(12, '5362', 'Achet', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(13, '4219', 'Acosse', 'WASSEIGES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(14, '6280', 'Acoz', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(15, '9991', 'Adegem', 'MALDEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(16, '8660', 'Adinkerke', 'DE PANNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(17, '9051', 'Afsnee', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(18, '5544', 'Agimont', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(19, '4317', 'Aineffe', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(20, '5310', 'Aische-En-Refail', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(21, '6250', 'Aiseau', 'AISEAU-PRESLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(22, '5070', 'Aisemont', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(23, '5550', 'Alle', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(24, '4432', 'Alleur', 'ANS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(25, '1652', 'Alsemberg', 'BEERSEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(26, '6680', 'Amberloup', 'SAINTE-ODE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(27, '6953', 'Ambly', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(28, '4219', 'Ambresin', 'WASSEIGES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(29, '6997', 'Amonines', 'EREZ?E', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(30, '7750', 'Amougies', 'MONT-DE-L\'ENCLUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(31, '4540', 'Ampsin', 'AMAY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(32, '4821', 'Andrimont', 'DISON', 'LUIK', NULL, '2020-07-22 15:14:24'),
(33, '4031', 'Angleur', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(34, '7387', 'Angre', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(35, '7387', 'Angreau', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(36, '6721', 'Anlier', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(37, '6890', 'Anloy', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(38, '5537', 'Annevoie-Rouillon', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(39, '5500', 'Anseremme', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(40, '7750', 'Anseroeul', 'MONT-DE-L\'ENCLUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(41, '5520', 'Anth?e', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(42, '4520', 'Antheit', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(43, '7910', 'Anvaing', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(44, '9200', 'Appels', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(45, '9400', 'Appelterre-Eichem', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(46, '5170', 'Arbre', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(47, '7811', 'Arbre', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(48, '4990', 'Arbrefontaine', 'LIERNEUX', 'LUIK', NULL, '2020-07-22 15:14:24'),
(49, '7910', 'Arc-Aini?res', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(50, '7910', 'Arc-Wattripont', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(51, '1390', 'Archennes', 'GREZ-DOICEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(52, '4601', 'Argenteau', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:24'),
(53, '7181', 'Arquennes', 'SENEFFE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(54, '5060', 'Arsimont', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(55, '6870', 'Arville', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(56, '9404', 'Aspelare', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(57, '9890', 'Asper', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(58, '7040', 'Asquillies', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(59, '8310', 'Assebroek', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(60, '6860', 'Assenois', 'L?GLISE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(61, '3460', 'Assent', 'BEKKEVOORT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(62, '9800', 'Astene', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(63, '7387', 'Athis', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(64, '6791', 'Athus', 'AUBANGE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(65, '3404', 'Attenhoven', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(66, '3384', 'Attenrode', 'GLABBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(67, '7941', 'Attre', 'BRUGELETTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(68, '7972', 'Aubechies', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(69, '5660', 'Aublain', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(70, '6880', 'Auby-Sur-Semois', 'BERTRIX', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(71, '7382', 'Audregnies', 'QUI?VRAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(72, '7040', 'Aulnois', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(73, '6706', 'Autelbas', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(74, '1367', 'Autre-Eglise', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(75, '7387', 'Autreppe', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(76, '5060', 'Auvelais', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(77, '5580', 'Ave-Et-Auffe', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(78, '8630', 'Avekapelle', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(79, '4260', 'Avennes', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(80, '3271', 'Averbode', 'SCHERPENHEUVEL-ZICHEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(81, '4280', 'Avernas-Le-Bauduin', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(82, '4280', 'Avin', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(83, '6870', 'Awenne', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(84, '4400', 'Awirs', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(85, '6900', 'Aye', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(86, '4630', 'Ayeneux', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(87, '9890', 'Baaigem', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(88, '3128', 'Baal', 'TREMELO', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(89, '9310', 'Baardegem', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(90, '9200', 'Baasrode', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(91, '9800', 'Bachte-Maria-Leerne', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(92, '5550', 'Bagimont', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(93, '6464', 'Baileux', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(94, '6460', 'Baili?vre', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(95, '5555', 'Baillamont', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(96, '7730', 'Bailleul', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(97, '5377', 'Baillonville', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(98, '7380', 'Baisieux', 'QUI?VRAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(99, '1470', 'Baisy-Thy', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(100, '5190', 'Bal?tre', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(101, '9860', 'Balegem', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(102, '9420', 'Bambrugge', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(103, '6951', 'Bande', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(104, '6500', 'Barben?on', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(105, '4671', 'Barchon', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(106, '5570', 'Baronville', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(107, '7534', 'Barry', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(108, '5370', 'Barvaux-Condroz', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(109, '6940', 'Barvaux-Sur-Ourthe', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(110, '4520', 'Bas-Oha', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(111, '7971', 'Bas?cles', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(112, '4983', 'Basse-Bodeux', 'TROIS-PONTS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(113, '9968', 'Bassevelde', 'ASSENEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(114, '7830', 'Bassilly', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(115, '3870', 'Batsheers', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(116, '4651', 'Battice', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(117, '7130', 'Battignies', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(118, '7331', 'Baudour', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(119, '7870', 'Bauffe', 'LENS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(120, '7604', 'Baugnies', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(121, '1401', 'Baulers', 'NIVELLES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(122, '9520', 'Bavegem', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(123, '8531', 'Bavikhove', 'HARELBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(124, '9150', 'Bazel', 'KRUIBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(125, '4052', 'Beaufays', 'CHAUDFONTAINE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(126, '6980', 'Beausaint', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(127, '6594', 'Beauwelz', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(128, '7532', 'Beclers', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(129, '3960', 'Beek', 'BREE', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(130, '9630', 'Beerlegem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(131, '8600', 'Beerst', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(132, '1673', 'Beert', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(133, '9080', 'Beervelde', 'LOCHRISTI', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(134, '2580', 'Beerzel', 'PUTTE', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(135, '5000', 'Beez', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(136, '6987', 'Beffe', 'RENDEUX', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(137, '6672', 'Beho', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(138, '1852', 'Beigem', 'GRIMBERGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(139, '8480', 'Bekegem', 'ICHTEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(140, '1730', 'Bekkerzeel', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(141, '5001', 'Belgrade', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(142, '4610', 'Bellaire', 'BEYNE-HEUSAY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(143, '7170', 'Bellecourt', 'MANAGE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(144, '5555', 'Bellefontaine', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(145, '6730', 'Bellefontaine', 'TINTIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(146, '8510', 'Bellegem', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(147, '9881', 'Bellem', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(148, '6834', 'Bellevaux', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(149, '4960', 'Bellevaux-Ligneuville', 'MALMEDY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(150, '1674', 'Bellingen', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(151, '9111', 'Belsele', 'SINT-NIKLAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(152, '4500', 'Ben-Ahin', 'HUY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(153, '6941', 'Bende', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(154, '3540', 'Berbroek', 'HERK-DE-STAD', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(155, '2600', 'Berchem', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(156, '9690', 'Berchem', 'KLUISBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(157, '2040', 'Berendrecht', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(158, '1910', 'Berg', 'KAMPENHOUT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(159, '3700', 'Berg', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(160, '4360', 'Bergilers', 'OREYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(161, '3830', 'Berlingen', 'WELLEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(162, '4607', 'Berneau', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:24'),
(163, '6560', 'Bersillies-L\'Abbaye', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(164, '4280', 'Bertre', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(165, '5651', 'Berze', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(166, '8980', 'Beselare', 'ZONNEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(167, '3130', 'Betekom', 'BEGIJNENDIJK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(168, '4300', 'Bettincourt', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:24'),
(169, '5030', 'Beuzet', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(170, '2560', 'Bevel', 'NIJLEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(171, '4960', 'Beverc?', 'MALMEDY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(172, '9700', 'Bevere', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(173, '8791', 'Beveren', 'WAREGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(174, '8800', 'Beveren', 'ROESELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(175, '8691', 'Beveren-Aan-De-Ijzer', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(176, '3581', 'Beverlo', 'BERINGEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(177, '3740', 'Beverst', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(178, '6543', 'Bienne-Lez-Happart', 'LOBBES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(179, '6533', 'Bierce', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(180, '1301', 'Bierges', 'WAVRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(181, '1430', 'Bierghes', 'REBECQ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(182, '4460', 'Bierset', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(183, '5380', 'Bierwart', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(184, '5640', 'Biesme', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(185, '6531', 'Biesme-Sous-Thuin', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(186, '5640', 'Biesmere', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(187, '1390', 'Biez', 'GREZ-DOICEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(188, '6690', 'Bihain', 'VIELSALM', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(189, '8920', 'Bikschote', 'LANGEMARK-POELKAPELLE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(190, '4831', 'Bilstain', 'LIMBOURG', 'LUIK', NULL, '2020-07-22 15:14:24'),
(191, '3850', 'Binderveld', 'NIEUWERKERKEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(192, '3211', 'Binkom', 'LUBBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(193, '5537', 'Bioul', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(194, '8501', 'Bissegem', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(195, '7783', 'Bizet', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(196, '2830', 'Blaasveld', 'WILLEBROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(197, '5542', 'Blaimont', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(198, '7522', 'Blandain', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(199, '3052', 'Blanden', 'OUD-HEVERLEE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(200, '7040', 'Blaregnies', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(201, '7321', 'Blaton', 'BERNISSART', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(202, '7370', 'Blaugies', 'DOUR', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(203, '7620', 'Bl?haries', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(204, '4280', 'Blehen', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(205, '6760', 'Bleid', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(206, '4300', 'Bleret', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:24'),
(207, '7903', 'Blicquy', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(208, '4537', 'Bodegn?e', 'VERLAINE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(209, '3890', 'Boekhout', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(210, '9961', 'Boekhoute', 'ASSENEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(211, '4250', 'Bo?lhe', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:24'),
(212, '8904', 'Boezinge', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(213, '1670', 'Bogaarden', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(214, '5550', 'Bohan', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(215, '5140', 'Boigne', 'SOMBREFFE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(216, '4690', 'Boirs', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(217, '7170', 'Bois-D\'Haine', 'MANAGE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(218, '7866', 'Bois-De-Lessines', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(219, '5170', 'Bois-De-Villers', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(220, '4560', 'Bois-Et-Borsu', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:24'),
(221, '5310', 'Bolinne', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(222, '4653', 'Bolland', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(223, '1367', 'Bomal', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(224, '6941', 'Bomal-Sur-Ourthe', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(225, '4607', 'Bombaye', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:24'),
(226, '3840', 'Bommershoven', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(227, '7603', 'Bon-Secours', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(228, '4100', 'Boncelles', 'SERAING', 'LUIK', NULL, '2020-07-22 15:14:24'),
(229, '5310', 'Boneffe', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(230, '5021', 'Boninne', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(231, '1325', 'Bonlez', 'CHAUMONT-GISTOUX', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(232, '6700', 'Bonnert', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(233, '5300', 'Bonneville', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(234, '5377', 'Bonsin', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(235, '2221', 'Booischot', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(236, '8630', 'Booitshoeke', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(237, '3631', 'Boorsem', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(238, '1761', 'Borchtlombeek', 'ROOSDAAL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(239, '2140', 'Borgerhout', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(240, '4317', 'Borlez', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(241, '3891', 'Borlo', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(242, '6941', 'Borlon', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(243, '1404', 'Bornival', 'NIVELLES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(244, '9552', 'Borsbeke', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(245, '5032', 'Bossire', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(246, '8583', 'Bossuit', 'AVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(247, '1390', 'Bossut-Gottechain', 'GREZ-DOICEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(248, '3300', 'Bost', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(249, '5032', 'Bothey', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(250, '9820', 'Bottelare', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(251, '6200', 'Bouffioulx', 'CH?TELET', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(252, '5004', 'Bouge', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(253, '7040', 'Bougnies', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(254, '6464', 'Bourlers', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(255, '5575', 'Bourseigne-Neuve', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(256, '5575', 'Bourseigne-Vieille', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(257, '7110', 'Boussoit', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(258, '5660', 'Boussu-En-Fagne', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(259, '6440', 'Boussu-Lez-Walcourt', 'FROIDCHAPELLE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(260, '1470', 'Bousval', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(261, '5500', 'Bouvignes-Sur-Meuse', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(262, '7803', 'Bouvignies', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(263, '2288', 'Bouwel', 'GROBBENDONK', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(264, '8680', 'Bovekerke', 'KOEKELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(265, '3870', 'Bovelingen', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(266, '4300', 'Bovenistier', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:24'),
(267, '5081', 'Bovesse', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(268, '6671', 'Bovigny', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(269, '4990', 'Bra', 'LIERNEUX', 'LUIK', NULL, '2020-07-22 15:14:24'),
(270, '7604', 'Braffe', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(271, '5590', 'Braibant', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(272, '5310', 'Branchon', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(273, '6800', 'Bras', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(274, '7604', 'Brasm?nil', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(275, '7130', 'Bray', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(276, '2870', 'Breendonk', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(277, '4020', 'Bressoux', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(278, '8900', 'Brielen', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(279, '2520', 'Broechem', 'RANST', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(280, '3840', 'Broekom', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(281, '5660', 'Brely', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(282, '5660', 'Brely-De-Pesche', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(283, '1785', 'Brussegem', 'MERCHTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(284, '3800', 'Brustem', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(285, '7641', 'Bruyelle', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(286, '6222', 'Brye', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(287, '3440', 'Budingen', 'ZOUTLEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(288, '7911', 'Buissenal', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(289, '5580', 'Buissonville', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(290, '1501', 'Buizingen', 'HALLE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(291, '1910', 'Buken', 'KAMPENHOUT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(292, '8630', 'Bulskamp', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(293, '3380', 'Bunsbeek', 'GLABBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(294, '2070', 'Burcht', 'ZWIJNDRECHT', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(295, '6927', 'Bure', 'TELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(296, '9420', 'Burst', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(297, '7602', 'Bury', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(298, '3891', 'Buvingen', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(299, '7133', 'Buvrinnes', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(300, '6743', 'Buzenol', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(301, '6230', 'Buzet', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(302, '7604', 'Callenelle', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(303, '7642', 'Calonne', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(304, '7940', 'Cambron-Casteau', 'BRUGELETTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(305, '7870', 'Cambron-Saint-Vincent', 'LENS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(306, '6850', 'Carlsbourg', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(307, '7141', 'Carni?res', 'MORLANWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(308, '7061', 'Casteau', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(309, '5650', 'Castillon', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(310, '4317', 'Celles', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(311, '5561', 'Celles', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(312, '4632', 'Cerexhe-Heuseux', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(313, '1341', 'Ceroux-Mousty', 'OTTIGNIES-LOUVAIN-LA-NEUVE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(314, '4650', 'Chaineux', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(315, '5550', 'Chairiere', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(316, '5020', 'Champion', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(317, '6971', 'Champlon', 'TENNEVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(318, '6921', 'Chanly', 'WELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(319, '6742', 'Chantemelle', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(320, '7903', 'Chapelle-e-Oie', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(321, '7903', 'Chapelle-e-Wattines', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(322, '4537', 'Chapon-Seraing', 'VERLAINE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(323, '4654', 'Charneux', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(324, '6824', 'Chassepierre', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(325, '1450', 'Chastre-Villeroux-Blanmont', 'CHASTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(326, '5650', 'Chastr?s', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(327, '6200', 'Ch?telineau', 'CH?TELET', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(328, '6747', 'Ch?tillon', 'SAINT-L?GER', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(329, '7063', 'Chauss?e-Notre-Dame-Louvignies', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(330, '4032', 'Ch?n?e', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(331, '6673', 'Cherain', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(332, '4602', 'Cheratte', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:24'),
(333, '7521', 'Chercq', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(334, '5590', 'Chevetogne', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(335, '4987', 'Chevron', 'STOUMONT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(336, '4400', 'Chokier', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(337, '5560', 'Ciergnon', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(338, '4260', 'Ciplet', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(339, '7024', 'Ciply', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(340, '1480', 'Clabecq', 'TUBIZE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(341, '4890', 'Clermont', 'THIMISTER-CLERMONT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(342, '5650', 'Clermont', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(343, '4480', 'Clermont-Sous-Huy', 'ENGIS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(344, '5022', 'Cognel?e', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(345, '4180', 'Comblain-Fairon', 'HAMOIR', 'LUIK', NULL, '2020-07-22 15:14:24'),
(346, '4180', 'Comblain-La-Tour', 'HAMOIR', 'LUIK', NULL, '2020-07-22 15:14:24'),
(347, '5590', 'Conneux', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(348, '1435', 'Corbais', 'MONT-SAINT-GUIBERT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(349, '6838', 'Corbion', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(350, '7910', 'Cordes', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(351, '5620', 'Corenne', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(352, '4860', 'Cornesse', 'PEPINSTER', 'LUIK', NULL, '2020-07-22 15:14:24'),
(353, '5555', 'Cornimont', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(354, '5032', 'Corroy-Le-Ch?teau', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(355, '1325', 'Corroy-Le-Grand', 'CHAUMONT-GISTOUX', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(356, '4257', 'Corswarem', 'BERLOZ', 'LUIK', NULL, '2020-07-22 15:14:24'),
(357, '1450', 'Cortil-Noirmont', 'CHASTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(358, '5380', 'Cortil-Wodon', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(359, '6010', 'Couillet', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(360, '6120', 'Cour-Sur-Heure', 'HAM-SUR-HEURE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(361, '5336', 'Courri?re', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(362, '4218', 'Couthuin', 'H?RON', 'LUIK', NULL, '2020-07-22 15:14:24'),
(363, '5300', 'Coutisse', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(364, '1380', 'Couture-Saint-Germain', 'LASNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(365, '4280', 'Cras-Avernas', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(366, '4280', 'Crehen', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(367, '7120', 'Croix-Lez-Rouveroy', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(368, '4784', 'Crombach', 'SANKT-VITH', 'LUIK', NULL, '2020-07-22 15:14:24'),
(369, '5332', 'Crupet', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(370, '7033', 'Cuesmes', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(371, '6880', 'Cugnon', 'BERTRIX', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(372, '5660', 'Cul-Des-Sarts', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(373, '5562', 'Custinne', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(374, '8890', 'Dadizele', 'MOORSLEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(375, '5660', 'Dailly', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(376, '9160', 'Daknam', 'LOKEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(377, '6767', 'Dampicourt', 'ROUVROY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(378, '6020', 'Dampremy', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(379, '4253', 'Darion', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:24'),
(380, '5630', 'Daussois', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(381, '5020', 'Daussoulx', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(382, '5100', 'Dave', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(383, '9170', 'De Klinge', 'SINT-GILLIS-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(384, '8630', 'De Moeren', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(385, '9570', 'Deftinge', 'LIERDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(386, '9280', 'Denderbelle', 'LEBBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(387, '9450', 'Denderhoutem', 'HAALTERT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(388, '9400', 'Denderwindeke', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(389, '5537', 'Den?e', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(390, '7912', 'Dergneau', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(391, '8792', 'Desselgem', 'WAREGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(392, '9042', 'Desteldonk', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(393, '9831', 'Deurle', 'SINT-MARTENS-LATEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(394, '2100', 'Deurne', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(395, '3290', 'Deurne', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(396, '7864', 'Deux-Acren', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(397, '5310', 'Dhuy', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(398, '1831', 'Diegem', 'MACHELEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(399, '3700', 'Diets-Heur', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(400, '8900', 'Dikkebus', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(401, '9630', 'Dikkele', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(402, '9890', 'Dikkelvenne', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(403, '3650', 'Dilsen', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(404, '5570', 'Dion', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(405, '1325', 'Dion-Valmont', 'CHAUMONT-GISTOUX', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(406, '6960', 'Dochamps', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(407, '9130', 'Doel', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(408, '6836', 'Dohan', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(409, '4140', 'Dolembreux', 'SPRIMONT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(410, '1370', 'Dongelberg', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(411, '3540', 'Donk', 'HERK-DE-STAD', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(412, '6536', 'Donstiennes', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(413, '5530', 'Dorinne', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(414, '3440', 'Dormaal', 'ZOUTLEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(415, '7711', 'Dottenijs', 'MOESKROEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(416, '5670', 'Dourbes', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(417, '8951', 'Dranouter', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(418, '5500', 'Dr?hance', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(419, '8600', 'Driekapellen', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(420, '3350', 'Drieslinter', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(421, '9031', 'Drongen', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(422, '8380', 'Dudzele', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(423, '3080', 'Duisburg', 'TERVUREN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(424, '3803', 'Duras', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(425, '5530', 'Durnal', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(426, '1653', 'Dworp', 'BEERSEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(427, '4690', 'Eben-Emael', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(428, '6860', 'Ebly', 'L?GLISE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(429, '7190', 'Ecaussinnes-D\'Enghien', 'ECAUSSINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(430, '7191', 'Ecaussinnes-Lalaing', 'ECAUSSINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(431, '9700', 'Edelare', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(432, '8480', 'Eernegem', 'ICHTEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(433, '8740', 'Egem', 'PITTEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(434, '8630', 'Eggewaartskapelle', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(435, '4120', 'Ehein', 'NEUPR?', 'LUIK', NULL, '2020-07-22 15:14:24'),
(436, '4480', 'Ehein', 'ENGIS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(437, '3740', 'Eigenbilzen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(438, '2430', 'Eindhout', 'LAAKDAL', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(439, '9700', 'Eine', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(440, '3630', 'Eisden', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(441, '9810', 'Eke', 'NAZARETH', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(442, '2180', 'Ekeren', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(443, '9160', 'Eksaarde', 'LOKEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(444, '3941', 'Eksel', 'HECHTEL-EKSEL', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(445, '3650', 'Elen', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(446, '9620', 'Elene', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(447, '1982', 'Elewijt', 'ZEMST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(448, '3400', 'Eliksem', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(449, '1671', 'Elingen', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(450, '4590', 'Ellemelle', 'OUFFET', 'LUIK', NULL, '2020-07-22 15:14:24'),
(451, '7910', 'Ellignies-Lez-Frasnes', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(452, '7972', 'Ellignies-Sainte-Anne', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(453, '3670', 'Ellikom', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(454, '7370', 'Elouges', 'DOUR', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(455, '9790', 'Elsegem', 'WORTEGEM-PETEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(456, '4750', 'Elsenborn', 'BUTGENBACH', 'LUIK', NULL, '2020-07-22 15:14:24'),
(457, '9660', 'Elst', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(458, '8906', 'Elverdinge', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(459, '9140', 'Elversele', 'TEMSE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(460, '2520', 'Emblem', 'RANST', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(461, '4053', 'Embourg', 'CHAUDFONTAINE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(462, '8870', 'Emelgem', 'IZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(463, '5080', 'Emines', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(464, '5363', 'Emptinne', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(465, '9700', 'Ename', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(466, '3800', 'Engelmanshoven', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(467, '1350', 'Enines', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(468, '4800', 'Ensival', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(469, '7134', 'Epinois', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(470, '1980', 'Eppegem', 'ZEMST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(471, '5580', 'Eprave', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(472, '7050', 'Erbaut', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(473, '7050', 'Erbisoeul', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(474, '7500', 'Ere', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(475, '9320', 'Erembodegem', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(476, '5644', 'Ermeton-Sur-Biert', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(477, '5030', 'Ernage', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(478, '6972', 'Erneuville', 'TENNEVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(479, '4920', 'Ernonheid', 'AYWAILLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(480, '9420', 'Erondegem', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(481, '9420', 'Erpe', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(482, '5101', 'Erpent', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(483, '6441', 'Erpion', 'FROIDCHAPELLE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(484, '3071', 'Erps-Kwerps', 'KORTENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(485, '7387', 'Erquennes', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(486, '9940', 'Ertvelde', 'EVERGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(487, '9620', 'Erwetegem', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(488, '7760', 'Escanaffles', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(489, '8600', 'Esen', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(490, '7502', 'Esplechin', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(491, '7743', 'Esquelmes', 'PECQ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(492, '1790', 'Essene', 'AFFLIGEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(493, '7730', 'Estaimbourg', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(494, '7120', 'Estinnes-Au-Mont', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(495, '7120', 'Estinnes-Au-Val', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(496, '6760', 'Ethe', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(497, '9680', 'Etikhove', 'MAARKEDAL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(498, '8460', 'Ettelgem', 'OUDENBURG', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(499, '7080', 'Eugies', 'FRAMERIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(500, '4631', 'Evegn?e', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(501, '5350', 'Evelette', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(502, '9660', 'Everbeek', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(503, '3078', 'Everberg', 'KORTENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(504, '7730', 'Evregnies', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(505, '5530', 'Evrehailles', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(506, '4731', 'Eynatten', 'RAEREN', 'LUIK', NULL, '2020-07-22 15:14:24'),
(507, '3400', 'Ezemaal', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(508, '5600', 'Fagnolle', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(509, '5522', 'Fala?n', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(510, '5060', 'Falisolle', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(511, '4260', 'Fallais', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(512, '5500', 'Falmagne', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(513, '5500', 'Falmignoul', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(514, '7181', 'Familleureux', 'SENEFFE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(515, '5340', 'Faulx-Les-Tombes', 'GESVES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(516, '7120', 'Fauroeulx', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(517, '4950', 'Faymonville', 'WEISMES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(518, '6856', 'Fays-Les-Veneurs', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(519, '7387', 'Fayt-Le-Franc', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(520, '7170', 'Fayt-Lez-Manage', 'MANAGE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(521, '5570', 'Felenne', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(522, '7181', 'Feluy', 'SENEFFE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(523, '4607', 'Feneur', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:24'),
(524, '5570', 'Feschaux', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(525, '4458', 'Fexhe-Slins', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(526, '4181', 'Filot', 'HAMOIR', 'LUIK', NULL, '2020-07-22 15:14:24'),
(527, '5560', 'Finnevaux', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(528, '4530', 'Fize-Fontaine', 'VILLERS-LE-BOUILLET', 'LUIK', NULL, '2020-07-22 15:14:24'),
(529, '4367', 'Fize-Le-Marsal', 'CRISN?E', 'LUIK', NULL, '2020-07-22 15:14:24'),
(530, '6686', 'Flamierge', 'BERTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(531, '5620', 'Flavion', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(532, '5020', 'Flawinne', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(533, '4400', 'Fl?malle-Grande', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(534, '4400', 'Fl?malle-Haute', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(535, '7012', 'Fl?nu', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(536, '4540', 'Fl?ne', 'AMAY', 'LUIK', NULL, '2020-07-22 15:14:24'),
(537, '5334', 'Flor?e', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(538, '5150', 'Floriffoux', 'FLOREFFE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(539, '5370', 'Flostoy', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(540, '5572', 'Focant', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(541, '1350', 'Folx-Les-Caves', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(542, '6567', 'Fontaine-Valmont', 'MERBES-LE-CH?TEAU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(543, '5650', 'Fontenelle', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(544, '6820', 'Fontenoille', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(545, '7643', 'Fontenoy', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(546, '4340', 'Fooz', 'AWANS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(547, '6141', 'Forchies-La-Marche', 'FONTAINE-L\'EV?QUE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(548, '7910', 'Forest', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(549, '4870', 'For?t', 'TROOZ', 'LUIK', NULL, '2020-07-22 15:14:24'),
(550, '6596', 'Forge-Philippe', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(551, '6464', 'Forges', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(552, '6953', 'Forri?res', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(553, '5380', 'Forville', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(554, '4980', 'Fosse', 'TROIS-PONTS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(555, '7830', 'Fouleng', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(556, '6440', 'Fourbechies', 'FROIDCHAPELLE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(557, '5504', 'Foy-Notre-Dame', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(558, '4870', 'Fraipont', 'TROOZ', 'LUIK', NULL, '2020-07-22 15:14:24'),
(559, '5650', 'Fraire', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(560, '4557', 'Fraiture', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(561, '6853', 'Framont', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(562, '5380', 'Franc-Waret', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(563, '5600', 'Franchimont', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(564, '4970', 'Francorchamps', 'STAVELOT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(565, '5150', 'Frani?re', 'FLOREFFE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(566, '5660', 'Frasnes', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(567, '7911', 'Frasnes-Lez-Buissenal', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(568, '6210', 'Frasnes-Lez-Gosselies', 'LES BONS VILLERS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(569, '4347', 'Freloux', 'FEXHE-LE-HAUT-CLOCHER', 'LUIK', NULL, '2020-07-22 15:14:24'),
(570, '6800', 'Freux', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(571, '5576', 'Froidfontaine', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(572, '7504', 'Froidmont', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(573, '6990', 'Fronville', 'HOTTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(574, '7503', 'Froyennes', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(575, '4260', 'Fumal', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(576, '5500', 'Furfooz', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(577, '5641', 'Furnaux', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(578, '1750', 'Gaasbeek', 'LENNIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(579, '7943', 'Gages', 'BRUGELETTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(580, '7906', 'Gallaix', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(581, '7530', 'Gaurain-Ramecroix', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(582, '1367', 'Geest-G?rompont-Petit-Rosi?re', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(583, '5024', 'Gelbress?e', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(584, '3800', 'Gelinden', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(585, '3620', 'Gellik', 'LANAKEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(586, '3200', 'Gelrode', 'AARSCHOT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(587, '8980', 'Geluveld', 'ZONNEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(588, '8940', 'Geluwe', 'WERVIK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(589, '6929', 'Gembes', 'DAVERDISSE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(590, '4851', 'Gemmenich', 'PLOMBI?RES', 'LUIK', NULL, '2020-07-22 15:14:24'),
(591, '7040', 'Genly', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(592, '3770', 'Genoelselderen', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(593, '9050', 'Gentbrugge', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(594, '1450', 'Gentinnes', 'CHASTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(595, '1332', 'Genval', 'RIXENSART', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(596, '3960', 'Gerdingen', 'BREE', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(597, '5524', 'G?rin', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(598, '1367', 'G?rompont', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(599, '6769', 'G?rouville', 'MEIX-DEVANT-VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(600, '2590', 'Gestel', 'BERLAAR', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(601, '7822', 'Ghislenghien', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(602, '7011', 'Ghlin', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(603, '7863', 'Ghoy', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(604, '7823', 'Gibecq', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(605, '2275', 'Gierle', 'LILLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(606, '8691', 'Gijverinkhove', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(607, '9308', 'Gijzegem', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(608, '8570', 'Gijzelbrechtegem', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(609, '9860', 'Gijzenzele', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(610, '6060', 'Gilly', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(611, '5680', 'Gimn?e', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(612, '8830', 'Gits', 'HOOGLEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(613, '7041', 'Givry', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(614, '1473', 'Glabais', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(615, '4000', 'Glain', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(616, '4400', 'Gleixhe', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(617, '1315', 'Glimes', 'INCOURT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(618, '4690', 'Glons', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(619, '5680', 'Gochen?e', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(620, '7160', 'Godarville', 'CHAPELLE-LEZ-HERLAIMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(621, '5530', 'Godinne', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(622, '9620', 'Godveerdegem', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(623, '4834', 'Go?', 'LIMBOURG', 'LUIK', NULL, '2020-07-22 15:14:24'),
(624, '9500', 'Goeferdinge', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(625, '7040', 'Goegnies-Chauss?e', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24');
INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `updated_at`, `created_at`) VALUES
(626, '5353', 'Goesnes', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(627, '3300', 'Goetsenhoven', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(628, '4140', 'Gomz?-Andoumont', 'SPRIMONT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(629, '7830', 'Gondregnies', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(630, '5660', 'Gonrieux', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(631, '9090', 'Gontrode', 'MELLE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(632, '3840', 'Gors-Opleeuw', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(633, '3803', 'Gorsem', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(634, '6041', 'Gosselies', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(635, '3840', 'Gotem', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(636, '9800', 'Gottem', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(637, '7070', 'Gottignies', 'LE ROEULX', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(638, '6280', 'Gougnies', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(639, '5651', 'Gourdinne', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(640, '6030', 'Goutroux', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(641, '6181', 'Gouy-Lez-Pi?ton', 'COURCELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(642, '6534', 'Goz?e', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(643, '4460', 'Gr?ce-Berleur', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(644, '5555', 'Graide', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(645, '9800', 'Grammene', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(646, '4300', 'Grand-Axhe', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:24'),
(647, '4280', 'Grand-Hallet', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(648, '6698', 'Grand-Halleux', 'VIELSALM', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(649, '5031', 'Grand-Leez', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(650, '5030', 'Grand-Manil', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(651, '4650', 'Grand-Rechain', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(652, '6560', 'Grand-Reng', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(653, '1367', 'Grand-Rosi?re-Hottomont', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(654, '7973', 'Grandglise', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(655, '6940', 'Grandhan', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(656, '6960', 'Grandm?nil', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(657, '7900', 'Grandmetz', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(658, '6470', 'Grandrieu', 'SIVRY-RANCE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(659, '4360', 'Grandville', 'OREYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(660, '6840', 'Grandvoir', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(661, '6840', 'Grapfontaine', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(662, '7830', 'Graty', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(663, '5640', 'Graux', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(664, '3450', 'Grazen', 'GEETBETS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(665, '9200', 'Grembergen', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(666, '9506', 'Grimminge', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(667, '4030', 'Grivegn?e', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(668, '1702', 'Groot-Bijgaarden', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(669, '3800', 'Groot-Gelmen', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(670, '3840', 'Groot-Loon', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(671, '5555', 'Gros-Fays', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(672, '7950', 'Grosage', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(673, '3990', 'Grote-Brogel', 'PEER', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(674, '3740', 'Grote-Spouwen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(675, '9620', 'Grotenberge', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(676, '3670', 'Gruitrode', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(677, '6952', 'Grune', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(678, '6927', 'Grupont', 'TELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(679, '7620', 'Guignies', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(680, '3723', 'Guigoven', 'KORTESSEM', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(681, '6704', 'Guirsch', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(682, '8560', 'Gullegem', 'WEVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(683, '3870', 'Gutshoven', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(684, '9120', 'Haasdonk', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(685, '3053', 'Haasrode', 'OUD-HEVERLEE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(686, '6720', 'Habay-La-Neuve', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(687, '6723', 'Habay-La-Vieille', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(688, '6782', 'Habergy', 'MESSANCY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(689, '4684', 'Haccourt', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(690, '6720', 'Hachy', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(691, '7911', 'Hacquegnies', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(692, '5351', 'Haillot', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(693, '7100', 'Haine-Saint-Paul', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(694, '7100', 'Haine-Saint-Pierre', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(695, '7350', 'Hainin', 'HENSIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(696, '3300', 'Hakendover', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(697, '6792', 'Halanzy', 'AUBANGE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(698, '2220', 'Hallaar', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(699, '2980', 'Halle', 'ZOERSEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(700, '3440', 'Halle-Booienhoven', 'ZOUTLEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(701, '6986', 'Halleux', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(702, '6922', 'Halma', 'WELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(703, '3800', 'Halmaal', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(704, '5340', 'Haltinne', 'GESVES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(705, '5190', 'Ham-Sur-Sambre', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(706, '6840', 'Hamipr?', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(707, '1785', 'Hamme', 'MERCHTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(708, '1320', 'Hamme-Mille', 'BEAUVECHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(709, '3930', 'Hamont', 'HAMONT-ACHEL', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(710, '6990', 'Hampteau', 'HOTTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(711, '5580', 'Han-Sur-Lesse', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(712, '8610', 'Handzame', 'KORTEMARK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(713, '4357', 'Haneffe', 'DONCEEL', 'LUIK', NULL, '2020-07-22 15:14:24'),
(714, '4210', 'Hann?che', 'BURDINNE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(715, '5310', 'Hanret', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(716, '9850', 'Hansbeke', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(717, '6560', 'Hantes-Wih?ries', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(718, '5621', 'Hanzinelle', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(719, '5621', 'Hanzinne', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(720, '7321', 'Harchies', 'BERNISSART', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(721, '1130', 'Haren', 'BRUSSEL', 'BRUSSEL', NULL, '2020-07-22 15:14:24'),
(722, '3700', 'Haren', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(723, '3840', 'Haren', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(724, '6900', 'Hargimont', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(725, '7022', 'Harmignies', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(726, '6767', 'Harnoncourt', 'ROUVROY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(727, '6960', 'Harre', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(728, '6950', 'Harsin', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(729, '7022', 'Harveng', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(730, '4920', 'Harz?', 'AYWAILLE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(731, '5540', 'Hasti?re-Lavaux', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(732, '5541', 'Hasti?re-Par-Del?', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(733, '6870', 'Hatrival', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(734, '7120', 'Haulchin', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(735, '4730', 'Hauset', 'RAEREN', 'LUIK', NULL, '2020-07-22 15:14:24'),
(736, '6929', 'Haut-Fays', 'DAVERDISSE', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(737, '1461', 'Haut-Ittre', 'ITTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:24'),
(738, '5537', 'Haut-Le-Wastia', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(739, '7334', 'Hautrage', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(740, '7041', 'Havay', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(741, '5590', 'Haversin', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(742, '7531', 'Havinnes', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(743, '7021', 'Havr?', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(744, '3940', 'Hechtel', 'HECHTEL-EKSEL', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(745, '5543', 'Heer', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(746, '3740', 'Hees', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(747, '8551', 'Heestert', 'ZWEVEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(748, '2801', 'Heffen', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(749, '1670', 'Heikruis', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(750, '2830', 'Heindonk', 'WILLEBROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:24'),
(751, '6700', 'Heinsch', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:24'),
(752, '8301', 'Heist-Aan-Zee', 'KNOKKE-HEIST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(753, '1790', 'Hekelgem', 'AFFLIGEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(754, '3870', 'Heks', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(755, '3530', 'Helchteren', 'HOUTHALEN-HELCHTEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(756, '9450', 'Heldergem', 'HAALTERT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(757, '3440', 'Helen-Bos', 'ZOUTLEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(758, '8587', 'Helkijn', 'SPIERE-HELKIJN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(759, '7830', 'Hellebecq', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(760, '9571', 'Hemelveerdegem', 'LIERDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(761, '5380', 'Hemptinne', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(762, '5620', 'Hemptinne-Lez-Florennes', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(763, '3840', 'Hendrieken', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(764, '3700', 'Henis', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(765, '7090', 'Hennuy?res', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(766, '4841', 'Henri-Chapelle', 'WELKENRAEDT', 'LUIK', NULL, '2020-07-22 15:14:24'),
(767, '7090', 'Henripont', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(768, '3971', 'Heppen', 'LEOPOLDSBURG', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(769, '4771', 'Heppenbach', 'AMEL', 'LUIK', NULL, '2020-07-22 15:14:24'),
(770, '6220', 'Heppignies', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(771, '7050', 'Herchies', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(772, '3770', 'Herderen', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(773, '9310', 'Herdersem', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(774, '1540', 'Herfelingen', 'HERNE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:24'),
(775, '4728', 'Hergenrath', 'KELMIS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(776, '7742', 'H?rinnes-Lez-Pecq', 'PECQ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(777, '4681', 'Hermalle-Sous-Argenteau', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(778, '4480', 'Hermalle-Sous-Huy', 'ENGIS', 'LUIK', NULL, '2020-07-22 15:14:24'),
(779, '4680', 'Herm?e', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(780, '5540', 'Hermeton-Sur-Meuse', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(781, '7911', 'Herquegies', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(782, '7712', 'Herseaux', 'MOESKROEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(783, '7522', 'Hertain', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:24'),
(784, '3831', 'Herten', 'WELLEN', 'LIMBURG', NULL, '2020-07-22 15:14:24'),
(785, '8020', 'Hertsberge', 'OOSTKAMP', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(786, '8501', 'Heule', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:24'),
(787, '5377', 'Heure', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:24'),
(788, '4682', 'Heure-Le-Romain', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:24'),
(789, '9700', 'Heurne', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(790, '3550', 'Heusden', 'HEUSDEN-ZOLDER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(791, '9070', 'Heusden', 'DESTELBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(792, '4802', 'Heusy', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(793, '3191', 'Hever', 'BOORTMEERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(794, '3001', 'Heverlee', 'LEUVEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(795, '1435', 'H?villers', 'MONT-SAINT-GUIBERT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(796, '6941', 'Heyd', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(797, '9550', 'Hillegem', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(798, '2880', 'Hingene', 'BORNEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(799, '5380', 'Hingeon', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(800, '6984', 'Hives', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(801, '2660', 'Hoboken', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(802, '4351', 'Hodeige', 'REMICOURT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(803, '6987', 'Hodister', 'RENDEUX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(804, '4162', 'Hody', 'ANTHISNES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(805, '8340', 'Hoeke', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(806, '3746', 'Hoelbeek', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(807, '3471', 'Hoeleden', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(808, '3840', 'Hoepertingen', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(809, '2940', 'Hoevenen', 'STABROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(810, '1981', 'Hofstade', 'ZEMST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(811, '9308', 'Hofstade', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(812, '5377', 'Hogne', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(813, '4342', 'Hognoul', 'AWANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(814, '7620', 'Hollain', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(815, '6637', 'Hollange', 'FAUVILLERS', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(816, '8902', 'Hollebeke', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(817, '4460', 'Hollogne-Aux-Pierres', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(818, '4250', 'Hollogne-Sur-Geer', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(819, '2811', 'Hombeek', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(820, '4852', 'Hombourg', 'PLOMBI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(821, '6640', 'Hompr?', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(822, '6780', 'Hondelange', 'MESSANCY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(823, '5570', 'Honnay', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(824, '8690', 'Hoogstade', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(825, '4460', 'Horion-Hoz?mont', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(826, '7301', 'Hornu', 'BOUSSU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(827, '3870', 'Horpmaal', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(828, '7060', 'Horrues', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(829, '6724', 'Houdemont', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(830, '7110', 'Houdeng-Aimeries', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(831, '7110', 'Houdeng-Goegnies', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(832, '5575', 'Houdremont', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(833, '5563', 'Hour', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(834, '4671', 'Housse', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(835, '1476', 'Houtain-Le-Val', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(836, '4682', 'Houtain-Saint-Sim?on', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(837, '7812', 'Houtaing', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(838, '8377', 'Houtave', 'ZUIENKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(839, '8630', 'Houtem', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(840, '3530', 'Houthalen', 'HOUTHALEN-HELCHTEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(841, '7781', 'Houthem', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(842, '2235', 'Houtvenne', 'HULSHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(843, '3390', 'Houwaart', 'TIELT-WINGE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(844, '5530', 'Houx', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(845, '7830', 'Hoves', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(846, '7624', 'Howardries', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(847, '4520', 'Huccorgne', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(848, '9750', 'Huise', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(849, '7950', 'Huissignies', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(850, '1654', 'Huizingen', 'BEERSEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(851, '5560', 'Hulsonniaux', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(852, '8531', 'Hulste', 'HARELBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(853, '6900', 'Humain', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(854, '1851', 'Humbeek', 'GRIMBERGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(855, '9630', 'Hundelgem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(856, '1367', 'Huppaye', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(857, '7022', 'Hyon', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(858, '9472', 'Iddergem', 'DENDERLEEUW', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(859, '9506', 'Idegem', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(860, '9340', 'Impe', 'LEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(861, '8570', 'Ingooigem', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(862, '7801', 'Irchonwelz', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(863, '7822', 'Isi?res', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(864, '5032', 'Isnes', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(865, '2222', 'Itegem', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(866, '1701', 'Itterbeek', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(867, '4400', 'Ivoz-Ramet', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(868, '6810', 'Izel', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(869, '8691', 'Izenberge', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(870, '6941', 'Izier', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(871, '5354', 'Jallet', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(872, '5600', 'Jamagne', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(873, '5100', 'Jambes', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(874, '5600', 'Jamiolle', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(875, '6120', 'Jamioulx', 'HAM-SUR-HEURE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(876, '6810', 'Jamoigne', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(877, '1350', 'Jandrain-Jandrenouille', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(878, '1350', 'Jauche', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(879, '1370', 'Jauchelette', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(880, '5570', 'Javingue', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(881, '4540', 'Jehay', 'AMAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(882, '6880', 'Jehonville', 'BERTRIX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(883, '7012', 'Jemappes', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(884, '5580', 'Jemelle', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(885, '4101', 'Jemeppe-Sur-Meuse', 'SERAING', 'LUIK', NULL, '2020-07-22 15:14:25'),
(886, '4357', 'Jeneffe', 'DONCEEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(887, '5370', 'Jeneffe', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(888, '3840', 'Jesseren', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(889, '3890', 'Jeuk', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(890, '1370', 'Jodoigne-Souveraine', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(891, '7620', 'Jollain-Merlin', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(892, '6280', 'Joncret', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(893, '4650', 'Jul?mont', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(894, '6040', 'Jumet', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(895, '4020', 'Jupille-Sur-Meuse', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(896, '6642', 'Juseret', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(897, '8600', 'Kaaskerke', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(898, '8870', 'Kachtem', 'IZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(899, '3293', 'Kaggevinne', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(900, '7540', 'Kain', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(901, '9270', 'Kalken', 'LAARNE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(902, '9120', 'Kallo', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(903, '9130', 'Kallo', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(904, '8700', 'Kanegem', 'TIELT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(905, '3770', 'Kanne', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(906, '3381', 'Kapellen', 'GLABBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(907, '8572', 'Kaster', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(908, '3950', 'Kaulille', 'BOCHOLT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(909, '8600', 'Keiem', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(910, '4367', 'Kemexhe', 'CRISN?E', 'LUIK', NULL, '2020-07-22 15:14:25'),
(911, '8956', 'Kemmel', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(912, '9190', 'Kemzeke', 'STEKENE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(913, '8581', 'Kerkhove', 'AVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(914, '3370', 'Kerkom', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(915, '3800', 'Kerkom-Bij-Sint-Truiden', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(916, '9451', 'Kerksken', 'HAALTERT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(917, '3510', 'Kermt', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(918, '3840', 'Kerniel', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(919, '3472', 'Kersbeek-Miskom', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(920, '2560', 'Kessel', 'NIJLEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(921, '3010', 'Kessel Lo', 'LEUVEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(922, '3640', 'Kessenich', 'KINROOI', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(923, '1755', 'Kester', 'GOOIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(924, '4701', 'Kettenis', 'EUPEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(925, '5060', 'Keumi?e', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(926, '9130', 'Kieldrecht', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(927, '3870', 'Klein-Gelmen', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(928, '3990', 'Kleine-Brogel', 'PEER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(929, '3740', 'Kleine-Spouwen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(930, '8420', 'Klemskerke', 'DE HAAN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(931, '8650', 'Klerken', 'HOUTHULST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(932, '9940', 'Kluizen', 'EVERGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(933, '9910', 'Knesselare', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(934, '8300', 'Knokke', 'KNOKKE-HEIST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(935, '1730', 'Kobbegem', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(936, '3582', 'Koersel', 'BERINGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(937, '3700', 'Kolmont', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(938, '3840', 'Kolmont', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(939, '7780', 'Komen', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(940, '2500', 'Koningshooikt', 'LIER', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(941, '3700', 'Koninksem', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(942, '8510', 'Kooigem', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(943, '8000', 'Koolkerke', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(944, '8851', 'Koolskamp', 'ARDOOIE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(945, '3060', 'Korbeek-Dijle', 'BERTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(946, '3360', 'Korbeek-Lo', 'BIERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(947, '3890', 'Kortijs', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(948, '3220', 'Kortrijk-Dutsel', 'HOLSBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(949, '3850', 'Kozen', 'NIEUWERKERKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(950, '8972', 'Krombeke', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(951, '9770', 'Kruishoutem', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(952, '3300', 'Kumtich', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(953, '3511', 'Kuringen', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(954, '3840', 'Kuttekoven', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(955, '3945', 'Kwaadmechelen', 'HAM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(956, '9690', 'Kwaremont', 'KLUISBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(957, '1320', 'L\'Ecluse', 'BEAUVECHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(958, '6464', 'L\'Escaill?re', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(959, '7080', 'La Bouverie', 'FRAMERIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(960, '7611', 'La Glanerie', 'RUMES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(961, '4987', 'La Gleize', 'STOUMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(962, '7170', 'La Hestre', 'MANAGE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(963, '4910', 'La Reid', 'THEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(964, '3400', 'Laar', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(965, '6567', 'Labuissi?re', 'MERBES-LE-CH?TEAU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(966, '6821', 'Lacuisine', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(967, '7950', 'Ladeuze', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(968, '5550', 'Lafor?t', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(969, '7890', 'Lahamaide', 'ELLEZELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(970, '1020', 'Laken', 'BRUSSEL', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(971, '7522', 'Lamain', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(972, '4800', 'Lambermont', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(973, '6220', 'Lambusart', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(974, '4350', 'Lamine', 'REMICOURT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(975, '4210', 'Lamontz?e', 'BURDINNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(976, '6767', 'Lamorteau', 'ROUVROY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(977, '8600', 'Lampernisse', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(978, '4600', 'Lanaye', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(979, '9850', 'Landegem', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(980, '6111', 'Landelies', 'MONTIGNY-LE-TILLEUL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(981, '5300', 'Landenne', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(982, '9860', 'Landskouter', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(983, '5651', 'Laneffe', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(984, '3201', 'Langdorp', 'AARSCHOT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(985, '8920', 'Langemark', 'LANGEMARK-POELKAPELLE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(986, '3650', 'Lanklaar', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(987, '7800', 'Lanquesaint', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(988, '4450', 'Lantin', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(989, '4300', 'Lantremange', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:25'),
(990, '7622', 'Laplaigne', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(991, '8340', 'Lapscheure', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(992, '1380', 'Lasne-Chapelle-Saint-Lambert', 'LASNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(993, '1370', 'Lathuy', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(994, '4261', 'Latinne', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(995, '6761', 'Latour', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(996, '3700', 'Lauw', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(997, '8930', 'Lauwe', 'MENEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(998, '6681', 'Lavacherie', 'SAINTE-ODE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(999, '5580', 'Lavaux-Sainte-Anne', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1000, '4217', 'Lavoir', 'H?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1001, '5670', 'Le Mesnil', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1002, '5070', 'Le Roux', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1003, '9050', 'Ledeberg', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1004, '3061', 'Leefdaal', 'BERTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1005, '1755', 'Leerbeek', 'GOOIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1006, '6142', 'Leernes', 'FONTAINE-L\'EV?QUE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1007, '6530', 'Leers-Et-Fosteau', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1008, '7730', 'Leers-Nord', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1009, '2811', 'Leest', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1010, '9620', 'Leeuwergem', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1011, '8432', 'Leffinge', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1012, '5590', 'Leignon', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1013, '8691', 'Leisele', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1014, '8600', 'Leke', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1015, '1502', 'Lembeek', 'HALLE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1016, '9971', 'Lembeke', 'KAPRIJKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1017, '9820', 'Lemberge', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1018, '4280', 'Lens-Saint-Remy', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1019, '4250', 'Lens-Saint-Servais', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1020, '4360', 'Lens-Sur-Geer', 'OREYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1021, '4560', 'Les Avins', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1022, '6811', 'Les Bulles', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1023, '6830', 'Les Hayons', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1024, '4317', 'Les Waleffes', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1025, '7621', 'Lesdain', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1026, '5580', 'Lessive', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1027, '6953', 'Lesterny', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1028, '5170', 'Lesve', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1029, '7850', 'Lettelingen', 'EDINGEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1030, '9521', 'Letterhoutem', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1031, '6500', 'Leugnies', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1032, '9700', 'Leupegem', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1033, '3630', 'Leut', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1034, '5310', 'Leuze', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1035, '6500', 'Leval-Chaudeville', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1036, '7134', 'Leval-Trahegnies', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1037, '6238', 'Liberchies', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1038, '2460', 'Lichtaart', 'KASTERLEE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1039, '9400', 'Lieferinge', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1040, '5310', 'Liernu', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1041, '4042', 'Liers', 'HERSTAL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1042, '2870', 'Liezele', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1043, '7812', 'Ligne', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1044, '4254', 'Ligney', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1045, '5140', 'Ligny', 'SOMBREFFE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1046, '2040', 'Lillo', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1047, '1428', 'Lillois-Witterz?e', 'BRAINE-L\'ALLEUD', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1048, '1300', 'Limal', 'WAVRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1049, '1342', 'Limelette', 'OTTIGNIES-LOUVAIN-LA-NEUVE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1050, '6670', 'Limerl?', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1051, '4357', 'Limont', 'DONCEEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1052, '3210', 'Linden', 'LUBBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1053, '3560', 'Linkhout', 'LUMMEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1054, '1357', 'Linsmeau', 'H?L?CINE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1055, '2890', 'Lippelo', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1056, '5501', 'Lisogne', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1057, '8380', 'Lissewege', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1058, '5101', 'Lives-Sur-Meuse', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1059, '4600', 'Lixhe', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1060, '8647', 'Lo', 'LO-RENINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1061, '6042', 'Lodelinsart', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1062, '2990', 'Loenhout', 'WUUSTWEZEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1063, '8958', 'Loker', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1064, '3545', 'Loksbergen', 'HALEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1065, '8434', 'Lombardsijde', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1066, '7870', 'Lombise', 'LENS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1067, '4783', 'Lommersweiler', 'SANKT-VITH', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1068, '6463', 'Lompret', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1069, '6924', 'Lomprez', 'WELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1070, '4431', 'Loncin', 'ANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1071, '5310', 'Longchamps', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1072, '6688', 'Longchamps', 'BERTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1073, '6840', 'Longlier', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1074, '1325', 'Longueville', 'CHAUMONT-GISTOUX', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1075, '6600', 'Longvilly', 'BASTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1076, '5030', 'Lonz?e', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1077, '3040', 'Loonbeek', 'HULDENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1078, '8210', 'Loppem', 'ZEDELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1079, '4987', 'Lorc?', 'STOUMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1080, '1651', 'Lot', 'BEERSEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1081, '9880', 'Lotenhulle', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1082, '5575', 'Louette-Saint-Denis', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1083, '5575', 'Louette-Saint-Pierre', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1084, '1471', 'Loupoigne', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1085, '1348', 'Louvain-La-Neuve', 'OTTIGNIES-LOUVAIN-LA-NEUVE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1086, '4141', 'Louveign?', 'SPRIMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1087, '4920', 'Louveign?', 'AYWAILLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1088, '9920', 'Lovendegem', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1089, '3360', 'Lovenjoel', 'BIERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1090, '6280', 'Loverval', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1091, '5101', 'Loyers', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1092, '7700', 'Luingne', 'MOESKROEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1093, '5170', 'Lustin', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1094, '6238', 'Luttre', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1095, '9680', 'Maarke-Kerkem', 'MAARKEDAL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1096, '6663', 'Mabompr?', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1097, '9870', 'Machelen', 'ZULTE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1098, '6591', 'Macon', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1099, '6593', 'Macquenoise', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1100, '5374', 'Maffe', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1101, '7810', 'Maffle', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1102, '4623', 'Magn?e', 'FL?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1103, '5330', 'Maillen', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1104, '7812', 'Mainvault', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1105, '7020', 'Maisi?res', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1106, '6852', 'Maissin', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1107, '5300', 'Maizeret', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1108, '3700', 'Mal', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1109, '1840', 'Malderen', 'LONDERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1110, '6960', 'Malempr?', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1111, '1360', 'Mal?ves-Sainte-Marie-Wastinnes', 'PERWEZ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1112, '5020', 'Malonne', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1113, '5575', 'Malvoisin', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1114, '4760', 'Manderfeld', 'B?LLINGEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1115, '8433', 'Mannekensvere', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1116, '1380', 'Maransart', 'LASNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1117, '1495', 'Marbais', 'VILLERS-LA-VILLE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1118, '6120', 'Marbaix', 'HAM-SUR-HEURE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1119, '5024', 'Marche-Les-Dames', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1120, '7190', 'Marche-Lez-Ecaussinnes', 'ECAUSSINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1121, '6030', 'Marchienne-Au-Pont', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1122, '7387', 'Marchipont', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1123, '5380', 'Marchovelette', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1124, '6001', 'Marcinelle', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1125, '6987', 'Marcourt', 'RENDEUX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1126, '6990', 'Marenne', 'HOTTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1127, '9030', 'Mariakerke', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1128, '2880', 'Mariekerke', 'BORNEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1129, '5660', 'Mariembourg', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1130, '1350', 'Marilles', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1131, '7850', 'Mark', 'EDINGEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1132, '8510', 'Marke', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1133, '8720', 'Markegem', 'DENTERGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1134, '4210', 'Marneffe', 'BURDINNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1135, '7522', 'Marquain', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1136, '3742', 'Martenslinde', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1137, '5573', 'Martouzin-Neuville', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1138, '6953', 'Masbourg', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1139, '7050', 'Masnuy-Saint-Jean', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1140, '7050', 'Masnuy-Saint-Pierre', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1141, '9230', 'Massemen', 'WETTEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1142, '2240', 'Massenhoven', 'ZANDHOVEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1143, '5680', 'Matagne-La-Grande', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1144, '5680', 'Matagne-La-Petite', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1145, '9700', 'Mater', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1146, '7640', 'Maubray', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1147, '7534', 'Maulde', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1148, '7110', 'Maurage', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1149, '5670', 'Maz?e', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1150, '1745', 'Mazenzele', 'OPWIJK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1151, '5032', 'Mazy', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1152, '5372', 'M?an', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1153, '3630', 'Mechelen-Aan-De-Maas', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1154, '3870', 'Mechelen-Bovelingen', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1155, '4219', 'Meeffe', 'WASSEIGES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1156, '3391', 'Meensel-Kiezegem', 'TIELT-WINGE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1157, '2321', 'Meer', 'HOOGSTRATEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1158, '3078', 'Meerbeek', 'KORTENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1159, '9402', 'Meerbeke', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1160, '9170', 'Meerdonk', 'SINT-GILLIS-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1161, '2328', 'Meerle', 'HOOGSTRATEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1162, '3630', 'Meeswijk', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1163, '8377', 'Meetkerke', 'ZUIENKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1164, '3670', 'Meeuwen', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1165, '5310', 'Mehaigne', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1166, '9800', 'Meigem', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1167, '9630', 'Meilegem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1168, '6747', 'Meix-Le-Tige', 'SAINT-L?GER', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1169, '9700', 'Melden', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1170, '3320', 'Meldert', 'HOEGAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1171, '3560', 'Meldert', 'LUMMEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1172, '9310', 'Meldert', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1173, '4633', 'Melen', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1174, '1370', 'M?lin', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1175, '3350', 'Melkwezer', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1176, '1495', 'Mellery', 'VILLERS-LA-VILLE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1177, '7540', 'Melles', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1178, '6211', 'Mellet', 'LES BONS VILLERS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1179, '6860', 'Mellier', 'L?GLISE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1180, '1820', 'Melsbroek', 'STEENOKKERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1181, '9120', 'Melsele', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1182, '9820', 'Melsen', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1183, '4837', 'Membach', 'BAELEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1184, '5550', 'Membre', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1185, '3770', 'Membruggen', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1186, '9042', 'Mendonk', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1187, '6567', 'Merbes-Sainte-Marie', 'MERBES-LE-CH?TEAU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1188, '4280', 'Merdorp', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1189, '9420', 'Mere', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1190, '9850', 'Merendree', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1191, '8650', 'Merkem', 'HOUTHULST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1192, '2170', 'Merksem', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1193, '5600', 'Merlemont', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1194, '7822', 'Meslin-L\'Ev?que', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1195, '5560', 'Mesnil-Eglise', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1196, '5560', 'Mesnil-Saint-Blaise', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1197, '9200', 'Mespelare', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1198, '3272', 'Messelbroek', 'SCHERPENHEUVEL-ZICHEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1199, '7022', 'Mesvin', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1200, '3870', 'Mettekoven', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1201, '5081', 'Meux', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1202, '7942', 'M?vergnies-Lez-Lens', 'BRUGELETTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1203, '4770', 'Meyerode', 'AMEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1204, '9660', 'Michelbeke', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1205, '4630', 'Micheroux', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1206, '9992', 'Middelburg', 'MALDEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1207, '5376', 'Mi?cret', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1208, '3891', 'Mielen-Boven-Aalst', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1209, '7070', 'Mignault', 'LE ROEULX', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1210, '3770', 'Millen', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1211, '4041', 'Milmort', 'HERSTAL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1212, '2322', 'Minderhout', 'HOOGSTRATEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1213, '6870', 'Mirwart', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1214, '3790', 'Moelingen', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1215, '8552', 'Moen', 'ZWEVEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1216, '9500', 'Moerbeke', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1217, '8470', 'Moere', 'GISTEL', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1218, '8340', 'Moerkerke', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1219, '9220', 'Moerzeke', 'HAMME', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1220, '4520', 'Moha', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1221, '5361', 'Mohiville', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1222, '5060', 'Moignel?e', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1223, '6800', 'Moircy', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1224, '7760', 'Molenbaix', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1225, '3461', 'Molenbeek-Wersbeek', 'BEKKEVOORT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1226, '3640', 'Molenbeersel', 'KINROOI', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1227, '3294', 'Molenstede', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1228, '1730', 'Mollem', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1229, '4350', 'Momalle', 'REMICOURT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1230, '5555', 'Monceau-En-Ardenne', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1231, '6592', 'Monceau-Imbrechies', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1232, '6031', 'Monceau-Sur-Sambre', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1233, '4400', 'Mons-Lez-Li?ge', 'FL?MALLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1234, '1400', 'Monstreux', 'NIVELLES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1235, '5530', 'Mont', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1236, '6661', 'Mont', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1237, '5580', 'Mont-Gauthier', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1238, '1367', 'Mont-Saint-Andr?', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1239, '7542', 'Mont-Saint-Aubert', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25');
INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `updated_at`, `created_at`) VALUES
(1240, '7141', 'Mont-Sainte-Aldegonde', 'MORLANWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1241, '6540', 'Mont-Sainte-Genevi?ve', 'LOBBES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1242, '6032', 'Mont-Sur-Marchienne', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1243, '6470', 'Montbliart', 'SIVRY-RANCE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1244, '4420', 'Montegn?e', 'SAINT-NICOLAS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1245, '3890', 'Montenaken', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1246, '7870', 'Montignies-Lez-Lens', 'LENS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1247, '6560', 'Montignies-Saint-Christophe', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1248, '7387', 'Montignies-Sur-Roc', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1249, '6061', 'Montignies-Sur-Sambre', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1250, '6674', 'Montleban', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1251, '7911', 'Montroeul-Au-Bois', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1252, '7350', 'Montroeul-Sur-Haine', 'HENSIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1253, '4850', 'Montzen', 'PLOMBI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1254, '9310', 'Moorsel', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1255, '8560', 'Moorsele', 'WEVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1256, '9860', 'Moortsele', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1257, '3740', 'Mopertingen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1258, '9790', 'Moregem', 'WORTEGEM-PETEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1259, '4850', 'Moresnet', 'PLOMBI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1260, '6640', 'Morhet', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1261, '5621', 'Morialm?', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1262, '2200', 'Morkhoven', 'HERENTALS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1263, '7140', 'Morlanwelz-Mariemont', 'MORLANWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1264, '6997', 'Mormont', 'EREZ?E', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1265, '5190', 'Mornimont', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1266, '4670', 'Mortier', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1267, '4607', 'Mortroux', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1268, '5620', 'Morville', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1269, '7812', 'Moulbaix', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1270, '7543', 'Mourcourt', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1271, '7911', 'Moustier', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1272, '5190', 'Moustier-Sur-Sambre', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1273, '5550', 'Mouzaive', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1274, '4280', 'Moxhe', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1275, '5340', 'Mozet', 'GESVES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1276, '2812', 'Muizen', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1277, '3891', 'Muizen', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1278, '9700', 'Mullem', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1279, '9630', 'Munkzwalm', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1280, '6820', 'Muno', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1281, '3740', 'Munsterbilzen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1282, '9820', 'Munte', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1283, '6750', 'Mussy-La-Ville', 'MUSSON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1284, '4190', 'My', 'FERRI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1285, '7062', 'Naast', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1286, '6660', 'Nadrin', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1287, '5550', 'Nafraiture', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1288, '6120', 'Nalinnes', 'HAM-SUR-HEURE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1289, '5300', 'Nam?che', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1290, '5100', 'Naninne', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1291, '5555', 'Naom?', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1292, '5360', 'Natoye', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1293, '7730', 'N?chin', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1294, '1120', 'Neder-Over-Heembeek', 'BRUSSEL', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(1295, '9500', 'Nederboelare', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1296, '9660', 'Nederbrakel', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1297, '9700', 'Nederename', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1298, '9400', 'Nederhasselt', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1299, '1910', 'Nederokkerzeel', 'KAMPENHOUT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1300, '9636', 'Nederzwalm-Hermelgem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1301, '3670', 'Neerglabbeek', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1302, '3620', 'Neerharen', 'LANAKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1303, '3350', 'Neerhespen', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1304, '1357', 'Neerheylissem', 'H?L?CINE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1305, '3040', 'Neerijse', 'HULDENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1306, '3404', 'Neerlanden', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1307, '3350', 'Neerlinter', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1308, '3680', 'Neeroeteren', 'MAASEIK', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1309, '3910', 'Neerpelt', 'PELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1310, '3700', 'Neerrepen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1311, '3370', 'Neervelp', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1312, '7784', 'Neerwaasten', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1313, '3400', 'Neerwinden', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1314, '9403', 'Neigem', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1315, '3700', 'Nerem', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1316, '4870', 'Nessonvaux', 'TROOZ', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1317, '1390', 'Nethen', 'GREZ-DOICEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1318, '5377', 'Nettinne', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1319, '4721', 'Neu-Moresnet', 'KELMIS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1320, '4608', 'Neufch?teau', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1321, '7332', 'Neufmaison', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1322, '7063', 'Neufvilles', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1323, '5600', 'Neuville', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1324, '4121', 'Neuville-En-Condroz', 'NEUPR?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1325, '9850', 'Nevele', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1326, '3668', 'Niel-Bij-As', 'AS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1327, '3890', 'Niel-Bij-Sint-Truiden', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1328, '9506', 'Nieuwenhove', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1329, '1880', 'Nieuwenrode', 'KAPELLE-OP-DEN-BOS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1330, '9320', 'Nieuwerkerken', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1331, '8600', 'Nieuwkapelle', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1332, '8950', 'Nieuwkerke', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1333, '9100', 'Nieuwkerken-Waas', 'SINT-NIKLAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1334, '8377', 'Nieuwmunster', 'ZUIENKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1335, '3221', 'Nieuwrode', 'HOLSBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1336, '1457', 'Nil-Saint-Vincent-Saint-Martin', 'WALHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1337, '7020', 'Nimy', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1338, '5670', 'Nismes', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1339, '5680', 'Niverl?e', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1340, '6640', 'Nives', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1341, '6717', 'Nobressart', 'ATTERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1342, '1320', 'Nodebais', 'BEAUVECHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1343, '1350', 'Noduwez', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1344, '7080', 'Noirchain', 'FRAMERIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1345, '6831', 'Noirfontaine', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1346, '5377', 'Noiseux', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1347, '9771', 'Nokere', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1348, '6851', 'Nollevaux', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1349, '2200', 'Noorderwijk', 'HERENTALS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1350, '8647', 'Noordschote', 'LO-RENINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1351, '1930', 'Nossegem', 'ZAVENTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1352, '6717', 'Nothomb', 'ATTERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1353, '7022', 'Nouvelles', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1354, '4347', 'Noville', 'FEXHE-LE-HAUT-CLOCHER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1355, '6600', 'Noville', 'BASTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1356, '5380', 'Noville-Les-Bois', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1357, '5310', 'Noville-Sur-Mehaigne', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1358, '9681', 'Nukerke', 'MAARKEDAL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1359, '6230', 'Obaix', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1360, '7743', 'Obigies', 'PECQ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1361, '7034', 'Obourg', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1362, '6890', 'Ochamps', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1363, '4560', 'Ocquier', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1364, '6960', 'Odeigne', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1365, '4367', 'Odeur', 'CRISN?E', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1366, '8730', 'Oedelem', 'BEERNEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1367, '8800', 'Oekene', 'ROESELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1368, '2520', 'Oelegem', 'RANST', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1369, '8690', 'Oeren', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1370, '8720', 'Oeselgem', 'DENTERGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1371, '1755', 'Oetingen', 'GOOIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1372, '7911', 'Oeudeghien', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1373, '2260', 'Oevel', 'WESTERLO', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1374, '6850', 'Offagne', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1375, '7862', 'Ogy', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1376, '1380', 'Ohain', 'LASNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1377, '5670', 'Oignies-En-Thi?rache', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1378, '1480', 'Oisquercq', 'TUBIZE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1379, '5555', 'Oizy', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1380, '9400', 'Okegem', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1381, '4300', 'Oleye', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1382, '7866', 'Ollignies', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1383, '5670', 'Olloy-Sur-Viroin', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1384, '2491', 'Olmen', 'BALEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1385, '9870', 'Olsene', 'ZULTE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1386, '4252', 'Omal', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1387, '4540', 'Ombret', 'AMAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1388, '5600', 'Omez?e', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1389, '6900', 'On', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1390, '9500', 'Onkerzele', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1391, '7387', 'Onnezies', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1392, '5190', 'Onoz', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1393, '1760', 'Onze-Lieve-Vrouw-Lombeek', 'ROOSDAAL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1394, '2861', 'Onze-Lieve-Vrouw-Waver', 'SINT-KATELIJNE-WAVER', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1395, '8710', 'Ooigem', 'WIELSBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1396, '9700', 'Ooike', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1397, '9790', 'Ooike', 'WORTEGEM-PETEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1398, '9520', 'Oombergen', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1399, '9620', 'Oombergen', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1400, '3300', 'Oorbeek', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1401, '9340', 'Oordegem', 'LEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1402, '9041', 'Oostakker', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1403, '8670', 'Oostduinkerke', 'KOKSIJDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1404, '9968', 'Oosteeklo', 'ASSENEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1405, '3945', 'Oostham', 'HAM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1406, '8340', 'Oostkerke', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1407, '8600', 'Oostkerke', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1408, '2390', 'Oostmalle', 'MALLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1409, '8840', 'Oostnieuwkerke', 'STADEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1410, '8640', 'Oostvleteren', 'VLETEREN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1411, '9931', 'Oostwinkel', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1412, '9660', 'Opbrakel', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1413, '9255', 'Opdorp', 'BUGGENHOUT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1414, '3660', 'Opglabbeek', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1415, '3630', 'Opgrimbie', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1416, '1421', 'Ophain-Bois-Seigneur-Isaac', 'BRAINE-L\'ALLEUD', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1417, '9500', 'Ophasselt', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1418, '3870', 'Opheers', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1419, '1357', 'Opheylissem', 'H?L?CINE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1420, '3640', 'Ophoven', 'KINROOI', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1421, '3960', 'Opitter', 'BREE', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1422, '3300', 'Oplinter', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1423, '3680', 'Opoeteren', 'MAASEIK', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1424, '6852', 'Opont', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1425, '1315', 'Opprebais', 'INCOURT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1426, '2890', 'Oppuurs', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1427, '3360', 'Opvelp', 'BIERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1428, '1360', 'Orbais', 'PERWEZ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1429, '5550', 'Orchimont', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1430, '7501', 'Orcq', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1431, '3800', 'Ordingen', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1432, '5640', 'Oret', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1433, '6880', 'Orgeo', 'BERTRIX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1434, '7802', 'Ormeignies', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1435, '1350', 'Orp-Le-Grand', 'ORP-JAUCHE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1436, '7750', 'Orroir', 'MONT-DE-L\'ENCLUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1437, '3350', 'Orsmaal-Gussenhoven', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1438, '6983', 'Ortho', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1439, '7804', 'Ostiches', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1440, '8553', 'Otegem', 'ZWEVEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1441, '4210', 'Oteppe', 'BURDINNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1442, '4340', 'Oth?e', 'AWANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1443, '4360', 'Otrange', 'OREYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1444, '3040', 'Ottenburg', 'HULDENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1445, '9420', 'Ottergem', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1446, '1340', 'Ottignies', 'OTTIGNIES-LOUVAIN-LA-NEUVE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1447, '9200', 'Oudegem', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1448, '8600', 'Oudekapelle', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1449, '1600', 'Oudenaken', 'SINT-PIETERS-LEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1450, '4102', 'Ougr?e', 'SERAING', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1451, '9406', 'Outer', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1452, '3321', 'Outgaarden', 'HOEGAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1453, '4577', 'Outrelouxhe', 'MODAVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1454, '8582', 'Outrijve', 'AVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1455, '9750', 'Ouwegem', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1456, '9500', 'Overboelare', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1457, '3350', 'Overhespen', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1458, '9290', 'Overmere', 'BERLARE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1459, '3900', 'Overpelt', 'PELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1460, '3700', 'Overrepen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1461, '3400', 'Overwinden', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1462, '3583', 'Paal', 'BERINGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1463, '4452', 'Paifve', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1464, '4560', 'Pailhe', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1465, '1760', 'Pamel', 'ROOSDAAL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1466, '7861', 'Papignies', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1467, '9661', 'Parike', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1468, '8980', 'Passendale', 'ZONNEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1469, '5575', 'Patignies', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1470, '7340', 'P?turages', 'COLFONTAINE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1471, '9630', 'Paulatem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1472, '7120', 'Peissant', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1473, '4287', 'Pellaines', 'LINCENT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1474, '3212', 'Pellenberg', 'LUBBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1475, '1820', 'Perk', 'STEENOKKERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1476, '7640', 'P?ronnes-Lez-Antoing', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1477, '7134', 'P?ronnes-Lez-Binche', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1478, '8600', 'Pervijze', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1479, '5352', 'Perwez-Haillot', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1480, '5660', 'Pesche', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1481, '5590', 'Pessoux', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1482, '9800', 'Petegem-Aan-De-Leie', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1483, '9790', 'Petegem-Aan-De-Schelde', 'WORTEGEM-PETEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1484, '5660', 'P?tigny', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1485, '5555', 'Petit-Fays', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1486, '4280', 'Petit-Hallet', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1487, '4800', 'Petit-Rechain', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1488, '7090', 'Petit-Roeulx-Lez-Braine', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1489, '7181', 'Petit-Roeulx-Lez-Nivelles', 'SENEFFE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1490, '6692', 'Petit-Thier', 'VIELSALM', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1491, '5660', 'Petite-Chapelle', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1492, '1800', 'Peutie', 'VILVOORDE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1493, '7160', 'Pi?ton', 'CHAPELLE-LEZ-HERLAIMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1494, '1370', 'Pi?train', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1495, '1315', 'Pi?trebais', 'INCOURT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1496, '7904', 'Pipaix', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1497, '3700', 'Piringen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1498, '6240', 'Pironchamps', 'FARCIENNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1499, '4122', 'Plainevaux', 'NEUPR?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1500, '1380', 'Plancenoit', 'LASNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1501, '7782', 'Ploegsteert', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1502, '2275', 'Poederlee', 'LILLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1503, '9880', 'Poeke', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1504, '8920', 'Poelkapelle', 'LANGEMARK-POELKAPELLE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1505, '9850', 'Poesele', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1506, '9401', 'Pollare', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1507, '4800', 'Polleur', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1508, '4910', 'Polleur', 'THEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1509, '8647', 'Pollinkhove', 'LO-RENINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1510, '7322', 'Pommeroeul', 'BERNISSART', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1511, '5574', 'Pondr?me', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1512, '6250', 'Pont-De-Loup', 'AISEAU-PRESLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1513, '5380', 'Pontillas', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1514, '2382', 'Poppel', 'RAVELS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1515, '7760', 'Popuelles', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1516, '5370', 'Porcheresse', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1517, '6929', 'Porcheresse', 'DAVERDISSE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1518, '7760', 'Pottes', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1519, '4280', 'Poucet', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1520, '4171', 'Poulseur', 'COMBLAIN-AU-PONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1521, '6830', 'Poupehan', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1522, '4350', 'Pousset', 'REMICOURT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1523, '5660', 'Presgaux', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1524, '6250', 'Presles', 'AISEAU-PRESLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1525, '8972', 'Proven', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1526, '5650', 'Pry', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1527, '2242', 'Pulderbos', 'ZANDHOVEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1528, '2243', 'Pulle', 'ZANDHOVEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1529, '5530', 'Purnode', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1530, '5550', 'Pussemange', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1531, '2870', 'Puurs', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1532, '7540', 'Quartes', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1533, '1430', 'Quenast', 'REBECQ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1534, '4610', 'Queue-Du-Bois', 'BEYNE-HEUSAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1535, '7972', 'Quevaucamps', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1536, '7040', 'Qu?vy-Le-Grand', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1537, '7040', 'Qu?vy-Le-Petit', 'QU?VY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1538, '6792', 'Rachecourt', 'AUBANGE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1539, '4287', 'Racour', 'LINCENT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1540, '6532', 'Ragnies', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1541, '4987', 'Rahier', 'STOUMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1542, '7971', 'Ramegnies', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1543, '7520', 'Ramegnies-Chin', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1544, '4557', 'Ramelot', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1545, '1880', 'Ramsdonk', 'KAPELLE-OP-DEN-BOS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1546, '2230', 'Ramsel', 'HERSELT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1547, '8301', 'Ramskapelle', 'KNOKKE-HEIST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1548, '8620', 'Ramskapelle', 'NIEUWPOORT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1549, '6470', 'Rance', 'SIVRY-RANCE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1550, '6043', 'Ransart', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1551, '3470', 'Ransberg', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1552, '7804', 'Rebaix', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1553, '1430', 'Rebecq-Rognon', 'REBECQ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1554, '4780', 'Recht', 'SANKT-VITH', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1555, '6800', 'Recogne', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1556, '6890', 'Redu', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1557, '2840', 'Reet', 'RUMST', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1558, '3621', 'Rekem', 'LANAKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1559, '8930', 'Rekkem', 'MENEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1560, '1731', 'Relegem', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1561, '6800', 'Remagne', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1562, '3791', 'Remersdaal', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1563, '8647', 'Reninge', 'LO-RENINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1564, '8970', 'Reningelst', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1565, '6500', 'Renlies', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1566, '3950', 'Reppel', 'BOCHOLT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1567, '7134', 'Ressaix', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1568, '9551', 'Ressegem', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1569, '6927', 'Resteigne', 'TELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1570, '4621', 'Retinne', 'FL?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1571, '4790', 'Reuland', 'BURG-REULAND', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1572, '6210', 'R?ves', 'LES BONS VILLERS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1573, '5080', 'Rhisnes', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1574, '4600', 'Richelle', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1575, '5575', 'Rienne', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1576, '6464', 'Ri?zes', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1577, '3840', 'Rijkel', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1578, '3740', 'Rijkhoven', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1579, '2820', 'Rijmenam', 'BONHEIDEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1580, '3700', 'Riksingen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1581, '3202', 'Rillaar', 'AARSCHOT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1582, '5170', 'Rivi?re', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1583, '6460', 'Robechies', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1584, '6769', 'Robelmont', 'MEIX-DEVANT-VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1585, '4950', 'Robertville', 'WEISMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1586, '9630', 'Roborst', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1587, '6830', 'Rochehaut', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1588, '4761', 'Rocherath', 'B?LLINGEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1589, '4690', 'Roclenge-Sur-Geer', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1590, '4000', 'Rocourt', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1591, '8972', 'Roesbrugge-Haringe', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1592, '5651', 'Rogn?e', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1593, '7387', 'Roisin', 'HONNELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1594, '8460', 'Roksem', 'OUDENBURG', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1595, '8510', 'Rollegem', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1596, '8880', 'Rollegem-Kapelle', 'LEDEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1597, '4347', 'Roloux', 'FEXHE-LE-HAUT-CLOCHER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1598, '5600', 'Roly', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1599, '5600', 'Romedenne', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1600, '5680', 'Romer?e', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1601, '3730', 'Romershoven', 'HOESELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1602, '4624', 'Roms?e', 'FL?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1603, '7623', 'Rongy', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1604, '7090', 'Ronqui?res', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1605, '9932', 'Ronsele', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1606, '3370', 'Roosbeek', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1607, '5620', 'Ros?e', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1608, '6250', 'Roselies', 'AISEAU-PRESLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1609, '1331', 'Rosi?res', 'RIXENSART', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1610, '3740', 'Rosmeer', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1611, '4257', 'Rosoux-Crenwick', 'BERLOZ', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1612, '6730', 'Rossignol', 'TINTIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1613, '3650', 'Rotem', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1614, '4120', 'Rotheux-Rimi?re', 'NEUPR?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1615, '7601', 'Roucourt', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1616, '7120', 'Rouveroy', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1617, '4140', 'Rouvreux', 'SPRIMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1618, '6044', 'Roux', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1619, '1315', 'Roux-Miroir', 'INCOURT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1620, '6900', 'Roy', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1621, '9630', 'Rozebeke', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1622, '8020', 'Ruddervoorde', 'OOSTKAMP', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1623, '6760', 'Ruette', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1624, '9690', 'Ruien', 'KLUISBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1625, '1601', 'Ruisbroek', 'SINT-PIETERS-LEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1626, '2870', 'Ruisbroek', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1627, '3870', 'Rukkelingen-Loon', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1628, '6724', 'Rulles', 'HABAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1629, '8800', 'Rumbeke', 'ROESELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1630, '7540', 'Rumillies', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1631, '3454', 'Rummen', 'GEETBETS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1632, '3400', 'Rumsdorp', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1633, '3803', 'Runkelen', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1634, '9150', 'Rupelmonde', 'KRUIBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1635, '7750', 'Russeignies', 'MONT-DE-L\'ENCLUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1636, '3700', 'Rutten', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1637, '3798', 'S Gravenvoeren', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1638, '6221', 'Saint-Amand', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1639, '4606', 'Saint-Andr?', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1640, '5620', 'Saint-Aubin', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1641, '7034', 'Saint-Denis', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1642, '5081', 'Saint-Denis-Bovesse', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1643, '5640', 'Saint-G?rard', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1644, '5310', 'Saint-Germain', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1645, '1450', 'Saint-G?ry', 'CHASTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1646, '1370', 'Saint-Jean-Geest', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1647, '7730', 'Saint-L?ger', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1648, '5003', 'Saint-Marc', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1649, '6762', 'Saint-Mard', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1650, '5190', 'Saint-Martin', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1651, '7500', 'Saint-Maur', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1652, '6887', 'Saint-M?dard', 'HERBEUMONT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1653, '6800', 'Saint-Pierre', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1654, '4672', 'Saint-Remy', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1655, '6460', 'Saint-Remy', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1656, '1370', 'Saint-Remy-Geest', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1657, '7912', 'Saint-Sauveur', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1658, '5002', 'Saint-Servais', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1659, '4550', 'Saint-S?verin', 'NANDRIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1660, '7030', 'Saint-Symphorien', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1661, '7100', 'Saint-Vaast', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1662, '6730', 'Saint-Vincent', 'TINTIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1663, '6820', 'Sainte-C?cile', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1664, '6800', 'Sainte-Marie-Chevigny', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1665, '6740', 'Sainte-Marie-Sur-Semois', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1666, '1480', 'Saintes', 'TUBIZE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1667, '4671', 'Saive', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1668, '6460', 'Salles', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1669, '5600', 'Samart', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1670, '6982', 'Samr?e', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1671, '7080', 'Sars-La-Bruy?re', 'FRAMERIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1672, '6542', 'Sars-La-Buissi?re', 'LOBBES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1673, '5330', 'Sart-Bernard', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1674, '5575', 'Sart-Custinne', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1675, '1495', 'Sart-Dames-Avelines', 'VILLERS-LA-VILLE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1676, '5600', 'Sart-En-Fagne', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1677, '5070', 'Sart-Eustache', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1678, '4845', 'Sart-Lez-Spa', 'JALHAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1679, '5070', 'Sart-Saint-Laurent', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1680, '6470', 'Sautin', 'SIVRY-RANCE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1681, '5600', 'Sautour', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1682, '5030', 'Sauveni?re', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1683, '3290', 'Schaffen', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1684, '3732', 'Schalkhoven', 'HOESELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1685, '5364', 'Schaltin', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1686, '9820', 'Schelderode', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1687, '9860', 'Scheldewindeke', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1688, '9260', 'Schellebelle', 'WICHELEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1689, '9506', 'Schendelbeke', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1690, '1703', 'Schepdaal', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1691, '3270', 'Scherpenheuvel', 'SCHERPENHEUVEL-ZICHEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1692, '9200', 'Schoonaarde', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1693, '8433', 'Schore', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1694, '9688', 'Schorisse', 'MAARKEDAL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1695, '2223', 'Schriek', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1696, '8700', 'Schuiferskapelle', 'TIELT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1697, '3540', 'Schulen', 'HERK-DE-STAD', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1698, '4782', 'Sch?nberg', 'SANKT-VITH', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1699, '5300', 'Sclayn', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1700, '5361', 'Scy', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1701, '5300', 'Seilles', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1702, '6781', 'S?lange', 'MESSANCY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1703, '6596', 'Seloignes', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1704, '9890', 'Semmerzake', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1705, '6832', 'Sensenruth', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1706, '4557', 'Seny', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1707, '5630', 'Senzeilles', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1708, '6940', 'Septon', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1709, '4537', 'Seraing-Le-Ch?teau', 'VERLAINE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1710, '5590', 'Serinchamps', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1711, '9260', 'Serskamp', 'WICHELEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1712, '5521', 'Serville', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1713, '6640', 'Sibret', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1714, '6750', 'Signeulx', 'MUSSON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1715, '8340', 'Sijsele', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1716, '5630', 'Silenrieux', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1717, '9112', 'Sinaai-Waas', 'SINT-NIKLAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1718, '5377', 'Sinsin', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1719, '3040', 'Sint-Agatha-Rode', 'HULDENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1720, '2890', 'Sint-Amands', 'PUURS-SINT-AMANDS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1721, '9040', 'Sint-Amandsberg', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1722, '8200', 'Sint-Andries', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1723, '9550', 'Sint-Antelinks', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1724, '8710', 'Sint-Baafs-Vijve', 'WIELSBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1725, '9630', 'Sint-Blasius-Boekel', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1726, '8554', 'Sint-Denijs', 'ZWEVEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1727, '9630', 'Sint-Denijs-Boekel', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1728, '9051', 'Sint-Denijs-Westrem', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1729, '8793', 'Sint-Eloois-Vijve', 'WAREGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1730, '8880', 'Sint-Eloois-Winkel', 'LEDEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1731, '9200', 'Sint-Gillis-Dendermonde', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1732, '9620', 'Sint-Goriks-Oudenhove', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1733, '3730', 'Sint-Huibrechts-Hern', 'HOESELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1734, '3910', 'Sint-Huibrechts-Lille', 'PELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1735, '8600', 'Sint-Jacobs-Kapelle', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1736, '8900', 'Sint-Jan', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1737, '9982', 'Sint-Jan-In-Eremo', 'SINT-LAUREINS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1738, '2960', 'Sint-Job-In-\'T-Goor', 'BRECHT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1739, '8620', 'Sint-Joris', 'NIEUWPOORT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1740, '8730', 'Sint-Joris', 'BEERNEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1741, '3051', 'Sint-Joris-Weert', 'OUD-HEVERLEE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1742, '3390', 'Sint-Joris-Winge', 'TIELT-WINGE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1743, '1742', 'Sint-Katherina-Lombeek', 'TERNAT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1744, '9667', 'Sint-Kornelis-Horebeke', 'HOREBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1745, '8310', 'Sint-Kruis', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1746, '9042', 'Sint-Kruis-Winkel', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1747, '1750', 'Sint-Kwintens-Lennik', 'LENNIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1748, '3500', 'Sint-Lambrechts-Herk', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1749, '1600', 'Sint-Laureins-Berchem', 'SINT-PIETERS-LEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1750, '2960', 'Sint-Lenaarts', 'BRECHT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1751, '9550', 'Sint-Lievens-Esse', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1752, '9981', 'Sint-Margriete', 'SINT-LAUREINS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1753, '3300', 'Sint-Margriete-Houtem', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1754, '3470', 'Sint-Margriete-Houtem', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1755, '9667', 'Sint-Maria-Horebeke', 'HOREBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1756, '9630', 'Sint-Maria-Latem', 'ZWALM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1757, '9570', 'Sint-Maria-Lierde', 'LIERDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1758, '9620', 'Sint-Maria-Oudenhove', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1759, '9660', 'Sint-Maria-Oudenhove', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1760, '1700', 'Sint-Martens-Bodegem', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1761, '9800', 'Sint-Martens-Leerne', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1762, '1750', 'Sint-Martens-Lennik', 'LENNIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1763, '9572', 'Sint-Martens-Lierde', 'LIERDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1764, '3790', 'Sint-Martens-Voeren', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1765, '8200', 'Sint-Michiels', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1766, '9170', 'Sint-Pauwels', 'SINT-GILLIS-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1767, '1541', 'Sint-Pieters-Kapelle', 'HERNE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1768, '8433', 'Sint-Pieters-Kapelle', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1769, '3220', 'Sint-Pieters-Rode', 'HOLSBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1770, '3792', 'Sint-Pieters-Voeren', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1771, '8690', 'Sint-Rijkers', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1772, '1932', 'Sint-Stevens-Woluwe', 'ZAVENTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1773, '1700', 'Sint-Ulriks-Kapelle', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1774, '4851', 'Sippenaeken', 'PLOMBI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1775, '7332', 'Sirault', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1776, '6470', 'Sivry', 'SIVRY-RANCE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1777, '9940', 'Sleidinge', 'EVERGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1778, '8433', 'Slijpe', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1779, '4450', 'Slins', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1780, '3700', 'Sluizen', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1781, '9506', 'Smeerebbe-Vloerzegem', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1782, '9340', 'Smetlede', 'LEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1783, '6890', 'Smuid', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1784, '8470', 'Snaaskerke', 'GISTEL', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1785, '8490', 'Snellegem', 'JABBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1786, '4557', 'Soheit-Tinlot', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1787, '6920', 'Sohier', 'WELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1788, '4861', 'Soiron', 'PEPINSTER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1789, '6500', 'Solre-Saint-G?ry', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1790, '6560', 'Solre-Sur-Sambre', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1791, '6769', 'Sommethonne', 'MEIX-DEVANT-VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1792, '5523', 'Sommi?re', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1793, '5651', 'Somz?e', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1794, '5340', 'Sor?e', 'GESVES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1795, '5333', 'Sorinne-La-Longue', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1796, '5503', 'Sorinnes', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1797, '5537', 'Sosoye', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1798, '4920', 'Sougn?-Remouchamps', 'AYWAILLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1799, '5680', 'Soulme', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1800, '5630', 'Soumoy', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1801, '4950', 'Sourbrodt', 'WEISMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1802, '6182', 'Souvret', 'COURCELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1803, '5590', 'Sovet', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1804, '6997', 'Soy', 'EREZ?E', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1805, '5150', 'Soye', 'FLOREFFE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1806, '3510', 'Spalbeek', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1807, '7032', 'Spiennes', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1808, '8587', 'Spiere', 'SPIERE-HELKIJN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1809, '5530', 'Spontin', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1810, '5190', 'Spy', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1811, '8490', 'Stalhille', 'JABBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1812, '7973', 'Stambruges', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1813, '5646', 'Stave', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1814, '8691', 'Stavele', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1815, '9140', 'Steendorp', 'TEMSE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1816, '1840', 'Steenhuffel', 'LONDERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1817, '9550', 'Steenhuize-Wijnhuize', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1818, '8630', 'Steenkerke', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1819, '7090', 'Steenkerque', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1820, '4801', 'Stembert', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1821, '8400', 'Stene', 'OOSTENDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1822, '1933', 'Sterrebeek', 'ZAVENTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1823, '3512', 'Stevoort', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1824, '3650', 'Stokkem', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1825, '3511', 'Stokrooie', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1826, '6887', 'Straimont', 'HERBEUMONT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1827, '6511', 'Str?e', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1828, '4577', 'Str?e-Lez-Huy', 'MODAVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1829, '7110', 'Str?py-Bracquegnies', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1830, '9620', 'Strijpen', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1831, '1760', 'Strijtem', 'ROOSDAAL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1832, '1853', 'Strombeek-Bever', 'GRIMBERGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1833, '8600', 'Stuivekenskerke', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1834, '5020', 'Suarl?e', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25');
INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `updated_at`, `created_at`) VALUES
(1835, '5550', 'Sugny', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1836, '5600', 'Surice', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1837, '6812', 'Suxy', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1838, '6661', 'Tailles', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1839, '7618', 'Taintignies', 'RUMES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1840, '5060', 'Tamines', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1841, '5651', 'Tarcienne', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1842, '4163', 'Tavier', 'ANTHISNES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1843, '5310', 'Taviers', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1844, '6662', 'Tavigny', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1845, '7520', 'Templeuve', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1846, '5020', 'Temploux', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1847, '1790', 'Teralfene', 'AFFLIGEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1848, '2840', 'Terhagen', 'RUMST', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1849, '6813', 'Termes', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1850, '7333', 'Tertre', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1851, '4560', 'Terwagne', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1852, '3272', 'Testelt', 'SCHERPENHEUVEL-ZICHEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1853, '3793', 'Teuven', 'VOEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1854, '6717', 'Thiaumont', 'ATTERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1855, '7070', 'Thieu', 'LE ROEULX', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1856, '7901', 'Thieulain', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1857, '7061', 'Thieusies', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1858, '6230', 'Thim?on', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1859, '4890', 'Thimister', 'THIMISTER-CLERMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1860, '7533', 'Thimougies', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1861, '1402', 'Thines', 'NIVELLES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1862, '6500', 'Thirimont', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1863, '4280', 'Thisnes', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1864, '4791', 'Thommen', 'BURG-REULAND', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1865, '5300', 'Thon', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1866, '1360', 'Thorembais-Les-B?guines', 'PERWEZ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1867, '1360', 'Thorembais-Saint-Trond', 'PERWEZ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1868, '7830', 'Thoricourt', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1869, '6536', 'Thuillies', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1870, '7350', 'Thulin', 'HENSIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1871, '7971', 'Thumaide', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1872, '5621', 'Thy-Le-Baudouin', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1873, '5651', 'Thy-Le-Ch?teau', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1874, '5502', 'Thynes', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1875, '4367', 'Thys', 'CRISN?E', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1876, '8573', 'Tiegem', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1877, '2460', 'Tielen', 'KASTERLEE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1878, '9140', 'Tielrode', 'TEMSE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1879, '3390', 'Tielt', 'TIELT-WINGE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1880, '4630', 'Tign?e', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1881, '4500', 'Tihange', 'HUY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1882, '3150', 'Tildonk', 'HAACHT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1883, '4130', 'Tilff', 'ESNEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1884, '6680', 'Tillet', 'SAINTE-ODE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1885, '4420', 'Tilleur', 'SAINT-NICOLAS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1886, '5380', 'Tillier', 'FERNELMONT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1887, '1495', 'Tilly', 'VILLERS-LA-VILLE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1888, '6637', 'Tintange', 'FAUVILLERS', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1889, '2830', 'Tisselt', 'WILLEBROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1890, '6700', 'Toernich', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1891, '6941', 'Tohogne', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1892, '1570', 'Tollembeek', 'GALMAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1893, '2260', 'Tongerlo', 'WESTERLO', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1894, '3960', 'Tongerlo', 'BREE', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1895, '7951', 'Tongre-Notre-Dame', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1896, '7950', 'Tongre-Saint-Martin', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1897, '5140', 'Tongrinne', 'SOMBREFFE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1898, '6717', 'Tontelange', 'ATTERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1899, '6767', 'Torgny', 'ROUVROY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1900, '4263', 'Tourinne', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1901, '1320', 'Tourinnes-La-Grosse', 'BEAUVECHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1902, '1457', 'Tourinnes-Saint-Lambert', 'WALHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1903, '6840', 'Tournay', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1904, '7904', 'Tourpes', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1905, '6890', 'Transinne', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1906, '6183', 'Trazegnies', 'COURCELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1907, '5670', 'Treignes', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1908, '4670', 'Trembleur', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1909, '7100', 'Trivi?res', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1910, '4280', 'Trogn?e', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1911, '6833', 'Ucimont', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1912, '3631', 'Uikhoven', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1913, '9290', 'Uitbergen', 'BERLARE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1914, '8370', 'Uitkerke', 'BLANKENBERGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1915, '3832', 'Ulbeek', 'WELLEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1916, '5310', 'Upigny', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1917, '9910', 'Ursel', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1918, '3054', 'Vaalbeek', 'OUD-HEVERLEE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1919, '3770', 'Val-Meer', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1920, '6741', 'Vance', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1921, '2431', 'Varendonk', 'LAAKDAL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1922, '8490', 'Varsenare', 'JABBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1923, '5680', 'Vaucelles', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1924, '7536', 'Vaulx', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1925, '6462', 'Vaulx-Lez-Chimay', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1926, '6960', 'Vaux-Chavanne', 'MANHAY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1927, '4530', 'Vaux-Et-Borset', 'VILLERS-LE-BOUILLET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1928, '6640', 'Vaux-Lez-Rosi?res', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1929, '4051', 'Vaux-Sous-Ch?vremont', 'CHAUDFONTAINE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1930, '3870', 'Vechmaal', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1931, '5020', 'Vedrin', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1932, '2431', 'Veerle', 'LAAKDAL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1933, '5060', 'Velaine-Sur-Sambre', 'SAMBREVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1934, '7760', 'Velaines', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1935, '8210', 'Veldegem', 'ZEDELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1936, '3620', 'Veldwezelt', 'LANAKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1937, '7120', 'Vellereille-Le-Sec', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1938, '7120', 'Vellereille-Les-Brayeux', 'ESTINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1939, '3806', 'Velm', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1940, '4460', 'Velroux', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1941, '3020', 'Veltem-Beisem', 'HERENT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1942, '9620', 'Velzeke-Ruddershove', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1943, '5575', 'Vencimont', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1944, '6440', 'Vergnies', 'FROIDCHAPELLE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1945, '5370', 'Verl?e', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1946, '9130', 'Verrebroek', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1947, '3370', 'Vertrijk', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1948, '6870', 'Vesqueville', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1949, '3870', 'Veulen', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(1950, '5300', 'Vezin', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1951, '7538', 'Vezon', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1952, '9500', 'Viane', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1953, '8570', 'Vichte', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1954, '4317', 'Viemme', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1955, '2240', 'Viersel', 'ZANDHOVEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(1956, '4577', 'Vierset-Barse', 'MODAVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1957, '5670', 'Vierves-Sur-Viroin', 'VIROINVAL', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1958, '6230', 'Viesville', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1959, '1472', 'Vieux-Genappe', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1960, '4530', 'Vieux-Waleffe', 'VILLERS-LE-BOUILLET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1961, '4190', 'Vieuxville', 'FERRI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1962, '6890', 'Villance', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1963, '4260', 'Ville-En-Hesbaye', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1964, '7322', 'Ville-Pommeroeul', 'BERNISSART', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1965, '7070', 'Ville-Sur-Haine', 'LE ROEULX', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1966, '7334', 'Villerot', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1967, '4161', 'Villers-Aux-Tours', 'ANTHISNES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1968, '5630', 'Villers-Deux-Eglises', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1969, '6823', 'Villers-Devant-Orval', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1970, '5600', 'Villers-En-Fagne', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1971, '4340', 'Villers-L\'Ev?que', 'AWANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1972, '6600', 'Villers-La-Bonne-Eau', 'BASTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1973, '6769', 'Villers-La-Loue', 'MEIX-DEVANT-VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1974, '6460', 'Villers-La-Tour', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1975, '5600', 'Villers-Le-Gambon', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1976, '4280', 'Villers-Le-Peuplier', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1977, '4550', 'Villers-Le-Temple', 'NANDRIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1978, '5080', 'Villers-Lez-Heest', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1979, '7812', 'Villers-Notre-Dame', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1980, '6210', 'Villers-Perwin', 'LES BONS VILLERS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1981, '6280', 'Villers-Poterie', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1982, '7812', 'Villers-Saint-Amand', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1983, '7031', 'Villers-Saint-Ghislain', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1984, '4453', 'Villers-Saint-Sim?on', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1985, '6941', 'Villers-Sainte-Gertrude', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1986, '5580', 'Villers-Sur-Lesse', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1987, '6740', 'Villers-Sur-Semois', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1988, '4520', 'Vinalmont', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1989, '9921', 'Vinderhoute', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1990, '8630', 'Vinkem', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1991, '9800', 'Vinkt', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1992, '6461', 'Virelles', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(1993, '1460', 'Virginal-Samme', 'ITTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1994, '3300', 'Vissenaken', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(1995, '5070', 'Vitrival', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(1996, '4683', 'Vivegnis', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(1997, '6833', 'Vivy', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(1998, '8600', 'Vladslo', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(1999, '8908', 'Vlamertinge', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2000, '9420', 'Vlekkem', 'ERPE-MERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2001, '1602', 'Vlezenbeek', 'SINT-PIETERS-LEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2002, '3724', 'Vliermaal', 'KORTESSEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2003, '3721', 'Vliermaalroot', 'KORTESSEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2004, '9520', 'Vlierzele', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2005, '3770', 'Vlijtingen', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2006, '2340', 'Vlimmeren', 'BEERSE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2007, '8421', 'Vlissegem', 'DE HAAN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2008, '5600', 'Vodec?e', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2009, '5680', 'Vodel?e', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2010, '5650', 'Vogen?e', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2011, '9700', 'Volkegem', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2012, '1570', 'Vollezele', 'GALMAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2013, '5570', 'Von?che', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2014, '9400', 'Voorde', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2015, '8902', 'Voormezele', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2016, '3840', 'Voort', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2017, '4347', 'Voroux-Goreux', 'FEXHE-LE-HAUT-CLOCHER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2018, '4451', 'Voroux-Lez-Liers', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2019, '3890', 'Vorsen', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2020, '2430', 'Vorst', 'LAAKDAL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2021, '9850', 'Vosselare', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2022, '3080', 'Vossem', 'TERVUREN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2023, '4041', 'Vottem', 'HERSTAL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2024, '9120', 'Vrasene', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2025, '2531', 'Vremde', 'BOECHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2026, '3700', 'Vreren', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2027, '3770', 'Vroenhoven', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2028, '3630', 'Vucht', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2029, '9890', 'Vurste', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2030, '4570', 'Vyle-Et-Tharoul', 'MARCHIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2031, '3473', 'Waanrode', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2032, '9506', 'Waarbeke', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2033, '8020', 'Waardamme', 'OOSTKAMP', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2034, '2550', 'Waarloos', 'KONTICH', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2035, '8581', 'Waarmaarde', 'AVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2036, '9950', 'Waarschoot', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2037, '3401', 'Waasmont', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2038, '7784', 'Waasten', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2039, '7971', 'Wadelincourt', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2040, '6223', 'Wagnel?e', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2041, '6900', 'Waha', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2042, '5377', 'Waillet', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2043, '8720', 'Wakken', 'DENTERGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2044, '2800', 'Walem', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2045, '1457', 'Walhain-Saint-Paul', 'WALHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2046, '4711', 'Walhorn', 'LONTZEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2047, '3401', 'Walsbets', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2048, '3401', 'Walshoutem', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2049, '3740', 'Waltwilder', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2050, '1741', 'Wambeek', 'TERNAT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2051, '5570', 'Wancennes', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2052, '4020', 'Wandre', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2053, '6224', 'Wanferc?e-Baulet', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2054, '3400', 'Wange', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2055, '6220', 'Wangenies', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2056, '5564', 'Wanlin', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2057, '4980', 'Wanne', 'TROIS-PONTS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2058, '7861', 'Wannebecq', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2059, '9772', 'Wannegem-Lede', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2060, '4280', 'Wansin', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2061, '9340', 'Wanzele', 'LEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2062, '7548', 'Warchin', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2063, '7740', 'Warcoing', 'PECQ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2064, '6600', 'Wardin', 'BASTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2065, '4217', 'Waret-L\'Ev?que', 'H?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2066, '5310', 'Waret-La-Chauss?e', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2067, '5080', 'Warisoulx', 'LA BRUY?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2068, '5537', 'Warnant', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2069, '4530', 'Warnant-Dreye', 'VILLERS-LE-BOUILLET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2070, '7340', 'Warquignies', 'COLFONTAINE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2071, '4608', 'Warsage', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2072, '4590', 'Warz?e', 'OUFFET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2073, '7340', 'Wasmes', 'COLFONTAINE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2074, '7604', 'Wasmes-Audemez-Briffoeil', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2075, '7390', 'Wasmuel', 'QUAREGNON', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2076, '9988', 'Waterland-Oudeman', 'SINT-LAUREINS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2077, '9988', 'Watervliet', 'SINT-LAUREINS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2078, '8978', 'Watou', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2079, '7910', 'Wattripont', 'FRASNES-LEZ-ANVAING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2080, '7131', 'Waudrez', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2081, '5540', 'Waulsort', 'HASTI?RE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2082, '1440', 'Wauthier-Braine', 'BRAINE-LE-CH?TEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2083, '5580', 'Wavreille', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2084, '6210', 'Wayaux', 'LES BONS VILLERS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2085, '1474', 'Ways', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2086, '3290', 'Webbekom', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2087, '2275', 'Wechelderzande', 'LILLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2088, '2381', 'Weelde', 'RAVELS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2089, '1982', 'Weerde', 'ZEMST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2090, '2880', 'Weert', 'BORNEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2091, '4860', 'Wegnez', 'PEPINSTER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2092, '5523', 'Weillen', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2093, '9700', 'Welden', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2094, '9473', 'Welle', 'DENDERLEEUW', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2095, '8420', 'Wenduine', 'DE HAAN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2096, '5100', 'W?pion', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2097, '4190', 'Werbomont', 'FERRI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2098, '3118', 'Werchter', 'ROTSELAAR', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2099, '6940', 'W?ris', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2100, '8610', 'Werken', 'KORTEMARK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2101, '3730', 'Werm', 'HOESELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2102, '3150', 'Wespelaar', 'HAACHT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2103, '8434', 'Westende', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2104, '8300', 'Westkapelle', 'KNOKKE-HEIST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2105, '8460', 'Westkerke', 'OUDENBURG', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2106, '2390', 'Westmalle', 'MALLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2107, '2235', 'Westmeerbeek', 'HULSHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2108, '8954', 'Westouter', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2109, '9230', 'Westrem', 'WETTEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2110, '8840', 'Westrozebeke', 'STADEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2111, '8640', 'Westvleteren', 'VLETEREN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2112, '7620', 'Wez-Velvain', 'BRUNEHAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2113, '3111', 'Wezemaal', 'ROTSELAAR', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2114, '3401', 'Wezeren', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2115, '6666', 'Wibrin', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2116, '3700', 'Widooie', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2117, '2222', 'Wiekevorst', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2118, '5100', 'Wierde', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2119, '7608', 'Wiers', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2120, '5571', 'Wiesme', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2121, '9280', 'Wieze', 'LEBBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2122, '7370', 'Wih?ries', 'DOUR', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2123, '4452', 'Wihogne', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2124, '3990', 'Wijchmaal', 'PEER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2125, '3850', 'Wijer', 'NIEUWERKERKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2126, '3018', 'Wijgmaal', 'LEUVEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2127, '3670', 'Wijshagen', 'OUDSBERGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2128, '8953', 'Wijtschate', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2129, '3803', 'Wilderen', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2130, '7904', 'Willaupuis', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2131, '3370', 'Willebringen', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2132, '7506', 'Willemeau', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2133, '5575', 'Willerzie', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2134, '2610', 'Wilrijk', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2135, '3012', 'Wilsele', 'LEUVEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2136, '8431', 'Wilskerke', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2137, '3501', 'Wimmertingen', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2138, '5570', 'Winenne', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2139, '3020', 'Winksele', 'HERENT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2140, '3722', 'Wintershoven', 'KORTESSEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2141, '6860', 'Witry', 'L?GLISE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2142, '7890', 'Wodecq', 'ELLEZELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2143, '8640', 'Woesten', 'VLETEREN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2144, '6780', 'Wolkrange', 'MESSANCY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2145, '1861', 'Wolvertem', 'MEISE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2146, '3350', 'Wommersom', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2147, '4690', 'Wonck', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2148, '9032', 'Wondelgem', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2149, '9800', 'Wontergem', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2150, '9790', 'Wortegem', 'WORTEGEM-PETEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2151, '2323', 'Wortel', 'HOOGSTRATEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2152, '9550', 'Woubrechtegem', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2153, '8600', 'Woumen', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2154, '8670', 'Wulpen', 'KOKSIJDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2155, '8952', 'Wulvergem', 'HEUVELLAND', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2156, '8630', 'Wulveringem', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2157, '4652', 'Xhendelesse', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2158, '4432', 'Xhendremael', 'ANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2159, '4190', 'Xhoris', 'FERRI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2160, '4550', 'Yern?e-Fraineux', 'NANDRIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2161, '5650', 'Yves-Gomez?e', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2162, '9080', 'Zaffelare', 'LOCHRISTI', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2163, '9506', 'Zandbergen', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2164, '8680', 'Zande', 'KOEKELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2165, '2040', 'Zandvliet', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2166, '8400', 'Zandvoorde', 'OOSTENDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2167, '8980', 'Zandvoorde', 'ZONNEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2168, '9500', 'Zarlardinge', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2169, '8610', 'Zarren', 'KORTEMARK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2170, '8380', 'Zeebrugge', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2171, '9660', 'Zegelsem', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2172, '3545', 'Zelem', 'HALEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2173, '1731', 'Zellik', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2174, '3800', 'Zepperen', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2175, '8490', 'Zerkegem', 'JABBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2176, '1370', 'Z?trud-Lumay', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2177, '8470', 'Zevekote', 'GISTEL', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2178, '9080', 'Zeveneken', 'LOCHRISTI', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2179, '9800', 'Zeveren', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2180, '9840', 'Zevergem', 'DE PINTE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2181, '3271', 'Zichem', 'SCHERPENHEUVEL-ZICHEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2182, '3770', 'Zichen-Zussen-Bolder', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2183, '8902', 'Zillebeke', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2184, '9750', 'Zingem', 'KRUISEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2185, '2260', 'Zoerle-Parwijs', 'WESTERLO', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2186, '3550', 'Zolder', 'HEUSDEN-ZOLDER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2187, '9930', 'Zomergem', 'LIEVEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2188, '9520', 'Zonnegem', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2189, '8630', 'Zoutenaaie', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2190, '8904', 'Zuidschote', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2191, '9690', 'Zulzeke', 'KLUISBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2192, '8750', 'Zwevezele', 'WINGENE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2193, '9052', 'Zwijnaarde', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2194, '9300', 'Aalst', 'AALST', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2195, '9880', 'Aalter', 'AALTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2196, '3200', 'Aarschot', 'AARSCHOT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2197, '2630', 'Aartselaar', 'AARTSELAAR', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2198, '1790', 'Affligem', 'AFFLIGEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2199, '3570', 'Alken', 'ALKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2200, '8690', 'Alveringem', 'ALVERINGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2201, '4540', 'Amay', 'AMAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2202, '4770', 'Amel', 'AMEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2203, '5300', 'Andenne', 'ANDENNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2204, '1070', 'Anderlecht', 'ANDERLECHT', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2205, '6150', 'Anderlues', 'ANDERLUES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2206, '5537', 'Anh?e', 'ANH?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2207, '4430', 'Ans', 'ANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2208, '4160', 'Anthisnes', 'ANTHISNES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2209, '7640', 'Antoing', 'ANTOING', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2210, '2000', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2211, '2018', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2212, '2020', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2213, '2030', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2214, '2040', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2215, '2050', 'Antwerpen - Linkeroever', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2216, '2060', 'Antwerpen', 'ANTWERPEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2217, '8570', 'Anzegem', 'ANZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2218, '8850', 'Ardooie', 'ARDOOIE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2219, '2370', 'Arendonk', 'ARENDONK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2220, '6700', 'Arlon', 'ARLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2221, '3665', 'As', 'AS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2222, '1730', 'Asse', 'ASSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2223, '9960', 'Assenede', 'ASSENEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2224, '5330', 'Assesse', 'ASSESSE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2225, '7800', 'Ath', 'ATH', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2226, '6717', 'Attert', 'ATTERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2227, '6790', 'Aubange', 'AUBANGE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2228, '4880', 'Aubel', 'AUBEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2229, '8580', 'Avelgem', 'AVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2230, '4340', 'Awans', 'AWANS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2231, '4920', 'Aywaille', 'AYWAILLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2232, '2387', 'Baarle-Hertog', 'BAARLE-HERTOG', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2233, '4837', 'Baelen', 'BAELEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2234, '2490', 'Balen', 'BALEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2235, '4690', 'Bassenge', 'BASSENGE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2236, '6600', 'Bastogne', 'BASTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2237, '6500', 'Beaumont', 'BEAUMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2238, '5570', 'Beauraing', 'BEAURAING', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2239, '1320', 'Beauvechain', 'BEAUVECHAIN', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2240, '8730', 'Beernem', 'BEERNEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2241, '2340', 'Beerse', 'BEERSE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2242, '1650', 'Beersel', 'BEERSEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2243, '3130', 'Begijnendijk', 'BEGIJNENDIJK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2244, '3460', 'Bekkevoort', 'BEKKEVOORT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2245, '7970', 'Beloeil', 'BELOEIL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2246, '3580', 'Beringen', 'BERINGEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2247, '2590', 'Berlaar', 'BERLAAR', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2248, '9290', 'Berlare', 'BERLARE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2249, '4257', 'Berloz', 'BERLOZ', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2250, '7320', 'Bernissart', 'BERNISSART', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2251, '3060', 'Bertem', 'BERTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2252, '6687', 'Bertogne', 'BERTOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2253, '6880', 'Bertrix', 'BERTRIX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2254, '1547', 'Bever', 'BEVER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2255, '9120', 'Beveren-Waas', 'BEVEREN-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2256, '4610', 'Beyne-Heusay', 'BEYNE-HEUSAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2257, '3360', 'Bierbeek', 'BIERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2258, '5555', 'Bi?vre', 'BI?VRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2259, '3740', 'Bilzen', 'BILZEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2260, '7130', 'Binche', 'BINCHE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2261, '8370', 'Blankenberge', 'BLANKENBERGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2262, '4670', 'Bl?gny', 'BL?GNY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2263, '3950', 'Bocholt', 'BOCHOLT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2264, '2530', 'Boechout', 'BOECHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2265, '2820', 'Bonheiden', 'BONHEIDEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2266, '2850', 'Boom', 'BOOM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2267, '3190', 'Boortmeerbeek', 'BOORTMEERBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2268, '3840', 'Borgloon', 'BORGLOON', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2269, '2880', 'Bornem', 'BORNEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2270, '2150', 'Borsbeek', 'BORSBEEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2271, '6830', 'Bouillon', 'BOUILLON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2272, '7300', 'Boussu', 'BOUSSU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2273, '3370', 'Boutersem', 'BOUTERSEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2274, '1420', 'Braine-L\'Alleud', 'BRAINE-L\'ALLEUD', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2275, '1440', 'Braine-Le-Ch?teau', 'BRAINE-LE-CH?TEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2276, '7090', 'Braine-Le-Comte', 'BRAINE-LE-COMTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2277, '4260', 'Braives', 'BRAIVES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2278, '9660', 'Brakel', 'BRAKEL', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2279, '2930', 'Brasschaat', 'BRASSCHAAT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2280, '2960', 'Brecht', 'BRECHT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2281, '8450', 'Bredene', 'BREDENE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2282, '3960', 'Bree', 'BREE', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2283, '7940', 'Brugelette', 'BRUGELETTE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2284, '8000', 'Brugge', 'BRUGGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2285, '1000', 'Brussel', 'BRUSSEL', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2286, '9255', 'Buggenhout', 'BUGGENHOUT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2287, '4210', 'Burdinne', 'BURDINNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2288, '4750', 'Butgenbach', 'BUTGENBACH', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2289, '4760', 'B?llingen', 'B?LLINGEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2290, '7760', 'Celles', 'CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2291, '5630', 'Cerfontaine', 'CERFONTAINE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2292, '7160', 'Chapelle-Lez-Herlaimont', 'CHAPELLE-LEZ-HERLAIMONT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2293, '6000', 'Charleroi', 'CHARLEROI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2294, '6200', 'Ch?telet', 'CH?TELET', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2295, '4050', 'Chaudfontaine', 'CHAUDFONTAINE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2296, '1325', 'Chaumont-Gistoux', 'CHAUMONT-GISTOUX', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2297, '7950', 'Chi?vres', 'CHI?VRES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2298, '6460', 'Chimay', 'CHIMAY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2299, '6810', 'Chiny', 'CHINY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2300, '5590', 'Ciney', 'CINEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2301, '4560', 'Clavier', 'CLAVIER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2302, '7340', 'Colfontaine', 'COLFONTAINE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2303, '4170', 'Comblain-Au-Pont', 'COMBLAIN-AU-PONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2304, '6180', 'Courcelles', 'COURCELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2305, '1490', 'Court-Saint-Etienne', 'COURT-SAINT-ETIENNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2306, '5660', 'Couvin', 'COUVIN', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2307, '4367', 'Crisn?e', 'CRISN?E', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2308, '4607', 'Dalhem', 'DALHEM', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2309, '8340', 'Damme', 'DAMME', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2310, '6929', 'Daverdisse', 'DAVERDISSE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2311, '8420', 'De Haan', 'DE HAAN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2312, '8660', 'De Panne', 'DE PANNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2313, '9840', 'De Pinte', 'DE PINTE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2314, '8540', 'Deerlijk', 'DEERLIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2315, '9800', 'Deinze', 'DEINZE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2316, '9470', 'Denderleeuw', 'DENDERLEEUW', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2317, '9200', 'Dendermonde', 'DENDERMONDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2318, '8720', 'Dentergem', 'DENTERGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2319, '2480', 'Dessel', 'DESSEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2320, '9070', 'Destelbergen', 'DESTELBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2321, '3590', 'Diepenbeek', 'DIEPENBEEK', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2322, '3290', 'Diest', 'DIEST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2323, '8600', 'Diksmuide', 'DIKSMUIDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2324, '1700', 'Dilbeek', 'DILBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2325, '3650', 'Dilsen-Stokkem', 'DILSEN-STOKKEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2326, '5500', 'Dinant', 'DINANT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2327, '4820', 'Dison', 'DISON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2328, '5680', 'Doische', 'DOISCHE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2329, '4357', 'Donceel', 'DONCEEL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2330, '7370', 'Dour', 'DOUR', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2331, '1620', 'Drogenbos', 'DROGENBOS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2332, '2570', 'Duffel', 'DUFFEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2333, '6940', 'Durbuy', 'DURBUY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2334, '2650', 'Edegem', 'EDEGEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2335, '7850', 'Edingen', 'EDINGEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2336, '9900', 'Eeklo', 'EEKLO', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2337, '5310', 'Eghez?e', 'EGHEZ?E', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2338, '7890', 'Ellezelles', 'ELLEZELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2339, '1050', 'Elsene', 'ELSENE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2340, '4480', 'Engis', 'ENGIS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2341, '6997', 'Erez?e', 'EREZ?E', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2342, '6560', 'Erquelinnes', 'ERQUELINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2343, '4130', 'Esneux', 'ESNEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2344, '2910', 'Essen', 'ESSEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2345, '7730', 'Estaimpuis', 'ESTAIMPUIS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2346, '6740', 'Etalle', 'ETALLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2347, '1040', 'Etterbeek', 'ETTERBEEK', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2348, '4700', 'Eupen', 'EUPEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2349, '1140', 'Evere', 'EVERE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2350, '9940', 'Evergem', 'EVERGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2351, '4317', 'Faimes', 'FAIMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2352, '6240', 'Farciennes', 'FARCIENNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2353, '6637', 'Fauvillers', 'FAUVILLERS', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2354, '4190', 'Ferri?res', 'FERRI?RES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2355, '4347', 'Fexhe-Le-Haut-Clocher', 'FEXHE-LE-HAUT-CLOCHER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2356, '4620', 'Fl?ron', 'FL?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2357, '6220', 'Fleurus', 'FLEURUS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2358, '5150', 'Floreffe', 'FLOREFFE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2359, '5620', 'Florennes', 'FLORENNES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2360, '6820', 'Florenville', 'FLORENVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2361, '6140', 'Fontaine-L\'Ev?que', 'FONTAINE-L\'EV?QUE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2362, '5070', 'Fosses-La-Ville', 'FOSSES-LA-VILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2363, '7080', 'Frameries', 'FRAMERIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2364, '6440', 'Froidchapelle', 'FROIDCHAPELLE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2365, '1570', 'Galmaarden', 'GALMAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2366, '1083', 'Ganshoren', 'GANSHOREN', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2367, '9890', 'Gavere', 'GAVERE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2368, '5575', 'Gedinne', 'GEDINNE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2369, '2440', 'Geel', 'GEEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2370, '4250', 'Geer', 'GEER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2371, '3450', 'Geetbets', 'GEETBETS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2372, '5030', 'Gembloux', 'GEMBLOUX', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2373, '1470', 'Genappe', 'GENAPPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2374, '3600', 'Genk', 'GENK', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2375, '9000', 'Gent', 'GENT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2376, '9500', 'Geraardsbergen', 'GERAARDSBERGEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2377, '6280', 'Gerpinnes', 'GERPINNES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2378, '5340', 'Gesves', 'GESVES', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2379, '3890', 'Gingelom', 'GINGELOM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2380, '8470', 'Gistel', 'GISTEL', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2381, '3380', 'Glabbeek', 'GLABBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2382, '1755', 'Gooik', 'GOOIK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2383, '6670', 'Gouvy', 'GOUVY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2384, '4460', 'Gr?ce-Hollogne', 'GR?CE-HOLLOGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2385, '1390', 'Grez-Doiceau', 'GREZ-DOICEAU', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2386, '1850', 'Grimbergen', 'GRIMBERGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2387, '2280', 'Grobbendonk', 'GROBBENDONK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2388, '3150', 'Haacht', 'HAACHT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2389, '9450', 'Haaltert', 'HAALTERT', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2390, '3545', 'Halen', 'HALEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2391, '1500', 'Halle', 'HALLE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2392, '6120', 'Ham-Sur-Heure', 'HAM-SUR-HEURE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2393, '9220', 'Hamme', 'HAMME', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2394, '4180', 'Hamoir', 'HAMOIR', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2395, '5360', 'Hamois', 'HAMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2396, '4280', 'Hannut', 'HANNUT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2397, '8530', 'Harelbeke', 'HARELBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2398, '3500', 'Hasselt', 'HASSELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2399, '5370', 'Havelange', 'HAVELANGE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2400, '3870', 'Heers', 'HEERS', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2401, '2220', 'Heist-Op-Den-Berg', 'HEIST-OP-DEN-BERG', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2402, '2620', 'Hemiksem', 'HEMIKSEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2403, '7350', 'Hensies', 'HENSIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2404, '6887', 'Herbeumont', 'HERBEUMONT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2405, '3020', 'Herent', 'HERENT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2406, '2200', 'Herentals', 'HERENTALS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2407, '2270', 'Herenthout', 'HERENTHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2408, '3540', 'Herk-De-Stad', 'HERK-DE-STAD', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2409, '1540', 'Herne', 'HERNE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2410, '4217', 'H?ron', 'H?RON', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2411, '2230', 'Herselt', 'HERSELT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2412, '4040', 'Herstal', 'HERSTAL', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2413, '3717', 'Herstappe', 'HERSTAPPE', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2414, '4650', 'Herve', 'HERVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2415, '9550', 'Herzele', 'HERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2416, '3550', 'Heusden-Zolder', 'HEUSDEN-ZOLDER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2417, '3320', 'Hoegaarden', 'HOEGAARDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2418, '1560', 'Hoeilaart', 'HOEILAART', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2419, '3730', 'Hoeselt', 'HOESELT', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2420, '3220', 'Holsbeek', 'HOLSBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2421, '8830', 'Hooglede', 'HOOGLEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2422, '2320', 'Hoogstraten', 'HOOGSTRATEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2423, '6990', 'Hotton', 'HOTTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2424, '6660', 'Houffalize', 'HOUFFALIZE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2425, '8650', 'Houthulst', 'HOUTHULST', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2426, '5560', 'Houyet', 'HOUYET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2427, '2540', 'Hove', 'HOVE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2428, '3040', 'Huldenberg', 'HULDENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2429, '2235', 'Hulshout', 'HULSHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2430, '4500', 'Huy', 'HUY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2431, '8480', 'Ichtegem', 'ICHTEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2432, '8900', 'Ieper', 'IEPER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2433, '1315', 'Incourt', 'INCOURT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2434, '8770', 'Ingelmunster', 'INGELMUNSTER', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2435, '1460', 'Ittre', 'ITTRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2436, '8870', 'Izegem', 'IZEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2437, '8490', 'Jabbeke', 'JABBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2438, '4845', 'Jalhay', 'JALHAY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2439, '5190', 'Jemeppe-Sur-Sambre', 'JEMEPPE-SUR-SAMBRE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2440, '1090', 'Jette', 'JETTE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2441, '1370', 'Jodoigne', 'JODOIGNE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2442, '4450', 'Juprelle', 'JUPRELLE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2443, '7050', 'Jurbise', 'JURBISE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25');
INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `updated_at`, `created_at`) VALUES
(2444, '2920', 'Kalmthout', 'KALMTHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2445, '1910', 'Kampenhout', 'KAMPENHOUT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2446, '1880', 'Kapelle-Op-Den-Bos', 'KAPELLE-OP-DEN-BOS', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2447, '2950', 'Kapellen', 'KAPELLEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2448, '9970', 'Kaprijke', 'KAPRIJKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2449, '2460', 'Kasterlee', 'KASTERLEE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2450, '3140', 'Keerbergen', 'KEERBERGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2451, '4720', 'Kelmis', 'KELMIS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2452, '3640', 'Kinrooi', 'KINROOI', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2453, '8680', 'Koekelare', 'KOEKELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2454, '1081', 'Koekelberg', 'KOEKELBERG', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2455, '8670', 'Koksijde', 'KOKSIJDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2456, '7780', 'Komen-Waasten', 'KOMEN-WAASTEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2457, '2550', 'Kontich', 'KONTICH', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2458, '8610', 'Kortemark', 'KORTEMARK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2459, '3470', 'Kortenaken', 'KORTENAKEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2460, '3070', 'Kortenberg', 'KORTENBERG', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2461, '3720', 'Kortessem', 'KORTESSEM', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2462, '8500', 'Kortrijk', 'KORTRIJK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2463, '1950', 'Kraainem', 'KRAAINEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2464, '9150', 'Kruibeke', 'KRUIBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2465, '8520', 'Kuurne', 'KUURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2466, '1310', 'La Hulpe', 'LA HULPE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2467, '7100', 'La Louvi?re', 'LA LOUVI?RE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2468, '6980', 'La Roche-En-Ardenne', 'LA ROCHE-EN-ARDENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2469, '9270', 'Laarne', 'LAARNE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2470, '3620', 'Lanaken', 'LANAKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2471, '3400', 'Landen', 'LANDEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2472, '7070', 'Le Roeulx', 'LE ROEULX', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2473, '9280', 'Lebbeke', 'LEBBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2474, '9340', 'Lede', 'LEDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2475, '8880', 'Ledegem', 'LEDEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2476, '6860', 'L?glise', 'L?GLISE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2477, '8860', 'Lendelede', 'LENDELEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2478, '7870', 'Lens', 'LENS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2479, '3970', 'Leopoldsburg', 'LEOPOLDSBURG', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2480, '7860', 'Lessines', 'LESSINES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2481, '3000', 'Leuven', 'LEUVEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2482, '7900', 'Leuze-En-Hainaut', 'LEUZE-EN-HAINAUT', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2483, '6890', 'Libin', 'LIBIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2484, '6800', 'Libramont-Chevigny', 'LIBRAMONT-CHEVIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2485, '8810', 'Lichtervelde', 'LICHTERVELDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2486, '1770', 'Liedekerke', 'LIEDEKERKE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2487, '4000', 'Li?ge', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2488, '4020', 'Li?ge', 'LI?GE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2489, '2500', 'Lier', 'LIER', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2490, '4990', 'Lierneux', 'LIERNEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2491, '2275', 'Lille', 'LILLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2492, '4830', 'Limbourg', 'LIMBOURG', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2493, '4287', 'Lincent', 'LINCENT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2494, '1630', 'Linkebeek', 'LINKEBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2495, '2547', 'Lint', 'LINT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2496, '3350', 'Linter', 'LINTER', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2497, '6540', 'Lobbes', 'LOBBES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2498, '9080', 'Lochristi', 'LOCHRISTI', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2499, '9160', 'Lokeren', 'LOKEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2500, '3920', 'Lommel', 'LOMMEL', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2501, '1840', 'Londerzeel', 'LONDERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2502, '4710', 'Lontzen', 'LONTZEN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2503, '3210', 'Lubbeek', 'LUBBEEK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2504, '3560', 'Lummen', 'LUMMEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2505, '3680', 'Maaseik', 'MAASEIK', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2506, '3630', 'Maasmechelen', 'MAASMECHELEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2507, '1830', 'Machelen', 'MACHELEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2508, '9990', 'Maldegem', 'MALDEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2509, '2390', 'Malle', 'MALLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2510, '4960', 'Malmedy', 'MALMEDY', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2511, '7170', 'Manage', 'MANAGE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2512, '6900', 'Marche-En-Famenne', 'MARCHE-EN-FAMENNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2513, '4570', 'Marchin', 'MARCHIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2514, '6630', 'Martelange', 'MARTELANGE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2515, '2800', 'Mechelen', 'MECHELEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2516, '2450', 'Meerhout', 'MEERHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2517, '1860', 'Meise', 'MEISE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2518, '6769', 'Meix-Devant-Virton', 'MEIX-DEVANT-VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2519, '9090', 'Melle', 'MELLE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2520, '8930', 'Menen', 'MENEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2521, '6567', 'Merbes-Le-Ch?teau', 'MERBES-LE-CH?TEAU', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2522, '1785', 'Merchtem', 'MERCHTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2523, '9820', 'Merelbeke', 'MERELBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2524, '2330', 'Merksplas', 'MERKSPLAS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2525, '8957', 'Mesen', 'MESEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2526, '6780', 'Messancy', 'MESSANCY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2527, '5640', 'Mettet', 'METTET', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2528, '8760', 'Meulebeke', 'MEULEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2529, '8430', 'Middelkerke', 'MIDDELKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2530, '4577', 'Modave', 'MODAVE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2531, '9180', 'Moerbeke-Waas', 'MOERBEKE-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2532, '7700', 'Moeskroen', 'MOESKROEN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2533, '2400', 'Mol', 'MOL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2534, '6590', 'Momignies', 'MOMIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2535, '7000', 'Mons', 'MONS', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2536, '1435', 'Mont-Saint-Guibert', 'MONT-SAINT-GUIBERT', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2537, '6110', 'Montigny-Le-Tilleul', 'MONTIGNY-LE-TILLEUL', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2538, '8890', 'Moorslede', 'MOORSLEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2539, '2640', 'Mortsel', 'MORTSEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2540, '6750', 'Musson', 'MUSSON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2541, '5000', 'Namur', 'NAMUR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2542, '4550', 'Nandrin', 'NANDRIN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2543, '6950', 'Nassogne', 'NASSOGNE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2544, '9810', 'Nazareth', 'NAZARETH', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2545, '6840', 'Neufch?teau', 'NEUFCH?TEAU', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2546, '2845', 'Niel', 'NIEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2547, '3850', 'Nieuwerkerken', 'NIEUWERKERKEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2548, '8620', 'Nieuwpoort', 'NIEUWPOORT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2549, '2560', 'Nijlen', 'NIJLEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2550, '9400', 'Ninove', 'NINOVE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2551, '1400', 'Nivelles', 'NIVELLES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2552, '5350', 'Ohey', 'OHEY', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2553, '2250', 'Olen', 'OLEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2554, '4877', 'Olne', 'OLNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2555, '5520', 'Onhaye', 'ONHAYE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2556, '8400', 'Oostende', 'OOSTENDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2557, '9860', 'Oosterzele', 'OOSTERZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2558, '8020', 'Oostkamp', 'OOSTKAMP', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2559, '8780', 'Oostrozebeke', 'OOSTROZEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2560, '1745', 'Opwijk', 'OPWIJK', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2561, '4360', 'Oreye', 'OREYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2562, '3050', 'Oud-Heverlee', 'OUD-HEVERLEE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2563, '2360', 'Oud-Turnhout', 'OUD-TURNHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2564, '9700', 'Oudenaarde', 'OUDENAARDE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2565, '8460', 'Oudenburg', 'OUDENBURG', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2566, '1160', 'Oudergem', 'OUDERGEM', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2567, '4590', 'Ouffet', 'OUFFET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2568, '4680', 'Oupeye', 'OUPEYE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2569, '3090', 'Overijse', 'OVERIJSE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2570, '6850', 'Paliseul', 'PALISEUL', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2571, '7740', 'Pecq', 'PECQ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2572, '3990', 'Peer', 'PEER', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2573, '1670', 'Pepingen', 'PEPINGEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2574, '4860', 'Pepinster', 'PEPINSTER', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2575, '7600', 'P?ruwelz', 'P?RUWELZ', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2576, '1360', 'Perwez', 'PERWEZ', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2577, '5600', 'Philippeville', 'PHILIPPEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2578, '8740', 'Pittem', 'PITTEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2579, '6230', 'Pont-?-Celles', 'PONT-?-CELLES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2580, '8970', 'Poperinge', 'POPERINGE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2581, '5170', 'Profondeville', 'PROFONDEVILLE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2582, '2580', 'Putte', 'PUTTE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2583, '7390', 'Quaregnon', 'QUAREGNON', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2584, '7380', 'Qui?vrain', 'QUI?VRAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2585, '4730', 'Raeren', 'RAEREN', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2586, '1367', 'Ramillies', 'RAMILLIES', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2587, '2520', 'Ranst', 'RANST', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2588, '2380', 'Ravels', 'RAVELS', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2589, '4350', 'Remicourt', 'REMICOURT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2590, '6987', 'Rendeux', 'RENDEUX', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2591, '2470', 'Retie', 'RETIE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2592, '3770', 'Riemst', 'RIEMST', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2593, '2310', 'Rijkevorsel', 'RIJKEVORSEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2594, '1330', 'Rixensart', 'RIXENSART', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2595, '5580', 'Rochefort', 'ROCHEFORT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2596, '8800', 'Roeselare', 'ROESELARE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2597, '9600', 'Ronse', 'RONSE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2598, '1760', 'Roosdaal', 'ROOSDAAL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2599, '3110', 'Rotselaar', 'ROTSELAAR', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2600, '8755', 'Ruiselede', 'RUISELEDE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2601, '7610', 'Rumes', 'RUMES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2602, '2840', 'Rumst', 'RUMST', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2603, '4470', 'Saint-Georges-Sur-Meuse', 'SAINT-GEORGES-SUR-MEUSE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2604, '7330', 'Saint-Ghislain', 'SAINT-GHISLAIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2605, '6870', 'Saint-Hubert', 'SAINT-HUBERT', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2606, '6747', 'Saint-L?ger', 'SAINT-L?GER', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2607, '4420', 'Saint-Nicolas', 'SAINT-NICOLAS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2608, '4780', 'Sankt-Vith', 'SANKT-VITH', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2609, '1030', 'Schaarbeek', 'SCHAARBEEK', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2610, '2627', 'Schelle', 'SCHELLE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2611, '2970', 'Schilde', 'SCHILDE', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2612, '2900', 'Schoten', 'SCHOTEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2613, '7180', 'Seneffe', 'SENEFFE', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2614, '4100', 'Seraing', 'SERAING', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2615, '7830', 'Silly', 'SILLY', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2616, '1082', 'Sint-Agatha-Berchem', 'SINT-AGATHA-BERCHEM', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2617, '1640', 'Sint-Genesius-Rode', 'SINT-GENESIUS-RODE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2618, '1060', 'Sint-Gillis', 'SINT-GILLIS', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2619, '9170', 'Sint-Gillis-Waas', 'SINT-GILLIS-WAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2620, '1080', 'Sint-Jans-Molenbeek', 'SINT-JANS-MOLENBEEK', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2621, '1210', 'Sint-Joost-Ten-Noode', 'SINT-JOOST-TEN-NOODE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2622, '2860', 'Sint-Katelijne-Waver', 'SINT-KATELIJNE-WAVER', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2623, '1200', 'Sint-Lambrechts-Woluwe', 'SINT-LAMBRECHTS-WOLUWE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2624, '9980', 'Sint-Laureins', 'SINT-LAUREINS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2625, '9520', 'Sint-Lievens-Houtem', 'SINT-LIEVENS-HOUTEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2626, '9830', 'Sint-Martens-Latem', 'SINT-MARTENS-LATEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2627, '9100', 'Sint-Niklaas', 'SINT-NIKLAAS', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2628, '1600', 'Sint-Pieters-Leeuw', 'SINT-PIETERS-LEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2629, '1150', 'Sint-Pieters-Woluwe', 'SINT-PIETERS-WOLUWE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2630, '3800', 'Sint-Truiden', 'SINT-TRUIDEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2631, '7060', 'Soignies', 'SOIGNIES', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2632, '5140', 'Sombreffe', 'SOMBREFFE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2633, '5377', 'Somme-Leuze', 'SOMME-LEUZE', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2634, '4630', 'Soumagne', 'SOUMAGNE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2635, '4900', 'Spa', 'SPA', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2636, '4140', 'Sprimont', 'SPRIMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2637, '2940', 'Stabroek', 'STABROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2638, '8840', 'Staden', 'STADEN', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2639, '4970', 'Stavelot', 'STAVELOT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2640, '1820', 'Steenokkerzeel', 'STEENOKKERZEEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2641, '9190', 'Stekene', 'STEKENE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2642, '4987', 'Stoumont', 'STOUMONT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2643, '6927', 'Tellin', 'TELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2644, '9140', 'Temse', 'TEMSE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2645, '6970', 'Tenneville', 'TENNEVILLE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2646, '1740', 'Ternat', 'TERNAT', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2647, '3080', 'Tervuren', 'TERVUREN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2648, '3980', 'Tessenderlo', 'TESSENDERLO', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2649, '4910', 'Theux', 'THEUX', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2650, '6530', 'Thuin', 'THUIN', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2651, '8700', 'Tielt', 'TIELT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2652, '3300', 'Tienen', 'TIENEN', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2653, '4557', 'Tinlot', 'TINLOT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2654, '6730', 'Tintigny', 'TINTIGNY', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2655, '3700', 'Tongeren', 'TONGEREN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2656, '8820', 'Torhout', 'TORHOUT', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2657, '7500', 'Tournai', 'TOURNAI', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2658, '3120', 'Tremelo', 'TREMELO', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2659, '4980', 'Trois-Ponts', 'TROIS-PONTS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2660, '1480', 'Tubize', 'TUBIZE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2661, '2300', 'Turnhout', 'TURNHOUT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2662, '1180', 'Ukkel', 'UKKEL', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2663, '6640', 'Vaux-Sur-S?re', 'VAUX-SUR-S?RE', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2664, '4537', 'Verlaine', 'VERLAINE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2665, '4800', 'Verviers', 'VERVIERS', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2666, '8630', 'Veurne', 'VEURNE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2667, '6690', 'Vielsalm', 'VIELSALM', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2668, '1495', 'Villers-La-Ville', 'VILLERS-LA-VILLE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2669, '4530', 'Villers-Le-Bouillet', 'VILLERS-LE-BOUILLET', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2670, '1800', 'Vilvoorde', 'VILVOORDE', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2671, '6760', 'Virton', 'VIRTON', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2672, '4600', 'Vis?', 'VIS?', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2673, '7880', 'Vloesberg', 'VLOESBERG', 'HENEGOUWEN', NULL, '2020-07-22 15:14:25'),
(2674, '2290', 'Vorselaar', 'VORSELAAR', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2675, '1190', 'Vorst', 'VORST', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2676, '2350', 'Vosselaar', 'VOSSELAAR', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2677, '5550', 'Vresse-Sur-Semois', 'VRESSE-SUR-SEMOIS', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2678, '9250', 'Waasmunster', 'WAASMUNSTER', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2679, '9185', 'Wachtebeke', 'WACHTEBEKE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2680, '5650', 'Walcourt', 'WALCOURT', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2681, '4520', 'Wanze', 'WANZE', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2682, '8790', 'Waregem', 'WAREGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2683, '4300', 'Waremme', 'WAREMME', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2684, '4219', 'Wasseiges', 'WASSEIGES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2685, '1410', 'Waterloo', 'WATERLOO', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2686, '1170', 'Watermaal-Bosvoorde', 'WATERMAAL-BOSVOORDE', 'BRUSSEL', NULL, '2020-07-22 15:14:25'),
(2687, '1300', 'Wavre', 'WAVRE', 'WAALS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2688, '4950', 'Weismes', 'WEISMES', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2689, '4840', 'Welkenraedt', 'WELKENRAEDT', 'LUIK', NULL, '2020-07-22 15:14:25'),
(2690, '3830', 'Wellen', 'WELLEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2691, '6920', 'Wellin', 'WELLIN', 'LUXEMBURG', NULL, '2020-07-22 15:14:25'),
(2692, '1780', 'Wemmel', 'WEMMEL', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2693, '8940', 'Wervik', 'WERVIK', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2694, '2260', 'Westerlo', 'WESTERLO', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2695, '9230', 'Wetteren', 'WETTEREN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2696, '8560', 'Wevelgem', 'WEVELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2697, '1970', 'Wezembeek-Oppem', 'WEZEMBEEK-OPPEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2698, '9260', 'Wichelen', 'WICHELEN', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2699, '8710', 'Wielsbeke', 'WIELSBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2700, '2110', 'Wijnegem', 'WIJNEGEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2701, '2830', 'Willebroek', 'WILLEBROEK', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2702, '8750', 'Wingene', 'WINGENE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2703, '2160', 'Wommelgem', 'WOMMELGEM', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2704, '2990', 'Wuustwezel', 'WUUSTWEZEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2705, '5530', 'Yvoir', 'YVOIR', 'NAMEN', NULL, '2020-07-22 15:14:25'),
(2706, '2240', 'Zandhoven', 'ZANDHOVEN', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2707, '1930', 'Zaventem', 'ZAVENTEM', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2708, '8210', 'Zedelgem', 'ZEDELGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2709, '9240', 'Zele', 'ZELE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2710, '9060', 'Zelzate', 'ZELZATE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2711, '1980', 'Zemst', 'ZEMST', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2712, '2980', 'Zoersel', 'ZOERSEL', 'ANTWERPEN', NULL, '2020-07-22 15:14:25'),
(2713, '3520', 'Zonhoven', 'ZONHOVEN', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2714, '8980', 'Zonnebeke', 'ZONNEBEKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2715, '9620', 'Zottegem', 'ZOTTEGEM', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2716, '3440', 'Zoutleeuw', 'ZOUTLEEUW', 'VLAAMS-BRABANT', NULL, '2020-07-22 15:14:25'),
(2717, '8377', 'Zuienkerke', 'ZUIENKERKE', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2718, '9870', 'Zulte', 'ZULTE', 'OOST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2719, '3690', 'Zutendaal', 'ZUTENDAAL', 'LIMBURG', NULL, '2020-07-22 15:14:25'),
(2720, '8550', 'Zwevegem', 'ZWEVEGEM', 'WEST-VLAANDEREN', NULL, '2020-07-22 15:14:25'),
(2721, '2070', 'Zwijndrecht', 'ZWIJNDRECHT', 'ANTWERPEN', NULL, '2020-07-22 15:14:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `booking_codes`
--
ALTER TABLE `booking_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dedzedzed` (`category`,`code`) USING BTREE;

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_nr` (`order_nr`,`wholesale_artnr`,`delivery_nr`,`lotnr`,`due_date`);

--
-- Indexes for table `delivery_slip`
--
ALTER TABLE `delivery_slip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment` (`payment`),
  ADD KEY `pet` (`pet`),
  ADD KEY `vet` (`vet`);

--
-- Indexes for table `events_procedures`
--
ALTER TABLE `events_procedures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedures_id` (`procedures_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events_products`
--
ALTER TABLE `events_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `events_upload`
--
ALTER TABLE `events_upload`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event` (`event`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab`
--
ALTER TABLE `lab`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_id` (`lab_id`);

--
-- Indexes for table `lab_detail`
--
ALTER TABLE `lab_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sample_id` (`sample_id`,`lab_code`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telephone` (`telephone`),
  ADD KEY `mobile` (`mobile`),
  ADD KEY `street` (`street`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `death` (`death`);

--
-- Indexes for table `pets_weight`
--
ALTER TABLE `pets_weight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `procedures`
--
ALTER TABLE `procedures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`input_barcode`),
  ADD KEY `name` (`name`),
  ADD KEY `wholesale` (`wholesale`);

--
-- Indexes for table `products_price`
--
ALTER TABLE `products_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products_type`
--
ALTER TABLE `products_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `register_in`
--
ALTER TABLE `register_in`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sticky`
--
ALTER TABLE `sticky`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `gsl_lookup` (`eol`,`location`,`lotnr`);

--
-- Indexes for table `stock_input`
--
ALTER TABLE `stock_input`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_limit`
--
ALTER TABLE `stock_limit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock` (`stock`,`product_id`);

--
-- Indexes for table `stock_location`
--
ALTER TABLE `stock_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tooth`
--
ALTER TABLE `tooth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pet` (`pet`,`tooth`);

--
-- Indexes for table `tooth_msg`
--
ALTER TABLE `tooth_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet` (`pet`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  ADD KEY `fk_users_groups_users1_idx` (`user_id`),
  ADD KEY `fk_users_groups_groups1_idx` (`group_id`);

--
-- Indexes for table `vaccine_pet`
--
ALTER TABLE `vaccine_pet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wholesale`
--
ALTER TABLE `wholesale`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `wholesale_price`
--
ALTER TABLE `wholesale_price`
  ADD PRIMARY KEY (`id`),
  ADD KEY `art_nr` (`art_nr`);

--
-- Indexes for table `zipcodes`
--
ALTER TABLE `zipcodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zip` (`zip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_codes`
--
ALTER TABLE `booking_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_slip`
--
ALTER TABLE `delivery_slip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_procedures`
--
ALTER TABLE `events_procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_products`
--
ALTER TABLE `events_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_upload`
--
ALTER TABLE `events_upload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_detail`
--
ALTER TABLE `lab_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pets_weight`
--
ALTER TABLE `pets_weight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_price`
--
ALTER TABLE `products_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_type`
--
ALTER TABLE `products_type`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `register_in`
--
ALTER TABLE `register_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sticky`
--
ALTER TABLE `sticky`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_input`
--
ALTER TABLE `stock_input`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_limit`
--
ALTER TABLE `stock_limit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_location`
--
ALTER TABLE `stock_location`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tooth`
--
ALTER TABLE `tooth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tooth_msg`
--
ALTER TABLE `tooth_msg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users_groups`
--
ALTER TABLE `users_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `vaccine_pet`
--
ALTER TABLE `vaccine_pet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale`
--
ALTER TABLE `wholesale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale_price`
--
ALTER TABLE `wholesale_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zipcodes`
--
ALTER TABLE `zipcodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2722;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
