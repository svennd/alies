<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_lab_api_backfill extends CI_Migration {

	protected $up_version = "047";
	protected $down_version = "046";
	protected $canonical_map = array();

	public function up()
	{
		$this->config->load('lab/canonical', true);
		$this->canonical_map = (array) $this->config->item('canonical', 'lab/canonical');

		$labs = $this->db->order_by('id', 'asc')->get('lab')->result_array();

		foreach ($labs as $lab)
		{
			$details = $this->db
				->where('lab_id', (int) $lab['id'])
				->order_by('id', 'asc')
				->get('lab_detail')
				->result_array();

			if (empty($lab['pet']))
			{
				$this->store_pending_legacy_report($lab, $details);
				continue;
			}

			$report_id = $this->create_report($lab);

			if ($report_id === null)
			{
				continue;
			}

			# overwrite protection ?
			# this is a new table
			// $has_results = $this->db
			// 	->where('report_id', $report_id)
			// 	->count_all_results('lab_results') > 0;

			// if ($has_results)
			// {
			// 	continue;
			// }

			foreach ($details as $detail)
			{
				$legacy_text = $this->legacy_value_text($detail);
				if ($legacy_text == "niet medegedeeld") { continue; }
				
				$this->db->insert('lab_results', array(
					'report_id'  => $report_id,
					'code'       => $this->canonical_code($detail),
					'value_num'  => $this->legacy_value_num($detail),
					'value_text' => $legacy_text,
					'unit'       => $this->null_if_empty($detail['unit']),
					'ref_min'    => $this->legacy_decimal_or_null($detail['lower_limit']),
					'ref_max'    => $this->legacy_decimal_or_null($detail['upper_limit']),
				));
			}
		}

		return $this->up_version;
	}

	public function down()
	{
		$reports = $this->db->select('id, metadata')->get('lab_report')->result_array();

		foreach ($reports as $report)
		{
			$metadata = json_decode($report['metadata'], true);

			if (!is_array($metadata) || empty($metadata['legacy_lab_table_id']))
			{
				continue;
			}

			$this->db->where('report_id', (int) $report['id'])->delete('lab_results');
			$this->db->where('report_id', (int) $report['id'])->delete('lab_plots');
			$this->db->where('id', (int) $report['id'])->delete('lab_report');
		}

		$this->db
			->where('reason', 'legacy_missing_pet')
			->like('raw_payload', '"legacy_table":"lab"')
			->delete('lab_report_pending');

		return $this->down_version;
	}

	// private function find_or_create_report(array $lab)
	private function create_report(array $lab)
	{
		// $existing = $this->db
		// 	->select('id')
		// 	->where('source', $this->legacy_source($lab))
		// 	->where('source_id', $this->legacy_source_id($lab))
		// 	->get('lab_report')
		// 	->row_array();

		// if ($existing)
		// {
		// 	return (int) $existing['id'];
		// }

		$data = array(
			'pet_id'           => (int) $lab['pet'],
			'device'           => $this->legacy_device($lab),
			'source'           => $this->legacy_source($lab),
			'source_id'        => $this->legacy_source_id($lab),
			'software_version' => null,
			'metadata'         => json_encode($this->legacy_report_metadata($lab)),
			'sample_date'      => $this->legacy_sample_date($lab),
			'updated_at'       => $this->legacy_updated_at($lab),
			'deleted_at'       => $this->null_if_empty($lab['deleted_at']),
			'created_at'       => $this->legacy_created_at($lab),
		);

		$this->db->insert('lab_report', $data);
		return (int) $this->db->insert_id();
	}

	private function store_pending_legacy_report(array $lab, array $details)
	{
		$source = $this->legacy_source($lab);
		$source_id = $this->legacy_source_id($lab);

		$exists = $this->db
			->where('reason', 'legacy_missing_pet')
			->where('source', $source)
			->where('source_id', $source_id)
			->count_all_results('lab_report_pending') > 0;

		if ($exists)
		{
			return;
		}

		$this->db->insert('lab_report_pending', array(
			'device'      => $this->legacy_device($lab),
			'source'      => $source,
			'source_id'   => $source_id,
			'raw_payload' => json_encode(array(
				'legacy_table' => 'lab',
				'lab'          => $lab,
				'details'      => $details,
			)),
			'identifiers' => json_encode(array(
				'legacy_lab_table_id' => (int) $lab['id'],
				'legacy_lab_id'       => (int) $lab['lab_id'],
				'legacy_lab_patient_id' => $lab['lab_patient_id'],
				'pet_id'              => $lab['pet'],
			)),
			'reason'      => 'legacy_missing_pet',
			'updated_at'  => $this->legacy_updated_at($lab),
			'created_at'  => $this->legacy_created_at($lab),
		));
	}

	private function legacy_report_metadata(array $lab)
	{
		$metadata = array(
			'legacy_lab_table_id' => (int) $lab['id'],
			'legacy_lab_id' => (int) $lab['lab_id'],
		);

		if (!empty($lab['lab_patient_id']))
		{
			$metadata['legacy_lab_patient_id'] = (int) $lab['lab_patient_id'];
		}

		if ($this->null_if_empty($lab['lab_comment']) !== null)
		{
			$metadata['legacy_lab_comment'] = $lab['lab_comment'];
		}

		if ($this->null_if_empty($lab['comment']) !== null)
		{
			$metadata['legacy_comment'] = $lab['comment'];
		}

		if ($this->null_if_empty($lab['lab_created_at']) !== null)
		{
			$metadata['legacy_lab_created_at'] = $lab['lab_created_at'];
		}

		if ($this->null_if_empty($lab['lab_updated_at']) !== null)
		{
			$metadata['legacy_lab_updated_at'] = $lab['lab_updated_at'];
		}

		return $metadata;
	}

	private function canonical_code(array $detail)
	{
		$code = $this->null_if_empty($detail['lab_code_text']);

		if ($code === null || $code === '---')
		{
			return (string) $detail['lab_code'];
		}

		return isset($this->canonical_map[$code]) ? $this->canonical_map[$code] : $code;
	}

	private function legacy_value_num(array $detail)
	{
		$string_value = $this->null_if_empty($detail['string_value']);

		if ($string_value !== null && !is_numeric($string_value))
		{
			return null;
		}

		if ($string_value !== null && is_numeric($string_value))
		{
			return (float) $string_value;
		}

		return $this->legacy_decimal_or_null($detail['value']);
	}

	private function legacy_value_text(array $detail)
	{
		$string_value = $this->null_if_empty($detail['string_value']);

		if ($string_value === null)
		{
			return null;
		}

		return is_numeric($string_value) ? null : $string_value;
	}

	private function legacy_decimal_or_null($value)
	{
		if ($value === null || $value === '')
		{
			return null;
		}

		return (float) $value;
	}

	private function legacy_source(array $lab)
	{
		return substr((string) $lab['source'], 0, 64);
	}

	private function legacy_device(array $lab)
	{
		return substr((string) $lab['source'], 0, 64);
	}

	private function legacy_source_id(array $lab)
	{
		return substr((string) $lab['lab_id'], 0, 64);
	}

	private function legacy_sample_date(array $lab)
	{
		// bad date
		// if ($this->null_if_empty($lab['lab_created_at']) !== null)
		// {
		// 	return $lab['lab_created_at'];
		// }

		if ($this->null_if_empty($lab['lab_date']) !== null)
		{
			return $lab['lab_date'] . ' 00:00:00';
		}

		if ($this->null_if_empty($lab['created_at']) !== null)
		{
			return $lab['created_at'];
		}

		if ($this->null_if_empty($lab['updated_at']) !== null)
		{
			return $lab['updated_at'];
		}

		return date('Y-m-d H:i:s');
	}

	private function legacy_created_at(array $lab)
	{

		if ($this->null_if_empty($lab['lab_updated_at']) !== null)
		{
			return $lab['lab_updated_at'];
		}

		if ($this->null_if_empty($lab['lab_created_at']) !== null)
		{
			return $lab['lab_created_at'];
		}

		if ($this->null_if_empty($lab['updated_at']) !== null)
		{
			return $lab['updated_at'];
		}

		# medilab sometimes uses a weird date (2000-01-01)
		if ($this->null_if_empty($lab['created_at']) !== null)
		{
			return $lab['created_at'];
		}

		return date('Y-m-d H:i:s');
	}

	private function legacy_updated_at(array $lab)
	{
		if ($this->null_if_empty($lab['updated_at']) !== null)
		{
			return $lab['updated_at'];
		}

		if ($this->null_if_empty($lab['lab_updated_at']) !== null)
		{
			return $lab['lab_updated_at'];
		}

		if ($this->null_if_empty($lab['created_at']) !== null)
		{
			return $lab['created_at'];
		}

		// if ($this->null_if_empty($lab['lab_created_at']) !== null)
		// {
		// 	return $lab['lab_created_at'];
		// }

		return date('Y-m-d H:i:s');
	}

	private function null_if_empty($value)
	{
		if ($value === null)
		{
			return null;
		}

		if (is_string($value))
		{
			$value = trim($value);
		}

		return $value === '' ? null : $value;
	}
}
