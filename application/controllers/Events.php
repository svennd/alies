<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Events extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Pets_model', 'pets');
		$this->load->model('Owners_model', 'owners');
		$this->load->model('Products_model', 'products');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Events_upload_model', 'events_upload');
		$this->load->model('Events_procedures_model', 'eproc');
		$this->load->model('Events_products_model', 'eprod');
		$this->load->model('Booking_code_model', 'booking');
		$this->load->model('Vaccine_model', 'vaccine');
	}

	public function new_event($pet)
	{
		# search for open events on this pet
		$result = $this->events->where(array("pet" => $pet, "status" => STATUS_OPEN, "location" => $this->user->current_location))->get_all();

		# there is already an event for this animal
		# update, otherwise create it and redirect
		if ($result > 0) {
			$event_id = $result[0]['id'];
			$this->events->update(array(), $event_id);

			$this->logs->logger($this->user->id, INFO, "update_restart_event", "event_id: " . $event_id);
		} else {
			$event_id = $this->events->insert(array('pet' => $pet, "location" => $this->user->current_location, "vet" => $this->user->id));

			$this->logs->logger($this->user->id, INFO, "new_event", "event_id: " . $event_id);
		}
		redirect('/events/event/' . $event_id);
	}

	public function event($event_id, $update = false)
	{
		$event_info 		= $this->events->with_vet('fields: username')->with_vet_1_sup('fields: username')->with_vet_2_sup('fields: username')->get($event_id);
		$pet_id 				= $event_info['pet'];
		$pet_info 			= $this->pets->with_breeds()->with_pets_weight()->get($pet_id);
		$other_pets 		= $this->pets->where(array('owner' => $pet_info['owner'], 'death' => 0, 'lost' => 0))->fields('id, name')->limit(5)->get_all();
		$eprod 					= $this->eprod
										->with_product('fields: id, name, unit_sell, vaccin, vaccin_freq')
										->with_stock('fields: eol, lotnr, barcode')
										->with_prices('fields: volume, price')
										->with_vaccine('fields: id, redo')
										->where(array("event_id" => $event_id))
										->get_all();

		$data = array(
			"event_state"		=> $event_info['status'],
			"owner" 				=> $this->owners->get($pet_info['owner']),
			"pet"						=> $pet_info,
			"event_info"		=> $event_info,
			"booking_codes"	=> $this->booking->get_all(),
			"event_uploads"	=> $this->events_upload->where(array('event' => $event_id))->get_all(),
			"consumables"		=> $eprod,
			"event_id"			=> $event_id,
			"update" 				=> $update,
			"other_pets"		=> $other_pets,
			"u_location"		=> $this->user->current_location,
			"procedures_d"	=> $this->eproc->with_procedures()->where(array("event_id" => $event_id))->get_all(),
			"extra_header" 	=> inject_trumbowyg('header'),
			"extra_footer" 	=> '<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>' .
													inject_trumbowyg()
		);

		$this->_render_page('event/main', $data);
	}

	# in case its only medication pickup or food
	# and this isn't relevant for medical history purpose
	public function disable_history($event_id)
	{
		$this->events->update(array('no_history' => 1), $event_id);
		redirect('events/event/' . $event_id);
	}

	# the reverse
	public function enable_history($event_id)
	{
		$this->events->update(array('no_history' => 0), $event_id);
		redirect('events/event/' . $event_id);
	}

	# annoying but its allowed
	# edit the price based on what the vet tells us
	public function edit_price($event_id)
	{

		# only if price is different from original price
		if ($this->input->post('price') != $this->input->post('ori_net_price')) {
			# update procedure
			if ($this->input->post('submit') == 'store_proc_price') {
					$this->eproc->update(array(
												"net_price" 		=> $this->input->post('price'),
												"price" 			=> $this->input->post('price')*((100 + $this->input->post('btw'))/100),
												"calc_net_price"	=> $this->input->post('ori_net_price')
											), array("id" => $this->input->post('event_proc_id'), "procedures_id" => $this->input->post('pid'), "event_id" => $event_id));
			} elseif ($this->input->post('submit') == 'store_prod_price') {
					$this->eprod->update(array(
												"net_price" 		=> $this->input->post('price'),
												"price" 			=> $this->input->post('price')*((100 + $this->input->post('btw'))/100),
												"calc_net_price"	=> $this->input->post('ori_net_price')
											), array("id" => $this->input->post('event_product_id'), "product_id" => $this->input->post('pid'), "event_id" => $event_id));
			}
		}

		$eprod 				= $this->eprod
											->with_product('fields: id, name, unit_sell, vaccin, vaccin_freq')
											->with_stock('fields: eol, lotnr, barcode')
											->with_prices('fields: volume, price')
											->with_vaccine('fields: id, redo')
											->where(array("event_id" => $event_id))
											->get_all();
		$eproc				= $this->eproc->with_procedures()->where(array("event_id" => $event_id))->get_all();

		$event_info 		= $this->events->get($event_id);
		$pet_info 			= $this->pets->get($event_info['pet']);

		$data = array(
						"event_info"		=> $event_info,
						"owner" 				=> $this->owners->get($pet_info['owner']),
						"pet"						=> $pet_info,
						"consumables"		=> $eprod,
						"procedures_d"	=> $eproc,
					);

		$this->_render_page('event/price_edit', $data);
	}

	# auto reduce all procedures & products based on $reduction
	public function edit_event_price($event_id, $reduction)
	{
		$eprod = $this->eprod->where(array("event_id" => $event_id))->get_all();
		if ($eprod)
		{
			foreach ($eprod as $prod) {
				if ($prod['calc_net_price'] != 0) {
					$new_net_price = $prod['calc_net_price'] * ((100 - $reduction) / 100);
				} else {
					$new_net_price = $prod['net_price'] * ((100 - $reduction) / 100);
				}

				$this->eprod->where(array('id' => $prod['id'], 'event_id' => $event_id))->update(array(
								"net_price" 			=> $new_net_price,
								"price" 					=> $new_net_price * ((100 + $prod['btw'])/100),
								"calc_net_price"	=> ($prod['calc_net_price'] != 0) ? $prod['calc_net_price'] : $prod['net_price']
							));
			}
		}

		$eproc = $this->eproc->where(array("event_id" => $event_id))->get_all();
		if ($eproc)
		{
			foreach ($eproc as $proc) {
				if ($proc['calc_net_price'] != 0) {
					$new_net_price = $proc['calc_net_price'] * ((100 - $reduction) / 100);
				} else {
					$new_net_price = $proc['net_price'] * ((100 - $reduction) / 100);
				}
				$this->eproc->where(array('id' => $proc['id'], 'event_id' => $event_id))->update(array(
								"net_price" 			=> $new_net_price,
								"price" 					=> $new_net_price * ((100 + $proc['btw'])/100),
								"calc_net_price"	=> ($proc['calc_net_price'] != 0) ? $proc['calc_net_price'] : $proc['net_price']
							));
			}
		}
		redirect('events/edit_price/' . $event_id, 'refresh');
	}

	#
	# reports
	#
	public function update_report($event_id)
	{
		if ($this->events->get_status($event_id) == STATUS_HISTORY) {
			echo "cannot change due to status : status_history";
			return false;
		}

		if ($this->input->post('submit') != 'report' && $this->input->post('submit') != 'finished_report') { echo "no post data"; return false; }

		$this->events->update(
			array(
					"title" 						=> $this->input->post('title'),
					"anamnese" 					=> $this->input->post('anamnese'),
					"type" 							=> (int) $this->input->post('type'),
					"vet_support_1"			=> (!empty($this->input->post('supp_vet_1'))) ? (int) $this->input->post('supp_vet_1') : 0,
					"vet_support_2" 		=> (!empty($this->input->post('supp_vet_2'))) ? (int) $this->input->post('supp_vet_2') : 0,
					"report"						=> ($this->input->post('submit') == 'finished_report') ? REPORT_DONE : REPORT_OPEN,
					),
			$event_id
		);

		# report is finished, redirect to pet overview
		if ($this->input->post('submit') == 'finished_report') {
			redirect('/pets/fiche/' . $this->input->post('pet_id'));
		}
		redirect('/events/event/' . $event_id . '/report');

	}

	public function add_proc_prod($event_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}

		$pid = (int) $this->input->post('pid');
		$btw = $this->input->post('btw');
		$booking = $this->input->post('booking_default');

		# nothing given
		if (!$pid) {
			redirect('events/event/' . $event_id);
		}

		if ($this->input->post('prod')) {
			// check if we have to deal with a differnt booking_code + btw
			if ($this->input->post('booking_default') != $this->input->post('booking_code')) {
				$result = $this->booking->fields('btw, id')->get($this->input->post('booking_code'));
				$booking = $result['id'];
				$btw = $result['btw'];
			}

			// add product to event
			list($price, $net_price) = $this->calculate_price_product($pid, $this->input->post('volume'), $btw);

			$prod_line = $this->eprod->insert(array(
								"product_id" 	=> $pid,
								"event_id"		=> $event_id,
								"volume"		=> $this->input->post('volume'),
								"barcode"		=> (!empty($this->input->post('barcode'))) ? $this->input->post('barcode') : '',
								"btw"			=> $btw,
								"booking"		=> $booking,
								"price"			=> $price,
								"net_price"		=> $net_price,
							));

			# in case its a vaccin
			# add it to the table
			if ($this->input->post('vaccin')) {
				$event = $this->events->fields('pet')->get($event_id);

				$date = new DateTime();
				$date->modify('+' . $this->input->post('vaccin_freq') . ' day');

				$this->vaccine->insert(array(
											"product_id" 	=> $pid,
											"event_id" 		=> $event_id,
											"event_line"	=> $prod_line,
											"pet" 			=> $event['pet'],
											"redo"			=> $date->format('Y-m-d'),
											"location"		=> $this->user->current_location,
											"vet"			=> $this->user->id
										));
			}

			redirect('events/event/' . $event_id);
		} else {
			// check if we have to deal with a differnt booking_code + btw
			if ($this->input->post('booking_default') != $this->input->post('booking_code')) {
				$result 	= $this->booking->fields('btw, id')->get($this->input->post('booking_code'));
				$booking 	= $result['id'];
				$btw 		= $result['btw'];
			}

			// add procedure to event
			$volume = (empty($this->input->post('volume'))) ? 1 : $this->input->post('volume');

			// don't trust the vet recalculate based on input
			$proc_info = $this->procedures->where(array("id" => $pid))->get();

			$net_price = (float)$proc_info['price'] * $volume;

			$this->eproc->insert(array(
						"procedures_id" 	=> $pid,
						"event_id" 			=> $event_id,
						"amount"			=> $volume,
						"net_price" 		=> $net_price,
						"booking" 			=> $booking,
						"price" 			=> round(($net_price * (1 + ($btw/100))), 2, PHP_ROUND_HALF_UP),
						"btw" 				=> $btw
					));
			redirect('events/event/' . $event_id);
		}
	}

	private function calculate_price_product($pid, $volume, $btw)
	{
		$this->load->model('Product_price_model', 'prices');

		# get all prices in a sortable array
		$all_prices = $this->prices->fields('volume, price')->where(array("product_id" => $pid))->get_all();
		$prices = array();
		foreach ($all_prices as $price) {
			$prices[] = $price['price'];
			$volumes[] = $price['volume'];
		}

		# determ the price to use per volume
		$to_use_price = array();
		if (count($all_prices) == 1) {
			$to_use_price = $prices[0];
		} else {
			# sort so we can loop
			array_multisort($volumes, $prices);

			$i = 0;
			foreach ($prices as $price) {
				if ($volume < $volumes[$i]) {
					$to_use_price = ($i == 0) ? $prices[0] : $prices[$i-1];
					break;
				} else {
					$to_use_price = $prices[$i];
				}
				$i++;
			}
		}
		$net_price = $to_use_price * $volume;
		$price = round(($net_price * (1 + ($btw/100))), 2, PHP_ROUND_HALF_UP);
		return array($price, $net_price);
	}

	public function edit_vaccin($event_id, $id)
	{
		$this->vaccine->update(array("redo" => $this->input->post('redo')), $id);
		redirect('events/event/' . $event_id);
	}

	#
	# Procedure CRUD
	#

	# adapt a procedure
	public function proc_edit($event_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}
		echo "not implemented yet <a href='". base_url() ."/events/event/" . $event_id . "/consumables'>return</a>";
	}

	# remove a procedure
	public function proc_remove($event_id, $ep_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}
		# remove ep
		$this->eproc->delete($ep_id);

		# push an event update
		$this->events->update(array(), $event_id);

		# get back
		redirect('/events/event/' . $event_id);
	}

	#
	# Products CRUD
	#

	# adapt product
	public function prod_edit($event_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}
		if ($this->input->post('submit') == 'edit_prod') {
			list($price, $net_price) = $this->calculate_price_product($this->input->post('pid'), $this->input->post('volume'), $this->input->post('btw'));
			$this->eprod->where(array("id" => $this->input->post('event_product_id'), "event_id" => $event_id))->update(array(
														"volume" 		=> $this->input->post('volume'),
														"price"			=> $price,
														"net_price"		=> $net_price,
										));
			redirect('/events/event/' . $event_id);
		} else {
			echo "no post data";
			return false;
		}
	}

	# remove a product
	public function prod_remove($event_id, $product_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}
		# remove ep
		$this->eprod->delete($product_id);

		# in case its an vaccine
		$this->vaccine->where(array('event_line' => $product_id, 'event_id' => $event_id))->delete();

		# push an event update
		$this->events->update(array(), $event_id);

		# get back
		redirect('/events/event/' . $event_id);
	}

	# delete event
	public function del($event_id, $owner_id)
	{
		$this->events->delete($event_id);
		redirect('/owners/detail/' . $owner_id);
	}

	# lock event
	public function lock($event_id)
	{
		$this->events->update(array("status" => STATUS_CLOSED), $event_id);
		redirect('/events/event/' . $event_id);
	}
}
