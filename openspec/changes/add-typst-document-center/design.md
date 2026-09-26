## Context

See proposal.md for motivation and scope. This CodeIgniter 3 application uses `Pdf.php` for dompdf rendering and PDF merging. `Invoice::get_bill()` mixes document preparation with recalculating bills, linking open events, and automatic file generation. Its private `generate_pdf()` writes to `data/stored/.invoices/`. These paths must not become dependencies of the new renderer.

`Vaccine::export_vaccine()` renders a per-pet overview; its HTML view excludes records with `event_id = 0`. Reminder candidates already come from `Vaccine_model::get_expiring_vaccines()` with month/product filters, animal status exclusions, `no_rappel`, disabled-recipient checks, and companion-over-owner recipient selection. Its current output lacks a stable vaccination row ID. The CSV view explicitly selects columns, but the new selection query should avoid changing that existing contract.

Admin authorization is implemented by `Admin_Controller`, while invoice and vaccine screens use `Vet_Controller`. Existing settings use `Config_model`, but `config.value` is only VARCHAR(255). The user approved a dedicated `document_settings` table during implementation so longer wording and structured settings do not alter existing configuration storage. The default application language is Dutch, with English language files also present.

The user reports installing Typst and expects Noto Sans in the PHP container. During planning on 2026-09-20, `podman exec alies-php ...` could not resolve Typst on PATH or at common installation paths; fontconfig was also unavailable, so font presence was not verified. The running PHP 8.4 container is Debian-based and bind-mounts the repository, while the checked-in Containerfile uses Alpine. This is a deployment verification item, not a reason to change the selected architecture or modify containers during planning.

## Goals / Non-Goals

**Goals:**

- Establish a reusable boundary between read-only document data, editable presentation settings, maintained templates, and PDF delivery.
- Keep the first rollout additive and independently removable, with no changes to existing PDF classes or legacy generation semantics.
- Make resource use predictable enough to evaluate a small synchronous workload.

**Non-Goals:**

- Switching the default renderer, rewriting legacy invoice flows, replacing PDF merging, or updating archived documents.
- An advanced Typst editor, arbitrary template uploads, email delivery, background queues, or recipient grouping.
- Changing invoice accounting rules, vaccination eligibility, or inclusion of externally administered vaccinations.

## Decisions

### 1. Separate controllers and services

Add an admin document-center controller extending `Admin_Controller`, and a generation controller extending `Vet_Controller` with the existing month-access restrictions enforced for reminders. Admins configure/preview documents; users already authorized for invoice/vaccine exports can use new Typst buttons. Endpoints recheck authorization and record existence; hiding a button is not authorization.

Use new document settings, read-only data builder, and Typst rendering services. Do not alter `Pdf.php`, the existing HTML print views, or the existing PDF generation endpoints. Add navigation and buttons in their corresponding views. A separate controller avoids accidentally running `Invoice::get_bill()` and its write operations. Existing billing models/helpers can be used only where their calls are read-only.

```text
Admin settings / unsaved preview values
                    |
New document endpoint -> read-only data builder -> JSON + approved assets
                                                    |
                                           maintained .typ template
                                                    |
                                               Typst CLI
                                                    |
                                  Single PDF download per request

Existing PDF and CSV endpoints continue independently.
```

Alternative: add a renderer selector to `Pdf.php`. Rejected for this phase because it would couple the experiment to cached invoices, automatic generation, and other document types.

### 2. CLI rendering with explicit runtime configuration

Invoke a configured Typst executable using `proc_open` with an argument array, not shell concatenation. Execute inside the PHP container under the PHP service account, without spawning Podman from PHP. Keep the binary path and compiler limits in server-side configuration, not editable form fields. Use maintained local templates and no runtime package downloads.

Set Noto Sans explicitly in every template. Verify both executable access and font discovery under the PHP service user, including regular and bold rendering. Missing Noto Sans is an actionable setup failure rather than a silent font substitution. Record the validated Typst version and font provisioning steps in deployment documentation; update the applicable image recipe during implementation only after reconciling the running image with the checked-in build setup.

