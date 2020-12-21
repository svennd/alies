<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Events_procedures_model extends MY_Model
{
	public $table = 'events_procedures';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['procedures'] = array(
					'foreign_model' => 'Procedures_model',
					'foreign_table' => 'procedures',
					'foreign_key' => 'id',
					'local_key' => 'procedures_id'
				);
						
		parent::__construct();
	}
}
