<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabReportPendingModelContractTest extends TestCase
{
    public function testModelDefinesActiveLifecycleAndRetainsPayloadOnDelete(): void
    {
        $source = file_get_contents(APPPATH . 'models/LabReportPending_model.php');

        $this->assertStringContainsString('function get_active()', $source);
        $this->assertStringContainsString('function count_active()', $source);
        $this->assertStringContainsString('function lock_active(int $pending_id)', $source);
        $this->assertStringContainsString('FOR UPDATE', $source);
        $this->assertStringContainsString('function mark_resolved(', $source);
        $this->assertStringContainsString('function soft_delete_active(', $source);
        $this->assertGreaterThanOrEqual(4, substr_count($source, "where('resolved_at IS NULL'"));
        $this->assertGreaterThanOrEqual(4, substr_count($source, "where('deleted_at IS NULL'"));
        $this->assertStringNotContainsString("'raw_payload' => null", $source);
    }

    public function testCreatePersistsSourceIdentityWithoutChangingCallerContract(): void
    {
        $source = file_get_contents(APPPATH . 'models/LabReportPending_model.php');

		$this->assertStringContainsString('function create_or_refresh(array $data)', $source);
		$this->assertStringContainsString("\$data['source'] ?? null", $source);
		$this->assertStringContainsString("\$data['source_id'] ?? null", $source);
		$this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $source);
		$this->assertStringContainsString('LAST_INSERT_ID(`id`)', $source);
		$this->assertStringContainsString('last_received_at', $source);
		$this->assertStringContainsString('COALESCE(last_received_at, created_at)', $source);
    }
}
