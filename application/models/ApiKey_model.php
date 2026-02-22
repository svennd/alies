<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class ApiKey_model extends MY_Model
{
	public $table = 'api_keys';
	public $primary_key = 'id';

    public function findActive(string $key_hash)
    {
        return $this->fields('id')->where(array('key_hash' => $key_hash, 'active' => 1))->get();
    }
}