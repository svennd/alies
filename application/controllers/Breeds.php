<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Breeds extends Vet_Controller
{
	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Breeds_model', 'breeds');
	}

    # search for types
    public function search_breed(string $query = "")
    {
        $type = !empty($this->input->get('type')) ? (int) $this->input->get('type') : -1;
        if (empty($query))
        {
            $results = $this->breeds
                        ->fields('id, name')
                        ->where(array('type' => $type))
                        ->limit(5)
                        ->order_by('name', 'ASC')
                        ->get_all();
        }
        else
        {
            $results = $this->breeds
                    ->fields('id, name')
                    ->where('name', 'like', $query, true)
                    ->where(array('type' => $type))
                    ->limit(5)
                    ->order_by('name', 'ASC')
                    ->get_all();
        }
        if (!$results) { echo json_encode(array("results" => array())); return 0; }
        foreach ($results as $r) {
        	$return[] = array(
        				"id" => $r['id'],
        				"text" => $r['name']
        				);
        }
        echo json_encode(array("results" => $return));
    }
}