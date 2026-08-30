<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabPendingIdentityMigrationTest extends TestCase
{
	private string $source;

	protected function setUp(): void
	{
		$this->source = file_get_contents(APPPATH . 'migrations/056_lab_pending_identity.php');
	}

	public function testMigrationAddsIdentityReceiptSupersessionAndUniqueIndexWithRollback(): void
	{
		foreach (['identity_hash', 'last_received_at', 'superseded_by_id'] as $column) {
			$this->assertStringContainsString("'{$column}'", $this->source);
			$this->assertStringContainsString("field_exists(\$name, 'lab_report_pending')", $this->source);
		}
		$this->assertStringContainsString('UNIQUE INDEX `pending_identity`', $this->source);
		$this->assertStringContainsString('DROP INDEX `pending_identity`', $this->source);
		$this->assertStringContainsString('DROP `{$name}`', $this->source);
	}

	public function testMigrationConsolidatesLifecycleGroupsBeforeAddingTheIndex(): void
	{
		$consolidateAt = strpos($this->source, '$this->consolidateIdentities()');
		$indexAt = strpos($this->source, 'ADD UNIQUE INDEX `pending_identity`');

		$this->assertNotFalse($consolidateAt);
		$this->assertNotFalse($indexAt);
		$this->assertLessThan($indexAt, $consolidateAt);
		$this->assertStringContainsString('lab_pending_identity_selector->plan', $this->source);
		$this->assertStringContainsString('findReport', $this->source);
		$this->assertStringContainsString("'superseded_by_id' => (int) \$canonical['id']", $this->source);
		$this->assertStringContainsString("'created_at' => \$created_at", $this->source);
		$this->assertStringContainsString("'last_received_at' => \$last_received_at", $this->source);
	}

	public function testMigrationLeavesNullIdentitiesIndependentAndValidatesCanonicalUniqueness(): void
	{
		$this->assertMatchesRegularExpression('/if \(\$identity === null\) \{\s*continue;/s', $this->source);
		$this->assertStringContainsString('HAVING COUNT(*) > 1', $this->source);
		$this->assertStringContainsString('hash collision detected', $this->source);
		$this->assertStringContainsString('consolidation left duplicate canonical hashes', $this->source);
	}
}
