<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DropLegacyLabTablesMigrationTest extends TestCase
{
    public function testMigrationDropsDetailsBeforeReportsAndDocumentsDataOnlyRollback(): void
    {
        $source = file_get_contents(APPPATH . 'migrations/055_drop_legacy_lab_tables.php');

        $dropDetails = strpos($source, 'DROP TABLE IF EXISTS `lab_detail`');
        $dropReports = strpos($source, 'DROP TABLE IF EXISTS `lab`');

        $this->assertIsInt($dropDetails);
        $this->assertIsInt($dropReports);
        $this->assertLessThan($dropReports, $dropDetails);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `lab`', $source);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `lab_detail`', $source);
        $this->assertStringContainsString('not data discarded by up()', $source);
    }
}
