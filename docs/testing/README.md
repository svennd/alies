# Test plan for the CI3 → CI4 migration

Goal: build a test suite that pins down what the application does **today** (CodeIgniter 3), so the same suite can be run against the CodeIgniter 4 port and every behavioural difference shows up as a failing test.

This folder splits that work into small, numbered test tasks. Each task is scoped so a smaller model can finish it in a single prompt. Give one prompt a batch of 3–6 consecutive IDs from the same file. Related tests sit next to each other, so the batch shares fixtures and context.

| File | IDs | Area |
|---|---|---|
| [00-foundation.md](00-foundation.md) | F01–F10 | Test infrastructure. **Done** (F07 stubs still open). Read it before writing tests: it documents `HttpTestCase`, `Factory` and the seed data. |
| [01-helpers-pure.md](01-helpers-pure.md) | T001–T029 | Helpers, device parsers, pure libraries (no DB). |
| [02-auth-access.md](02-auth-access.md) | T030–T049 | Login, groups, access control per base controller. |
| [03-owners-pets.md](03-owners-pets.md) | T050–T099 | Owners, pets, weights, breeds, transfer, avatars, search. |
| [04-events-billing.md](04-events-billing.md) | T100–T159 | Consultations (events), lines, pricing, bills, payment, invoices. |
| [05-stock-products.md](05-stock-products.md) | T160–T219 | Products, prices, stock, moves, write-offs, limits, wholesale, delivery. |
| [06-clinical.md](06-clinical.md) | T220–T259 | Vaccines, teeth, Rx, files/uploads, lab. |
| [07-reports-export-vamreg.md](07-reports-export-vamreg.md) | T260–T299 | Reports, exports, accounting, stats, Vamreg. |
| [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) | T300–T349 | Admin settings, members, tools, restore, CLI cron jobs, REST API, upgrade. |
| [09-model-layer.md](09-model-layer.md) | T350–T399 | Model-level tests for logic that HTTP tests cannot reach well (MY_Model, raw-SQL methods). |

## The three test layers

The migration rewrites controllers, models and the framework itself, so most tests must **not** depend on CI3 internals. Every task names its layer.

### Layer P: Pure (`tests/Pure/…`)
- Plain PHPUnit with no framework boot and no DB.
- Covers helpers (`application/helpers/*`), device parsers (`application/third_party/api/devices/*`), and libraries that don't need `get_instance()`.
- During the migration these tests only need their `require_once` path changed.

### Layer H: HTTP black-box (`tests/Http/…`)
This is the **main migration safety net**.
- Tests talk to a running copy of the app over HTTP, using Guzzle with a cookie jar.
- They check the database **directly through PDO**, never through CI models.
- Because they use no CI classes, the same test file runs unchanged against CI3 and CI4.
- Base class: `HttpTestCase` (`tests/Support/HttpTestCase.php`). It provides `loginAs()`, `get()`, `post()`, `assertRedirectsTo()`, `find()`, `row()`, `assertRowExists()` and more, see 00-foundation.md.

### Layer M: Model (`tests/Models/…`, `tests/Integration/…`)
- Boots CI3 in-process and calls models directly. The existing `CodeIgniterDatabaseTestCase` wraps each test in a transaction.
- Use this layer only for logic that is awkward to reach over HTTP: complex raw SQL, money calculations, stock maths, edge cases.
- These tests will be ported together with their model in CI4, so keep them focused on **inputs → DB state / return value**.
- Do not assert on CI3 internals.

**Never write new "contract" tests that regex-match PHP source** (as `tests/Controllers/*ContractTest.php` do today). They cannot survive a rewrite. T349 plans to replace the existing ones with HTTP tests.

## Characterisation, not correction

These tests record **current** behaviour, bugs included, so the port is a faithful copy. When a test pins a known bug (for example a GET request that mutates data, or a wrong check digit):
- add `#[Group('known-bug')]`, and
- add a one-line comment `// KNOWN BUG: <what> — decide in CI4 whether to keep`.

The CI4 port can then fix it on purpose and update that one test. Specs flag these bugs with **⚠ BUG**.

## Rules for every test author (paste these into each prompt)

1. Read the spec entry and the source files it names **before** writing anything. The spec is a guide; the code is the truth. If they disagree, test what the code does and note the difference in a comment.
2. Put the test in the file path the spec names. Use one test class per spec ID unless the spec says otherwise. Class names end in `Test`, and test methods are named `test<Behaviour>`.
3. Use only the seeded rows (constants in `tests/Support/Fixtures.php`) and the factory helpers in `tests/Support/Factory.php`. Do not rely on rows that happen to exist in someone's local database.
4. Layer H tests:
   - must not `require` any `application/` file or call `get_instance()`;
   - assert on status code, redirect target, JSON body, download headers, stable text in the HTML (names, amounts, IDs), and DB rows read through PDO;
   - must not assert on whole HTML pages or CSS classes.
5. Money:
   - compare with `assertEqualsWithDelta($expected, $actual, 0.001)`;
   - store expected values as literals worked out by hand in a comment, never recomputed with the same formula the code uses.
6. Dates: build them relative to "now" (`date('Y-m-d', strtotime('-3 days'))`). The app reads the real clock, so it cannot be frozen in Layer H.
7. External services (mail, Vamreg, Covetrus, wholesaler FTP) must never be reached. Use the stubs from F07 (not built yet), or skip with `markTestSkipped('needs external stub')` and say why.
8. Each test cleans up after itself, or relies on the per-class reseed in `HttpTestCase`. Tests must pass in any order and when run alone.
9. Run `vendor/bin/phpunit --filter <ClassName>` and make sure it is green before you finish. If the behaviour looks like a bug, keep the test green against the current behaviour and mark it `known-bug`.
10. Do not change application code. If something cannot be tested without a change, stop and report it.

## Prompt template for a smaller model

```
You are writing PHPUnit tests for the Alies CodeIgniter 3 app (repo root: /home/svenn/alies/alies).
Read docs/testing/README.md (rules + layers) and docs/testing/00-foundation.md (test infrastructure).
Then implement these test tasks from docs/testing/<FILE>.md: <ID>, <ID>, <ID>.
For each ID: read the listed source files first, write the test at the given path, run it with
`vendor/bin/phpunit --filter <ClassName>`, and iterate until green. Follow the "Rules for every
test author" exactly. When done, tick the IDs in docs/testing/STATUS.md and list any spec
mismatches or suspected bugs you found.
```

## Running

Everything runs inside the `alies-php` container; the scripts do that for you (details: [tests/README.md](../../tests/README.md)).

```bash
tests/bin/ci.sh                      # fresh database + app server + pure, model and http
tests/bin/ci.sh pure                 # one layer: pure | model | http
tests/bin/ci.sh http --filter Smoke  # extra arguments go to phpunit
tests/bin/dc.sh vendor/bin/phpunit -c phpunit.http.xml.dist --filter MyTest   # one class, server must be running
```

Before the first HTTP test run `tests/bin/reset-db.sh` and `tests/bin/serve.sh start` (or let `ci.sh` do it). Exclude known bugs with `--exclude-group known-bug`.

Progress is tracked in [STATUS.md](STATUS.md).
