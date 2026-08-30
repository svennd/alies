<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_lab_pending_identity extends CI_Migration
{
	protected $up_version = "056";
	protected $down_version = "055";

	public function up()
	{
		$this->load->library('lab_source_identity');
		$this->load->library('lab_pending_identity_selector');

		$columns = array(
			'identity_hash' => "CHAR(64) NULL DEFAULT NULL AFTER `source_id`",
			'last_received_at' => "DATETIME NULL DEFAULT NULL AFTER `created_at`",
			'superseded_by_id' => "INT(11) NULL DEFAULT NULL AFTER `deleted_by`",
		);
		foreach ($columns as $name => $definition) {
			if (!$this->db->field_exists($name, 'lab_report_pending')) {
				$this->db->query("ALTER TABLE `lab_report_pending` ADD `{$name}` {$definition};");
			}
		}

		$this->db->query(
			'UPDATE `lab_report_pending` SET `last_received_at` = `created_at` WHERE `last_received_at` IS NULL'
		);
		$this->consolidateIdentities();

		$duplicates = $this->db->query(
			'SELECT `identity_hash`, COUNT(*) AS `row_count` FROM `lab_report_pending` '
			. 'WHERE `identity_hash` IS NOT NULL GROUP BY `identity_hash` HAVING COUNT(*) > 1'
		)->result_array();
		if ($duplicates) {
			throw new RuntimeException('Pending lab identity consolidation left duplicate canonical hashes.');
		}

		$indexes = $this->db->query(
			"SHOW INDEX FROM `lab_report_pending` WHERE Key_name = 'pending_identity'"
		)->result_array();
		if (!$indexes) {
			$this->db->query(
				'ALTER TABLE `lab_report_pending` ADD UNIQUE INDEX `pending_identity` (`identity_hash`)'
			);
		}

		return $this->up_version;
	}

	public function down()
	{
		$indexes = $this->db->query(
			"SHOW INDEX FROM `lab_report_pending` WHERE Key_name = 'pending_identity'"
		)->result_array();
		if ($indexes) {
			$this->db->query('ALTER TABLE `lab_report_pending` DROP INDEX `pending_identity`');
		}

		foreach (array('superseded_by_id', 'last_received_at', 'identity_hash') as $name) {
			if ($this->db->field_exists($name, 'lab_report_pending')) {
				$this->db->query("ALTER TABLE `lab_report_pending` DROP `{$name}`;");
			}
		}

		return $this->down_version;
	}

	private function consolidateIdentities(): void
	{
		$groups = array();
		$rows = $this->db->order_by('created_at', 'ASC')->order_by('id', 'ASC')
			->get('lab_report_pending')->result_array();
		foreach ($rows as $row) {
			$identity = $this->lab_source_identity->derive(
				$row['device'] ?? null,
				$row['source'] ?? null,
				$row['source_id'] ?? null
			);
			if ($identity === null) {
				continue;
			}
			$hash = $identity['hash'];
			if (isset($groups[$hash]) && $groups[$hash]['canonical'] !== $identity['canonical']) {
				throw new RuntimeException('Pending lab source identity hash collision detected.');
			}
			if (!isset($groups[$hash])) {
				$groups[$hash] = array('canonical' => $identity['canonical'], 'identity' => $identity, 'rows' => array());
			}
			$groups[$hash]['rows'][] = $row;
		}

		foreach ($groups as $hash => $group) {
			$report = $this->findReport($group['rows'][0]);
			$plan = $this->lab_pending_identity_selector->plan($group['rows'], $report);
			$canonical = $plan['canonical'];
			$created_at = $plan['created_at'];
			$last_received_at = $plan['last_received_at'];
			$canonicalUpdate = array(
				'identity_hash' => $hash,
				'created_at' => $created_at,
				'last_received_at' => $last_received_at,
				'superseded_by_id' => null,
			);

			if ($report) {
				$canonicalUpdate += array(
					'resolved_at' => $canonical['resolved_at'] ?: $last_received_at,
					'resolved_pet_id' => (int) $report['pet_id'],
					'report_id' => (int) $report['id'],
					'deleted_at' => null,
					'deleted_by' => null,
				);
			}
			$this->db->where('id', (int) $canonical['id'])
				->update('lab_report_pending', $canonicalUpdate);

			foreach ($group['rows'] as $row) {
				if ((int) $row['id'] === (int) $canonical['id']) {
					continue;
				}
				$update = array(
					'identity_hash' => null,
					'superseded_by_id' => (int) $canonical['id'],
				);
				if ($this->lab_pending_identity_selector->is_active($row)) {
					$update['deleted_at'] = $last_received_at;
					$update['deleted_by'] = null;
				}
				$this->db->where('id', (int) $row['id'])
					->update('lab_report_pending', $update);
			}
		}
	}

	private function findReport(array $pending): ?array
	{
		$candidates = $this->lab_source_identity->candidates(
			$pending['device'] ?? null,
			$pending['source'] ?? null,
			$pending['source_id'] ?? null
		);
		foreach ($candidates as $identity) {
			$report = $this->db
				->where($identity['kind'], $identity['authority'])
				->where('source_id', $identity['source_id'])
				->order_by('id', 'DESC')
				->get('lab_report')
				->row_array();
			if ($report) {
				return $report;
			}
		}
		return null;
	}

}
