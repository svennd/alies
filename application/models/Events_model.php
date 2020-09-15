<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Events_model extends MY_Model
{
    public $table = 'events';
    public $primary_key = 'id';
	
	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['pet'] = array(
					'foreign_model' => 'Pets_model',
					'foreign_table' => 'pets',
					'foreign_key' => 'id',
					'local_key' => 'pet'
				);
		$this->has_one['location'] = array(
					'foreign_model' => 'Stock_location_model',
					'foreign_table' => 'stock_location',
					'foreign_key' => 'id',
					'local_key' => 'location'
				);
		$this->has_one['vet'] = array(
					'foreign_model' => 'Users_model',
					'foreign_table' => 'users',
					'foreign_key' => 'id',
					'local_key' => 'vet'
				);
				
		/*
			has_many
		*/
		$this->has_many['eprod'] = array(
					'foreign_model' => 'Events_products_model',
					'foreign_table' => 'events_products',
					'foreign_key' => 'event_id',
					'local_key' => 'id'
				);
		$this->has_many['eproc'] = array(
					'foreign_model' => 'Events_procedures_model',
					'foreign_table' => 'events_procedures',
					'foreign_key' => 'event_id',
					'local_key' => 'id'
				);
			
		/*
			pivot
		*/
		$this->has_many_pivot['products'] = array(
						'foreign_model'	=> 'Products_model',
						'pivot_table'	=> 'events_products',
						'local_key'		=> 'id',
						'pivot_local_key' => 'event_id',
						'pivot_foreign_key' => 'product_id',
						'foreign_key' => 'id',
						'get_relate'=> false
		);
		
		$this->has_many_pivot['procedures'] = array(
						'foreign_model'	=> 'Procedures_model',
						'pivot_table'	=> 'events_procedures',
						'local_key'		=> 'id',
						'pivot_local_key' => 'event_id',
						'pivot_foreign_key' => 'procedures_id',
						'foreign_key' => 'id',
						'get_relate'=> false
		);
		
		parent::__construct();
	}
	
	
	/*
		called in invoice
	*/
	public function get_products_and_procedures($event_id)
	{
		$products 	= array();
		$procedures = array();
		$tally 		= array();
		$booking	= array();
		
		/* get products on this event */
		$sql = "
				SELECT product_id, volume, net_price, price, btw, events_products.btw, booking, barcode, products.name, products.unit_sell
				FROM `events_products`
				JOIN
					`products`
				ON
					product_id = products.id
				WHERE `events_products`.`event_id` = " . $event_id . "
		";	
		$product_array = $this->db->query($sql)->result_array();
		if ($product_array)
		{
			foreach ($product_array as $product)
			{
				$products[] = $product;
				$tally[$product['btw']] = (isset($tally[$product['btw']])) ? ( $tally[$product['btw']] + $product['net_price'] ) : $product['net_price'];
				$booking[$product['booking']] = (isset($tally[$product['booking']])) ? ( (float) $tally[$product['booking']] + $product['net_price'] ) : (float) $product['net_price'];
			}
		}
		
		/* get procedures on every event */
		$sql = "
				SELECT procedures_id, amount, net_price, booking, events_procedures.price, btw, procedures.name
				FROM `events_procedures`
				JOIN
					`procedures`
				ON
					procedures_id = procedures.id
				WHERE `events_procedures`.`event_id` = " . $event_id . "
		";	
		$procedure_array = $this->db->query($sql)->result_array();
		if ($procedure_array)
		{
			foreach ($procedure_array as $proc)
			{
				$procedures[] = $proc;
				$tally[$proc['btw']] = (isset($tally[$proc['btw']])) ? (float) ( $tally[$proc['btw']] + $proc['net_price'] ) : (float) $proc['net_price'];
				$booking[$proc['booking']] = (isset($tally[$proc['booking']])) ? ( (float) $tally[$proc['booking']] + $proc['net_price'] ) : (float) $proc['net_price'];
			}
		}
		
		return array(
					"prod" 		=> $products, 
					"proc" 		=> $procedures,
					"tally"		=> $tally,
					"booking"	=> $booking
					);
	}
	
	public function get_status($event_id)
	{
		$status = $this->fields('status')->get($event_id);
		return ($status['status']);
	}
}