<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Lab_pending_identity_selector.php';

use PHPUnit\Framework\TestCase;

final class LabPendingIdentitySelectorTest extends TestCase
{
	private Lab_pending_identity_selector $selector;

	protected function setUp(): void
	{
		$this->selector = new Lab_pending_identity_selector();
	}

	public function testNewestActiveRowWinsAndReceiptRangeIsPreserved(): void
	{
		$plan = $this->selector->plan([
			$this->row(1, '2026-08-30 09:00:00'),
			$this->row(2, '2026-08-30 11:00:00'),
			$this->row(3, '2026-08-30 10:00:00', ['deleted_at' => '2026-08-30 12:00:00']),
		], null);

		$this->assertSame(2, $plan['canonical']['id']);
		$this->assertSame('2026-08-30 09:00:00', $plan['created_at']);
		$this->assertSame('2026-08-30 11:00:00', $plan['last_received_at']);
	}

	public function testMatchingResolvedReportTakesLifecyclePrecedence(): void
	{
		$plan = $this->selector->plan([
			$this->row(1, '2026-08-30 09:00:00', ['resolved_at' => '2026-08-30 09:30:00', 'report_id' => 44]),
			$this->row(2, '2026-08-30 11:00:00'),
			$this->row(3, '2026-08-30 10:00:00', ['resolved_at' => '2026-08-30 10:30:00', 'report_id' => 55]),
		], ['id' => 55, 'pet_id' => 9]);

		$this->assertSame(3, $plan['canonical']['id']);
	}

	public function testNewestLifecycleRowWinsWhenNoActiveOrMatchingReportExists(): void
	{
		$plan = $this->selector->plan([
			$this->row(1, '2026-08-30 09:00:00', ['deleted_at' => '2026-08-30 09:30:00']),
			$this->row(2, '2026-08-30 11:00:00', ['resolved_at' => '2026-08-30 11:30:00']),
		], null);

		$this->assertSame(2, $plan['canonical']['id']);
	}

	private function row(int $id, string $createdAt, array $overrides = []): array
	{
		return array_merge([
			'id' => $id,
			'created_at' => $createdAt,
			'last_received_at' => $createdAt,
			'resolved_at' => null,
			'deleted_at' => null,
			'report_id' => null,
		], $overrides);
	}
}
