<?php

declare(strict_types=1);

/**
 * Builds and resets the test database. No CodeIgniter classes are used, so it works from
 * every layer (and, after the migration, against the CI4 application too).
 *
 * Environment (defaults match the podman setup):
 *   DB_HOST (db), DB_NAME (alies_test), DB_USER (alies), DB_PASS (alies)
 *   DB_ROOT_USER (root), DB_ROOT_PASS (root)  - only used to create the database and grant access
 */
final class TestDatabase
{
    private const FIXTURES = __DIR__ . '/../Fixtures';

    public static function host(): string
    {
        return getenv('DB_HOST') ?: 'db';
    }

    public static function name(): string
    {
        $name = getenv('DB_NAME') ?: 'alies_test';

        // the tests wipe this database; never allow it to be a real one
        if (stripos($name, 'test') === false) {
            throw new RuntimeException('Refusing to use database "' . $name . '": the name must contain "test".');
        }

        return $name;
    }

    public static function user(): string
    {
        return getenv('DB_USER') ?: 'alies';
    }

    public static function password(): string
    {
        return getenv('DB_PASS') ?: 'alies';
    }

    /**
     * Connection used by tests and factories. It runs non-strict, like the application does
     * (stricton => false), so a factory can leave out columns it does not care about.
     */
    public static function connect(): PDO
    {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', self::host(), self::name()),
            self::user(),
            self::password(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        $pdo->exec("SET SESSION sql_mode = REPLACE(REPLACE(@@sql_mode, 'STRICT_TRANS_TABLES', ''), 'STRICT_ALL_TABLES', '')");

        return $pdo;
    }

    /** Drops and recreates the database, then loads schema.sql and seed.sql. */
    public static function rebuild(): void
    {
        $root = self::rootConnection();
        $db = self::name();

        $root->exec('DROP DATABASE IF EXISTS `' . $db . '`');
        $root->exec('CREATE DATABASE `' . $db . '` CHARACTER SET utf8mb4');
        $root->exec('GRANT ALL PRIVILEGES ON `' . $db . '`.* TO ' . $root->quote(self::user()) . '@' . $root->quote('%'));

        $pdo = self::connect();
        self::runFile($pdo, self::FIXTURES . '/schema.sql');
        self::runFile($pdo, self::FIXTURES . '/seed.sql');
    }

    /** Empties every table and loads seed.sql again (fast path used between test classes). */
    public static function reseed(?PDO $pdo = null): void
    {
        $pdo ??= self::connect();

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(PDO::FETCH_NUM) as [$table]) {
            // truncating an empty table still costs a table rebuild, skip those
            if ($pdo->query('SELECT 1 FROM `' . $table . '` LIMIT 1')->fetchColumn() !== false) {
                $pdo->exec('TRUNCATE TABLE `' . $table . '`');
            }
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        self::runFile($pdo, self::FIXTURES . '/seed.sql');
    }

    private static function rootConnection(): PDO
    {
        return new PDO(
            sprintf('mysql:host=%s;charset=utf8mb4', self::host()),
            getenv('DB_ROOT_USER') ?: 'root',
            getenv('DB_ROOT_PASS') ?: 'root',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private static function runFile(PDO $pdo, string $file): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException('Cannot read ' . $file);
        }

        foreach (self::splitStatements($sql) as $statement) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    basename($file) . ': ' . $e->getMessage() . "\nin: " . substr($statement, 0, 300),
                    0,
                    $e
                );
            }
        }
    }

    /**
     * Splits an SQL script on ';' while respecting quotes and comments. Enough for dump
     * output and for hand written seed data (no DELIMITER support).
     *
     * @return list<string>
     */
    public static function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $length = strlen($sql);
        $quote = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($quote !== null) {
                $current .= $char;
                if ($char === '\\' && $quote !== '`') {
                    $current .= $next;
                    $i++;
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $current .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if ($char === '-' && $next === '-') {
                $end = strpos($sql, "\n", $i);
                $i = $end === false ? $length : $end;
                continue;
            }
            if ($char === '#') {
                $end = strpos($sql, "\n", $i);
                $i = $end === false ? $length : $end;
                continue;
            }
            if ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i = $end === false ? $length : $end + 1;
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $statements[] = trim($current);
        }

        return $statements;
    }
}
