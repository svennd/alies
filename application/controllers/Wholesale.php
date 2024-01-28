<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wholesale extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Register_in_model', 'ri');
	}

	public function index()
	{
		$data = array(
			"products" => $this->wholesale->with_product()->get_all(),
		);
		$this->_render_page('wholesale/index', $data);
	}

	/*
		accept the new price
	*/
	public function accept(int $id, string $from = 'index')
	{
		$this->wholesale->accept_price($id);

		if ($from == 'history')
		{
			redirect('wholesale/get_history/' . $id);
		}
		redirect('wholesale/index');
	}

	/*
		accept the new price
	*/
	public function ignore(int $id, string $from = 'index')
	{
		$this->wholesale->update(array("ignore_change" => 1), $id);

		if ($from == 'history')
		{
			redirect('wholesale/get_history/' . $id);
		}
		redirect('wholesale/index');
	}
	public function unignore(int $id, string $from = 'index')
	{
		$this->wholesale->update(array("ignore_change" => 0), $id);

		if ($from == 'history')
		{
			redirect('wholesale/get_history/' . $id);
		}
		redirect('wholesale/index');
	}

	/*
		get history from a single product
	*/
	public function get_history(int $id)
	{
		# do we know the internal product ?
		$wholesale = $this->wholesale->with_product()->get($id);
		$manual_delivery = false;
		if (isset($wholesale['product']))
		{
			$pid = $wholesale['product']['id'];
			$manual_delivery = $this->ri->where(array('product' => $pid))->with_delivery_slip()->get_all();
		}
		
		$data = array(
						"manual_delivery" => $manual_delivery,
						"data" => $this->wholesale->with_product()->with_deliveries()->with_wholesale_prices()->get($id)
					);
		$this->_render_page('wholesale/history', $data);
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
