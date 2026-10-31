# 08 — Admin settings, members, tools, restore, CLI, REST API, upgrade (T300–T349)

**CLI tests** are black-box tests too.
- Run them with `HttpTestCase::cli(['cli','daily'])`, defined in F04. It runs `CI_ENV=testing php index.php <args>` from the repo root using `proc_open`, and returns `[exitCode, stdout, stderr]`.
- Assert on stdout and on the DB through PDO. In CI4 the command becomes `php spark …`, and only `cli()` has to change.
- Settings read by the CLI are **base64-decoded** strings in the `config` table. Seed them with `base64_encode(...)`.

## Admin (`Admin.php`)

### T300 — Settings page
- **File:** `tests/Http/Admin/SettingsTest.php`.
- **Assert:** a POST to `admin/settings` with `submit`, `conf_due_date=14` and `conf_iban=BE71…` stores `config.value = base64('14')` and `base64('BE71…')`.
- **Assert:** a setting that does not exist yet is inserted, through the upsert.
- **Assert:** `check_pruning=on` stores `base64('1')`. Leaving it out stores `base64('0')`, and the same goes for the other four checkboxes.
- **Pin:** posting an empty `conf_x` does not clear the stored value.
- **Assert:** the GET shows the current values.

### T301 — Locations
- **Assert:** `admin/locations` with `submit=add_location` and `name` inserts a location. `submit=update_location_name` renames one.
- **Assert:** `admin/locations_rm/{id}` soft-deletes it.
- **Assert:** `restore/locations/{id}` restores it, and `restore/locations` lists the trashed ones.

### T302 — Booking codes
- **Assert:** `admin/booking` with `submit=add_booking_code`, `category`, `code` and `btw` inserts a code. The page shows the product and procedure counts.
- **Assert:** `admin/booking_rm/{id}` soft-deletes it, and `restore/booking/{id}` restores it.

### T303 — Product types and labels
- **File:** `tests/Http/Admin/ProductTypesTest.php`.
- **Types:**
  - `add_product_type` with `name=" Foo "`, `root=<rootId>`, `icon="Fa-Dog!!  x"` and `icon_color="#ABCDEF"` stores `name="Foo"`, a sanitised icon (`"a-dog x"`-like; check the regex) and `icon_color="#abcdef"`;
  - `icon_color="red"` is stored as NULL;
  - a root that is itself a child is normalised to NULL, so there are never more than 2 levels.
- **Labels:**
  - `add_product_label` with `name=" Pijn "` stores `"pijn"`;
  - adding the same name again inserts nothing.
- **Deleting a type:** `admin/product_types_rm/{id}`:
  - sets the children's `root` to NULL;
  - deletes the type;
  - sets `products.type=0` for products of that type.
- **Deleting a label:** `admin/product_labels_rm/{id}` removes the label and its links. `admin/product_labels` redirects to `admin/product_types`.

## Members (`Member.php`)

### T305 — User management
- **File:** `tests/Http/Admin/MemberTest.php`.
- **Create:** `member/create_user` with `submit=create_user`, `email`, `password` and `password_confirm` (12+ characters), names, `phone`, `order_nr` and `groups[]=[3]`:
  - a `users` row exists with `username="First Last"`;
  - the user is in group 3 only;
  - they can log in (T031) and open `/`.
- **Mismatched passwords:** the warning "email or password not correct;" is shown.
- **Pin:** a user created **without** groups ends up in `members` only and gets a 500 on `/`.
- **Edit:** `member/edit_user/{id}` with `active` unchecked deactivates the user. With an empty `password` the password is unchanged. `groups[]` replaces the groups.
- **List:** `member` lists active users. `?show_inactive=1` includes inactive ones.

## Tools (`Tools.php`)

### T310 — Duplicate finders
- **Assert:** `tools/duplicate_phones` lists 2 seeded owners that share a `04…` mobile number.
- **Assert:** `tools/duplicate_chips` lists 2 pets with the same chip.
- **Pin (`known-bug`):** `tools/duplicate_clients` fails with a DB error (500) because of an `owner.id` typo.

