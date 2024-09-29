<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Debug
class Debug extends Frontend_Controller
{
	// initialize
	public $ion_auth;

	# constructor
	public function __construct()
	{
		parent::__construct();
		
	}

	/*
	* function: downgrade
	* downgrade the database
	*/
    public function downgrade() : void
	{
        if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->load->library('migration');

		$version = $this->db->query("SELECT * FROM `migrations`")->result_array()[0]['version'];
        echo "current version : $version<br/>";
        echo "downgrading to : " . $version - 1 . "<br/>";

        $this->db->query('UPDATE migrations SET version = ' . $version-1 . ';');
		$this->migration->version($version-1);
        
        echo "downgraded.<br/>";
	}

	/*
	* function: upgrade
	* upgrade the database (should not be needed)
	*/
    public function upgrade() : void
	{
        // if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
		$this->load->library('migration');

		$version = $this->migration->latest();
		if ($version === false) {
			show_error($this->migration->error_string());
		}
        
        echo "upgraded : " . var_dump($version);
	}
}
