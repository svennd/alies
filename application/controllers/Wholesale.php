<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wholesale extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Register_in_model', 'ri');
		$this->load->model('Delivery_model', 'delivery');
	}


	public function index()
	{
		$data = array(
			"products" => $this->wholesale->with_product()->get_all(),
			"deliveries" => $this->delivery->fields(array('delivery_date', 'count(id) as products', 'sum(amount) as number'), false)->limit(5)->group_by("delivery_date")->order_by('delivery_date', "desc")->get_all(),
		);
		$this->_render_page('wholesale/index', $data);
	}

	public function delivery(string $delivery_date)
	{
		$data = array(
			"deliveries" => $this->delivery->with_wholesale()->where(array('delivery_date' => $delivery_date))->get_all(),
		);
		$this->_render_page('wholesale/delivery', $data);
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

	public function ajax_get_history(int $id)
	{
		$data = $this->wholesale->with_deliveries('fields:delivery_date, bruto_price, netto_price, amount, lotnr, due_date')->get($id);
		echo json_encode($data);
	}
}