### T311 — Merge clients
- **Assert:** `tools/merge_clients/{A}/{B}`:
  - sets owner A to `disabled=1` with `msg` starting `merge_id_disable:{B}:`;
  - transfers each of A's pets to B, the same way as T066;
  - redirects to `tools/duplicate_clients`.
- **Pin:** there is no check that A ≠ B, and the request is a GET.

### T312 — API key management
- **Assert:** `tools/show_api_keys` with `create_key=1` and `device=ms4s2` inserts an active `api_keys` row with a 64-character hex `key_hash`.
- **Pin (`known-bug`):** the plain-text key is never shown.
- **Assert:** `revoke_key` with `key_id` sets `active=0`.

### T313 — Covetrus connection test
- **Assert:** `tests/test_connection_covetrus/u/p` makes an outbound call to `online.medilab.be`.
- **Skip unless the F07 stub can intercept it.** If it is hard-coded, mark the test skipped with the reason and list it as an external dependency.

## CLI (`Cli.php`)

### T320 — Help, and refusing web access
- **Assert:** `cli()` with no arguments prints "Welcome to alies, cli" and the list of functions.
- Web access is covered in T043.

### T321 — `cli daily` orchestration
- **Setup:** set every gate setting (`autoclose`, `pruning`, `autdeath`, and so on), plus seed data that each step will act on. Put the stub in place for `vamreg_update`.
- **Assert:** after `cli daily`, the effect of every step is visible in the DB:
  - autoclose has closed old events;
  - log pruning has run;
  - auto-death has run;
  - stock_clean has archived empty IN_USE rows;
  - a new `stock_value` row exists;
  - `usage_count` has been recalculated;
  - old rate-limit rows are gone.
- **Pin (`known-bug`):** a missing `vamreg_api_key` setting throws a TypeError and aborts the run.

### T322 — Autoclose
- **Setup:** `autoclose=base64('7')`, `autodisable=base64('1')` and `autotemplate=base64('<p>tpl</p>')`.
- **Assert** for events older than 7 days that are not finished:
  - an event whose anamnese is empty or equal to the template gets `report=2` and `no_history=1`;
  - an event with real anamnese text gets `report=2`, keeps `no_history=0`, and has `' [Auto-Closed]'` appended to its anamnese.
- **Assert:** events newer than 7 days are untouched.
- **Assert:** a log row `autoclose` is written.
- **Assert:** with `autoclose` set to an empty value, nothing happens.

### T323 — Prune
- **Setup:** `pruning=1`, `prune_global_log=30` (days), `prune_stock=2` (years) and `prune_stock_log=30` (days).
- **Assert:**
  - `log` rows older than 30 days are deleted;
  - HISTORY `stock` rows older than 2 years are deleted, and IN_USE rows are kept;
  - old `log_stock` rows are deleted;
  - a log row is written for each of the three.
- **Assert:** with `pruning=0`, nothing is deleted.
- Check the exact table names in the code: `log` versus `logs`.

### T324 — Auto-death settings
- **Assert:** with `autdeath=1` and `auto_dead_0=15`, an old dog with no recent events is marked dead and a log row "killed: 1 of type: 0" is written.
- **Assert:** types with a setting of 0 are skipped.

### T325 — Delivery import
- **File:** `tests/Http/Cli/DeliveryImportTest.php`.
- **Setup:** write a pipe-delimited file to `data/stored/delivery/test.csv`. Use the exact Dutch headers from `Cli::delivery`, with 2 rows:
  - one whose `Art. nr.` matches a seeded wholesale `vendor_id`, and whose CNK is in `vamreg_index`;
  - one that matches nothing.
- **Assert:** `cli delivery test.csv`:
  - inserts 2 `delivery` rows with these values:
    - dates converted from `j/m/Y` to `Y-m-d`;
    - prices with `,` converted to `.`;
    - `wholesale_id` set for row 1 and NULL for row 2;
  - inserts 1 `vamreg_in_buffer` DRAFT row for row 1 (`in_quantity_pack_count=amount`);
  - moves the file to `processed/`;
  - prints `lines : 2`;
  - writes a log row `import_delivery`.
