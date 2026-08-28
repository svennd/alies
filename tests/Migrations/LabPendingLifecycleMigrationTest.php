<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabPendingLifecycleMigrationTest extends TestCase
{
    public function testMigrationAddsNullableAuditColumnsAndActiveIndexWithRollback(): void
    {
        $source = file_get_contents(APPPATH . 'migrations/054_lab_pending_lifecycle.php');

        foreach (['resolved_at', 'resolved_by', 'resolved_pet_id', 'report_id', 'deleted_at', 'deleted_by'] as $column) {
            $this->assertStringContainsString("'{$column}'", $source);
            $this->assertStringContainsString("field_exists(\$name, 'lab_report_pending')", $source);
        }

        $this->assertGreaterThanOrEqual(6, substr_count($source, 'NULL DEFAULT NULL'));
        $this->assertStringContainsString('pending_active', $source);
        $this->assertStringContainsString('DROP INDEX `pending_active`', $source);
        $this->assertStringContainsString('DROP `{$name}`', $source);
    }
}