Alternative: ext-typst. It remains possible later but is unnecessary for the initial integration and adds a PHP-native extension lifecycle. CLI execution provides an independently terminable compiler process.

### 3. Structured data and bounded compilation

Write one JSON payload plus approved assets and template files into a unique private working directory outside public document storage. Set Typst's project root to that directory. Strings from records and settings remain data: templates insert them as text, never through `eval` or generated source. Pass JSON by file rather than putting personal data into process arguments. Copy only the selected logo and any generated payment QR image into the workspace.

Use an initial 10-second per-compilation timeout and a 60-second total request budget, both defined in code/configuration and measured during validation. A reminder batch is one compilation of a multi-page document. Terminate and reap timed-out children, bound captured diagnostics, validate successful PDF output, and clean temporary files on success or failure. A nonblocking file lock allows one active Typst generation request per PHP container; additional attempts receive a retry message instead of occupying workers in a queue. The lock covers previews, single downloads, and reminder batches. This is intentionally conservative for evaluation and is not a distributed concurrency solution.

Return readable errors without raw filesystem paths, record contents, or command arguments. Log document type, status, duration, and batch count without full payloads. No silent dompdf fallback: existing export buttons remain separately usable.

### 4. Simple settings with maintained templates

Persist a versioned, validated settings object in a dedicated `document_settings` table (`name` VARCHAR(64) primary key, `payload` MEDIUMTEXT, `updated_at` DATETIME), with built-in defaults for missing rows. Migration 057 creates the table; existing configuration records are untouched. Keep a shared branding section for practice display name, contact/address lines, logo, and accent color. Store branding assets under persistent data storage and serve them only through controlled document-center actions. Accept size-limited PNG/JPEG logos with image validation and generated filenames; arbitrary paths and SVG uploads are outside this version.

Each type exposes wording fields and a bounded subset of layout options: A4 portrait, font size, margins, and spacing. Noto Sans is the initial fixed font family. Invoice options include footer/payment wording and QR visibility; identifiers, IBAN/payment data, dates, tax, and totals remain supplied by application data. Reminder options include subject, greeting, body, and closing, with an allowlist of documented placeholders for recipient, pet, vaccine/disease, and due date. Reject unknown placeholders. Overview options include title/introduction/footer and optional vet, location, and next-due columns; vaccine and injection date remain core columns.

Preview unsaved values with sample data by default, or an authorized existing record/eligible reminder row. Never persist on preview. Save only validated settings. Restore defaults has an explicit scope (shared branding or one document type) and preserves other settings. Protect new write actions against forged submissions without globally changing legacy CSRF behavior. Use localized labels and defaults in the project's Dutch/English conventions.

Alternative: a general template/version database or raw source editor. Neither is needed for the agreed simple fields. A single dedicated settings table accommodates long text without widening the legacy configuration column or introducing template-version management.

### 5. Read-only invoice and overview downloads

Build invoice data from existing bill/owner/details/payment records and read-only formatting helpers. Cover invoices and unnumbered bills, alternate billing addresses, line items grouped by pet, write-off records, stored VAT/totals, payment status, structured reference, and existing QR generation. Do not recalculate balances, allocate invoice numbers, link events, mark email sent, or change stock. If a bill lacks the persisted data needed for a coherent document, show a validation message directing the user to the existing bill workflow rather than silently performing writes. Treat this output as a current document download, not a replacement for the stored invoice artifact.

The overview uses the existing owner/pet fields and administered vaccine rows, preserving the existing exclusion of `event_id = 0`. Include optional columns only when meaningful, and show an empty-state message when no qualifying vaccines remain. The design accommodates long names, accents, multi-page tables, repeated headers, and page numbering.

New downloads use distinct filenames and transient storage. They never read from, create in, or overwrite the legacy invoice cache. Template/settings changes therefore affect future Typst downloads only.

