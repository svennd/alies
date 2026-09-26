# Document center

Open **Administration → Documentencentrum** (`/document_center`) as an administrator. New **Typst PDF** buttons appear on invoice and pet vaccination screens; the monthly vaccine screen has a separate Typst reminder action. Existing print, PDF, mail, archive, merge, and CSV actions keep their original routes and renderer.

## Settings and previews

Shared branding offers practice name, address, contact details, accent color, and a PNG/JPEG logo (maximum 2 MiB, 4096 pixels per side, 12 million pixels). Logos are normalized to PNG and stored with random filenames in `data/documents/`. Stored files have a PHP exit guard and restrictive permissions; only the authenticated admin logo action returns image bytes. Keep this directory persistent and writable by the PHP service account, and preserve normal PHP handling on the web server. It is excluded from Git.

All types use A4 portrait and Noto Sans, with font size 8–14 pt, margins 12–30 mm, and paragraph spacing 1–10 mm.

| Type | Editable fields |
| --- | --- |
| Invoice | Payment wording, footer, payment QR visibility |
| Reminder | Subject, greeting, body, closing |
| Per-pet overview | Title, introduction, footer, veterinarian/location/next-due columns |

Reminder placeholders: `{recipient}`, `{pet}`, `{vaccine}`, `{disease}`, `{due_date}`. Unknown placeholders are rejected. Record values are inserted literally, without recursively expanding placeholders or interpreting Typst commands. Text fields allow 600 characters, except reminder body (4000). A valid text length does not guarantee that a letter fits: preview and batch generation reject overflow and identify the affected letter.

**Preview** uses unsaved values and sample data. Enter a bill ID, pet ID, or eligible vaccination-row ID to preview existing records. Reminder previews also use a month offset (0 = this month, 1 = next month). Preview never saves settings or prepares a bill. **Save** persists the current tab; **Restore defaults** resets only that tab. Dutch and English labels/defaults are available. Accounting values and payment configuration remain supplied by existing records.

## Downloads

Invoices require sufficient persisted data. Drafts must first use the established billing workflow. Typst never recalculates bills, allocates invoice numbers, links events, changes stock/payments, sends mail, or replaces cached invoices. QR images reuse the installed EPC/QR libraries with explicit PNG output; legacy `Qr.php` is unchanged.

Pet overviews preserve the old PDF's exclusion of externally recorded vaccinations (`event_id = 0`). Empty overviews show a message. Invoice and overview tables paginate with repeated headings.

For reminders, choose a month on the existing vaccine screen, open the Typst action, optionally exclude products, and select rows. At most the first 10 are initially selected; deselect rows to select later candidates. Generation downloads **one PDF, one complete letter per vaccination row**, ordered by due date and row ID. Two rows for one pet produce two pages. The server rechecks month access, uniqueness, the 1–10 limit, filters, and eligibility. Nothing is marked as sent. Any failed or overflowing letter prevents the entire download.

`Document_data_model::REMINDER_BATCH_LIMIT` remains **10** and is not an admin setting. One nonblocking lock covers all previews/downloads per PHP container. Busy requests receive a retry message; individual-download endpoints return HTTP 429. There is no automatic dompdf fallback.

## Database deployment

Apply migration 057 using the existing explicit upgrade workflow:

```sh
podman exec alies-php php index.php upgrade up 57
```

It creates `document_settings` (`name` VARCHAR(64) primary key, `payload` MEDIUMTEXT, `updated_at` DATETIME). Defaults apply until settings are saved. Legacy `config` schema/values are untouched. The historical `migration_version` setting stays unchanged; upgrades use the explicit version argument.

## Runtime

Verified on 2026-09-20 as `www-data` inside `alies-php`: PHP 8.4.25, `/usr/local/bin/typst` 0.14.2, Noto Sans normal 400 and 700 at standard width.

The active development build is `/home/svenn/alies/php/Dockerfile` with `/home/svenn/alies/compose.yml`, not the repository's Alpine recipe. That Debian Dockerfile already installs pinned Typst 0.14.2 and `fonts-noto-core`/`fonts-noto-extra`. It remains owned by the local deployment and is not overwritten by this change.

Server overrides: `ALIES_TYPST_BINARY` (default `/usr/local/bin/typst`) and optional `ALIES_TYPST_FONT_PATH`. PHP must be able to execute Typst and discover Noto Sans regular/bold. No external Typst packages are used. Each compilation has a 10-second deadline within a 60-second renderer budget; one request per PHP container can render at once.

