<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Product_type_model extends MY_Model
{
	public $table = 'products_type';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_many['products'] = array(
					'foreign_model' => 'Products_model',
					'foreign_table' => 'products',
					'foreign_key' => 'type',
					'local_key' => 'id'
				);
		parent::__construct();
	}

	public function get_type_options(): array
	{
		$types = $this->get_ordered_types();
		$rootNames = $this->get_root_name_map($types);
		$options = array();

		foreach ($types as $type) {
			$type['display_name'] = $this->format_display_name($type, $rootNames);
			$options[] = $type;
		}

		return $options;
	}

	public function get_admin_listing(): array
	{
		$types = $this->get_ordered_types();
		$rootNames = $this->get_root_name_map($types);
		$listing = array();

		foreach ($types as $type) {
			$type['display_name'] = $this->format_display_name($type, $rootNames);
			$type['root_name'] = is_null($type['root']) ? null : (isset($rootNames[(int) $type['root']]) ? $rootNames[(int) $type['root']] : null);
			$type['depth'] = is_null($type['root']) ? 0 : 1;
			$type['has_children'] = $this->has_children((int) $type['id']);
			$listing[] = $type;
		}

		return $listing;
	}

	public function get_root_options(int $exclude_id = 0): array
	{
		$this->db->where('root IS NULL', null, false);
		if ($exclude_id > 0) {
			$this->db->where('id !=', $exclude_id);
		}

		return $this->db
			->order_by('name', 'ASC')
			->get($this->table)
			->result_array();
	}

	public function normalize_root(?int $root_id, int $current_id = 0)
	{
		$root_id = (int) $root_id;
		if ($root_id <= 0) {
			return null;
		}

		if ($current_id > 0 && $root_id === $current_id) {
			return null;
		}

		$root = $this->db->where('id', $root_id)->get($this->table)->row_array();
		if (!$root || !is_null($root['root'])) {
			return null;
		}

		if ($current_id > 0 && $this->has_children($current_id)) {
			return null;
		}

		return $root_id;
	}

	public function detach_children(int $parent_id): bool
	{
		return (bool) $this->db
			->where('root', $parent_id)
			->update($this->table, array('root' => null));
	}

	public function get_descendant_ids(int $type_id): array
	{
		$children = $this->db
			->select('id')
			->where('root', $type_id)
			->get($this->table)
			->result_array();

		return array_map('intval', array_column($children, 'id'));
	}

	private function has_children(int $type_id): bool
	{
		return $this->db
			->where('root', $type_id)
			->count_all_results($this->table) > 0;
	}

	private function get_ordered_types(): array
	{
		return $this->db
			->order_by('root IS NOT NULL', 'ASC', false)
			->order_by('name', 'ASC')
			->get($this->table)
			->result_array();
	}

	private function get_root_name_map(array $types): array
	{
		$rootNames = array();

		foreach ($types as $type) {
			$rootNames[(int) $type['id']] = $type['name'];
		}

		return $rootNames;
	}

	private function format_display_name(array $type, array $rootNames): string
	{
		if (is_null($type['root'])) {
			return $type['name'];
		}

		$rootName = isset($rootNames[(int) $type['root']]) ? $rootNames[(int) $type['root']] : '';
		return trim($rootName . ' / ' . $type['name'], ' /');
	}
}
