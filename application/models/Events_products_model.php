<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Events_products_model extends MY_Model
{
	public $table = 'events_products';
	public $primary_key = 'id';
	
	public function __construct()
	{
		/*
			has_one
		*/
		$this->has_one['product'] = array(
					'foreign_model' => 'Products_model',
					'foreign_table' => 'products',
					'foreign_key' => 'id',
					'local_key' => 'product_id'
				);

		$this->has_one['event'] = array(
					'foreign_model' => 'Events_model',
					'foreign_table' => 'events',
					'foreign_key' => 'id',
					'local_key' => 'event_id'
				);
				
		$this->has_one['stock'] = array(
					'foreign_model' => 'Stock_model',
					'foreign_table' => 'stock',
					'foreign_key' => 'id',
					'local_key' => 'stock_id'
				);
				
		$this->has_one['vaccine'] = array(
					'foreign_model' => 'Vaccine_model',
					'foreign_table' => 'vaccine_pet',
					'foreign_key' => 'event_line',
					'local_key' => 'id'
				);
				
		/*
			has_many
		*/
		$this->has_many['prices'] = array(
					'foreign_model' => 'Product_price_model',
					'foreign_table' => 'products_price',
					'foreign_key' => 'product_id',
					'local_key' => 'product_id'
				);
				
		parent::__construct();
	}

	public function get_monthly_earning(datetime $date)
	{
		$sql = "
			SELECT 
				sum(price_net) as total,
				btw
			FROM
				events_products
			WHERE
				DATE(created_at) >= STR_TO_DATE('" . $date->format('Y-m-d') . "', '%Y-%m-%d')
			AND
				DATE(created_at) <= LAST_DAY('" . $date->format('Y-m-d') . "')
			GROUP BY
				btw
			";
	
		
		$result = $this->db->query($sql)->result_array();

		if (!$result) {return array("6" => 0, "21" => 0, "total" => 0); }
		$return = array();
		$total = 0;
		foreach ($result as $r)
		{
			$return[$r['btw']] = $r['total'];
			$total += $r['total'];
		}
		$return['total'] = $total;
		return $return;
	}

	public function get_monthly_usage(int $product, int $month = 6)
	{
		$sql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month_year,
            SUM(volume) AS total_volume
        FROM 
            events_products
        WHERE
            created_at >= DATE_SUB(NOW(), INTERVAL ". $month ." MONTH)
        AND
            product_id = " . $product . "
        GROUP BY 
            YEAR(created_at), MONTH(created_at)
        ORDER BY 
            YEAR(created_at), MONTH(created_at);
        ";
		return $this->db->query($sql)->result_array();
	}

	public function usage_summary($product_id, $search_from, $search_to)
	{
		# WITH rollup = add "total" as a location
		$sql = "
		SELECT 
			IFNULL(stock_location.name, 'total') AS location,
			SUM(volume) AS total_volume,
			SUM(price_net) AS total_netto_price,
			SUM(price_brut) AS total_bruto_price, -- perhaps can be dropped
			COUNT(ep.id) AS total_transactions,
			COUNT(CASE WHEN reduction_reason = 'TIER_REDUCTION' THEN 1 END) AS tier_reduction_count,
			COUNT(CASE WHEN reduction_reason = 'AUTO_REDUCTION' THEN 1 END) AS auto_reduction_count,
			COUNT(CASE WHEN reduction_reason = 'MANUAL' THEN 1 END) AS manual_reduction_count
		FROM
			events_products AS ep
		LEFT JOIN
			events
		ON
			events.id = ep.event_id
		LEFT JOIN 
			stock_location
		ON
			events.location = stock_location.id
		WHERE
			ep.product_id = " . $product_id . "
		AND
			ep.created_at >= STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
		AND
			ep.created_at <= STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
		GROUP BY 
			stock_location.name WITH ROLLUP
	";
		return $this->db->query($sql)->result_array();
	}
}