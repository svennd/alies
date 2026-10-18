-- Deterministic test data. Loaded by tests/Support/TestDatabase.php after schema.sql, and again
-- before every HTTP test class.
--
-- * Everything is synthetic. Never copy real client or pet data in here.
-- * IDs are fixed and mirrored as constants in tests/Support/Fixtures.php. Change both together.
-- * Dates are relative to the moment of loading, so "next month", "expired" etc. stay true.
-- * Tests must not change shared seed rows unless they restore them. Create your own rows with
--   tests/Support/Factory.php instead (billing flows especially: opening a bill attaches ALL open
--   events of the owner, so use a fresh owner).
-- * Statements are split on ';' by TestDatabase::splitStatements(): no ';' inside strings please.

-- ---------------------------------------------------------------------------------------------
-- migrations: the test database is already at the latest version (welcome/index runs migrations)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `migrations` (`version`) VALUES (56);

-- ---------------------------------------------------------------------------------------------
-- locations, groups, users
-- ---------------------------------------------------------------------------------------------
INSERT INTO `stock_location` (`id`, `name`, `color`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Praktijk', '#3b82f6', NOW(), NOW(), NULL),
(2, 'Wagen', '#10b981', NOW(), NOW(), NULL),
(3, 'Oude locatie', '#999999', NOW(), NOW(), NOW());

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'General User'),
(3, 'vet', 'Veterinarian'),
(4, 'accounting', 'Accounting');

-- password of every user: TestPassword123!   (admin: bcrypt cost 12, others cost 10, like Ion_auth)
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `created_on`, `last_login`, `active`,
                     `first_name`, `last_name`, `phone`, `user_date`, `search_config`, `current_location`,
                     `created_at`, `image`, `sidebar`, `order_nr`) VALUES
