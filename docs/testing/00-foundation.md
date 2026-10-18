# 00 — Foundation (F01–F10): status **done** (F07 not built)

The test infrastructure exists and is verified: `tests/bin/ci.sh` rebuilds the database, starts the app and runs all layers. How to run it is in [tests/README.md](../../tests/README.md). This file is what a test author needs to **use** the foundation, plus how it deviates from the first plan.

Smoke check: `tests/bin/ci.sh http` must pass (`tests/Http/SmokeTest.php`, 8 tests) before writing any new test.

## Facts about this application that shape every test

- **Database:** MariaDB. `bills.set_invoice_id` uses a subquery on its own table, which MySQL rejects, so the test database must be MariaDB (the `mariadb` image of the compose file).
- **Environment:** `ENVIRONMENT` comes from `$_SERVER['CI_ENV']` (`index.php:56`). **PHP's built-in server does not put environment variables in `$_SERVER`**, so `tests/bin/router.php` sets `$_SERVER['CI_ENV'] = 'testing'` itself. Without that the app silently runs in `development` against the real database. `getenv()` does work, which is what the testing config files use.
- **Config loading:** CI3 loads `config/config.php` and then `config/<env>/config.php` (overrides only), but only **one** `database.php`: the environment one wins.
- **Strict mode:** the application runs non-strict (`stricton => false`). Test connections do the same, so a factory may leave out columns.
- **Redirects:** `redirect($url)` sends a `Location` header (303 after POST, 307 after GET). `redirect($url, 'refresh')` sends **status 200 plus a `Refresh: 0;url=…` header and no `Location`**, and much of the app uses this. `assertRedirectsTo()` handles both and compares only the path. Never assert a redirect's status code (CI4 will differ).
- **Login:** identity is the e-mail address; passwords are 12–40 characters. After login the session location is **0**; the user must then pick one with `welcome/change_location/{id}` (`loginAs()` does both). `Welcome::index` runs `migration->latest()` on every hit. Staff screens (`Vet_Controller`) answer a wrong group with **500** (`show_error`), not a redirect.
- **Home page** is a search form. The header shows counters ("Verslagen" = open reports of the user, "Plakbriefjes" = stickies) and the location buttons; it does **not** print the user's name.
- **Dates:** the application and the database use their own clock. Build dates in tests relative to now. The seed uses `NOW()`/`CURDATE()` expressions, so "next month" and "expired" stay true.
- **Files:** the test server runs as root inside the container (host user, rootless podman), so it can write `data/…`. Tests that create files must remove them.
- **A `DEBUG_MODE` file** in the repository root changes application behaviour; `serve.sh start` refuses to start while it exists.

## What exists

| Path | Purpose |
|---|---|
| `application/config/testing/{database,config}.php` | the `testing` environment: database `alies_test`, base URL from `APP_URL`, separate session folder |
| `tests/Fixtures/schema.sql` | generated structure of all 56 tables (`tests/bin/dump-schema.sh` regenerates it) |
| `tests/Fixtures/seed.sql` | synthetic data with fixed ids (see below) |
| `tests/Support/TestDatabase.php` | `rebuild()`, `reseed()`, `connect()`; no CodeIgniter |
| `tests/Support/Fixtures.php` | constants for every seeded id/value |
| `tests/Support/Factory.php` | create rows from tests |
| `tests/Support/HttpTestCase.php` | base class of the black-box tests |
| `tests/Support/CodeIgniterDatabaseTestCase.php` | base class of the in-process model tests (unchanged, rolls back per test) |
| `tests/bin/*` | `reset-db`, `serve`, `ci`, `dc`, `dump-schema`, `router.php` |
| `tests/bootstrap.php` / `bootstrap-light.php` | with / without booting CodeIgniter |

### Seed data (`Fixtures::`)

Everything is synthetic. Every user's password is `Fixtures::PASSWORD` (`TestPassword123!`).

| Area | Rows |
|---|---|
| users | 1 admin, 2 vet (order nr 123456), 3 accounting, 4 member only, 5 no group, 6 second vet, 7 inactive. Groups: 1 admin, 2 members, **3 vet** (`Users_model::get_active_vets` hard-codes 3), 4 accounting |
| locations | 1 Praktijk, 2 Wagen, 3 soft-deleted |
| owners | 1001 Janssens (landline, mobile, mail), 1002 low budget, 1003 company with VAT number, 1004 disabled, 1005/1006 share a mobile number, 1007 soft-deleted, **1837** write-off client |
| pets | 2001 Rex (chip, weights), 2002 Mimi (cat), 2003 Bobby (owner 1002), 2004 dead, 2005 transferred, 2006 horse, 2007/2008 share a chip, 2009 soft-deleted, 2010/2011 a historical transfer pair |
| products | 4001 tiers {1:10, 5:8, 10:6} at 6% with GTIN, 4002 vaccine, 4003 antibiotic (CNK 1234567), 4004 dead volume 0.50, 4005 no stock, 4006 soft-deleted with stock, 4007 not sellable |
| stock | 5001–5008: in use at both locations, one expired, one `STOCK_ERROR`, one for the deleted product. No `CHECK` rows (create them with `Factory::stock(..., ['state' => 0])`) |
| billing | events 6001 (open, no bill), 6002 (closed, paid bill 7001, invoice 1), 6003 (on pending bill 7002); bill 7003 soft-deleted |
| other | wholesale 5001–5003, vaccines 11001–11003, lab report 10001, 3 rx, one stat query, one sticky, 2 API keys, all settings except `vamreg_api_key` |

