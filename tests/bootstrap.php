<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

$_SERVER['CI_ENV'] = 'development';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/cli/index';
$_SERVER['argv'] = ['index.php', 'cli', 'index'];
$_SERVER['argc'] = 3;

ob_start();
require_once dirname(__DIR__) . '/index.php';
ob_end_clean();

require_once APPPATH . 'helpers/cnk_helper.php';
require_once APPPATH . 'helpers/gs1_helper.php';
require_once APPPATH . 'helpers/generate_bill_id_helper.php';
require_once APPPATH . 'helpers/online_helper.php';
require_once __DIR__ . '/Support/CodeIgniterDatabaseTestCase.php';
