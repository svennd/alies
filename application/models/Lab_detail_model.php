<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Lab_detail_model extends MY_Model
{
	public $table = 'lab_detail';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	// lab_id : link to lab table
	// sample_id : link to external resource
	public function add_line(int $lab_id, int $sample_id, array $lab_info) 
	{
		$add_sample = "
		INSERT INTO lab_detail
			(
				lab_id, sample_id,
				value, string_value, upper_limit, lower_limit, report, unit, lab_updated_at, lab_code, lab_code_text, comment,
				updated_at, created_at
			)
		VALUES 
			(

				'". $lab_id ."', '". $sample_id ."', 

				'". $lab_info['resultaat'] ."',
				'". $lab_info['sp_resultaat'] ."',
				'". $lab_info['bovenlimiet'] ."',
				'". $lab_info['onderlimiet'] ."',
				'". $lab_info['rapport'] ."',
				'". $lab_info['eenheid'] ."',
				'". $lab_info['updated_at'] ."',
				'". $lab_info['tabulatie_code'] ."',
				'". $lab_info['lab_code_text'] ."',
				'". $lab_info['commentaar'] ."',

				'" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "'
			)
		ON DUPLICATE KEY UPDATE
			updated_at = '" . date('Y-m-d H:i:s') . "';		
		";
		return $this->db->query($add_sample);
	}
}
