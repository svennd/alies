# Briefing for whoever writes tests from the specs

Read this first, then [README.md](README.md) (layers and rules) and [00-foundation.md](00-foundation.md) (the test API). This file is about how to work, and what went wrong before.

## What you deliver

For each task ID you are given: a passing test file at the path the spec names (or a sensible one next to it), written from the **source code**, not from the spec text. Report at the end; do not edit `STATUS.md`, the specs, `seed.sql`, or the shared support files (see "Shared files").

## Working in a slot (several people work at the same time)

You are given a slot number `N`. Prefix every command with `TEST_SLOT=N`. It gives you your own database (`alies_test_N`) and server (port 810N). Never run anything without your slot, and never use another slot.

```bash
TEST_SLOT=N tests/bin/t.sh http  tests/Http/Events            # directory or file; creates db + starts server when needed
TEST_SLOT=N tests/bin/t.sh http  tests/Http/Events/LockTest.php --filter testSomething
TEST_SLOT=N tests/bin/t.sh model tests/Models/FooTest.php      # CodeIgniter 3 in-process, same database
tests/bin/t.sh pure                                            # no database
TEST_SLOT=N RESET=1 tests/bin/t.sh http tests/Http/X           # rebuild the database first
TEST_SLOT=N LINES_MAX=200 tests/bin/t.sh http ...              # more output
```

The output is compact on purpose. Nothing here touches the development database (`alies`); the test code refuses to.

When you are done, stop your server: `TEST_SLOT=N tests/bin/serve.sh stop`.

## The code is the truth

Earlier analysis notes (including the spec files) contained wrong claims. Examples found while writing tests: `format_phone("0470123456 (gsm)")` gives `0470123456` (not `…(gsm`); the QR library returns SVG, not PNG; `Upgrade` is not open over HTTP; the home page does not print the user's name; `auth/edit_group` can never save. So:

1. Read the controller/model/view the task is about before writing any assertion.
2. Derive expected numbers by hand (or with a separate calculation), never by running the code under test and copying the result.
3. When the code does something odd or wrong, **keep the test green against the current behaviour**, tag it `#[Group('known-bug')]`, and say why in a comment starting with `KNOWN BUG:`. The CI4 port decides later. Do not fix application code and do not skip tests because the behaviour is silly.
4. Add a **positive control** where a test says "X is refused": show in the same test that the same action works for the allowed case. Otherwise the test can pass for the wrong reason.
5. After a test passes the first time, ask whether it could pass when the feature was broken. Tighten it if so.

## Conventions

- Layers: `tests/Http/<Area>/` black-box over HTTP (extends `HttpTestCase`, **no CodeIgniter classes**); `tests/Pure/` no framework; `tests/Models/` CodeIgniter in-process (extends `CodeIgniterDatabaseTestCase`, rolled back per test; call `Factory::useMysqli($this->ci->db->conn_id)` in `setUp` when you use Factory there).
- One class per task or per coherent group of tasks, `final class XxxTest`, methods `testSomethingInPlainEnglish`. Start the class with a docblock: task id, source file, the rules in plain words.
- Data: create your own rows with `Factory` (never change seeded rows; the database is reseeded per test **class**, tests in one class share state). Seeded ids are in `Fixtures`. Billing flows use a fresh `Factory::owner()` because opening a bill attaches **all** open events of the owner.
- Forms: use `Forms::owner()` / `Forms::pet()`, or post the complete field list yourself. The application inserts NULL for every omitted field, so half forms give a 500 on "add" and silently blank the record on "edit".
- Redirects: `assertRedirectsTo($response, 'path')` compares the path only. CodeIgniter 3 sends `redirect($u,'refresh')` as status 200 + a `Refresh` header; never assert a redirect's status code.
- Dates: relative to now. Money: `assertEqualsWithDelta` with literals computed by hand in a comment.
- Files your test creates (uploads, stored PDFs, import files): unique names (`uniqid`), remove them in `tearDown`. Other people run in parallel on the same `data/` folder.
- Log rows: table `log`, columns `event`, `level` (FATAL 1, ERROR 2, WARN 3, INFO 4, DEBUG 5), `msg`. Stock log: `log_stock`.
- Soft deleted: bills, payment, owners, pets, sticky, stock_location, booking_codes, procedures, products (`deleted_at`). Hard deleted: events and their lines, stock, products_price, breeds, vaccine_pet, pets_weight, users.
- `MY_Model::get()/get_all()` return `false` for nothing (not `[]`); `update($data)` without a where changes every row.
- Constants (`application/config/constants.php`): STATUS_OPEN 0/CLOSED 1/HISTORY 2; BILL_DRAFT 0, PENDING 1, UNPAID 2, INCOMPLETE 3, PAID 4, OVERDUE 5, ONHOLD 6, HISTORICAL 7; stock state CHECK 0, IN_USE 1, HISTORY 2, ERROR 3, MERGE 4.
- Staff screens answer a wrong group with a **500** ("Not part of the correct group"); admin-only controllers redirect to `/`.

## Traps that cost time before

- PHPUnit 13: no `expectWarning()`; use attributes (`#[DataProvider]`, `#[Group]`, `#[IgnoreDeprecations]`); a data provider that supplies more arguments than the test method accepts is a PHPUnit warning, so accept them all. Do not name a helper `fail()` (it is `Assert::fail`).
- A `'é'` typed into a file through some tools becomes `é`: assert on `u00e9` without the backslash if you need it.
- `--filter` matches class and method names only, not directories; pass one path per `t.sh` call.
- Class names must be unique over the whole `tests/` tree (a duplicate kills the run for everyone). Do not name a helper `count()` or `fail()` in a TestCase.
- Model tests: a query you provoke to fail on purpose (`db_debug = false`) is rolled back by the base class; restore `db_debug` afterwards.
- Several redirects point at pages that do not exist (`auth`, `vet/dashboard`, `auth/forgot_password`): following them gives 404. That is a known bug, not a test problem.
- The built-in test server does not give `$_SERVER` the environment; nothing for you to do, but do not start servers any other way than `tests/bin/serve.sh`.
- `users.current_location` is TINYINT; `groups.name` is varchar(20); non-strict SQL mode silently cuts and converts. If a stored value surprises you, look at the column type.
- Responses of AJAX endpoints that return `false`/`array()` have an **empty body**, not JSON.
- Printing a whole page in a failure message makes the output unreadable; use `assertSee` (it only shows a short excerpt).
- External services must never be called: no Vamreg, mail, Medilab, wholesaler. If a test needs one, test up to the boundary and skip the rest with `markTestSkipped('needs F07 stub: ...')`, or use a recording subclass in the model layer.

## Shared files

Do not edit: `tests/Support/*`, `tests/Fixtures/seed.sql`, `schema.sql`, `tests/bootstrap*.php`, `phpunit*.xml.dist`, `tests/bin/*`, `STATUS.md`, the spec files, or anything in `application/`. Other people are editing next to you.

If you need a helper, put it in your own test directory and `require_once` it (name it after your area, for example `tests/Http/Stock/StockHelper.php`, class `StockHelper`). If you need a row the factories cannot make, use `Factory::insert('table', [...])`. If the seed lacks something, create it inside your test and say so in your report.

## Report

Finish with a short report (max ~350 words plus the table):

| Task | File(s) | Tests | Notes |
|---|---|---|---|

then: **spec corrections** (where the spec text was wrong, with the right fact), **bugs found** (one line each, tagged known-bug in the tests), **not done / skipped** and why, and the final run line of each directory you own. Mention anything you had to work around in the foundation.