The repository's Alpine `podman/Containerfile` now has a pinned `typst-runtime` stage installing Typst 0.14.2 and `font-noto`, inherited by the application runtime. That stage was built separately and compiled Noto Sans text as `www-data` with networking disabled. Running application containers were not replaced.

`application/config/documents.php` controls server paths/limits. PHP needs `proc_open`, GD, mbstring, Composer dependencies, writable private temporary storage, and write access to the logo directory. Typst runs directly as the PHP service account; PHP never invokes Podman. Payloads use private JSON files, never command-line arguments. Temporary inputs/PDFs are deleted on success/failure; only the lock remains under the system temporary directory's `alies-documents/`. Downloads use private/no-store headers. Logs contain type, status, count, and duration, not record payloads.

## Baseline

Existing billing helper tests passed before implementation: 3 tests, 6 assertions. Baseline/regression checks cover dompdf cache reuse, merging, CSV columns, the unchanged invoice template and automatic invoice-file generator in isolated temporary storage, and a real legacy vaccination-PDF HTTP download. **237 existing invoice hashes remained unchanged** after integration checks. No mail was sent. Legacy controllers, print templates, `Pdf.php`, and `Qr.php` remain unchanged.

## Verification

Run focused checks as the service user in the local development environment:

```sh
podman exec --user www-data \
  -e DOCUMENT_HTTP_BASE=http://alies-nginx alies-php \
  php vendor/bin/phpunit --do-not-cache-result \
  tests/Integration/DocumentHttpTest.php tests/Integration/DocumentDataTest.php \
  tests/Libraries/DocumentSettingsTest.php tests/Libraries/TypstDocumentTest.php \
  tests/Libraries/TypstFailureTest.php tests/Libraries/LegacyPdfCompatibilityTest.php \
  tests/Views/DocumentReminderSelectionTest.php tests/Helpers/BillingHelperTest.php
```

Data fixtures roll back their transactions. Opt-in HTTP tests use temporary sessions. The settings-save/upload test additionally requires `DOCUMENT_HTTP_ALLOW_SETTINGS_WRITE=1`; it restores original settings and removes its upload. Enable that only on an idle, isolated development instance. Do not run HTTP tests against production or while someone is editing document settings: concurrent saves invalidate the preview immutability comparison. Existing PHP/CodeIgniter and test-configuration deprecation notices remain.

Coverage includes roles/month authorization, request tokens, save/reload/restore, logo access, sample/existing-record previews, paid/unpaid downloads, read-only data, companion recipients, excluded/stale/missing/duplicate/oversized selections, later-row selection, exact 1/5/10-page batches, and missing compiler/font, malformed output, timeout, overflow, and concurrency failures.

Visual checks covered all three types, a 65-line/four-page invoice, a 70-row/five-page overview, accents, repeated headers, and page numbering. The rendered payment QR decoded to the expected EPC beneficiary, IBAN, EUR 3146.00 amount, and reference. Command-like text extracted literally from the PDF. `pdfinfo` independently confirmed batch page counts, in addition to template assertions.

Isolated service-user measurements, including font checks and compilation:

| Letters/pages | Elapsed | PHP peak | Child peak RSS | PDF bytes |
| --- | --- | --- | --- | --- |
| 1 | 0.335 s | 8 MiB | 37.9 MiB | 24,720 |
| 5 | 0.356 s | 8 MiB | 38.0 MiB | 42,199 |
| 10 | 0.366 s | 8 MiB | 37.8 MiB | 64,109 |

Concurrent attempts were rejected before a second compiler started; cleanup left only the lock. The local FPM pool has five workers, PHP execution limit 30 seconds, and no additional FPM termination limit. Nginx has no custom FastCGI timeout. Font discovery and compilation each have a 10-second deadline, so their combined normal worst-case wait is below service limits; the renderer's 60-second budget is a further ceiling. These small local fixtures do not establish production capacity. Keep the batch limit at 10 pending workload evaluation.

## Rollback

Remove/disable the new navigation, buttons, and controllers. Existing exports need no conversion or recovery. Retained settings/assets are inert and can be kept for later use. If deliberately discarding settings, migration 057's `down()` drops only `document_settings`; do not roll back unrelated migrations. Keep backed-up logo assets if the feature may be restored.
