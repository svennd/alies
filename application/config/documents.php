<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Server configuration only; never accept these paths from an HTTP request.
$config['binary'] = getenv('ALIES_TYPST_BINARY') ?: '/usr/local/bin/typst';
$config['font_path'] = getenv('ALIES_TYPST_FONT_PATH') ?: '';
$config['temporary_path'] = sys_get_temp_dir() . '/alies-documents';
$config['compile_timeout'] = 10;
$config['request_timeout'] = 60;
$config['asset_path'] = FCPATH . 'data/documents';