### 6. One reminder page per row in a single PDF, maximum 10

Add a reminder-generation button beside the existing export action. It opens a selection screen for the chosen month with product exclusions, a count, and eligible rows. Use a new read-only query returning stable vaccine IDs and pet IDs with the existing eligibility/recipient rules, ordered by due date and vaccine ID. Preserve the legacy query and CSV format.

The user can select 1–10 rows; initially select at most the first 10 eligible rows and clearly show that remaining rows are not included. Allow selecting later rows for subsequent batches. Define `REMINDER_BATCH_LIMIT = 10` in code; do not expose the limit as an admin setting. Validate count, unique IDs, month access, filters, and current eligibility server-side before any compilation. Reject duplicates, over-limit requests, and stale/ineligible selections without silently truncating or substituting rows.

Pass the selected rows as an ordered array in one JSON payload and compile the whole batch into one PDF. The template renders one letter per row, with explicit page breaks between letters and no leading or trailing blank page. Preserve the selection screen's due-date/row-ID order regardless of the submitted ID order. Two rows for the same owner or pet produce two separate pages, even if their displayed details are similar. Each letter includes the recipient postal address, pet, vaccine/disease, due date, and configured wording. Reuse companion-over-owner resolution from reminder eligibility.

Require each complete letter to fit on exactly one A4 page. Validate per-letter layout boundaries and the final page count against the selected row count; if wording, branding, or record data causes overflow, report which letter needs shorter wording or adjusted layout and return no PDF. Do not clip text or shrink it below supported font-size limits. Apply this same fit check to single-letter previews. A one-row batch produces a one-page PDF; a ten-row batch produces a ten-page PDF.

Download the complete PDF with a safe batch filename only after all letters and page checks succeed. On failure remove the incomplete output and let the user adjust or retry. An empty candidate list produces an informative screen and no download. Generation does not mark reminders sent or mutate clinical records. Direct batch compilation avoids separate-file packaging and requires no changes to the existing PDF merger.

## Risks / Trade-offs

- Runtime installation differs from the reported setup → Verify the actual executable/fonts as the PHP user and document persistent installation; do not assume a host binary or root-only installation is sufficient.
- A small batch can still consume worker time → A single compilation for at most 10 letters, one active render request per container, compilation and request deadlines, and measured 1/5/10-row runs. The batch limit is not a claim of proven capacity.
- Legacy invoice preparation includes hidden writes and presentation calculations → Use dedicated read-only builders and verify database/file invariants with representative paid, unpaid, unnumbered, and write-off records.
- Concurrent eligibility changes can invalidate a selection → Revalidate IDs and filters at generation time and ask the user to refresh when records no longer qualify.
- Large wording or tables can overflow → Bound settings, use natural pagination for invoices/overviews, and reject reminder layouts that cannot fit a complete letter on one page. Visually inspect long-content fixtures using Noto Sans.
- A failed or overflowing letter prevents the batch download → Report the affected letter and allow adjustment/retry; deliver only a complete PDF with exactly one page per selected row.
- Single-container locking does not limit future replicas globally → Revisit coordination if the deployment becomes multi-container; queues and distributed locks are deferred.

## Migration Plan

1. Verify/document Typst, Noto Sans, and temporary-directory permissions in the actual PHP runtime. Make provisioning reproducible in the applicable container build without replacing unrelated user configuration.
2. Apply migration 057, then deploy the new services/templates and settings defaults. No legacy PDF files or configuration values are migrated.
3. Add the admin center and separate generation buttons, leaving all current actions/defaults intact.
4. Validate representative PDFs, legacy exports, read-only guarantees, error handling, and 1/5/10-row batch timings before evaluating any larger limit.
5. Roll back by removing/disabling the new entry points and services. Legacy exports need no conversion or recovery; retained document settings/assets are inert. Rolling migration 057 down drops only the new settings table and its presentation settings; do so only when that removal is intended.
