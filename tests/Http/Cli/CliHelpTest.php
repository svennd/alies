<?php

declare(strict_types=1);

/**
 * T320 - `php index.php cli` (no method) runs Cli::index: it prints the welcome line and the list of
 * functions, changes nothing and exits 0. Refusing web access is T043 (CliOverHttpTest).
 */
final class CliHelpTest extends HttpTestCase
{
    public function testWithoutArgumentsTheHelpListIsPrinted(): void
    {
        $logs = $this->countWhere('log');

        [$exit, $out] = $this->cli(['cli']);

        $this->assertSame(0, $exit);
        $this->assertStringStartsWith("Welcome to alies, cli\nfunctions :\n", $out);
        foreach (
            ['delivery [filename]', 'pricelist [filename]', 'stock_clean', 'autoclose', 'prune', 'auto_death',
             'import_rx', 'recalculate_usage', 'monthly_product_usage_dump [YYYY-MM] [force=0|1]'] as $function
        ) {
            $this->assertStringContainsString('  - ' . $function . ' :', $out);
        }
        $this->assertSame($logs, $this->countWhere('log'), 'the help text must not write log rows');
    }

    public function testTheIndexMethodGivesTheSameOutput(): void
    {
        [, $plain] = $this->cli(['cli']);
        [$exit, $explicit] = $this->cli(['cli', 'index']);

        $this->assertSame(0, $exit);
        $this->assertSame($plain, $explicit);
    }
}
