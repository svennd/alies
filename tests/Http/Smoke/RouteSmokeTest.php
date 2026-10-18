<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * T345 - route smoke crawl. Logged in as admin, every GET route of tests/Fixtures/routes.php must answer
 * with status 200 or a redirect, show none of the PHP/database error markers, and change no table.
 * Routes that misbehave today are listed in KNOWN_BROKEN with the reason; they are asserted to still
 * misbehave in that way, so the list cannot go stale unnoticed.
 */
final class RouteSmokeTest extends HttpTestCase
{
    /** path => reason. Empty when everything is fine. */
    public const KNOWN_BROKEN = [
        // (the dental chart, tooth/fiche/*, is only fine with short_open_tag=0: views/pets/tooth/*.xml start with
        // "<?xml". tests/bin/serve.sh sets that, like a production php.ini; with short tags on it is a 500)
        // KNOWN BUG: Tools::duplicate_clients query selects owner.id but the table alias is missing: Database error
        'tools/duplicate_clients' => 'SQL: Unknown column owner.id',
    ];

    private const MARKERS = ['A PHP Error was encountered', 'Fatal error', 'Severity:', 'Database error', 'Uncaught '];

    /** Tables that legitimately change on a page view. */
    private const IGNORED_TABLES = ['log', 'api_rate_limit'];

    /** @return array<string, array{string}> */
    public static function routes(): array
    {
        $routes = require dirname(__DIR__, 2) . '/Fixtures/routes.php';

        return array_combine(array_map(static fn (string $r): string => $r === '' ? '(home)' : $r, $routes), array_map(static fn (string $r): array => [$r], $routes));
    }

    /** @return array<string, string> */
    private function checksums(): array
    {
        $tables = array_diff(
            array_column($this->rows('SHOW TABLES'), 'Tables_in_' . $this->scalar('SELECT DATABASE()')),
            self::IGNORED_TABLES
        );
        $sql = 'CHECKSUM TABLE `' . implode('`, `', $tables) . '`';
        $result = [];
        foreach ($this->rows($sql) as $row) {
            $result[$row['Table']] = (string) $row['Checksum'];
        }

        return $result;
    }

    /** @return list<string> markers found in the body */
    private function markers(string $body): array
    {
        return array_values(array_filter(self::MARKERS, static fn (string $m): bool => str_contains($body, $m)));
    }

    /** @return list<string> */
    private function problemsOf(string $route): array
    {
        $before = $this->checksums();
        $response = $this->get($route);
        $body = (string) $response->getBody();
        $changed = array_keys(array_diff_assoc($this->checksums(), $before));

        $problems = [];
        if ($response->getStatusCode() !== 200 && $this->redirectTarget($response) === null) {
            $problems[] = 'status ' . $response->getStatusCode();
        }
        foreach ($this->markers($body) as $marker) {
            $problems[] = 'body contains "' . $marker . '"';
        }
        if ($changed) {
            $problems[] = 'changed tables: ' . implode(', ', $changed);
        }

        return $problems;
    }

    #[DataProvider('routes')]
    public function testTheRouteRendersWithoutErrorsAndWithoutChangingData(string $route): void
    {
        $this->loginAs('admin');
        $problems = $this->problemsOf($route);

        if (isset(self::KNOWN_BROKEN[$route])) {
            $this->assertNotSame([], $problems, $route . ' is listed as broken (' . self::KNOWN_BROKEN[$route] . ') but works now');

            return;
        }
        $this->assertSame([], $problems, $route . ': ' . implode('; ', $problems));
    }

    public function testTheCheckReallyCatchesAProblem(): void
    {
        $this->loginAs('admin');

        $this->assertSame(['status 404'], $this->problemsOf('no/such/route'));
        // and a data change: opening a bill recalculates it (see routes.php, why it is not crawled)
        $owner = Factory::owner();
        $bill = Factory::bill($owner, BILL_PENDING_STATUS);
        Factory::event(Factory::pet($owner), 0);
        $this->assertNotSame([], $this->problemsOf('invoice/get_bill/' . $bill));
    }
}

const BILL_PENDING_STATUS = 1;
