<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wholesale extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Wholesale_model', 'wholesale');
		$this->load->model('Register_in_model', 'ri');
		$this->load->model('Delivery_model', 'delivery');
		$this->load->model('Products_model', 'products');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Register_in_model', 'regin');
	}

	public function pricing()
	{
		$data = array(
			"products" 				=> $this->wholesale->get_bruto_increase(100),
			"search_product"		=> $this->products->search_product($this->input->get('search_query')),
			"search_procedure"		=> $this->procedures->search_procedure($this->input->get('search_query')),
		);
		$this->_render_page('wholesale/pricing', $data);
	}

	public function index()
	{
		$data = array(
			"products"		=> $this->wholesale->with_product()->get_all(),
		);
		$this->_render_page('wholesale/index', $data);
	}

	public function delivery_overview()
	{
		/* input */
		$dt = new DateTime();
		$search_to = (!is_null($this->input->post('search_to'))) ? $this->input->post('search_to') : $dt->format('Y-m-d');
		$dt->modify('-1 month');
		$search_from = (!is_null($this->input->post('search_from'))) ? $this->input->post('search_from') : $dt->format('Y-m-d');

		$data = array(
			"deliveries" 		=> $this->delivery
										->fields(array('*', 'count(id) as products', 'sum(amount) as number'), false)
										->where('delivery_date > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
										->where('delivery_date < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)					
										->group_by("delivery_date")
										->order_by('delivery_date', "desc")
										->get_all(),
			"search_from"		=> is_null($search_from) ? date("Y-m-d", strtotime("-1 months")) : $search_from,
			"search_to"			=> is_null($search_to) ? date("Y-m-d") : $search_to,
		);
		$this->_render_page('wholesale/delivery_overview', $data);
	}

	public function delivery(string $delivery_date)
	{
		$data = array(
			"deliveries"	=> $this->delivery->with_wholesale()->where(array('delivery_date' => $delivery_date))->get_all(),
			"regin"			=> $this->regin->get_delivery($delivery_date),
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
		ignore the new price
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
		if($this->input->is_ajax_request())
		{
			$data = $this->wholesale->with_deliveries('fields:delivery_date, bruto_price, netto_price, amount, lotnr, due_date')->get($id);
			echo json_encode($data);
		}
		return false;
	}
}