- **Assert:** for a missing file the output contains "File not found".
- **Pin:** running the same import again duplicates the rows.
- **Clean up** the files.

### T326 — Price list import
- **File:** `tests/Http/Cli/PricelistImportTest.php`.
- **Setup:** a pipe-delimited file in `data/stored/pricelist/` with a header row and 11 positional columns. Include 3 rows:
  - an existing `vendor_id` with a new bruto;
  - a new article;
  - a row with `bruto='-'`.
- **Assert:**
  - the existing row is updated, and a `wholesale_price` row stores the **old** bruto;
  - the new article is inserted with `last_bruto=bruto`;
  - the `-` row is skipped;
  - an empty group becomes the `wholesale_type` "unknown", which is created if it does not exist;
  - the file is moved to `processed/`.
- The import sleeps 0.5 s per 100 lines, so keep the fixture small.

### T327 — Stock clean and usage recalculation
- **Assert:** `cli stock_clean`:
  - moves IN_USE rows with volume 0 to state 2;
  - archives IN_USE rows of soft-deleted products, keeping their volume;
  - writes WARN log rows.
- **Assert:** `cli recalculate_usage` sets `products.usage_count` to the **number of** `events_products` rows in the last 90 days.
- **Pin:** a product with no recent use keeps its old count.

### T328 — Monthly product usage dump
- **Assert:** `cli monthly_product_usage_dump 2026-09 1` writes `data/stored/reports/monthly_product_usage/monthly_product_usage_2026-09.csv`:
  - its header row is exactly the documented column list (golden file);
  - it has one row per `events_products` line on events created in September 2026;
  - lines from August and October are excluded.
- **Assert:** running it again without `force` prints "already exists".
- **Assert:** `2026-13` prints "Invalid month, expected YYYY-MM".
- **Assert:** with no arguments on a day other than the 1st, it prints "Skip".

### T329 — Rate-limit cleanup and Vamreg update
- **Assert:** `cli cleanup_rate_limits` deletes `api_rate_limit` rows with a `minute` older than 2 days and keeps recent ones.
- **Assert:** `cli vamreg_update`, with the stub in place, upserts `vamreg_index` and writes the log rows (see T284).

## REST API (`api/Lab.php`, `API_Controller`)

### T335 — API authentication and rate limit
- **File:** `tests/Http/Api/ApiAuthTest.php`.
- **Assert:**
  - no key returns 401 JSON `{"status":"error","message":"Missing API key"}`;
  - an unknown key returns 401 "Invalid API key", and so does a revoked key;
  - `Authorization: Bearer <key>` works, and so does `X-API-Key: <key>`;
  - `Authorization: Token <key>` returns 401 even when `X-API-Key` is also valid (pin this).
- **Assert:** a successful call updates `api_keys.last_used_at`.
- **Rate limit:** the 61st call within the same minute returns 429 "Rate limit exceeded". Pre-seed `api_rate_limit` with `count=60` for the current minute, so the test doesn't need 61 calls.
- The key is compared **as stored** with `api_keys.key_hash`, with no hashing. Seed it accordingly.

### T336 — Lab import endpoint
- **File:** `tests/Http/Api/LabImportTest.php`.
- **Assert:**
  - `POST api/lab/import/unknown` returns an HTML 404 (pin: not JSON);
  - an invalid JSON body returns 400 `{"status":"error","message":"Invalid JSON payload"}`;
  - a valid Ms4s2 payload (`tests/Fixtures/ms4s2_payload.json`) with a pet that matches by `pet_id` returns `{"status":"ok"}` and creates `lab_report` and `lab_results` rows;
  - the same payload with an unknown pet returns `{"status":"pending"}` and creates a `lab_report_pending` row;
  - posting the same payload twice keeps one report, with its results replaced.
