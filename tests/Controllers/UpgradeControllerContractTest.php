<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UpgradeControllerContractTest extends TestCase
{
    public function testMedilabRetryRunsMigrationWithoutChangingMigrationVersion(): void
    {
        $source = file_get_contents(APPPATH . 'controllers/Upgrade.php');

        $this->assertStringContainsString('function retry_medilab_codes()', $source);
        $this->assertStringContainsString("new Migration_medilab_result_code_resolve()", $source);
        $this->assertStringContainsString('$version_after !== $current', $source);
        $this->assertStringNotContainsString("migration->version(48)", $source);
    }
}
