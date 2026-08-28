## Context

See `proposal.md` for motivation and `specs/unmatched-lab-result-recovery/spec.md` for required behavior. Unmatched imports are stored in `lab_report_pending`; current API records contain the original device payload, JSON matching hints, and a reason, while migration 047 also created `legacy_missing_pet` records whose payload wraps rows from the former `lab` and `lab_detail` tables. Neither kind currently has a lifecycle beyond creation.

Normal reports are split across `lab_report`, `lab_results`, and optional `lab_plots`. `LabResultService` parses live payloads through device adapters and persists these records, but automatic pet resolution is embedded in the ingestion path and each write is not currently grouped into one transaction. The API controller owns a private adapter factory, preventing the web controller from safely parsing a pending payload for preview or replay. The Lab detail view retained assignment/reset controls from the legacy implementation although the rewritten controller no longer handles them.

The existing pet Select2 endpoint returns pets independently of an explicit owner-selection step. Normal reports can also be linked to clinical events through `events_labs`, so moving a report to another pet can leave incompatible event links behind unless reassignment handles them transactionally. `Vet_Controller` admits veterinarian, administrator, and accounting groups, so payload inspection and mutating routes need a narrower authorization check than controller inheritance alone.

## Goals / Non-Goals

**Goals:**

- Establish an explicit pending-record lifecycle with mutually exclusive active, resolved, and soft-deleted states.
- Reuse one normalized persistence path for live ingestion and manual recovery.
- Reuse payload parsing to produce a safe structured preview while retaining access to the literal JSON for authorized investigation.
- Make recovery atomic and resistant to repeated submissions or simultaneous recover/delete actions.
- Preserve enough provenance to audit how a pending payload left the active queue.
- Keep pet ownership consistent through an owner-first, pet-second workflow with server-side relationship validation.
- Make correction of a recovered report atomic, auditable, and consistent with its clinical event links.

**Non-Goals:**

- Automatically create or select a clinical event when a report is recovered; the existing event linking workflow remains separate.
- Add an interface to restore soft-deleted pending records.
- Edit or replace retained raw lab payloads in the browser.
- Change device API request formats or automatic matching rules.
- Enable accounting-only users to mutate laboratory patient associations.

## Decisions

### Model pending records as retained lifecycle records

Add nullable `resolved_at`, `resolved_by`, `resolved_pet_id`, `report_id`, `deleted_at`, and `deleted_by` fields to `lab_report_pending`, plus an index supporting the active predicate. A row is active only when both lifecycle timestamps are null. Successful recovery sets the resolution fields; dismissal sets the deletion fields. Both operations retain the raw payload, identifiers, source details, and reason.

This is preferable to deleting rows after recovery or dismissal because it preserves provenance, supports incident review, and makes repeated submissions detectable. A separate archive table was rejected because it duplicates the payload schema and makes lifecycle updates unnecessarily complex.

New pending writes should also persist the already available `source` and `source_id` values. Existing rows remain recoverable because these values can be obtained by parsing the retained payload or, for legacy rows, from their existing columns and wrapper data.

### Give Lab separate queue and pending-detail pages

Add a Lab queue route, conceptually `lab/pending`, and a pending-detail route addressed by pending ID. The queue uses model methods that return/count only active rows and decodes only the small `identifiers` object for display. Each queue entry links to the detail page, which owns investigation, assignment, recovery, and explicitly confirmed soft deletion. This keeps the queue scannable and prevents the complete payload from being copied into every row.

Veterinarians and administrators may inspect payload details and execute lifecycle actions; accounting-only users are denied. Recovery and soft-delete actions remain separate POST-only routes and validate method, permission, active pending record, selected owner, and selected pet on the server regardless of browser controls. Successful and failed outcomes redirect back with a flash message, avoiding mutation on refresh. The project has global CSRF protection disabled, so this change will follow existing form infrastructure rather than claiming isolated CSRF coverage; enabling CSRF application-wide is a separate security change.

Putting pending assignment directly into the ordinary lab detail view was rejected because true unmatched payloads do not yet have a `lab_report` ID. The pending-detail page and normal-report detail page can nevertheless share the same owner-first and pet-second controls: the former creates a report from retained input, while the latter corrects the association of an existing report. The obsolete legacy controls are replaced instead of retained alongside the supported workflow.

### Build the pending preview from normalized parser output

Parse current device payloads with the shared adapter factory and legacy wrappers with the dedicated legacy mapper. Convert their normalized output into a preview data structure containing the report metadata, measurements, reference limits, units, plots, source information, and matching hints that are available. Rendering consumes this preview rather than reaching into device-specific payload shapes, which keeps new devices aligned between ingestion, recovery, and inspection.

The detail view also receives a safely encoded, pretty-printed copy of the retained JSON in a collapsed technical section. This literal representation is intentionally restricted to the authorized detail page and is never interpolated as HTML. If parsing fails or produces only partial data, the page still renders the available metadata and literal JSON with a clear warning; parser details remain server-side. Blocking the entire detail page on successful parsing was rejected because malformed payloads are precisely the cases where staff most need the retained evidence. Showing raw JSON in the queue was rejected because it increases disclosure and makes routine review unwieldy.

### Select owner first and constrain pet selection

Use separate searchable controls for owner and pet. The owner control is available first; choosing it enables a pet lookup constrained by owner ID. Changing or clearing the owner clears the selected pet so a stale pair cannot be submitted. The server reloads both records and verifies their current relationship for recovery and reassignment, because client filtering is only a usability aid.

Deriving the owner silently from one combined pet choice was rejected because it makes the two associations hard to review and correct. Loading every pet for an owner into the initial page was rejected because owner records can have enough pets to warrant the existing remote-search pattern.

