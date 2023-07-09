<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Booking_code_model extends MY_Model
{
	public $table = 'booking_codes';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->soft_deletes = true;
		parent::__construct();
	}

	public function get_usage_sum(int $booking, string $search_from, string $search_to)
	{
		$sum = 0;
		$sql = "
			select 
				SUM(ep.net_price) as sum
			from 
				events_products as ep
			LEFT JOIN
				events
			ON
				events.id = ep.event_id
			where 
				ep.booking = '" . $booking . "' 
			AND
				events.status = ". STATUS_CLOSED ."
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			group by
				ep.booking
		";
		$x = $this->db->query($sql)->result_array();
		if ($x)
		{
			$sum += ($x[0]['sum']);
		}

		$sql = "
			select 
				SUM(ep.net_price) as sum
			from 
				events_procedures as ep
			LEFT JOIN
				events
			ON
				events.id = ep.event_id
			where 
				ep.booking = '" . $booking . "' 
			AND
				events.status = ". STATUS_CLOSED ."
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			group by
				ep.booking
		";
		$x = $this->db->query($sql)->result_array();
		if ($x)
		{
			// var_dump($x[0]);
			$sum += ($x[0]['sum']);
		}
		return $sum;
	}

	public function get_usage_detail( int $booking, string $search_from, string $search_to)
	{
		$sql = "
			select 
				ep.net_price, ep.calc_net_price,
				(select in_price from stock where ep.barcode = stock.barcode limit 1) as in_price,
				stock_location.name,
				events.id,
				products.name as pname,
				owners.last_name as lname,
				bills.invoice_id,
				bills.invoice_date,
				bills.id as bill_id
			from 
				events_products as ep
			JOIN
				products
			ON
				products.id = ep.product_id
			LEFT JOIN
				events
			ON
				events.id = ep.event_id
			LEFT JOIN
				pets
			ON
				events.pet = pets.id
			LEFT JOIN
				owners
			ON
				pets.owner = owners.id
			LEFT JOIN 
				stock_location
			ON
				events.location = stock_location.id
			LEFT JOIN
				bills
			ON
				bills.id = events.payment
			where 
				ep.booking = '" . $booking . "' 
			AND
				events.status = ". STATUS_CLOSED ."
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			order by
				ep.created_at DESC
		";
		$products = $this->db->query($sql)->result_array();

		$sql = "
			select 
				ep.net_price, ep.calc_net_price,
				events.id,
				proc.name, proc.price,
				events.id
			from 
				events_procedures as ep
			JOIN
				procedures as proc
			ON
				proc.id = ep.procedures_id
			
			LEFT JOIN
				events
			ON
				events.id = ep.event_id
				LEFT JOIN
					pets
				ON
					events.pet = pets.id
				LEFT JOIN
					owners
				ON
					pets.owner = owners.id
			where 
				ep.booking = '" . $booking . "' 
			AND
				events.status = ". STATUS_CLOSED ."
			AND
				events.created_at > STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
			AND
				events.created_at < STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
			order by
				ep.created_at DESC
		";
		$proc = $this->db->query($sql)->result_array();

		return array($products, $proc);

	}
}
