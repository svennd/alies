<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Rx
class Rx extends Vet_Controller
{

	private string $rx_dir = "data/stored/rx/";

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
	}

	public function list(int $pet_id)
    {
		$pet = $this->pets->get($pet_id);
		$owner = $this->owners->get($pet['owner']);
    	$this->_render_page('rx/index', array(
			"data" => $this->get_jpgs($pet_id),
			"pet" => $pet,
			"owner" => $owner
		));
	}

	private function get_jpgs(int $pet_id)
	{
		if (is_dir($this->rx_dir))
		{
			$files = glob($this->rx_dir . $pet_id . "/*.jpg");
			if (!empty($files)) {
				return $files;
			} 
		}
		return false;
	}
}