(1, '127.0.0.1', 'Alice Admin',    '$2y$12$MgrqPrP0YKk1Vpe0Fyl3q.kArx46cSSV8OlW3dTuMn.OIIHTTuOgW', 'admin@test.local',    UNIX_TIMESTAMP(), NULL, 1, 'Alice',  'Admin',    '0470000001', 'd-m-Y', 0, 0, NOW(), '', '#4169e1', ''),
(2, '127.0.0.1', 'Victor Vet',     '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'vet@test.local',      UNIX_TIMESTAMP(), NULL, 1, 'Victor', 'Vet',      '0470000002', 'd-m-Y', 0, 0, NOW(), '', '#008040', '123456'),
(3, '127.0.0.1', 'Anna Account',   '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'acc@test.local',      UNIX_TIMESTAMP(), NULL, 1, 'Anna',   'Account',  '0470000003', 'd-m-Y', 0, 0, NOW(), '', '#ff8040', ''),
(4, '127.0.0.1', 'Mark Member',    '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'member@test.local',   UNIX_TIMESTAMP(), NULL, 1, 'Mark',   'Member',   '0470000004', 'd-m-Y', 0, 0, NOW(), '', '#e55343', ''),
(5, '127.0.0.1', 'Nora Nogroup',   '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'nogroup@test.local',  UNIX_TIMESTAMP(), NULL, 1, 'Nora',   'Nogroup',  '0470000005', 'd-m-Y', 0, 0, NOW(), '', '#584edf', ''),
(6, '127.0.0.1', 'Veerle Vet',     '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'vet2@test.local',     UNIX_TIMESTAMP(), NULL, 1, 'Veerle', 'Vet',      '0470000006', 'd-m-Y', 0, 0, NOW(), '', '#008040', '654321'),
(7, '127.0.0.1', 'Ines Inactive',  '$2y$10$9EsIk8s/BD7c9a1q7nnn/eYkboAvNIHVBYu/UzRinAyyXecBmIQ1C', 'inactive@test.local', UNIX_TIMESTAMP(), NULL, 0, 'Ines',   'Inactive', '0470000007', 'd-m-Y', 0, 0, NOW(), '', '#008040', '');

INSERT INTO `users_groups` (`user_id`, `group_id`) VALUES
(1, 1), (2, 3), (3, 4), (4, 2), (6, 3), (7, 3);

-- ---------------------------------------------------------------------------------------------
-- settings (config table; values are base64 encoded, see Config_model::store)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `config` (`name`, `value`, `created_at`, `updated_at`) VALUES
('due_date',                 TO_BASE64('30'),                       NOW(), NOW()),
('invoice_prefix',           TO_BASE64('1'),                        NOW(), NOW()),
('struct_config',            TO_BASE64('0'),                        NOW(), NOW()),
('nameiban',                 TO_BASE64('Test Praktijk'),            NOW(), NOW()),
('iban',                     TO_BASE64('BE68 5390 0754 7034'),      NOW(), NOW()),
('bic',                      TO_BASE64('BBRUBEBB'),                 NOW(), NOW()),
('emailtitle',               TO_BASE64('Test Praktijk'),            NOW(), NOW()),
('emailcontent',             TO_BASE64('Test inhoud'),              NOW(), NOW()),
('RestrictBills',            TO_BASE64('0'),                        NOW(), NOW()),
('autoclose',                TO_BASE64('0'),                        NOW(), NOW()),
('autodisable',              TO_BASE64('0'),                        NOW(), NOW()),
('autotemplate',             TO_BASE64('<p>template</p>'),          NOW(), NOW()),
('pruning',                  TO_BASE64('0'),                        NOW(), NOW()),
('prune_global_log',         TO_BASE64('30'),                       NOW(), NOW()),
('prune_stock',              TO_BASE64('2'),                        NOW(), NOW()),
('prune_stock_log',          TO_BASE64('30'),                       NOW(), NOW()),
('autdeath',                 TO_BASE64('0'),                        NOW(), NOW()),
('vamreg_push',              TO_BASE64('0'),                        NOW(), NOW()),
('vamreg_auto_send_months',  TO_BASE64('1'),                        NOW(), NOW()),
('index_api_url',            TO_BASE64('http://127.0.0.1:8090'),    NOW(), NOW()),
('index_api_key',            TO_BASE64('test-index-key'),           NOW(), NOW());
-- not seeded on purpose: vamreg_api_key (tests set it when they need it)

-- ---------------------------------------------------------------------------------------------
-- API keys (api_keys.key_hash is compared as stored, see ApiKey_model::findActive)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `api_keys` (`id`, `key_hash`, `device`, `active`, `rate_limit`, `created_at`) VALUES
(1, 'test-active-api-key',  'ms4s2', 1, 60, NOW()),
(2, 'test-revoked-api-key', 'ms4s2', 0, 60, NOW());

-- ---------------------------------------------------------------------------------------------
-- reference data: zip codes, breeds, booking codes, product types and labels
-- ---------------------------------------------------------------------------------------------
INSERT INTO `zipcodes` (`id`, `zip`, `city`, `main_city`, `province`, `created_at`, `updated_at`) VALUES
(1, '2000', 'Antwerpen', 'Antwerpen', 'Antwerpen', NOW(), NOW()),
(2, '9000', 'Gent', 'Gent', 'Oost-Vlaanderen', NOW(), NOW()),
(3, '3000', 'Leuven', 'Leuven', 'Vlaams-Brabant', NOW(), NOW()),
(4, '1000', 'Brussel', 'Brussel', 'Brussel', NOW(), NOW());

-- breed 1 is the "unknown" breed, Breeds::rebuild_frequenty skips it
INSERT INTO `breeds` (`id`, `name`, `type`, `freq`, `male_min_weight`, `male_max_weight`, `female_min_weight`, `female_max_weight`, `created_at`, `updated_at`) VALUES
(1, 'Onbekend',          0, 0,  0.00,  0.00,  0.00,  0.00, NOW(), NOW()),
(2, 'Labrador',          0, 0, 29.00, 36.00, 25.00, 32.00, NOW(), NOW()),
(3, 'Duitse herder',     0, 0, 30.00, 40.00, 22.00, 32.00, NOW(), NOW()),
(4, 'Beagle',            0, 0, 10.00, 11.00,  9.00, 10.00, NOW(), NOW()),
(5, 'Europese korthaar', 1, 0,  4.00,  6.00,  3.00,  5.00, NOW(), NOW()),
(6, 'Pers',              1, 0,  4.00,  6.00,  3.00,  5.00, NOW(), NOW());

INSERT INTO `booking_codes` (`id`, `category`, `code`, `btw`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Producten', '700000', 6,  NOW(), NOW(), NULL),
(2, 'Diensten',  '701000', 21, NOW(), NOW(), NULL),
(3, 'Oud',       '709999', 12, NOW(), NOW(), NOW());

INSERT INTO `products_type` (`id`, `name`, `icon`, `icon_color`, `root`, `created_at`, `updated_at`) VALUES
(1, 'Medicatie', 'pills',  '#3b82f6', NULL, NOW(), NOW()),
(2, 'Voeding',   'bone',   '#10b981', NULL, NOW(), NOW()),
(3, 'Antibiotica', NULL,   NULL,      1,    NOW(), NOW());

INSERT INTO `products_label` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'pijn', NOW(), NOW()),
(2, 'huid', NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- procedures
-- ---------------------------------------------------------------------------------------------
INSERT INTO `procedures` (`id`, `name`, `price`, `booking_code`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3001, 'Consultatie',          25.00, 2, NOW(), NOW(), NULL),
(3002, 'Nagels knippen',       10.00, 1, NOW(), NOW(), NULL),
(3003, 'Verwijderde ingreep',  50.00, 2, NOW(), NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- wholesale (5001 linked to product 4001, 5002 unlinked, 5003 unlinked with a price increase)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `wholesale_type` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Medicatie', NOW(), NOW());

INSERT INTO `wholesale` (`id`, `vendor_id`, `description`, `bruto`, `last_bruto`, `last_bruto_date`, `btw`, `sell_price`,
                         `distributor`, `distributor_id`, `CNK`, `VHB`, `type`, `ignore_change`, `created_at`, `updated_at`) VALUES
(5001, 'W-1001', 'Amoxi Tabs 500 mg 100 tabletten', 10.00, 10.00, CURDATE(),                         6, 14.00, 'Test Distri', 'D1', '1234500', 'VHB-0001', 1, 0, NOW(), NOW()),
(5002, 'W-1002', 'Ontsmettingsmiddel 250 ml',         5.00,  5.00, CURDATE(),                         6,  7.50, 'Test Distri', 'D1', '1234501', NULL,       1, 0, NOW(), NOW()),
(5003, 'W-1003', 'Shampoo 500 ml',                   12.00, 10.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 21, 16.00, 'Test Distri', 'D1', '1234502', NULL,       1, 0, NOW(), NOW());

INSERT INTO `wholesale_price` (`id`, `art_nr`, `bruto`, `created_at`, `updated_at`) VALUES
(1, 'W-1001', 9.50, DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY));

INSERT INTO `delivery` (`id`, `order_date`, `order_nr`, `my_ref`, `wholesale_artnr`, `wholesale_id`, `CNK`, `delivery_date`,
                        `delivery_nr`, `bruto_price`, `netto_price`, `amount`, `lotnr`, `due_date`, `btw`, `billing`, `created_at`, `updated_at`) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 1001, 'REF-1', 'W-1001', 5001, 1234500, DATE_SUB(CURDATE(), INTERVAL 7 DAY),
 2001, 10.00, 8.50, 5, 'AMX-001', DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 6, 'Test', NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- products
--   4001 tiered prices {1:10, 5:8, 10:6} at 6%, GTIN barcode, linked to wholesale 5001
--   4002 vaccine (yearly)
--   4003 antibiotic (CNK 1234567 is in vamreg_index)
--   4004 dead volume 0.50
--   4005 no stock at all, limit_stock 5
--   4006 soft deleted, still has stock
--   4007 not sellable
-- ---------------------------------------------------------------------------------------------
INSERT INTO `products` (`id`, `name`, `wholesale_name`, `short_name`, `producer`, `supplier`, `posologie`, `toedieningsweg`, `type`,
                        `dead_volume`, `buy_volume`, `sell_volume`, `buy_price`, `unit_buy`, `unit_sell`, `input_barcode`,
                        `btw_buy`, `btw_sell`, `booking_code`, `delay`, `comment`, `sellable`, `discontinued`, `limit_stock`,
                        `vaccin`, `vaccin_freq`, `vhbcode`, `cnk`, `wholesale`, `usage_count`,
                        `is_antibiotic`, `default_indication`, `ab_unit`, `ab_unit_volume`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4001, 'Amoxi Tabs',          'Amoxi Tabs 500 mg',  'amoxi',   'Test Pharma', 'Test Distri', '', '', 1, 0.00, 100, 1.00, 30.00, 'doos',  'tab',   '05414567000017', 6,  6,  1, 0, '', 1, 0, 10, 0, 0,   'VHB-0001', '1234500', 5001, 5, 0, NULL,    NULL,    NULL, NOW(), NOW(), NULL),
(4002, 'Nobivac Vaccin',      'Nobivac Vaccin',     'nobivac', 'Test Pharma', 'Test Distri', '', '', 1, 0.00,  10, 1.00, 80.00, 'doos',  'dosis', '05414567000024', 6,  6,  1, 0, '', 1, 0,  0, 1, 365, '',         '2345600', 0,    3, 0, NULL,    NULL,    NULL, NOW(), NOW(), NULL),
(4003, 'Clavubactin AB',      'Clavubactin 250 mg', 'clavu',   'Test Pharma', 'Test Distri', '', '', 3, 0.00,  20, 1.00, 40.00, 'doos',  'tab',   NULL,             6,  6,  1, 0, '', 1, 0,  0, 0, 0,   '',         '1234567', 0,    2, 1, 'RESP',  'PACKS', 1.00, NOW(), NOW(), NULL),
(4004, 'Dead Volume Drops',   'Dead Volume Drops',  'drops',   'Test Pharma', 'Test Distri', '', '', 1, 0.50, 100, 1.00, 20.00, 'fles',  'ml',    NULL,             6,  6,  1, 0, '', 1, 0,  0, 0, 0,   '',         NULL,      0,    1, 0, NULL,    NULL,    NULL, NOW(), NOW(), NULL),
(4005, 'Zonder voorraad',     'Zonder voorraad',    '',        'Test Pharma', 'Test Distri', '', '', 2, 0.00,   1, 1.00,  3.00, 'stuk',  'stuk',  NULL,             21, 21, 2, 0, '', 1, 0,  5, 0, 0,   '',         NULL,      0,    0, 0, NULL,    NULL,    NULL, NOW(), NOW(), NULL),
(4006, 'Verwijderd product',  'Verwijderd product', '',        'Test Pharma', 'Test Distri', '', '', 1, 0.00,   1, 1.00,  1.00, 'stuk',  'stuk',  NULL,             6,  6,  1, 0, '', 1, 0,  0, 0, 0,   '',         NULL,      0,    0, 0, NULL,    NULL,    NULL, NOW(), NOW(), NOW()),
(4007, 'Niet verkoopbaar',    'Niet verkoopbaar',   '',        'Test Pharma', 'Test Distri', '', '', 1, 0.00,   1, 1.00,  1.00, 'stuk',  'stuk',  NULL,             6,  6,  1, 0, '', 0, 0,  0, 0, 0,   '',         NULL,      0,    0, 0, NULL,    NULL,    NULL, NOW(), NOW(), NULL);

INSERT INTO `products_price` (`id`, `product_id`, `volume`, `price`, `created_at`, `updated_at`) VALUES
(1, 4001,  1.00, 10.00, NOW(), NOW()),
(2, 4001,  5.00,  8.00, NOW(), NOW()),
(3, 4001, 10.00,  6.00, NOW(), NOW()),
(4, 4002,  1.00, 20.00, NOW(), NOW()),
(5, 4003,  1.00, 12.50, NOW(), NOW()),
(6, 4004,  1.00,  2.00, NOW(), NOW()),
(7, 4005,  1.00,  5.00, NOW(), NOW());

INSERT INTO `products_product_label` (`product_id`, `label_id`, `created_at`, `updated_at`) VALUES
(4001, 1, NOW(), NOW());

INSERT INTO `stock_limit` (`id`, `stock`, `product_id`, `volume`, `created_at`, `updated_at`) VALUES
(1, 1, 4001, 20, NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- stock (location is a varchar column holding the location id)
--   state: 0 check, 1 in use, 2 history, 3 error
-- ---------------------------------------------------------------------------------------------
INSERT INTO `stock` (`id`, `product_id`, `eol`, `location`, `in_price`, `cat_price`, `lotnr`, `volume`, `barcode`, `supplier`, `state`, `info`, `created_at`, `updated_at`) VALUES
(5001, 4001, DATE_ADD(CURDATE(), INTERVAL 1 YEAR),   '1', 0.28, 0.30, 'AMX-001', 100.00, 'BC5001', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5002, 4001, DATE_ADD(CURDATE(), INTERVAL 1 YEAR),   '2', 0.28, 0.30, 'AMX-001',  20.00, 'BC5001', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5003, 4002, DATE_ADD(CURDATE(), INTERVAL 6 MONTH),  '1', 8.00, 8.00, 'VAC-001',  10.00, 'BC5003', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5004, 4003, DATE_ADD(CURDATE(), INTERVAL 2 YEAR),   '1', 2.00, 2.00, 'AB-001',   30.00, 'BC5004', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5005, 4004, DATE_ADD(CURDATE(), INTERVAL 1 YEAR),   '1', 0.20, 0.20, 'DV-001',   50.00, 'BC5005', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5006, 4001, DATE_SUB(CURDATE(), INTERVAL 30 DAY),   '1', 0.25, 0.25, 'AMX-OLD',   5.00, 'BC5006', 'Test Distri', 1, NULL,       NOW(), NOW()),
(5007, 4001, NULL,                                   '1', 0.28, NULL, 'AMX-ERR',  -2.00, 'BC5007', NULL,           3, 'NO_STOCK', NOW(), NOW()),
(5008, 4006, DATE_ADD(CURDATE(), INTERVAL 1 YEAR),   '1', 0.10, 0.10, 'DEL-001',   7.00, 'BC5008', 'Test Distri', 1, NULL,       NOW(), NOW());

INSERT INTO `stock_value` (`id`, `value`, `created_at`, `updated_at`) VALUES
(1, 100.00, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `delivery_slip` (`id`, `vet`, `note`, `regdate`, `location`, `created_at`, `updated_at`) VALUES
(1, 2, 'Test levering', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 1, NOW(), NOW());

INSERT INTO `register_in` (`id`, `product`, `volume`, `eol`, `in_price`, `cat_price`, `lotnr`, `supplier`, `delivery_slip`, `created_at`, `updated_at`) VALUES
(1, 4001, 100.00, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 0.28, 0.30, 'AMX-001', 'Test Distri', 1, NOW(), NOW()),
(2, 4002,  10.00, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 8.00, 8.00, 'VAC-001', 'Test Distri', 1, NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- vamreg index (CNK 1234567 = product 4003)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `vamreg_index` (`id`, `cti`, `cnk`, `ppnNl`, `packSize`, `susage`, `maName`, `maNumber`, `mahName`, `created_at`, `updated_at`) VALUES
(1, 'CTI-0001', '1234567', 'Clavubactin 250 mg 20 tabletten', '20', 'VETERINARY', 'Clavubactin', 'BE-V123456', 'Test Pharma', NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- owners
--   1001 normal (mobile + landline + mail)    1002 low budget       1003 company with VAT number
--   1004 disabled                             1005/1006 share a mobile number (duplicates)
--   1007 soft deleted                         1837 the write-off client (Logs::write_off_invoice)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `owners` (`id`, `first_name`, `last_name`, `telephone`, `mobile`, `street`, `nr`, `city`, `main_city`, `province`, `zip`,
                      `mail`, `msg`, `btw_nr`, `invoice_addr`, `debts`, `disabled`, `low_budget`, `language`, `contact`,
                      `last_bill`, `initial_vet`, `initial_loc`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1001, 'Jan',     'Janssens',  '031234567', '0470123456', 'Kerkstraat',    '12', 'Antwerpen', 'Antwerpen', 'Antwerpen',       '2000', 'jan.janssens@example.test', '', NULL,            NULL, 0, 0, 0, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NULL),
(1002, 'Els',     'Peeters',   NULL,        '0471222333', 'Molenweg',      '5',  'Gent',      'Gent',      'Oost-Vlaanderen', '9000', NULL,                        '', NULL,            NULL, 0, 0, 1, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NULL),
(1003, '',        'Dierenhandel De Ark BV', '092345678', NULL, 'Industrielaan', '7', 'Gent',      'Gent',      'Oost-Vlaanderen', '9000', 'info@dearkbv.example.test', '', 'BE0123456789', 'Industrielaan 7, 9000 Gent', 0, 0, 0, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NULL),
(1004, 'Piet',    'Verhuisd',  NULL,        '0472444555', 'Oudeweg',       '1',  'Leuven',    'Leuven',    'Vlaams-Brabant',  '3000', NULL,                        '', NULL,            NULL, 0, 1, 0, 0, 1, DATE_SUB(CURDATE(), INTERVAL 2 YEAR), 2, 1, NOW(), NOW(), NULL),
(1005, 'Karel',   'Dubbel',    NULL,        '0475999999', 'Dubbelstraat',  '1',  'Brussel',   'Brussel',   'Brussel',         '1000', NULL,                        '', NULL,            NULL, 0, 0, 0, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NULL),
(1006, 'Karla',   'Dubbel',    NULL,        '0475999999', 'Dubbelstraat',  '2',  'Brussel',   'Brussel',   'Brussel',         '1000', NULL,                        '', NULL,            NULL, 0, 0, 0, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NULL),
(1007, 'Wim',     'Verwijderd', NULL,       '0473666777', 'Wisstraat',     '9',  'Gent',      'Gent',      'Oost-Vlaanderen', '9000', NULL,                        '', NULL,            NULL, 0, 0, 0, 0, 1, CURDATE(), 2, 1, NOW(), NOW(), NOW()),
(1837, '',        'Afschrijving', NULL,     NULL,         'Praktijkweg',   '1',  'Antwerpen', 'Antwerpen', 'Antwerpen',       '2000', NULL,                        '', NULL,            NULL, 0, 0, 0, 0, 0, CURDATE(), 2, 1, NOW(), NOW(), NULL);

-- ---------------------------------------------------------------------------------------------
-- pets
--   2001 Rex (dog, chip, weights)  2002 Mimi (cat)  2003 Bobby (owner 1002)  2004 dead  2005 transferred
--   2006 horse (company)  2007/2008 share a chip (duplicates)  2009 soft deleted
-- ---------------------------------------------------------------------------------------------
INSERT INTO `pets` (`id`, `type`, `name`, `birth`, `death`, `death_date`, `breed`, `breed2`, `gender`, `color`, `last_weight`, `lost`,
                    `chip`, `hairtype`, `owner`, `location`, `init_vet`, `transfered`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2001, 0, 'Rex',      DATE_SUB(CURDATE(), INTERVAL 5 YEAR), 0, NULL,                                  2, NULL, 0, 'zwart',  25.50, 0, '981000000000001', 'kort', 1001, 1, 2, 0, NOW(), NOW(), NULL),
(2002, 1, 'Mimi',     DATE_SUB(CURDATE(), INTERVAL 3 YEAR), 0, NULL,                                  5, NULL, 1, 'grijs',   4.20, 0, NULL,              'kort', 1001, 1, 2, 0, NOW(), NOW(), NULL),
(2003, 0, 'Bobby',    DATE_SUB(CURDATE(), INTERVAL 7 YEAR), 0, NULL,                                  4, NULL, 0, 'bruin',  11.00, 0, '981000000000003', 'kort', 1002, 1, 2, 0, NOW(), NOW(), NULL),
(2004, 0, 'Oudje',    DATE_SUB(CURDATE(), INTERVAL 15 YEAR), 1, DATE_SUB(CURDATE(), INTERVAL 1 YEAR), 2, NULL, 0, 'geel',   30.00, 0, NULL,              'kort', 1001, 1, 2, 0, NOW(), NOW(), NULL),
(2005, 0, 'Zwerver',  DATE_SUB(CURDATE(), INTERVAL 4 YEAR), 0, NULL,                                  3, NULL, 0, 'zwart',  30.00, 0, NULL,              'kort', 1001, 1, 2, 1, NOW(), NOW(), NULL),
(2006, 2, 'Dolly',    DATE_SUB(CURDATE(), INTERVAL 9 YEAR), 0, NULL,                                  1, NULL, 1, 'vos',   450.00, 0, NULL,              'kort', 1003, 1, 2, 0, NOW(), NOW(), NULL),
(2007, 0, 'Dubbel Een', DATE_SUB(CURDATE(), INTERVAL 2 YEAR), 0, NULL,                                1, NULL, 0, 'wit',    12.00, 0, '981000000000099', 'kort', 1005, 1, 2, 0, NOW(), NOW(), NULL),
(2008, 0, 'Dubbel Twee', DATE_SUB(CURDATE(), INTERVAL 2 YEAR), 0, NULL,                               1, NULL, 1, 'wit',    11.00, 0, '981000000000099', 'kort', 1006, 1, 2, 0, NOW(), NOW(), NULL),
(2009, 0, 'Weggegooid', DATE_SUB(CURDATE(), INTERVAL 2 YEAR), 0, NULL,                                1, NULL, 0, 'wit',     5.00, 0, NULL,              'kort', 1001, 1, 2, 0, NOW(), NOW(), NOW());

-- A historical transfer: 2010 was handed over to owner 1002 and 2011 is the copy that owner got
-- (same name, type and birth, created within 10 seconds of the source's last update). This is the
-- shape Pets_model::resolve_historical_transfer_pairs() / backfill_transferred_pets() look for.
INSERT INTO `pets` (`id`, `type`, `name`, `birth`, `death`, `breed`, `gender`, `color`, `last_weight`, `lost`,
                    `note`, `hairtype`, `owner`, `location`, `init_vet`, `transfered`, `created_at`, `updated_at`) VALUES
(2010, 0, 'Verhuisd Hondje', '2020-05-05', 0, 1, 0, 'bruin', 8.00, 0, '[transfer:send:1002]', 'kort', 1001, 1, 2, 1, '2026-01-10 10:00:00', '2026-01-10 10:00:05'),
(2011, 0, 'Verhuisd Hondje', '2020-05-05', 0, 1, 0, 'bruin', 8.00, 0, '[transfer:owner:1001]', 'kort', 1002, 1, 2, 0, '2026-01-10 10:00:05', '2026-01-10 10:00:05');

INSERT INTO `pets_weight` (`id`, `pets`, `weight`, `created_at`, `updated_at`) VALUES
(1, 2001, 24.00, DATE_SUB(NOW(), INTERVAL 56 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY)),
(2, 2001, 25.50, DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY));

-- ---------------------------------------------------------------------------------------------
-- consultations, bills and payments
--   bill 7001 PAID (invoice 1) with event 6002:  net 45.00  BTW_6 20.00  BTW_21 25.00  gross 51.45
--   bill 7002 PENDING with event 6003:            net 25.00  BTW_21 25.00               gross 30.25
--   bill 7003 PENDING but soft deleted
--   event 6001 is OPEN without bill (product 4001 x2 + consultation)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `bills` (`id`, `owner_id`, `vet`, `location`, `total_brut`, `total_net`, `BTW_0`, `BTW_6`, `BTW_21`, `cash`, `card`, `transfer`,
                     `transfer_verified`, `invoice_id`, `invoice_date`, `modified`, `status`, `msg`, `msg_invoice`, `mail`,
                     `created_at`, `updated_at`, `deleted_at`) VALUES
(7001, 1001, 2, 1, 51.45, 45.00, 0.00, 20.00, 25.00, 51.45, 0.00, 0.00, 0, 1,    DATE_SUB(NOW(), INTERVAL 3 DAY), 0, 4, '', '', 0, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW(), NULL),
(7002, 1002, 2, 1, 30.25, 25.00, 0.00,  0.00, 25.00,  0.00, 0.00, 0.00, 0, NULL, NULL,                            0, 1, '', '', 0, NOW(), NOW(), NULL),
(7003, 1001, 2, 1, 0.00,  0.00,  0.00,  0.00,  0.00,  0.00, 0.00, 0.00, 0, NULL, NULL,                            0, 1, '', '', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW(), NOW());

-- payment.deleted_at is NOT NULL in the schema; the application stores the zero date
INSERT INTO `payment` (`id`, `card`, `cash`, `transfer`, `bill_id`, `vet`, `location`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9001, 0.00, 51.45, 0.00, 7001, 2, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), '0000-00-00 00:00:00');

INSERT INTO `events` (`id`, `title`, `anamnese`, `pet`, `type`, `status`, `payment`, `location`, `vet`, `vet_support_1`, `vet_support_2`,
                      `report`, `no_history`, `created_at`, `updated_at`) VALUES
(6001, 'Controle Rex',      '',                                  2001, 0, 0, 0,    1, 2, 0, 0, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY),  NOW()),
(6002, 'Vaccinatie Mimi',   'Jaarlijkse vaccinatie, alles in orde.', 2002, 2, 1, 7001, 1, 2, 0, 0, 2, 0, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(6003, 'Consultatie Bobby', 'Kreupel links voor.',               2003, 0, 0, 7002, 1, 2, 0, 0, 1, 0, DATE_SUB(NOW(), INTERVAL 2 DAY),  NOW());

INSERT INTO `events_products` (`id`, `product_id`, `event_id`, `volume`, `price_net`, `price_brut`, `unit_price`, `btw`, `booking`, `stock_id`, `created_at`, `updated_at`) VALUES
(8001, 4001, 6001, 2.00, 20.00, 21.20, 10.00, 6, 1, 5001, NOW(), NOW()),
(8002, 4002, 6002, 1.00, 20.00, 21.20, 20.00, 6, 1, 5003, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO `events_procedures` (`id`, `procedures_id`, `event_id`, `volume`, `price_net`, `price_brut`, `unit_price`, `btw`, `booking`, `created_at`, `updated_at`) VALUES
(8101, 3001, 6001, 1.00, 25.00, 30.25, 25.00, 21, 2, NOW(), NOW()),
(8102, 3001, 6002, 1.00, 25.00, 30.25, 25.00, 21, 2, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(8103, 3001, 6003, 1.00, 25.00, 30.25, 25.00, 21, 2, NOW(), NOW());

-- ---------------------------------------------------------------------------------------------
-- vaccines (11001 due 15th of next month, 11002 expired 2 months ago, 11003 reminder switched off)
-- ---------------------------------------------------------------------------------------------
INSERT INTO `vaccine_pet` (`id`, `product_id`, `event_id`, `event_line`, `product`, `pet`, `redo`, `no_rappel`, `location`, `vet`, `created_at`, `updated_at`) VALUES
(11001, 4002, 6002, 8002, 'Nobivac Vaccin', 2002, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-15'), INTERVAL 1 MONTH),  0, 1, 2, DATE_SUB(NOW(), INTERVAL 10 DAY), NOW()),
(11002, 4002, 0,    NULL, 'Nobivac Vaccin', 2001, DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-15'), INTERVAL 2 MONTH),  0, 1, 2, DATE_SUB(NOW(), INTERVAL 14 MONTH), NOW()),
(11003, 4002, 0,    NULL, 'Nobivac Vaccin', 2003, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-15'), INTERVAL 1 MONTH),  1, 1, 2, DATE_SUB(NOW(), INTERVAL 11 MONTH), NOW());

-- ---------------------------------------------------------------------------------------------
-- lab report for Rex, x-rays for Rex, a stat query and a sticky note
-- ---------------------------------------------------------------------------------------------
INSERT INTO `lab_report` (`id`, `pet_id`, `device`, `source`, `source_id`, `software_version`, `metadata`, `sample_date`, `created_at`, `updated_at`) VALUES
(10001, 2001, 'ms4s2', 'test', 'S-0001', '1.0', '{}', DATE_SUB(NOW(), INTERVAL 5 DAY), NOW(), NOW());

INSERT INTO `lab_results` (`id`, `report_id`, `code`, `value_num`, `value_text`, `unit`, `ref_min`, `ref_max`) VALUES
(10001, 10001, 'ALT',  45.00,  NULL, 'U/L',   10.00, 100.00),
(10002, 10001, 'CREA', 180.00, NULL, 'umol/L', 40.00, 150.00),
(10003, 10001, 'GLU',   4.50,  NULL, 'mmol/L', 3.50,   6.50);

INSERT INTO `rx` (`id`, `path`, `pet_id`, `studydate`, `description`, `bodypart`, `client`, `petname`, `gender`, `series`, `created_at`, `updated_at`) VALUES
(1, '2024-01-10_a/img1.jpg', 2001, '2024-01-10', 'Thorax LL',  'THORAX', 'JANSSENS', 'REX', 1, 1, NOW(), NOW()),
(2, '2024-01-10_a/img2.jpg', 2001, '2024-01-10', 'Thorax VD',  'THORAX', 'JANSSENS', 'REX', 1, 2, NOW(), NOW()),
(3, '2024-03-02_b/img1.jpg', 2001, '2024-03-02', 'Abdomen LL', 'ABDOMEN', 'JANSSENS', 'REX', 1, 1, NOW(), NOW());

INSERT INTO `stats` (`id`, `title`, `query`, `help`, `created_at`, `updated_at`) VALUES
(1, 'Nieuwe klanten', 'SELECT COUNT(*) AS n FROM owners WHERE created_at > ''%s'' AND created_at < ''%s''', 'Aantal nieuwe klanten', NOW(), NOW());

INSERT INTO `sticky` (`id`, `user_id`, `location`, `note`, `private`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Test sticky', 0, NOW(), NOW());
