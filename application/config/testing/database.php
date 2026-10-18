<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Database settings for CI_ENV=testing.
 *
 * Everything can be overridden with DB_HOST / DB_USER / DB_PASS / DB_NAME.
 * The defaults match the podman setup (compose service `db`).
 *
 * The tests wipe and reseed this database, so it must never be the
 * development database.
 */
$active_group = 'default';
$query_builder = true;

$test_db_name = getenv('DB_NAME') ?: 'alies_test';

if ($test_db_name === 'alies') {
	exit('Refusing to run the testing environment against the development database "alies".');
}

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => getenv('DB_HOST') ?: 'db',
	'username' => getenv('DB_USER') ?: 'alies',
	'password' => getenv('DB_PASS') ?: 'alies',
	'database' => $test_db_name,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => false,
	'db_debug' => true,
	'cache_on' => false,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => false,
	'compress' => false,
	'stricton' => false,
	'failover' => array(),
	'save_queries' => true
);
