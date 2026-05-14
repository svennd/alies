# Testing

This project now includes a PHPUnit suite for regression checks on production-critical PHP logic.

## What is covered

- Helper functions that affect billing and product parsing:
  - `application/helpers/cnk_helper.php`
  - `application/helpers/gs1_helper.php`
  - `application/helpers/generate_bill_id_helper.php`
  - `application/helpers/online_helper.php`
- Lab device payload parsing:
  - `application/third_party/api/devices/Ikems.php`
  - `application/third_party/api/devices/Ms4s2.php`

The suite has two layers:

- fast logic tests for helpers and device parsers
- database-backed workflow tests that boot CodeIgniter in CLI mode

The workflow tests run inside a database transaction and roll back after each test, so they should not leave residue in the local database.

## Run the tests

Install dependencies if needed:

```bash
composer install
```

The full suite now requires the local MySQL database configured for this project in `application/config/database.php`, because the workflow tests boot the application and exercise real models.

Run the full suite:

```bash
composer test
```

Or run PHPUnit directly:

```bash
vendor/bin/phpunit
```

## Before deploying

Use this as a minimal production gate:

```bash
composer test
```

If the suite fails, do not deploy until the regression is understood.

## Current limitations

- The suite does not boot the full web application.
- It currently covers model-level workflows rather than browser-level controller flows.
- There are still no authentication or end-to-end HTTP tests yet.
- The highest-value next step would be adding smoke tests for critical routes in a dedicated test environment.
