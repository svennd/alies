<?php

declare(strict_types=1);

/**
 * Bootstrap for the pure and HTTP suites: no framework is started.
 *
 * Helper files of the application start with `defined('BASEPATH') or exit(...)`, so the two path
 * constants are defined here; tests then require_once the file they test.
 */

date_default_timezone_set('UTC');

$root = dirname(__DIR__);

defined('FCPATH') or define('FCPATH', $root . '/');
defined('BASEPATH') or define('BASEPATH', $root . '/system/');
defined('APPPATH') or define('APPPATH', $root . '/application/');
defined('ENVIRONMENT') or define('ENVIRONMENT', 'testing');

// the application's own constants (STATUS_OPEN, BILL_PAID, ...), so pure tests use the real values
if (is_file(APPPATH . 'config/constants.php')) {
    require_once APPPATH . 'config/constants.php';
}

require_once $root . '/vendor/autoload.php';

require_once __DIR__ . '/Support/TestDatabase.php';
require_once __DIR__ . '/Support/Fixtures.php';
require_once __DIR__ . '/Support/Factory.php';
require_once __DIR__ . '/Support/Forms.php';
require_once __DIR__ . '/Support/HttpTestCase.php';
