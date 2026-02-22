<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Vamreg_in_buffer_model extends MY_Model
{
	public $table = 'vamreg_in_buffer';
	public $primary_key = 'id';
	
	public function __construct()
	{
        # different volumes
        $this->has_many['vamreg_index'] = array(
                    'foreign_model' => 'Vamreg_index_model',
                    'foreign_table' => 'vamreg_index',
                    'foreign_key'   => 'cnk',
                    'local_key'     => 'cnk'
                );
        
        $this->has_one['wholesale'] = array(
                    'foreign_model' => 'Wholesale_model',
                    'foreign_table' => 'wholesale',
                    'foreign_key'   => 'id',
                    'local_key'     => 'wholesale_id'
                );

	    parent::__construct();
	}
	

}
