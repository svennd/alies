<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Events_links_model extends MY_Model
{
	public $table = 'events_links';
	public $primary_key = 'id';

	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['events'] = array(
					'foreign_model' => 'Events_model',
					'foreign_table' => 'events',
					'foreign_key' => 'id',
					'local_key' => 'event_id'
				);
		parent::__construct();
	}

}