**Opening a bill attaches all open events of the owner** (`invoice/get_bill`), so billing tests use `Factory::owner()`, not a seeded owner. Likewise `events/new_event` reuses an open event of the same pet from the last 14 days.

## Using the foundation

### `HttpTestCase` (tests/Http, no CodeIgniter classes allowed)

```php
final class MyTest extends HttpTestCase
{
    public function testSomething(): void
    {
        $owner = Factory::owner();                       // rows via PDO
        $this->loginAs('vet');                           // admin vet vet2 acc member nogroup
        $response = $this->post('owners/edit/' . $owner, ['submit' => 1, 'last_name' => 'X']);
        $this->assertRedirectsTo($response, 'owners/detail/' . $owner . '/true');
        $this->assertSame('X', $this->find('owners', $owner)['last_name']);
    }
}
```

- Requests: `get`, `post`, `ajaxGet`, `ajaxPost` (sets `X-Requested-With`), `postJson`, `postMultipart`, `attemptLogin`, `cli(['cli', 'daily'])` → `[exit, stdout, stderr]`.
- Responses: `assertStatus`, `assertRedirectsTo` (path only; `''` = home page), `assertNotRedirect`, `followRedirect`, `assertSee` / `assertDontSee` (HTML-decoded text), `json`, `assertDownload`, `text`, `excerpt`.
- Forms: `hiddenFields($response)` returns the hidden inputs of a page (tokens, ids).
- Database (own PDO): `row`, `rows`, `scalar`, `find($table, $id)`, `countWhere`, `assertRowExists`, `assertRowMissing`, `assertRowCount($n, $table, $where)`, `logCount($event)`.
- The database is reseeded before **every test class** (about 0.8 s), not before every test: tests in one class must not depend on each other's changes, or must create their own rows.
- `$this->cookies->clear()` before logging in as a second role inside one test.

### `Forms` (tests/Support/Forms.php)

`Forms::owner([...])` and `Forms::pet($ownerId, [...])` are complete form submissions, as the browser sends them. **Always post complete forms**: the application inserts NULL for every field a form leaves out, so a half form is a database error on "add" and silently blanks the record on "edit" (see T050/T051). Checkboxes are only sent when ticked.

### `Factory`

`Factory::owner() pet($owner) petWeight breed procedure product(['price' => 12.5]) productPrice stock($pid, [...]) wholesale event($pet, $status, $payment) eventProduct($event, $product, $volume, [...]) eventProcedure bill($owner, $status) payment($bill) vaccine($pet) labReport($pet, [results]) setting($name, $value) apiKey()`. Every creator takes column overrides last and returns the new id; `Factory::raw('NOW()')` passes an SQL expression; `Factory::insert($table, $row)` is the generic form. `setting()` base64-encodes like `Config_model::store`. In model tests call `Factory::useMysqli($this->ci->db->conn_id)` so rows are part of the test's transaction.

## Running several test runs at once (slots)

`TEST_SLOT=<n>` gives a run its own database (`alies_test_<n>`) and server (port `810<n>`), so people or agents can work in parallel without wiping each other's data: `TEST_SLOT=3 tests/bin/t.sh http tests/Http/Events`. `tests/bin/t.sh pure|http|model [paths]` creates the database, starts the slot's server when needed and prints a compact result (failures with their first lines). Without a slot the defaults are used. Stop a slot's server with `TEST_SLOT=<n> tests/bin/serve.sh stop`. Files under `data/` are shared by all slots: tests that write files use unique names and remove them.

## Things learned while building the suites

