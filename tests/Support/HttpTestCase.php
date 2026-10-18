<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Black-box test case: talks to the running application over HTTP and checks the database through
 * its own PDO connection. It must not use any CodeIgniter class, so the same tests can later be
 * run against the CodeIgniter 4 version (set APP_URL, and replace cli() if the command changes).
 *
 * Every test class starts from a freshly seeded database (see TestDatabase::reseed). Set
 * HTTP_NO_RESEED=1 to skip that while debugging a single test.
 *
 * Environment: APP_URL (default http://127.0.0.1:8089/), DB_* (see TestDatabase).
 */
abstract class HttpTestCase extends TestCase
{
    private static ?PDO $pdo = null;
    private static bool $environmentChecked = false;

    protected Client $client;
    protected CookieJar $cookies;

    // ---- lifecycle ----------------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!getenv('HTTP_NO_RESEED')) {
            TestDatabase::reseed(self::pdo());
        }
        Factory::usePdo(self::pdo());
        self::guardEnvironment();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookies = new CookieJar();
        $this->client = new Client([
            'base_uri' => self::baseUrl(),
            RequestOptions::COOKIES => $this->cookies,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => 60,
        ]);
    }

    public static function baseUrl(): string
    {
        return rtrim(getenv('APP_URL') ?: 'http://127.0.0.1:8089/', '/') . '/';
    }

    /**
     * Guards against a server that runs in the wrong environment (for example the development
     * configuration that points at real data): the application builds its redirects from its own
     * base_url, which the testing configuration sets to APP_URL.
     */
    private static function guardEnvironment(): void
    {
        if (self::$environmentChecked || getenv('SKIP_ENVIRONMENT_GUARD')) {
            return;
        }

        $client = new Client(['base_uri' => self::baseUrl(), RequestOptions::ALLOW_REDIRECTS => false, RequestOptions::HTTP_ERRORS => false, RequestOptions::TIMEOUT => 30]);
        try {
            $response = $client->get('auth/login');
            $probe = $client->get('');
        } catch (Throwable $e) {
            throw new RuntimeException('The application is not reachable at ' . self::baseUrl() . ' (start it with tests/bin/serve.sh start): ' . $e->getMessage(), 0, $e);
        }

        $target = self::redirectTargetOf($probe);
        if ($target !== null && preg_match('#^https?://#', $target)) {
            $expected = parse_url(self::baseUrl());
            $actual = parse_url($target);
            if (($expected['host'] ?? '') !== ($actual['host'] ?? '') || ($expected['port'] ?? 80) !== ($actual['port'] ?? 80)) {
                throw new RuntimeException(sprintf(
                    'The application redirects to %s but the tests expect %s. It is probably not running with CI_ENV=testing; refusing to continue.',
                    $target,
                    self::baseUrl()
                ));
            }
        }
        unset($response);

        self::$environmentChecked = true;
    }

    // ---- database -----------------------------------------------------------------------------

    protected static function pdo(): PDO
    {
        return self::$pdo ??= TestDatabase::connect();
    }

    protected function db(): PDO
    {
        return self::pdo();
    }

    /** @return array<string, mixed>|null */
    protected function row(string $sql, array $params = []): ?array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    protected function rows(string $sql, array $params = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        $value = $statement->fetchColumn();

        return $value === false ? null : $value;
    }

    /** @return array<string, mixed>|null the row with the given id */
    protected function find(string $table, int $id): ?array
    {
        return $this->row('SELECT * FROM `' . $table . '` WHERE id = ?', [$id]);
    }

    /** @param array<string, mixed> $where column => value (null means IS NULL) */
    protected function countWhere(string $table, array $where = []): int
    {
        [$sql, $params] = $this->whereClause($where);

        return (int) $this->scalar('SELECT COUNT(*) FROM `' . $table . '`' . $sql, $params);
    }

    /** @param array<string, mixed> $where */
    protected function assertRowExists(string $table, array $where, string $message = ''): void
    {
        $this->assertGreaterThan(0, $this->countWhere($table, $where), $message ?: sprintf('Expected a row in %s matching %s', $table, json_encode($where)));
    }

    /** @param array<string, mixed> $where */
    protected function assertRowMissing(string $table, array $where, string $message = ''): void
    {
        $this->assertSame(0, $this->countWhere($table, $where), $message ?: sprintf('Expected no row in %s matching %s', $table, json_encode($where)));
    }

    /** @param array<string, mixed> $where */
    protected function assertRowCount(int $expected, string $table, array $where = [], string $message = ''): void
    {
        $this->assertSame($expected, $this->countWhere($table, $where), $message ?: sprintf('Row count in %s matching %s', $table, json_encode($where)));
    }

    /** @return array{0: string, 1: list<mixed>} */
    private function whereClause(array $where): array
    {
        if ($where === []) {
            return ['', []];
        }

        $parts = [];
        $params = [];
        foreach ($where as $column => $value) {
            if ($value === null) {
                $parts[] = '`' . $column . '` IS NULL';
            } else {
                $parts[] = '`' . $column . '` = ?';
                $params[] = $value;
            }
        }

        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    // ---- requests -----------------------------------------------------------------------------

    protected function get(string $uri, array $query = [], array $headers = []): ResponseInterface
    {
        $options = [RequestOptions::HEADERS => $headers];
        // an empty QUERY option would replace a query string that is part of the uri ('search?search_query=x')
        if ($query !== []) {
            $options[RequestOptions::QUERY] = $query;
        }

        return $this->client->get(ltrim($uri, '/'), $options);
    }

    protected function post(string $uri, array $form = [], array $headers = []): ResponseInterface
    {
        return $this->client->post(ltrim($uri, '/'), [RequestOptions::FORM_PARAMS => $form, RequestOptions::HEADERS => $headers]);
    }

    /** GET as an AJAX request (X-Requested-With header). */
    protected function ajaxGet(string $uri, array $query = []): ResponseInterface
    {
        return $this->get($uri, $query, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    /** POST as an AJAX request (X-Requested-With header). */
    protected function ajaxPost(string $uri, array $form = []): ResponseInterface
    {
        return $this->post($uri, $form, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    protected function postJson(string $uri, array|string $payload, array $headers = []): ResponseInterface
    {
        return $this->client->post(ltrim($uri, '/'), [
            RequestOptions::BODY => is_string($payload) ? $payload : json_encode($payload, JSON_THROW_ON_ERROR),
            RequestOptions::HEADERS => $headers + ['Content-Type' => 'application/json'],
        ]);
    }

    /**
     * Multipart POST. $files maps a field name to ['path' => ..., 'filename' => ..., 'type' => ...].
     *
     * @param array<string, scalar> $fields
     * @param array<string, array{path: string, filename?: string, type?: string}> $files
     */
    protected function postMultipart(string $uri, array $fields = [], array $files = [], array $headers = []): ResponseInterface
    {
        $parts = [];
        foreach ($fields as $name => $value) {
            $parts[] = ['name' => $name, 'contents' => (string) $value];
        }
        foreach ($files as $name => $file) {
            $parts[] = [
                'name' => $name,
                'contents' => fopen($file['path'], 'rb'),
                'filename' => $file['filename'] ?? basename($file['path']),
                'headers' => isset($file['type']) ? ['Content-Type' => $file['type']] : [],
            ];
        }

        return $this->client->post(ltrim($uri, '/'), [RequestOptions::MULTIPART => $parts, RequestOptions::HEADERS => $headers]);
    }

    // ---- login --------------------------------------------------------------------------------

    /**
     * Logs in like the browser does and, for staff roles, picks a location (after login the
     * session location is 0 and the app shows a location picker).
     *
     * @param string $role admin, vet, vet2, acc, member, nogroup (see Fixtures::USERS)
     */
    protected function loginAs(string $role, int $location = Fixtures::LOCATION_PRACTICE): void
    {
        $this->assertArrayHasKey($role, Fixtures::USERS, 'Unknown role ' . $role);
        [$email, , $staff] = Fixtures::USERS[$role];

        $response = $this->attemptLogin($email, Fixtures::PASSWORD);
        $this->assertRedirectsTo($response, '', 'Login as ' . $role . ' failed: expected a redirect to the home page, got ' . ($this->redirectTarget($response) ?? 'status ' . $response->getStatusCode()));

        if ($staff) {
            $change = $this->get('welcome/change_location/' . $location);
            $this->assertRedirectsTo($change, '');
        }
    }

    protected function attemptLogin(string $identity, string $password): ResponseInterface
    {
        return $this->post('auth/login', ['identity' => $identity, 'password' => $password]);
    }

    // ---- redirects and responses --------------------------------------------------------------

    /**
     * The target of a redirect, whichever way the application sends it: a Location header, or the
     * `Refresh: 0;url=...` header that CodeIgniter 3 uses for redirect($url, 'refresh').
     */
    protected function redirectTarget(ResponseInterface $response): ?string
    {
        return self::redirectTargetOf($response);
    }

    private static function redirectTargetOf(ResponseInterface $response): ?string
    {
        if ($response->hasHeader('Location')) {
            return $response->getHeaderLine('Location');
        }
        if ($response->hasHeader('Refresh') && preg_match('/url\s*=\s*(.+)$/i', $response->getHeaderLine('Refresh'), $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /** Path (without host and without the base path) of a redirect target, query included when present. */
    private function normalizePath(string $target): string
    {
        $base = parse_url(self::baseUrl(), PHP_URL_PATH) ?: '/';
        $path = parse_url($target, PHP_URL_PATH) ?? '';
        $query = parse_url($target, PHP_URL_QUERY);

        if ($base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return trim($path, '/') . ($query !== null && $query !== '' ? '?' . $query : '');
    }

    /**
     * Asserts where a response redirects to. Compares the path only (host, scheme and leading or
     * trailing slashes are ignored) and never looks at the status code, because CodeIgniter 3 and 4
     * use different ones. Pass '' for the home page.
     */
    protected function assertRedirectsTo(ResponseInterface $response, string $expectedPath, string $message = ''): void
    {
        $target = $this->redirectTarget($response);
        $this->assertNotNull($target, $message ?: sprintf('Expected a redirect to "%s" but got status %d without Location or Refresh header', $expectedPath, $response->getStatusCode()));
        $this->assertSame(trim($expectedPath, '/'), $this->normalizePath($target), $message ?: 'Redirect target');
    }

    protected function assertNotRedirect(ResponseInterface $response, string $message = ''): void
    {
        $this->assertNull($this->redirectTarget($response), $message ?: 'Expected no redirect, got: ' . $this->redirectTarget($response));
    }

    /** Performs a GET on the redirect target, whichever way the redirect was sent. */
    protected function followRedirect(ResponseInterface $response): ResponseInterface
    {
        $target = $this->redirectTarget($response);
        $this->assertNotNull($target, 'Cannot follow: the response is not a redirect');

        return $this->get($this->normalizePath($target));
    }

    protected function assertStatus(ResponseInterface $response, int $expected, string $message = ''): void
    {
        $this->assertSame($expected, $response->getStatusCode(), $message ?: 'HTTP status. Page text starts with: ' . $this->excerpt($response, 200));
    }

    /** Body with HTML entities decoded, so assertions can use the text a user would read. */
    protected function text(ResponseInterface $response): string
    {
        $body = (string) $response->getBody();
        $body = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $body) ?? $body;

        return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function assertSee(ResponseInterface $response, string $needle, string $message = ''): void
    {
        // not assertStringContainsString: on failure PHPUnit would print the whole page
        $this->assertTrue(
            str_contains($this->text($response), $needle),
            $message ?: sprintf('Response (status %d) should contain "%s". Page text starts with: %s', $response->getStatusCode(), $needle, $this->excerpt($response))
        );
    }

    protected function assertDontSee(ResponseInterface $response, string $needle, string $message = ''): void
    {
        $this->assertFalse(
            str_contains($this->text($response), $needle),
            $message ?: sprintf('Response (status %d) should not contain "%s"', $response->getStatusCode(), $needle)
        );
    }

    /**
     * The hidden form fields of a page as name => value (for forms that carry a token, like the
     * confirmation of auth/deactivate). Fields of all forms on the page are merged.
     *
     * @return array<string, string>
     */
    protected function hiddenFields(ResponseInterface $response): array
    {
        $fields = [];
        preg_match_all('#<input\b[^>]*>#i', (string) $response->getBody(), $inputs);
        foreach ($inputs[0] as $input) {
            if (!preg_match('#type\s*=\s*["\']hidden["\']#i', $input) || !preg_match('#name\s*=\s*["\']([^"\']*)["\']#i', $input, $name)) {
                continue;
            }
            $value = preg_match('#value\s*=\s*["\']([^"\']*)["\']#i', $input, $v) ? html_entity_decode($v[1]) : '';
            $fields[$name[1]] = $value;
        }

        return $fields;
    }

    /** First characters of the visible text of a response, for failure messages. */
    protected function excerpt(ResponseInterface $response, int $length = 300): string
    {
        return substr(trim((string) preg_replace('/\s+/', ' ', strip_tags($this->text($response)))), 0, $length);
    }

    /** @return array<mixed> */
    protected function json(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded, 'Response is not JSON: ' . substr((string) $response->getBody(), 0, 200));

        return $decoded;
    }

    protected function assertDownload(ResponseInterface $response, string $contentTypeStart, ?string $filenameContains = null): void
    {
        $this->assertStatus($response, 200);
        $this->assertStringStartsWith($contentTypeStart, $response->getHeaderLine('Content-Type'), 'Content-Type');
        $disposition = $response->getHeaderLine('Content-Disposition');
        $this->assertStringContainsStringIgnoringCase('attachment', $disposition, 'Content-Disposition');
        if ($filenameContains !== null) {
            $this->assertStringContainsString($filenameContains, $disposition, 'Content-Disposition filename');
        }
    }

    // ---- logs written by the application ------------------------------------------------------

    /** Number of rows in the `log` table for an event name (e.g. 'new_event'). */
    protected function logCount(string $event): int
    {
        return $this->countWhere('log', ['event' => $event]);
    }

    // ---- command line -------------------------------------------------------------------------

    /**
     * Runs `php index.php <args>` in the testing environment and returns [exit code, stdout, stderr].
     * After the migration this is the only method that has to learn `php spark <args>`.
     *
     * @param list<string> $args for example ['cli', 'daily']
     * @return array{0: int, 1: string, 2: string}
     */
    protected function cli(array $args): array
    {
        $root = dirname(__DIR__, 2);
        $command = array_merge([PHP_BINARY, $root . '/index.php'], $args);
        $process = proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
            array_merge(getenv(), ['CI_ENV' => 'testing'])
        );
        $this->assertIsResource($process, 'Could not start php');

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), (string) $stdout, (string) $stderr];
    }
}
