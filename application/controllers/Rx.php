<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Rx
class Rx extends Vet_Controller
{

	// data paths - should be in config somewhere
	private const RX_DIR = "data/stored/rx/";

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Rx_model', 'rx');
	}

	public function list(int $pet_id)
    {
		$pet = $this->pets->get($pet_id);
		$owner = $this->owners->get($pet['owner']);

    	$this->_render_page('rx/index', array(
			"data" 	=> $this->rx->get_images($pet_id),
			"pet" 	=> $pet,
			"owner" => $owner,
			"RX_DIR" => self::RX_DIR
		));
	}
}