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