- **Always roll back.** `CodeIgniterDatabaseTestCase::tearDown` used to skip the rollback after a failed query (`trans_status() === false`). Tests that pin SQL errors then left a transaction open and every later test ran inside it (slow and order dependent). It now always rolls back, and also clears the pending `fields()` selection of every loaded model (see `tests/Models/MyModelStaleSelectTest.php`, a real MY_Model bug).
- **Class names must be unique** across `tests/` (PHPUnit loads them all into one process): a duplicate crashes the whole HTTP run. Prefix with the area when in doubt.
- **`get('path?x=1')` works** (the query string of the uri is kept; an empty array passed as `$query` used to drop it).
- **The test server runs with `short_open_tag=0`.** The container has no php.ini, so short tags are on and `views/pets/tooth/*.xml` (which start with `<?xml`) are parsed as PHP: the dental chart gives a 500. That is also what the default dev container does; a production php.ini normally turns it off.
- **Log event coverage** is guarded by `tests/Pure/LogEventCoverageTest.php` (a new log event without any test fails). **Known bugs** are collected in [KNOWN-BUGS.md](KNOWN-BUGS.md), regenerated with `python3 tests/bin/known-bugs.py`.

## Deviations from the first plan

| Plan | As built |
|---|---|
| one `phpunit.xml.dist` with suites pure/http/model | three configs, because the suites need different bootstraps (`tests/bootstrap-light.php` starts no framework): `phpunit.pure.xml.dist`, `phpunit.http.xml.dist`, `phpunit.xml.dist` (model + pure). Composer: `test`, `test:pure`, `test:http`, `test:model`, `test:stable` |
| `reset-db.sh` loads SQL with the mariadb client | the php container has no client, so loading is done in PHP/PDO (`reset-db.php`); `reset-db.sh` is a wrapper |
| separate app server through compose | `php -S` inside `alies-php` (`serve.sh`); **no change to `compose.yml`**. The port is not published to the host |
| tests/bin scripts run on the host | they run commands in the container via `dc.sh`; the host only needs podman |
| F07 external stubs | **not built yet**, see below |
| existing tests keep `existingId()` | most still use it and pass against the seed; they should move to `Fixtures::` when touched (T373, T374) |
| `bootstrap.php` used `CI_ENV=development` | now `testing`, otherwise the old model tests ran against the real database |

## Task status

| ID | Task | Status | Notes |
|---|---|---|---|
| F01 | schema + reproducible database | done | `dump-schema.sh`, `schema.sql`, `reset-db.php/.sh` |
| F02 | testing environment + server | done | `config/testing`, `router.php`, `serve.sh` |
| F03 | seed data | done | `seed.sql`, `Fixtures.php`; includes a transfer pair so `PetTransferBackfillRollbackTest` has data |
| F04 | `HttpTestCase` | done | `SmokeTest` passes |
| F05 | factories | done | PDO and mysqli sinks |
| F06 | suites + composer scripts | done | see deviations |
| F07 | external service stubs | **open** | needed before the Vamreg, mail and Covetrus/Medilab tests (T283–T285, T313, T329). The mail library and `Vamregclient` build their targets from config or hard-coded URLs; each needs a configurable base URL or a test double. Until then those tasks use a `Vamregclient` subclass (model layer) or skip with a reason |
| F08 | reorganise existing tests | done | 41 pure tests moved to `tests/Pure/`; the rest stay in the model suite; contract tests tagged `ci3-only` |
| F09 | `STATUS.md` | done | generated; F01–F10 marked done |
| F10 | CI job | done | `tests/bin/ci.sh` |

### Removed tests

These five existing tests failed independently of the database and the foundation. They were not written for this plan, no longer matched the code, and were removed (the rest of each file stays):

| Removed | Why it could not pass |
|---|---|
| `PetAvatarControllerContractTest::testApacheCannotServeStoredPetFilesDirectly` | reads `.htaccess`, which is not in the repository (deployment file) |
| `PetAvatarControllerContractTest::testProjectRequestLimitsAllowTheSpecifiedEightMegabyteImage` | reads `.htaccess` and `.user.ini`, same reason |
| `FinalizedEventViewTest::testSummaryUsesEventDateLinksAndExplicitCostStates` | `event/report/block_report_header` renders an empty card; the test expects links and cost states |
| `FinalizedEventViewTest::testLabPanelEmbedsMultipleResultsAndProvidesLinkAndUnlinkControls` | `block_lab_results` now renders a bill accordion; the test expects another structure |
| `FinalizedEventViewTest::testLabPanelShowsAnEmptyStateWithoutLinkedLabs` | the view no longer has that empty state |

The two helpers they used (`baseData()`, `lab()`) were removed with them. The behaviour they meant to cover is planned as HTTP tests: the event page and lab panel in T101/T102/T105 (`04-events-billing.md`), attachment delivery in T240.

The model suite now passes: 111 tests. PHPUnit still prints about 147 deprecations ("creation of dynamic property …"). They come from CodeIgniter 3 running on PHP 8.4, not from the tests, and go away with the CI4 port.

## Not built

- **F07, external service stubs** (mail, Vamreg, Medilab/Covetrus). Documented in the status table above; the tests that need it (T283–T285, T313, T329) say what to do until it exists.
- **Every T-task** in `01`–`09` is a specification only; none is implemented yet apart from the legacy tests that already existed. `STATUS.md` tracks them.
