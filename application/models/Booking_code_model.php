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

	public function get_usage( int $booking, string $search_from, string $search_to)
	{
		$sql = "
			select 
				ep.net_price, ep.calc_net_price,
				stock.in_price,
				stock_location.name,
				events.id,
				products.name as pname
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
				stock
			ON
				ep.barcode = stock.barcode
			LEFT JOIN 
				stock_location
			ON
				events.location = stock_location.id
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
		return $this->db->query($sql)->result_array();
	}
}
