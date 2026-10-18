<?php

declare(strict_types=1);

/**
 * T340 - `php index.php upgrade up|down [version]` (controllers/Upgrade.php, CLI only, see CliOverHttpTest).
 *
 * Works on the slot database. up: validates the version (non-negative integer, not lower than the current
 * one, must exist on disk) and migrates; down: one version down, or to the given lower version.
 * Messages are printed to stdout, the exit code is 0 in every case (the methods only return false).
 * The down/up round trip drops and re-adds columns of lab_report_pending (migration 056), so the class
 * rebuilds the whole database afterwards for the classes that follow.
 */
final class UpgradeTest extends HttpTestCase
{
    public static function tearDownAfterClass(): void
    {
        TestDatabase::rebuild();
        parent::tearDownAfterClass();
    }

    private function latestOnDisk(): int
    {
        $versions = array_map(
            static fn (string $f): int => (int) basename($f),
            glob(dirname(__DIR__, 3) . '/application/migrations/[0-9][0-9][0-9]_*.php') ?: []
        );
        $this->assertNotSame([], $versions);

        return max($versions);
    }

    private function version(): int
    {
        return (int) $this->scalar('SELECT version FROM migrations');
    }

    private function pad(int $v): string
    {
        return sprintf('%03d', $v);
    }

    public function testTheSeededDatabaseIsAtTheLatestMigration(): void
    {
        $this->assertSame($this->latestOnDisk(), $this->version());
    }

    public function testUpAtTheLatestVersionSaysAlreadyThere(): void
    {
        $latest = $this->latestOnDisk();

        [$exit, $out] = $this->cli(['upgrade', 'up']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Already at migration version ' . $this->pad($latest) . '.', $out);
        $this->assertSame($latest, $this->version());
    }

    public function testUpToTheCurrentVersionExplicitlyIsAlsoANoOp(): void
    {
        $latest = $this->latestOnDisk();

        [, $out] = $this->cli(['upgrade', 'up', (string) $latest]);

        $this->assertStringContainsString('Already at migration version ' . $this->pad($latest), $out);
    }

    public function testUpRejectsAVersionThatIsNotAWholeNumber(): void
    {
        foreach (['abc', '-1', '1.5', '12a'] as $bad) {
            [, $out] = $this->cli(['upgrade', 'up', $bad]);
            $this->assertStringContainsString('Invalid version', $out, 'version ' . $bad);
        }
        $this->assertSame($this->latestOnDisk(), $this->version());
    }

    public function testUpRefusesToGoDown(): void
    {
        [, $out] = $this->cli(['upgrade', 'up', '1']);

        $this->assertStringContainsString('Refusing to migrate down', $out);
        $this->assertStringContainsString('Use down() instead', $out);
        $this->assertSame($this->latestOnDisk(), $this->version());
    }

    public function testUpToAVersionThatDoesNotExistIsRefused(): void
    {
        [, $out] = $this->cli(['upgrade', 'up', '999']);

        $this->assertStringContainsString('Migration version 999 does not exist.', $out);
        $this->assertSame($this->latestOnDisk(), $this->version());
    }

    public function testDownRejectsBadInputAndRefusesToGoUp(): void
    {
        [, $out] = $this->cli(['upgrade', 'down', 'abc']);
        $this->assertStringContainsString('Invalid version', $out);

        [, $out] = $this->cli(['upgrade', 'down', '999']);
        $this->assertStringContainsString('Refusing to migrate up via down()', $out);

        [, $out] = $this->cli(['upgrade', 'down', (string) $this->latestOnDisk()]);
        $this->assertStringContainsString('Already at migration version ' . $this->pad($this->latestOnDisk()), $out);

        $this->assertSame($this->latestOnDisk(), $this->version());
    }

    public function testDownMovesOneVersionAndUpReturnsToTheLatest(): void
    {
        $latest = $this->latestOnDisk();
        $columns = static fn (self $t): array => array_column(
            $t->rows('SHOW COLUMNS FROM lab_report_pending'),
            'Field'
        );
        $this->assertContains('identity_hash', $columns($this));

        [$exit, $out] = $this->cli(['upgrade', 'down']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Migrated down from ' . $this->pad($latest) . ' to ' . $this->pad($latest - 1), $out);
        $this->assertSame($latest - 1, $this->version());
        // migration 056 (the latest) added these columns, so down() removed them
        $this->assertNotContains('identity_hash', $columns($this));
        $this->assertNotContains('last_received_at', $columns($this));

        [$exit, $out] = $this->cli(['upgrade', 'up']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Migrated up from ' . $this->pad($latest - 1) . ' to ' . $this->pad($latest), $out);
        $this->assertSame($latest, $this->version());
        $this->assertContains('identity_hash', $columns($this));
        $this->assertContains('last_received_at', $columns($this));
    }

    public function testUpToASpecificVersionStopsThere(): void
    {
        $latest = $this->latestOnDisk();
        $this->cli(['upgrade', 'down']);
        $this->assertSame($latest - 1, $this->version());

        [, $out] = $this->cli(['upgrade', 'up', (string) $latest]);

        $this->assertStringContainsString('Migrated up from ' . $this->pad($latest - 1) . ' to ' . $this->pad($latest), $out);
        $this->assertSame($latest, $this->version());
    }
}
