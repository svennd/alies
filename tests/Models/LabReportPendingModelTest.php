<?php

declare(strict_types=1);

final class LabReportPendingModelTest extends CodeIgniterDatabaseTestCase
{
    public function testActiveQueueIncludesOldRowsAndExcludesResolvedAndDeletedRows(): void
    {
        $model = $this->model('LabReportPending_model', 'pending_model_test');
        $activeId = $this->insertPending(['created_at' => '2020-01-01 00:00:00']);
        $resolvedId = $this->insertPending(['resolved_at' => date('Y-m-d H:i:s')]);
        $deletedId = $this->insertPending(['deleted_at' => date('Y-m-d H:i:s')]);

        $activeIds = array_map('intval', array_column($model->get_active(), 'id'));

        $this->assertContains($activeId, $activeIds);
        $this->assertNotContains($resolvedId, $activeIds);
        $this->assertNotContains($deletedId, $activeIds);
        $this->assertSame($model->count_active(), $model->count_recent());
    }

	public function testActiveQueueOrdersByLatestReceiptWithLegacyFallback(): void
	{
		$model = $this->model('LabReportPending_model', 'pending_order_test');
		$latestId = $this->insertPending([
			'created_at' => '2026-08-30 10:00:00',
			'last_received_at' => '2026-08-30 13:00:00',
		]);
		$legacyId = $this->insertPending([
			'created_at' => '2026-08-30 12:00:00',
			'last_received_at' => null,
		]);

		$ids = array_map('intval', array_column($model->get_active(), 'id'));
		$this->assertLessThan(array_search($legacyId, $ids, true), array_search($latestId, $ids, true));
	}

    public function testLockResolveAndSoftDeleteRetainPayloadAndAuditFields(): void
    {
        $model = $this->model('LabReportPending_model', 'pending_lifecycle_test');
        $petId = $this->existingId('pets');
        $userId = $this->existingId('users');
        $reportId = $this->insertReport($petId);
        $resolvedId = $this->insertPending();
        $deletedId = $this->insertPending();

        $this->assertSame($resolvedId, (int) $model->lock_active($resolvedId)['id']);
        $this->assertTrue($model->mark_resolved($resolvedId, $reportId, $petId, $userId));
        $this->assertFalse($model->mark_resolved($resolvedId, $reportId, $petId, $userId));
        $this->assertFalse($model->soft_delete_active($resolvedId, $userId));

        $this->assertTrue($model->soft_delete_active($deletedId, $userId));
        $this->assertFalse($model->soft_delete_active($deletedId, $userId));
        $this->assertNull($model->lock_active($deletedId));

        $resolved = $this->ci->db->where('id', $resolvedId)->get('lab_report_pending')->row_array();
        $deleted = $this->ci->db->where('id', $deletedId)->get('lab_report_pending')->row_array();
        $this->assertSame('{"secret":"retained"}', $resolved['raw_payload']);
        $this->assertSame($reportId, (int) $resolved['report_id']);
        $this->assertSame($petId, (int) $resolved['resolved_pet_id']);
        $this->assertSame($userId, (int) $resolved['resolved_by']);
        $this->assertNotNull($resolved['resolved_at']);
        $this->assertSame('{"secret":"retained"}', $deleted['raw_payload']);
        $this->assertSame($userId, (int) $deleted['deleted_by']);
        $this->assertNotNull($deleted['deleted_at']);
    }

    public function testCreateStoresSourceIdentity(): void
    {
        $model = $this->model('LabReportPending_model', 'pending_create_test');
        $sourceId = $this->uniqueString('pending_source');
        $id = $model->create([
            'device' => 'medilab',
            'source' => 'remote',
            'source_id' => $sourceId,
            'raw_payload' => '{}',
            'identifiers' => '{}',
            'reason' => 'pet_not_found',
        ]);

        $row = $this->ci->db->where('id', $id)->get('lab_report_pending')->row_array();
        $this->assertSame('remote', $row['source']);
        $this->assertSame($sourceId, $row['source_id']);
		$this->assertNotNull($row['identity_hash']);
		$this->assertSame($row['created_at'], $row['last_received_at']);
    }

