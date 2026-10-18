<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * T043 - Cli and Upgrade are command line controllers. They extend Frontend_Controller (no login),
 * but both constructors refuse anything that is not the CLI with show_error('Direct access is not allowed').
 * Nothing may run over HTTP: no log rows, no changed tables, no migration.
 */
final class CliOverHttpTest extends HttpTestCase
{
    /** @return array<string, array{string}> */
    public static function routes(): array
    {
        $routes = [
            'cli',
            'cli/index',
            'cli/daily',
            'cli/prune',
            'cli/autoclose',
            'cli/auto_death',
            'cli/stock_clean',
            'cli/recalculate_usage',
            'cli/monthly_product_usage_dump/2026-01/1',
            'cli/vamreg_update',
            'cli/import_rx/1',
            'upgrade/up',
            'upgrade/up/1',
            'upgrade/down',
            'upgrade/retry_medilab_codes',
        ];

        return array_combine($routes, array_map(static fn (string $r): array => [$r], $routes));
    }

    #[DataProvider('routes')]
    public function testTheRouteIsRefusedForAnAnonymousVisitor(string $route): void
    {
        $response = $this->get($route);

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Direct access is not allowed');
        $this->assertNotRedirect($response);
    }

    #[DataProvider('routes')]
    public function testTheRouteIsRefusedForAnAdminToo(string $route): void
    {
        $this->loginAs('admin');

        $this->assertStatus($this->get($route), 500);
    }

    public function testNothingIsRunWhenTheDailyJobIsRequestedOverHttp(): void
    {
        $this->db()->exec('DELETE FROM log');
        $tables = ['log', 'log_stock', 'stock_value', 'events', 'stock', 'pets', 'products'];
        $before = $this->snapshot($tables);
        $migration = $this->scalar('SELECT version FROM migrations');

        $this->get('cli/daily');
        $this->post('cli/daily', ['force' => 1]);
        $this->get('upgrade/up');

        $this->assertSame($before, $this->snapshot($tables), 'a table changed');
        $this->assertSame($migration, $this->scalar('SELECT version FROM migrations'));
    }

    public function testPostAndOtherQueryStringsMakeNoDifference(): void
    {
        $this->assertStatus($this->post('cli/stock_clean', ['x' => 1]), 500);
        $this->assertStatus($this->get('cli/prune', ['force' => 1]), 500);
    }

    /**
     * @param list<string> $tables
     * @return array<string, array{int, string}> row count and checksum per table
     */
    private function snapshot(array $tables): array
    {
        $result = [];
        foreach ($tables as $table) {
            $checksum = $this->row('CHECKSUM TABLE `' . $table . '`');
            $result[$table] = [$this->countWhere($table), (string) $checksum['Checksum']];
        }

        return $result;
    }
}
