<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Events_lab_model extends CI_Model
{
	private $table = 'events_labs';

	public function get_linked_for_event(int $event_id, int $pet_id): array
	{
		return $this->db
			->select('lab_report.*')
			->from($this->table)
			->join('lab_report', 'lab_report.id = events_labs.lab_id', 'inner')
			->where('events_labs.event_id', $event_id)
			->where('lab_report.pet_id', $pet_id)
			->where('lab_report.deleted_at IS NULL', null, false)
			->order_by('lab_report.sample_date', 'DESC')
			->order_by('lab_report.id', 'DESC')
			->get()
			->result_array();
	}

	public function get_linkable_for_event(int $event_id, int $pet_id): array
	{
		$join = 'events_labs.lab_id = lab_report.id AND events_labs.event_id = ' . $this->db->escape($event_id);

		return $this->db
			->select('lab_report.*')
			->from('lab_report')
			->join($this->table, $join, 'left', false)
			->where('lab_report.pet_id', $pet_id)
			->where('lab_report.deleted_at IS NULL', null, false)
			->where('events_labs.lab_id IS NULL', null, false)
			->order_by('lab_report.sample_date', 'DESC')
			->order_by('lab_report.id', 'DESC')
			->get()
			->result_array();
	}

	public function link(int $event_id, int $lab_id, int $pet_id): bool
	{
		$eligible = $this->db
			->select('id')
			->from('lab_report')
			->where('id', $lab_id)
			->where('pet_id', $pet_id)
			->where('deleted_at IS NULL', null, false)
			->limit(1)
			->get()
			->row_array();

		if (!$eligible || $this->relationship_exists($event_id, $lab_id)) {
			return false;
		}

		$this->db->query(
			'INSERT IGNORE INTO `' . $this->table . '` (`event_id`, `lab_id`) VALUES (?, ?)',
			array($event_id, $lab_id)
		);

		return $this->db->affected_rows() === 1;
	}

	public function unlink(int $event_id, int $lab_id): bool
	{
		$this->db
			->where('event_id', $event_id)
			->where('lab_id', $lab_id)
			->delete($this->table);

		return $this->db->affected_rows() === 1;
	}

	public function relationship_exists(int $event_id, int $lab_id): bool
	{
		return $this->db
			->where('event_id', $event_id)
			->where('lab_id', $lab_id)
			->count_all_results($this->table) > 0;
	}
}