	public function testRepeatedActiveIdentityRefreshesOneRowAndPreservesFirstReceipt(): void
	{
		$model = $this->model('LabReportPending_model', 'pending_refresh_test');
		$sourceId = $this->uniqueString('pending_refresh');
		$first = $model->create_or_refresh([
			'device' => 'medilab', 'source' => 'remote', 'source_id' => $sourceId,
			'raw_payload' => '{"version":1}', 'identifiers' => '{"pet_name":"Old"}',
			'reason' => 'pet_not_found', 'received_at' => '2026-08-30 10:00:00',
		]);
		$second = $model->create_or_refresh([
			'device' => 'medilab', 'source' => 'remote', 'source_id' => $sourceId,
			'raw_payload' => '{"version":2}', 'identifiers' => '{"pet_name":"New"}',
			'reason' => 'pet_not_found', 'received_at' => '2026-08-30 11:00:00',
		]);

		$this->assertSame($first['id'], $second['id']);
		$this->assertSame(1, $this->ci->db->where('source_id', $sourceId)->count_all_results('lab_report_pending'));
		$row = $this->ci->db->where('id', $first['id'])->get('lab_report_pending')->row_array();
		$this->assertSame('{"version":2}', $row['raw_payload']);
		$this->assertSame('{"pet_name":"New"}', $row['identifiers']);
		$this->assertSame('2026-08-30 10:00:00', $row['created_at']);
		$this->assertSame('2026-08-30 11:00:00', $row['last_received_at']);
	}

	public function testDismissedAndResolvedIdentitiesAreNotRefreshed(): void
	{
		$model = $this->model('LabReportPending_model', 'pending_suppression_test');
		$petId = $this->existingId('pets');
		$userId = $this->existingId('users');
		$reportId = $this->insertReport($petId);

		foreach (['deleted', 'resolved'] as $state) {
			$sourceId = $this->uniqueString('pending_' . $state);
			$created = $model->create_or_refresh([
				'device' => 'medilab', 'source' => 'remote', 'source_id' => $sourceId,
				'raw_payload' => '{"version":1}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
			]);
			if ($state === 'deleted') {
				$this->assertTrue($model->soft_delete_active($created['id'], $userId));
			} else {
				$this->assertTrue($model->mark_resolved($created['id'], $reportId, $petId, null));
			}

			$result = $model->create_or_refresh([
				'device' => 'medilab', 'source' => 'remote', 'source_id' => $sourceId,
				'raw_payload' => '{"version":2}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
			]);
			$this->assertSame('suppressed', $result['state']);
			$this->assertSame('{"version":1}', $this->ci->db->where('id', $created['id'])->get('lab_report_pending')->row_array()['raw_payload']);
		}
	}

	public function testIdentitylessDeliveriesRemainIndependent(): void
	{
		$model = $this->model('LabReportPending_model', 'pending_identityless_test');
		$data = [
			'device' => null, 'source' => null, 'source_id' => null,
			'raw_payload' => '{}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
		];
		$first = $model->create_or_refresh($data);
		$second = $model->create_or_refresh($data);

		$this->assertNotSame($first['id'], $second['id']);
		$this->assertNull($first['identity_hash']);
		$this->assertNull($second['identity_hash']);
	}

	public function testHashCollisionDoesNotOverwriteAnotherIdentity(): void
	{
		$model = $this->model('LabReportPending_model', 'pending_collision_test');
		$this->ci->load->library('lab_source_identity');
		$sourceId = $this->uniqueString('pending_collision');
		$identity = $this->ci->lab_source_identity->derive('medilab', 'remote', $sourceId);
		$id = $this->insertPending([
			'device' => 'other-device', 'source' => 'other-source', 'source_id' => $sourceId,
			'identity_hash' => $identity['hash'], 'raw_payload' => '{"safe":true}',
		]);

		$this->expectException(RuntimeException::class);
		try {
			$model->create_or_refresh([
				'device' => 'medilab', 'source' => 'remote', 'source_id' => $sourceId,
				'raw_payload' => '{"unsafe":true}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
			]);
		} finally {
			$this->assertSame('{"safe":true}', $this->ci->db->where('id', $id)->get('lab_report_pending')->row_array()['raw_payload']);
		}
	}

    private function insertPending(array $overrides = []): int
    {
        $row = array_merge([
            'device' => 'phpunit',
            'source' => 'phpunit',
            'source_id' => $this->uniqueString('pending'),
            'raw_payload' => '{"secret":"retained"}',
            'identifiers' => '{"pet_name":"Example"}',
            'reason' => 'pet_not_found',
            'created_at' => date('Y-m-d H:i:s'),
        ], $overrides);
        $this->ci->db->insert('lab_report_pending', $row);

        return (int) $this->ci->db->insert_id();
    }

    private function insertReport(int $petId): int
    {
        $this->ci->db->insert('lab_report', [
            'pet_id' => $petId,
            'device' => 'phpunit',
            'source' => 'phpunit',
            'source_id' => $this->uniqueString('pending_report'),
            'sample_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->ci->db->insert_id();
    }
}
