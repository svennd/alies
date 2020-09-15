<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Procedures extends MY_Model
{
    public $table = 'procedures';
    public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}