- **Pin (`known-bug`):** a payload with a phone and an `owner_name` that has no `/` returns 500.
- Do the same for `ikems`, `lmscan` and `medilab` with one fixture each.

## Upgrade (`Upgrade.php`, CLI)

### T340 — Migrating up and down
- **Run this against a throwaway copy of the DB**, for example `alies_test_migr`.
- **Assert:** `upgrade up` at the latest version prints "Already at migration version 0NN".
- **Assert:** `upgrade up abc` prints "Invalid version".
- **Assert:** `upgrade up 1` prints "Refusing to migrate down".
- **Assert:** `upgrade up 999` prints "does not exist".
- **Assert:** `upgrade down` moves down 1 version and prints "Migrated down from X to Y". Then `upgrade up` returns to the latest version.
- This guards the migration chain, which CI4 replaces.

## Cross-cutting

### T345 — Route smoke crawl
- **File:** `tests/Http/RouteSmokeTest.php`.
- **Data provider:** every GET-able route without side effects. Generate it by listing controller methods. Exclude any method that mutates data (the specs above flag those) and replace `{id}` with seeded IDs.
- **Assert,** logged in as admin, for each route:
  - the status is 200, or a redirect;
  - the body contains no `A PHP Error was encountered`, `Fatal error`, `Severity:` or `Database error`.
- This is the broadest regression net for the port. Keep the route list in `tests/Fixtures/routes.php` so CI4 can reuse it.

### T346 — No PHP notices on the main pages
- **Assert:** with `error_reporting(E_ALL)` and `display_errors=1` on the test server, the pages from T101, T062, T052, T146, T180 and T093 contain no "Severity: Notice", "Warning" or "Deprecated".
- Collect the current offenders into a `known-bug` list instead of failing on them.

### T347 — Flash messages survive redirects
- **Assert:** after `events/link_lab` (T105) and `pets/save_avatar` (T069), following the redirect shows the flash text exactly once. The page after that does not show it.

### T348 — Every log event name
- **Layer:** H, meta-test.
- **Steps:** after running the full HTTP suite, gather the distinct `log.event` values, and compare them with the list of `logger(…, '<event>'` strings found by grepping `application/`.
- **Output:** a report of events never triggered by any test. **As built** (`tests/Pure/LogEventCoverageTest.php`): scans the application for literal event names and the tests for mentions, and compares the untested ones with `tests/Fixtures/log_events_untested.txt`. A new untested event fails; an event that became tested must be removed from the baseline (the list only shrinks). Events built from variables are not found.

### T349 — Replace the CI3-only contract tests
**Done.** Every test in `tests/Controllers/*ContractTest.php` reads the PHP source of a CodeIgniter 3 controller, so none of them survives a rewrite. They stay tagged `ci3-only` until the port and can then be deleted. The behaviour each one guarded is now covered over HTTP:

| Contract test | Now covered by |
|---|---|
| `FinalizedEventControllerContractTest` (lab link/unlink are POST only, preview is safe) | `tests/Http/Events/LinkLabTest.php` (T105), `tests/Http/Files/PreviewTest.php` and `EventUploadTest.php` (T240, T243) |
| `LabPendingControllerContractTest` (pending queue, POST only, audit logs, lookups, reassign) | `tests/Http/Lab/LabPendingHttpTest.php` (T255), `LabListDeleteReassignTest.php` (T254) |
| `PetAvatarControllerContractTest` (authenticated, POST only, safe delivery headers) | `tests/Http/Pets/PetAvatarHttpTest.php` (T069) |
| `PetTransferControllerContractTest` | `tests/Http/Pets/PetTransferHttpTest.php` (T066) |
| `UpgradeControllerContractTest` (`retry_medilab_codes`) | `tests/Http/Cli/UpgradeTest.php` (T340) and `CliOverHttpTest.php` (T043) |

The exclusion `phpunit --exclude-group ci3-only` therefore loses no coverage. Not carried over: the two avatar tests that were removed earlier because they read `.htaccess` / `.user.ini` (deployment files).
