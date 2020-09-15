<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Breeds_model extends MY_Model
{
    public $table = 'breeds';
    public $primary_key = 'id';
	
	public function __construct()
	{
		
		$this->has_many['pets'] = array(
			'foreign_model' => 'Pets_model',
			'foreign_table' => 'pets',
			'foreign_key' => 'breed',
			'local_key' => 'id'
		);
				
		parent::__construct();
	}
	
}