### Share adapter selection and normalized persistence

Extract device-adapter selection from the API controller into a reusable factory. Refactor `LabResultService` so both paths converge after parsing:

```text
API import                         Manual recovery
    │                                    │
adapter parse                       lock pending row
    │                              adapter/legacy parse
automatic pet match                       │
    └──────────── normalized data ────────┘
                         │                explicit pet
                         ▼
              transactional report persistence
```

Live ingestion continues to resolve the pet automatically. Recovery supplies the validated pet explicitly and never rewrites identifying fields in the raw payload to trick automatic matching. This separates patient choice from payload interpretation and makes the audit trail unambiguous.

Duplicating each adapter switch in `Lab` was rejected because adding another device would allow API ingestion and manual recovery support to drift. Calling the API controller internally was rejected because controller-to-controller invocation would blur authentication, HTTP response, and transaction boundaries.

### Convert legacy pending payloads through a dedicated mapper

Select the decoder from the pending reason/payload shape. Current records use the device adapter. `legacy_missing_pet` records use a dedicated mapper that converts the wrapped legacy report and detail rows into the same normalized report/results representation consumed by the shared persistence path, preserving legacy IDs and comments in metadata.

Trying to pass legacy wrapper payloads through modern adapters was rejected because they do not conform to any current device contract. Excluding legacy rows was rejected because they are part of the same unmatched queue and may contain clinically relevant results.

Malformed or unsupported payloads remain active and show a safe error; staff can then use the retained JSON on the authorized detail page to investigate or soft-delete them. Parser exception details are logged server-side rather than displayed.

The pending-detail preview is more permissive than recovery: it may show partially parsed information and raw JSON, while recovery still requires a complete normalized structure that can be validated and persisted. This avoids treating diagnostic visibility as proof that a payload is safe to import.

### Make recovery and soft deletion concurrency-safe

Start a database transaction before loading the target pending row for mutation and lock it for update. Continue only if it is active. Recovery validates the owner, pet, and their relationship, parses the payload, checks source identity, persists report/results/plots, sets resolution fields, and commits. Any failure rolls back the entire operation. Soft deletion similarly locks the row and conditionally sets deletion audit fields before committing.

For an existing normal report with the same source identity and selected pet, the shared upsert behavior may refresh its result data and resolve the pending row to that report. If that identity belongs to a different pet, recovery fails rather than silently moving or overwriting the existing report. The active-state lock prevents recovery and deletion from both succeeding, and resolved/deleted checks make repeated POSTs harmless.

Transaction orchestration belongs in the service rather than the controller so all persistence steps remain one unit. Relying only on disabled buttons or a preflight read was rejected because simultaneous requests could still create partial or contradictory state.

### Reassign recovered reports as one audited transaction

Add a reassignment operation for an existing normal report and expose it from the normal Lab detail page using the same owner-first, pet-second controls. The service locks or otherwise protects the report for the duration of the change, validates the replacement owner and pet, and treats an unchanged pair as a no-op. When the pet changes, it identifies `events_labs` links whose events belong to the previous pet and removes those links before committing the new report association. The confirmation text warns staff that incompatible event links will be removed.

The reassignment and event-link cleanup commit together, and the application audit log records the acting user plus previous and replacement owner/pet identifiers. Reusing recovery was rejected because reassignment must not replay the source payload or recreate results and plots. Keeping old-pet event links was rejected because it would expose one patient's report through another patient's clinical history. Automatically moving those links to the new pet was rejected because the system cannot infer the correct clinical event.

### Count all active records in Lab navigation

Replace the current 14-day pending count with the active lifecycle count and link the count-bearing Lab interface to the pending queue. This prevents older unresolved records from silently disappearing from staff awareness. The normal Lab index should also expose a queue action so discovery does not depend on the sidebar layout.

## Risks / Trade-offs

- [Retaining and displaying payloads exposes sensitive clinical and identifying data] → Restrict the detail route to authorized staff, keep raw JSON collapsed and escaped, omit it from queues and routine errors, and preserve the current retention policy; broader retention/pruning remains out of scope.
- [Legacy conversion may encounter shapes not represented by migration fixtures] → Cover representative legacy payloads with mapper tests and leave unsupported rows active with a safe failure path and soft-delete option.
- [A source collision could indicate a duplicate pending record rather than a true conflict] → Reuse an existing report only when its pet matches the explicit selection; otherwise require review and refuse recovery.
- [Locking and replaying a payload may take longer than a simple update] → Lock only one pending row, keep parsing and writes within a short request, and index the active queue predicate.
- [A stale browser can submit an owner/pet pair that is no longer valid] → Revalidate both records and their relationship inside every recovery or reassignment operation.
- [Reassignment can remove clinically meaningful event links] → Require explicit confirmation, remove only links incompatible with the former pet, preserve the report data, and record old/new associations in the audit log.
- [Global CSRF protection is disabled] → Use POST-only authorized routes and confirmation consistent with the application today; track framework-wide CSRF enablement separately.

## Migration Plan

1. Add the nullable lifecycle/audit columns and active-query index to `lab_report_pending`. Existing rows naturally remain active because all new fields default to null.
2. Deploy model, adapter-factory, preview, service, controller, lookup, and view changes together. Switch the navigation count to the new active predicate only after the columns exist.
3. Verify current and legacy previews and recovery, partial and malformed parsing, owner/pet relationship validation, reassignment and event-link cleanup, source conflicts, rollback behavior, authorization, repeated submissions, and soft deletion with focused tests.
4. Rollback application code before reversing the migration. The down migration may drop only the new columns and index; doing so loses lifecycle audit metadata and would make retained resolved/deleted rows appear active again, so database rollback requires an explicit operational decision or backup.
