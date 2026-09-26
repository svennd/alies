## 1. Runtime and compatibility baseline

- [x] 1.1 Resolve Typst's installed path in the actual PHP container and verify execution plus Noto Sans regular/bold discovery as the PHP service user; record version, font results, and any missing prerequisite.
- [x] 1.2 Make Typst/fonts provisioning reproducible in the applicable deployment recipe after reconciling the running Debian image and checked-in Alpine Containerfile; document binary/font configuration and verify a fresh runtime can compile a Noto Sans sample without network access.
- [x] 1.3 Record representative legacy invoice, vaccination overview, CSV, and other-PDF behavior before editing; verify baseline routes, invoice-cache reuse, and archived-file hashes in a development fixture setup.

## 2. Document settings and branding

- [x] 2.0 Add migration 057 for the dedicated document_settings table with a MEDIUMTEXT payload and verify discovery through the existing explicit upgrade command; verify migration application, long-text round trips, and that the legacy config schema and values are unchanged.

- [x] 2.1 Add versioned document settings with shared branding, three document-specific defaults, bounded layout values, Noto Sans, and supported reminder placeholders; verify default loading, save/reload, rejection of invalid input, and persistence in the dedicated table and isolation from existing configuration keys.
- [x] 2.2 Add validated PNG/JPEG logo persistence and controlled access with generated filenames and size limits; verify valid uploads, invalid/oversized rejection, access controls, and persistence across application restarts.
- [x] 2.3 Add scoped restore-default operations; verify restoring one document type or shared branding leaves all other settings unchanged.

## 3. Independent Typst renderer

- [x] 3.1 Add the CLI renderer with argument-array invocation, JSON input files, maintained templates, private workspaces, explicit root/font configuration, and no remote package dependencies; verify a real sample PDF and literal rendering of Typst metacharacters.
- [x] 3.2 Add initial per-compilation/request deadlines, one active request per container, bounded diagnostics, child termination, output validation, and cleanup; verify missing binary/font, nonzero exit, timeout, malformed output, and concurrent-request behavior with focused process tests.
- [x] 3.3 Add transient PDF delivery with safe filenames, complete-response handling, and cleanup after download; verify correct PDF response types, no publicly accessible temporary data, and no writes to legacy invoice storage.

## 4. Read-only data and templates

- [x] 4.1 Add invoice data preparation using read-only model/helper calls for numbered/unpaid/unnumbered/write-off cases, alternate addresses, stored totals/tax, payment details, and QR assets; verify expected data and unchanged database state, including rejection of insufficient persisted data.
- [x] 4.2 Create the invoice Typst template with shared branding, configurable wording/layout, line items, payment information, optional QR, repeated table headers, and pagination; visually verify short and multi-page fixtures and compare financial values against source records.
- [x] 4.3 Add per-pet overview data preparation and template with optional columns and the existing external-vaccination exclusion; verify mixed-origin, empty, long-name, and multi-page cases with source-data checks and PDF inspection.
- [x] 4.4 Create the reminder Typst template accepting an ordered row list with allowed placeholders, recipient address, pet, vaccine/disease, due date, and explicit page breaks between letters; verify exactly one complete letter per page, literal field insertion, accented text, no blank separator/trailing pages, and rejection of overflowing wording/layout in both previews and batches.

## 5. Document center and separate generation actions

- [x] 5.1 Add the admin controller, navigation entry, and localized settings forms for all three types with save/restore actions and request-forgery protection; verify admin access, non-admin direct-request rejection, field feedback, and Dutch/English labels.
- [x] 5.2 Add unsaved preview using sample data and authorized existing records; verify that preview reflects submitted values but changes neither stored settings, business records, nor archived PDFs.
- [x] 5.3 Add independently authorized invoice and overview download endpoints and clearly labelled Typst buttons beside existing actions; verify the new buttons use the new endpoints and all legacy links/classes/templates remain unchanged.

## 6. Vaccination reminder batches

- [x] 6.1 Add a dedicated read-only reminder selection query with stable vaccine/pet IDs, deterministic ordering, existing eligibility filters, and companion-over-owner recipient resolution; verify inclusion/exclusion cases and unchanged legacy CSV columns and filtering.
- [x] 6.2 Add the reminder button and selection screen with month/product filters, initially at most 10 selected rows, visible counts, and selection of later rows; verify empty, 1-row, 10-row, and more-than-10-candidate screens.
- [x] 6.3 Enforce the code-defined `REMINDER_BATCH_LIMIT = 10`, unique row IDs, month permissions, current eligibility, and filters before compilation; verify empty, duplicate, 11-row, stale, missing, filtered-out, and unauthorized requests are rejected without rendering.
- [x] 6.4 Compile the selected rows together into one PDF in selection-screen order and validate per-letter page boundaries plus final page count before delivery; verify 1/5/10 rows produce 1/5/10 pages, two rows for the same pet produce two separate letter pages, submitted ID order does not change output order, and overflow/compiler failure returns no partial PDF or clinical/sent-state changes.

## 7. Integration and evaluation

- [x] 7.1 Run authorization and mutation-invariant integration checks across settings, previews, individual downloads, and reminders; verify record state and legacy invoice-file hashes before/after, including failure cases.
- [x] 7.2 Repeat the legacy baseline on the completed change, covering automatic invoice generation/cache reuse, existing PDF downloads/merging, and vaccination CSV; verify no changed legacy PDF classes, template behavior, routes, or mail side effects.
- [x] 7.3 Render and visually inspect all three document types in the real container using Noto Sans, including long content and payment QR; verify expected text, readable pagination, no clipping, and successful QR decoding where displayed.
- [x] 7.4 Measure 1-, 5-, and 10-row reminder batches and a concurrent attempt, recording elapsed time and resource observations; verify deadlines fit PHP/FPM/web-server limits, cleanup succeeds, and the maximum remains 10 pending later evaluation.
- [x] 7.5 Document runtime setup, supported fields/placeholders, batch selection/download behavior, measured results, and rollback; verify a reviewer can locate these instructions and distinguish all new actions from legacy exports.
