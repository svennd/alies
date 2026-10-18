<?php

declare(strict_types=1);

/**
 * Bootstrap for the suites that need CodeIgniter 3 in-process (model tests, legacy tests):
 * boots the framework in CLI mode in the testing environment, so the database is alies_test.
 * Pure and HTTP tests use bootstrap-light.php instead.
 */

date_default_timezone_set('UTC');

// never the development environment: it points at the real database
$_SERVER['CI_ENV'] = 'testing';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/cli/index';
$_SERVER['argv'] = ['index.php', 'cli', 'index'];
$_SERVER['argc'] = 3;

require_once dirname(__DIR__) . '/vendor/autoload.php';

ob_start();
require_once dirname(__DIR__) . '/index.php';
ob_end_clean();

require_once APPPATH . 'helpers/cnk_helper.php';
require_once APPPATH . 'helpers/gs1_helper.php';
require_once APPPATH . 'helpers/generate_bill_id_helper.php';
require_once APPPATH . 'helpers/online_helper.php';

require_once __DIR__ . '/Support/TestDatabase.php';
require_once __DIR__ . '/Support/Fixtures.php';
require_once __DIR__ . '/Support/Factory.php';
require_once __DIR__ . '/Support/Forms.php';
require_once __DIR__ . '/Support/CodeIgniterDatabaseTestCase.php';
