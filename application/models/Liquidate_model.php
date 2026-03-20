<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Liquidate_model extends MY_Model
{
	public $table = 'liquidate';
	public $primary_key = 'id';

	public function __construct()
	{

		$this->has_one['vet'] = array(
			'foreign_model' => 'Users_model',
			'foreign_table' => 'users',
			'foreign_key' => 'id',
			'local_key' => 'user'
		);
		$this->has_one['location'] = array(
			'foreign_model' => 'Stock_location_model',
			'foreign_table' => 'stock_location',
			'foreign_key' => 'id',
			'local_key' => 'location'
		);

		$this->has_one['products'] = array(
			'foreign_model' => 'Products_model',
			'foreign_table' => 'products',
			'foreign_key' => 'id',
			'local_key' => 'product_id'
		);
		$this->has_one['bill'] = array(
			'foreign_model' => 'Bills_model',
			'foreign_table' => 'bills',
			'foreign_key' => 'id',
			'local_key' => 'bill_id'
		);

		parent::__construct();
	}

	public function get_unbilled()
	{
		return $this
			->where('bill_id IS NULL', null, null, false, false, true)
			->order_by('created_at', 'ASC')
			->get_all();
	}

	public function count_unbilled(): int
	{
		$result = $this->db->query("SELECT COUNT(*) as total FROM liquidate WHERE bill_id IS NULL")->row_array();
		return (int) $result['total'];
	}

	public function assign_bill(array $ids, int $bill_id): bool
	{
		if (empty($ids)) {
			return false;
		}

		$ids = array_map('intval', $ids);
		$sql = "
			UPDATE
				liquidate
			SET
				bill_id = " . (int) $bill_id . "
			WHERE
				id IN (" . implode(',', $ids) . ")
			AND
				bill_id IS NULL
		";

		return (bool) $this->db->query($sql);
	}

	public function get_bill_rows(int $bill_id): array
	{
		$sql = "
			SELECT
				liquidate.id,
				liquidate.product_id,
				liquidate.product_name,
				liquidate.volume,
				liquidate.eol,
				liquidate.lotnr,
				liquidate.reason,
				liquidate.created_at,
				COALESCE(products.unit_sell, '') AS unit_sell,
				stock_location.name AS location_name
			FROM
				liquidate
			LEFT JOIN
				products
			ON
				products.id = liquidate.product_id
			LEFT JOIN
				stock_location
			ON
				stock_location.id = liquidate.location
			WHERE
				liquidate.bill_id = " . (int) $bill_id . "
			ORDER BY
				liquidate.created_at ASC,
				liquidate.id ASC
		";

		return $this->db->query($sql)->result_array();
	}
}
