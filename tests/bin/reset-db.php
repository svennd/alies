<?php

declare(strict_types=1);

/**
 * Rebuilds the test database from tests/Fixtures/schema.sql + seed.sql.
 *
 *   php tests/bin/reset-db.php            full rebuild (drop database, schema, seed)
 *   php tests/bin/reset-db.php --seed     only truncate the tables and load the seed again
 *   php tests/bin/reset-db.php --check    exit code 0 when the database already exists
 *
 * Needs DB_HOST etc., see tests/Support/TestDatabase.php.
 */

require_once __DIR__ . '/../Support/TestDatabase.php';

$seedOnly = in_array('--seed', $argv, true);

if (in_array('--check', $argv, true)) {
    // exit code 0 when the test database exists and has the schema
    try {
        TestDatabase::connect()->query('SELECT 1 FROM users LIMIT 1');
        exit(0);
    } catch (Throwable) {
        exit(1);
    }
}

try {
    if ($seedOnly) {
        TestDatabase::reseed();
        echo 'reseeded ' . TestDatabase::name() . "\n";
    } else {
        TestDatabase::rebuild();
        echo 'rebuilt ' . TestDatabase::name() . "\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
