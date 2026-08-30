<?php

class Lab_pending_identity_selector
{
	public function plan(array $rows, ?array $report): array
	{
		if (!$rows) {
			throw new InvalidArgumentException('A pending identity group cannot be empty.');
		}

		usort($rows, static function (array $left, array $right): int {
			$time = strcmp((string) $right['created_at'], (string) $left['created_at']);
			return $time !== 0 ? $time : ((int) $right['id'] <=> (int) $left['id']);
		});
		$canonical = null;

		if ($report) {
			foreach ($rows as $row) {
				if (!empty($row['resolved_at']) && (int) ($row['report_id'] ?? 0) === (int) $report['id']) {
					$canonical = $row;
					break;
				}
			}
			if ($canonical === null) {
				foreach ($rows as $row) {
					if (!empty($row['resolved_at'])) {
						$canonical = $row;
						break;
					}
				}
			}
		} else {
			foreach ($rows as $row) {
				if ($this->is_active($row)) {
					$canonical = $row;
					break;
				}
			}
		}
		$canonical = $canonical ?? $rows[0];

		$created = array_filter(array_column($rows, 'created_at'));
		sort($created, SORT_STRING);
		$receipts = array();
		foreach ($rows as $row) {
			$receipts[] = $row['last_received_at'] ?? $row['created_at'];
		}
		rsort($receipts, SORT_STRING);

		return array(
			'canonical' => $canonical,
			'created_at' => (string) reset($created),
			'last_received_at' => (string) reset($receipts),
		);
	}

	public function is_active(array $row): bool
	{
		return empty($row['resolved_at']) && empty($row['deleted_at']);
	}
}
