# Running the tests

Three layers, each with its own PHPUnit configuration (details and the full test plan: `docs/testing/`).

| Layer | Config | Needs | What it is |
|---|---|---|---|
| pure | `phpunit.pure.xml.dist` | nothing | helpers, device parsers, libraries without CodeIgniter |
| model | `phpunit.xml.dist` (`--testsuite model`) | database | CodeIgniter 3 booted inside the test process |
| http | `phpunit.http.xml.dist` | database + running app | black-box tests over HTTP, database checked through PDO |

## With the podman setup

Everything runs **inside the `alies-php` container** (that is where the database host `db` resolves).
`tests/bin/dc.sh <command>` runs a command there, from the repository root, with `CI_ENV=testing`.

```bash
tests/bin/ci.sh                 # fresh database + server + all three layers, exit code 1 on failures
tests/bin/ci.sh pure            # one layer
tests/bin/ci.sh http --filter Smoke

# or step by step
tests/bin/reset-db.sh           # create/rebuild database alies_test (schema + seed)
tests/bin/serve.sh start        # application server for the http tests, 127.0.0.1:8089 inside the container
tests/bin/dc.sh vendor/bin/phpunit -c phpunit.http.xml.dist --testdox
tests/bin/serve.sh stop
```

Composer scripts (inside the container): `composer test`, `test:pure`, `test:model`, `test:http`, `test:stable`.

## Safety: the tests never touch the development database

- Tests use the database `alies_test`. `TestDatabase` refuses any database name that does not contain `test`,
  and `application/config/testing/database.php` refuses `alies`.
- The testing environment is `CI_ENV=testing` (`application/config/testing/`). The test server forces it,
  and `HttpTestCase` stops when the server's redirects do not point to `APP_URL` (a sign that it runs in another
  environment).
- Creating the database needs the MariaDB root account (`DB_ROOT_USER` / `DB_ROOT_PASS`, default `root` / `root`
  as in `compose.yml`). The application itself connects as `alies` / `alies`, which `reset-db` grants rights on `alies_test`.

## Environment variables

| Variable | Default | |
|---|---|---|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | `db`, `alies_test`, `alies`, `alies` | test database |
| `DB_ROOT_USER`, `DB_ROOT_PASS` | `root`, `root` | only for `reset-db` |
| `APP_URL` | `http://127.0.0.1:8089/` | where the http tests send requests |
| `APP_CONTAINER` | `alies-php` | container used by `dc.sh` / `serve.sh` |
| `HTTP_NO_RESEED=1` | | do not reseed before each http test class (debugging one test) |

## What is in here

```
tests/bin/            reset-db, serve, dc (container wrapper), ci, dump-schema, router for the built-in server
tests/Fixtures/       schema.sql (generated), seed.sql (hand written, synthetic), ms4s2_payload.json
tests/Support/        TestDatabase, Fixtures (seed ids), Factory (create rows), HttpTestCase
tests/Pure/           pure tests            tests/Http/      black-box tests
tests/Models, Integration, Migrations, Views, Libraries, Controllers   CodeIgniter in-process (model suite)
```

- **After adding a migration:** migrate a database, then run `tests/bin/dump-schema.sh` and commit `schema.sql`;
  also bump the version row in `seed.sql` (`INSERT INTO migrations`).
- **Seed data:** change `seed.sql` and `Fixtures.php` together. Prefer creating your own rows with `Factory` in a test.
- `tests/Controllers/*ContractTest.php` read PHP source and only make sense for CodeIgniter 3 (group `ci3-only`).
