<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MedilabResultCodeResolveMigrationTest extends TestCase
{
    public function testResolverUsesConfiguredIndexApiAndTracksTopUnresolvedCodes(): void
    {
        $source = file_get_contents(APPPATH . 'migrations/049_medilab_result_code_resolve.php');

        $this->assertStringContainsString("get_decoded_config_value('index_api_url')", $source);
        $this->assertStringContainsString("'/api/medilab/by-test-id/'", $source);
        $this->assertStringContainsString('arsort($unresolved_codes, SORT_NUMERIC)', $source);
        $this->assertStringContainsString('array_slice($unresolved_codes, 0, 10, true)', $source);
        $this->assertStringNotContainsString("'/fagg/api/medilab/by-test-id/'", $source);
    }
}
