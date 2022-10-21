<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Log_stock_model extends MY_Model
{
	public $table = 'log_stock';
	public $primary_key = 'id';

	public function __construct()
	{
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'user_id'
				);
		parent::__construct();
	}
}
