<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Overrides for CI_ENV=testing. CodeIgniter loads config/config.php first and
 * then this file, so only the differences are listed here.
 */
$config['base_url'] = getenv('APP_URL') ?: 'http://127.0.0.1:8089/';

// keep test sessions away from the sessions of a normal run
$config['sess_save_path'] = sys_get_temp_dir() . '/alies-test-sessions';
if (!is_dir($config['sess_save_path'])) {
	@mkdir($config['sess_save_path'], 0777, true);
}
