<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Product_label_model extends MY_Model
{
	public $table = 'products_label';
	public $primary_key = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	public function get_for_product(int $product_id): array
	{
		return $this->db
			->select('products_label.id, products_label.name')
			->from('products_product_label')
			->join('products_label', 'products_label.id = products_product_label.label_id')
			->where('products_product_label.product_id', $product_id)
			->order_by('products_label.name', 'ASC')
			->get()
			->result_array();
	}

	public function replace_product_labels(int $product_id, array $label_ids): bool
	{
		$label_ids = array_values(array_unique(array_filter(array_map('intval', $label_ids))));
		$valid_label_ids = array();

		if (!empty($label_ids)) {
			$valid_label_ids = array_map(
				'intval',
				array_column(
					$this->db
						->select('id')
						->from($this->table)
						->where_in('id', $label_ids)
						->get()
						->result_array(),
					'id'
				)
			);
		}

		$this->db->trans_start();
		$this->db->where('product_id', $product_id)->delete('products_product_label');

		if (!empty($valid_label_ids)) {
			$insert = array();
			foreach ($valid_label_ids as $label_id) {
				$insert[] = array(
					'product_id' => $product_id,
					'label_id' => $label_id,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				);
			}
			$this->db->insert_batch('products_product_label', $insert);
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function delete_with_links(int $label_id): bool
	{
		$this->db->trans_start();
		$this->db->where('label_id', $label_id)->delete('products_product_label');
		$this->delete($label_id);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
