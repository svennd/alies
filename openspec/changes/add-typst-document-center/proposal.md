## Why

The current HTML-to-PDF pipeline makes document layout difficult to maintain and provides no central place for administrators to adjust document presentation. Introduce Typst alongside the existing system to evaluate direct PDF generation for invoices and vaccination documents without disrupting established exports.

## What Changes

- Add an admin document center for shared branding and simple wording/layout settings for invoices, vaccination reminder letters, and per-pet vaccination overviews, with preview, save, and restore-default actions.
- Render maintained Typst templates from structured application data using the Typst CLI inside the PHP container, with Noto Sans as the default font.
- Add separate, clearly labelled Typst generation buttons beside relevant existing invoice and vaccination actions. Preserve the existing PDF classes, HTML templates, routes, automatic generation, archived invoices, email behavior, and CSV export.
- Generate one reminder letter per eligible vaccination row, without grouping by owner or pet. Enforce an initial code-defined batch maximum of 10 and download a single PDF containing exactly one letter per page, in selection-screen order.
- Keep previews and new downloads independent of billing mutations and existing invoice storage; validate settings and bound compiler execution.

## Capabilities

### New Capabilities

- `document-center`: Admin configuration, shared branding, document-specific settings, previews, and defaults for the three document types.
- `typst-document-generation`: Isolated CLI rendering with Noto Sans, read-only invoice and vaccination-overview data, and additive generation actions that preserve legacy behavior.
- `vaccination-reminder-documents`: One reminder letter per eligible row, stable row selection, bounded batches, and a single PDF download with exactly one letter per page.

### Modified Capabilities

None. Existing pet-fiche vaccine-status requirements and legacy PDF/export behavior remain unchanged.

## Impact

- New CodeIgniter admin/generation controllers, document settings/data services, Typst renderer, and three maintained templates; additive buttons in invoice and vaccination views and an admin navigation entry.
- Existing models supply invoice, owner, pet, and vaccine data through read-only queries. The reminder path needs stable vaccine row identifiers without changing the CSV format.
- Persist settings in a dedicated `document_settings` table with a `MEDIUMTEXT` payload and validated branding assets in persistent application data storage.
- Runtime prerequisites: an executable Typst CLI and Noto Sans fonts accessible to the PHP service user and bounded temporary storage. Document reproducible provisioning without replacing the user's container setup.
- Add focused compatibility, authorization, data-integrity, batch-limit, and real PDF rendering checks. No renderer switch, automatic emailing, queue infrastructure, or raw Typst editor is included.
