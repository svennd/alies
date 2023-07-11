<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricing extends Accounting_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
				
		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Procedures_model', 'proc');
		$this->load->model('Products_model', 'prod');
		$this->load->model('Booking_code_model', 'book');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Product_price_model', 'pprice');
		$this->load->model('Events_procedures_model', 'eproc');
	}

	public function prod($id = false)
	{
		# show list with all products prices
		# unless single product is selected
		if (!$id) {
			$this->list_product_page();
			return true;
		}

		# modification
		$modification = false;
		if ($this->input->post('submit')) {

			$modification = ($this->input->post('submit') == "edit") ?
				$this->pprice->update_price((int) $id, $this->input->post('price_id'), $this->input->post('price'), $this->input->post('volume'))
				:
				$this->pprice->add_price((int) $id, $this->input->post('price'), $this->input->post('volume'))
				;
		}

		$data = array(
						"product" 		=> $this->products
												->with_prices('fields:volume, price, id|order_inside:volume asc')
												->with_wholesale()
												->where(array("sellable" => 1))
												->fields('id, name, buy_volume, buy_price, buy_price_date, updated_at, unit_buy, unit_sell')
												->get($id),

						"stock_price"	=> $this->stock
												->where(array("product_id" => $id))
												->fields('in_price, volume, created_at')
												->order_by('created_at', 'DESC')
												->limit(5)
												->get_all(),
						"updated"	=> $modification
					);
					
		$this->_render_page('pricing/prod', $data);

	}

	# proc
	public function proc()
	{
		$name  = $this->input->post('name');
		$bkc   = $this->input->post('booking_code');
		$price = $this->input->post('price');

		if ($this->input->post('submit') == "add_proc" && !empty($name)) {
			$this->logs->logger(INFO, "new_procedure", "proc_name: " . $name);
			$this->proc->insert(array(
							"name" 			=> $name,
							"booking_code" 	=> $bkc,
							"price"			=> $price
							));
		}
		
		if ($this->input->post('action') == "edit_proc") {
			$this->logs->logger(INFO, "update_procedure", "proc_name: " . $name . " price :" . $price);
			$this->proc->update(
				array(
									"name" 			=> $name,
									"booking_code" 	=> $bkc,
									"price"			=> $price
								),
				array(
									"id" => (int) $this->input->post('id')
								)
			);
		}

		$data = array(
						"proc" 		=> $this->proc->with_booking_code('fields:code, category, btw')->get_all(),
						"booking" 	=> $this->book->get_all()
					);
	

		$this->_render_page('pricing/procedures', $data);
	}

	# change producedures
	public function proc_edit(int $id)
	{
		$data = array(
				"stat"		=> $this->eproc->get_net_income($id),
				"proc" 		=> $this->proc->with_booking_code('fields:code, category, btw')->get($id),
				"booking" 	=> $this->book->get_all()
			);
			
		$this->_render_page('pricing/procedures_edit', $data);
	}

	# update the manuel price
	public function man(int $pid)
	{
		$this->products->update(array(
					"buy_price" => $this->input->post('buy_price'),
					"buy_price_date" => $this->input->post('buy_price_date')
						), $pid);

		redirect('pricing/prod/' . $pid);
	}

	# remove proc (soft delete)
	public function delete_proc($id)
	{
		$this->proc->delete($id);
		redirect('pricing/proc', 'refresh');
	}

	# remove price
	public function rm_prod_price(int $price_id)
	{
		// remove price, log it
		$pid = $this->pprice->rm_price($price_id);
		redirect('pricing/prod/' . $pid);
	}

	/*
		generate a list with pricings for all products
	*/
	private function list_product_page()
	{
		$data = array(
						"products" 		=> $this->products
												->with_prices('fields:volume, price|order_inside:volume asc')
												->with_wholesale('fields:bruto')
												->fields('name, buy_volume, buy_price, sellable, updated_at, unit_sell')
												->where(array("sellable" => 1))
												->get_all()
					);

		$this->_render_page('pricing/product_list', $data);
	}
}
