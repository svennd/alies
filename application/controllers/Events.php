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
		$this->load->model('Bills_model', 'bills');
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

			$this->logs->logger(DEBUG, "update_restart_event", "event_id: " . $event_id);
		} else {
			$event_id = $this->events->insert(array('pet' => $pet, "location" => $this->user->current_location, "vet" => $this->user->id));

			$this->logs->logger(DEBUG, "new_event", "event_id: " . $event_id);
		}
		redirect('/events/event/' . $event_id);
	}

	public function event($event_id, $update = false)
	{
		$event_info 		= $this->events
										->with_vet('fields: first_name')
										->with_vet_1_sup('fields: first_name')
										->with_vet_2_sup('fields: first_name')
									->get($event_id);

		$pet_id 			= $event_info['pet'];
		$pet_info 			= $this->pets->with_pets_weight()->get($pet_id);
		$other_pets 		= $this->pets->where(array('owner' => $pet_info['owner'], 'death' => 0, 'lost' => 0))->fields('id, name')->limit(5)->get_all();

		# todo : write a custom function for this, too complex
		$eprod 				= $this->eprod
										->with_product('fields: id, name, unit_sell, vaccin, vaccin_freq')
										->with_stock('fields: eol, lotnr, id')
										->with_prices('fields: volume, price|order_inside:volume asc')
										->with_vaccine('fields: id, redo, no_rappel')
										->where(array("event_id" => $event_id))
										->get_all();
		$data = array(
			"event_state"		=> $event_info['status'],
			"owner"				=> $this->owners->get($pet_info['owner']),
			"pet"				=> $pet_info,
			"event_info"		=> $event_info,
			"booking_codes"		=> $this->booking->get_all(),
			"event_uploads"		=> $this->events_upload->where(array('event' => $event_id))->get_all(),
			"drawing_temp"		=> glob("data/stored/e" . $event_id . "_*_draw.jpeg"),
			"consumables"		=> $eprod,
			"event_id"			=> $event_id,
			"update" 			=> $update,
			"other_pets"		=> $other_pets,
			"autotemplate"		=> base64_decode($this->conf['autotemplate']['value']),
			"billing_info"		=> ($event_info['status'] != STATUS_CLOSED ) ? false: $this->bills->with_location()->get($event_info['payment']),
			"u_location"		=> $this->user->current_location,
			"procedures_d"		=> $this->eproc->with_procedures()->where(array("event_id" => $event_id))->get_all(),
			"extra_header" 		=> inject_trumbowyg('header'),
			"extra_footer" 		=> '<script src="'. base_url() .'assets/js/jquery.autocomplete.min.js"></script>' .
													inject_trumbowyg()
		);

		if ($event_info['status'] == STATUS_OPEN ) 
		{
			$this->_render_page('event/main', $data);
		}
		else 
		{
			$this->_render_page('event/main_report', $data);
		}
	}

	/**
	 * jquery push from events/block_add*
	 *
	 * @param int $event_id The ID of the event to add the line to.
	 * @param int $type The type of the line to add.
	 * @return void
	 */
	public function add_line(int $event_id, int $type)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			return false;
		}	

		// init
		$content_specific_array = array();

		// clean post values
		$line 	= (int) $this->input->post('line');
		$name 	= $this->input->post('title');
		$volume = $this->input->post('volume');
		$vaccin = (bool) $this->input->post('vaccin');
		$vaccin_freq = (int) $this->input->post('vaccin_freq');
		$stock = (int) $this->input->post('stock');

		// verify the booking code/btw wasn't changed
		list ($btw, $booking) = $this->check_booking(
			(int) $this->input->post('btw'), 
			(int) $this->input->post('booking'), 
			(int) $this->input->post('booking_default')
		);

		// add line to events_procedures
		if ($type == PROCEDURE)
		{
			list($return_id, $net_price, $brut_price, $btw) = $this->add_procedure($line, $volume, $btw, $booking, $event_id);

		}
		// add line to events_products
		elseif ($type == PRODUCT)
		{
			list($return_id, $net_price, $brut_price, $btw) = $this->add_product(
												$line, 
												$volume, 
												$btw, 
												$booking,
												$event_id, 
												$stock
											);
			
			// if its a vaccine add it to this pet
			$this->add_vaccine($vaccin, $vaccin_freq, $line, $name, $event_id, $return_id);

			// add stock info
			$stock_info = $this->stock->fields('id, lotnr, eol, volume')->get($stock);
			$content_specific_array = array(
					"stock_lotnr"	=> $stock_info['lotnr'],
					"stock_eol"		=> $stock_info['eol'],
					"stock_volume"	=> $stock_info['volume'],
				);
		}

		echo json_encode(
			array_merge(
				array(
					"name" 			=> $name, 
					"vaccin" 		=> $vaccin, 
					"volume"		=> $volume, 
					"return"		=> $return_id, 
					"btw" 			=> $btw,
					"net_price" 	=> $net_price,
					"brut_price"	=> $brut_price,
					"type"			=> $type,
					"event_id"		=> $event_id
				),
				$content_specific_array
			)
		);
	}

	// add line to procedures
	private function add_procedure(int $proc, $volume, int $btw, int $booking, int $event)
	{
		// price calculation procedures
		$proc_info 	= $this->procedures->fields('price')->get($proc);
		$unit_price = (float) $proc_info['price'];

		$net_price 	= $unit_price * $volume;
		$brut_price = $net_price * round(1 + ($btw/100), 2);

		// enter in events_proc
		$id = $this->eproc->insert(array(
			'procedures_id' 	=> $proc,
			'event_id' 			=> $event,
			'volume' 			=> $volume,
			'price_net'			=> $net_price,
			'price_brut'		=> $brut_price,
			'btw'				=> $btw,
			'booking'			=> $booking,
		));

		return array($id, $net_price, $brut_price, $btw);
	}

	// add line to product
	private function add_product(int $prod, $volume, int $btw, int $booking, int $event, int $stock)
	{
		// price calculation
		list($net_price, $unit_price) = $this->calculate_price_product($prod, $volume);
		$brut_price = $net_price * round(1 + ($btw/100), 2);

		$id = $this->eprod->insert(array(
			'product_id' 	=> $prod,
			'event_id' 		=> $event,
			'volume' 		=> $volume,
			'price_net' 	=> $net_price,
			'price_brut' 	=> $brut_price,
			'unit_price'	=> $unit_price,
			'btw' 			=> $btw,
			'booking' 		=> $booking,
			'stock_id' 		=> $stock
		));

		return array($id, $net_price, $brut_price, $btw);
	}

	// check if its a vaccine and add it to the vaccine table
	private function add_vaccine(bool $is_vaccin, int $vaccin_freq, int $product_id, string $product_name, int $event, int $event_line)
	{
		if (!$is_vaccin) { return true; }

		// get pet id
		$event_info = $this->events->fields('pet')->get($event);

		// calculate redo date
		$date = new DateTime();
		$date->modify('+' . $vaccin_freq . ' day');

		return $this->vaccine->insert(array(
									"product_id" 	=> $product_id,
									"event_id" 		=> $event,
									"product"		=> $product_name, # backup in case product_id ever gets removed/renamed to something else
									"event_line"	=> $event_line,
									"pet"			=> $event_info['pet'],
									"redo"			=> $date->format('Y-m-d'),
									"no_rappel"		=> 0,
									"location"		=> $this->user->current_location,
									"vet"			=> $this->user->id
								));
	}

	// check if the vet changed the booking code,
	// possible lower or higher btw
	private function check_booking(int $btw, int $booking, int $booking_default)
	{
		if ($booking_default != $booking && $booking != 0) {
			$result 	= $this->booking->fields('btw, id')->get($booking);
			$booking 	= $result['id'];
			$btw 		= $result['btw'];
		}
		return array($btw, $booking);
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
												"price_net" 		=> $this->input->post('price'),
												"price_brut"		=> $this->input->post('price')*((100 + $this->input->post('btw'))/100),
												"price_ori_net"		=> $this->input->post('price_ori_net'),
												"reduction_reason"	=> $this->input->post('reason')
											), array("id" => $this->input->post('event_proc_id'), "procedures_id" => $this->input->post('pid'), "event_id" => $event_id));
			} elseif ($this->input->post('submit') == 'store_prod_price') {
					$this->eprod->update(array(
												"price_net" 		=> $this->input->post('price'),
												"price_brut" 		=> $this->input->post('price')*((100 + $this->input->post('btw'))/100),
												"price_ori_net"		=> $this->input->post('price_ori_net'),
												"reduction_reason"	=> $this->input->post('reason')
											), array("id" => $this->input->post('event_product_id'), "product_id" => $this->input->post('pid'), "event_id" => $event_id));
			}
		}

		$eprod 	= $this->eprod
						->with_product('fields: id, name, unit_sell, vaccin, vaccin_freq')
						->with_stock('fields: eol, lotnr, id')
						->with_prices('fields: volume, price|order_inside:volume asc')
						// ->with_vaccine('fields: id, redo')
						->where(array("event_id" => $event_id))
						->get_all();

		$eproc				= $this->eproc->with_procedures()->where(array("event_id" => $event_id))->get_all();

		$event_info 		= $this->events->get($event_id);
		$pet_info 			= $this->pets->get($event_info['pet']);

		$data = array(
						"event_info"			=> $event_info,
						"owner" 				=> $this->owners->get($pet_info['owner']),
						"pet"					=> $pet_info,
						"consumables"			=> $eprod,
						"procedures_d"			=> $eproc,
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
				if ($prod['price_ori_net'] != 0) {
					$new_net_price = $prod['price_ori_net'] * ((100 - $reduction) / 100);
				} else {
					$new_net_price = $prod['price_net'] * ((100 - $reduction) / 100);
				}

				$this->eprod->where(array('id' => $prod['id'], 'event_id' => $event_id))->update(array(
								"price_net" 			=> $new_net_price,
								"price_brut" 			=> $new_net_price * ((100 + $prod['btw'])/100),
								"price_ori_net"			=> ($prod['price_ori_net'] != 0) ? $prod['price_ori_net'] : $prod['price_net'],
								"reduction_reason"		=> "AUTO_REDUCTION"
							));
			}
		}

		$eproc = $this->eproc->where(array("event_id" => $event_id))->get_all();
		if ($eproc)
		{
			foreach ($eproc as $proc) {
				if ($proc['price_ori_net'] != 0) {
					$new_net_price = $proc['price_ori_net'] * ((100 - $reduction) / 100);
				} else {
					$new_net_price = $proc['price_net'] * ((100 - $reduction) / 100);
				}
				$this->eproc->where(array('id' => $proc['id'], 'event_id' => $event_id))->update(array(
								"price_net" 			=> $new_net_price,
								"price_brut" 			=> $new_net_price * ((100 + $proc['btw'])/100),
								"price_ori_net"			=> ($proc['price_ori_net'] != 0) ? $proc['price_ori_net'] : $proc['price_net'],
								"reduction_reason"		=> "AUTO_REDUCTION"
							));
			}
		}
		redirect('events/edit_price/' . $event_id, 'refresh');
	}

	private function calculate_price_product(int $pid, $volume)
	{
		$this->load->model('Product_price_model', 'prices');

		# get all prices in a sortable array
		$all_prices = $this->prices->fields('volume, price')->order_by("volume", "ASC")->where(array("product_id" => $pid))->get_all();
		$prices = array();
		
		foreach ($all_prices as $price) {
			$prices[] = $price['price'];
			$volumes[] = $price['volume'];
		}
		$size_prices = count($all_prices);

		# determ the price to use per volume
		$to_use_price = ($size_prices == 1) ? $prices[0]: 0;

		if ($size_prices > 1) {
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

		return array($net_price, $to_use_price);
	}

	public function edit_vaccin(int $event_id, int $id)
	{
		if ($this->input->post('disable'))
		{
			$this->vaccine->update(array("no_rappel" => 1), $id);
		}
		else
		{
			$this->vaccine->update(array("redo" => $this->input->post('redo'), "no_rappel" => 0), $id);
		}
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
	public function proc_remove(int $event_id, int $ep_id)
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
	public function prod_edit(int $event_id)
	{
		if ($this->events->get_status($event_id) != STATUS_OPEN) {
			echo "cannot change due to status";
			return false;
		}
		
		if (!is_numeric($this->input->post('volume'))) { echo "You entered a non-numeric value!"; return false; }
		
		list($price, $net_price) = $this->calculate_price_product($this->input->post('pid'), $this->input->post('volume'), $this->input->post('btw'));
		$this->eprod->where(array("id" => $this->input->post('event_product_id'), "event_id" => $event_id))->update(array(
													"volume" 		=> $this->input->post('volume'),
													"price_brut"	=> $price,
													"price_net"		=> $net_price,
									));
		redirect('/events/event/' . $event_id);
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
