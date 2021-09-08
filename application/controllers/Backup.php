<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Backup extends Admin_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		// helpers
		$this->load->helper('file');
		$this->load->helper('download');
	}
	

	public function index()
	{
		# load dbutils
		$this->load->dbutil();
	
		# get all tables
		$tables = $this->db->query('SELECT
									  TABLE_NAME as table_name,
									  ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024),2) AS `size`
									FROM
									  information_schema.TABLES
									WHERE
									  TABLE_SCHEMA = "' . $this->db->database . '"
									ORDER BY
									  (DATA_LENGTH + INDEX_LENGTH)
									DESC;')->result_array();

		$data = array(
						"tables" => $tables,
					);

		$this->_render_page('admin/backup', $data);
	}
	
	public function sql($table = false, $zip = false)
	{
		# load dbutils
		$this->load->dbutil();
		
		# in case we want a full backup;
		if ($table == 'all') {
			$table = false;
		}
		
		# let compression be done on client side
		$prefs = array(
			'format'      => ($zip) ? 'zip' : 'txt',
			'filename'    => ($table) ? 'db_' .  $table . '_' . date("Y-m-d-H-i-s"). '.sql' : 'db_full_' . date("Y-m-d-H-i-s"). '.sql',
		);
	
		# if specific table
		if ($table) {
			$prefs['tables'] = array($table);
		}
		
		# hacky way to add .zip to name
		if ($zip) {
			$prefs['filename'] = $prefs['filename']. ".zip";
		}
		
		$this->settings->update(array("value" => '1'), array("name" => "backup_count"));
		$backup = $this->dbutil->backup($prefs);
		force_download($prefs['filename'], $backup);
	}
}
