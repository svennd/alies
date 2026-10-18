<?php

/**
 * Router script for PHP's built-in web server, used for the HTTP tests:
 *
 *   CI_ENV=testing php -S 127.0.0.1:8089 -t . tests/bin/router.php
 *
 * Static files (assets/, node_modules/, ...) are served directly, every other request goes to the
 * front controller, like the try_files rule in the nginx config does.
 */

$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = $root . $path;

if ($path !== '/' && is_file($file) && !preg_match('/\.php$/i', $path)) {
    return false;
}

// The built-in server does not copy environment variables into $_SERVER, but index.php reads
// $_SERVER['CI_ENV']. This router only exists for the tests, so always use the testing environment
// (it has its own database settings, see application/config/testing/).
$_SERVER['CI_ENV'] = 'testing';

chdir($root);
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';

require $root . '/index.php';
