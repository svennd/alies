<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wholesale extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
	}

	/*
		get history from a single product
	*/
	public function get_history(int $id)
	{
		$this->_render_page('wholesale/history', array("data" => $this->wholesale->with_wholesale_prices()->get($id)));
	}
	/*
	 * used on product/profile/$id
	 */
	public function ajax_get_articles()
	{
		if ($this->input->get("term"))
		{
			$term = $this->input->get("term");
			$articles = $this->wholesale->where('description','like', $term)->get_all();
		}
		# generate a list
		else
		{
			$articles = $this->wholesale->limit(25)->get_all();
        }

		if (!$articles) { return json_encode(array()); }	
        
        # loop the products
		$article_list = array();
		foreach ($articles as $u)
		{
            $article_list[] = array(
                    "id"        => $u['id'], 
                    "text"      => $u['description'],
                    "distr"     => $u['distributor'],
                    "bruto"     => $u['bruto'],
                    "vhb"       => $u['VHB'],
                );
		}

		echo json_encode(array("results" => $article_list));
	}
}
