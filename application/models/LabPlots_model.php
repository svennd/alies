<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class LabPlots_model extends MY_Model
{
	public $table = 'lab_plots';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}
}