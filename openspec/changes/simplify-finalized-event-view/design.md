## Context

See `proposal.md` for motivation and `specs/finalized-event-report/spec.md` for observable behavior. `Events::event()` currently renders `event/main_open` for open consultations and `event/main_report` for other states. The latter builds a three-card header, a tabbed report/media/files card, a canvas-based drawing tool, and an invoice-item sidebar.

Lab reports live in `lab_report` with their individual values in `lab_results`. Migration 050 provides the many-to-many `events_labs(event_id, lab_id)` table, but no current model, controller flow, or event view uses it. The full lab page currently computes presentation fields inside the controller, so directly copying that view would duplicate both calculation and markup concerns.

Event attachments are recorded in `events_upload` and stored outside the asset pipeline. The existing file endpoint forces downloads, so image previews need a separate authenticated inline response rather than direct public storage URLs.

## Goals / Non-Goals

**Goals:**

- Keep finalized-event assembly explicit and avoid adding lab-query overhead to the open consultation path.
- Make lab eligibility and result visibility safe even when requests are crafted or a lab is reassigned later.
- Reuse one interpretation of numeric/text lab results and reference-range status across the full and embedded views.
- Preserve existing uploaded content while removing the obsolete drawing workflow.
- Keep the layout usable when an event has multiple labs or many result rows.

**Non-Goals:**

- Redesigning the open consultation and billing workflows.
- Changing lab ingestion, pet assignment, full lab-report navigation, or print output.
- Migrating or deleting existing drawing attachments and temporary drawing files.
- Changing the `events_labs` schema or introducing a new external dependency.
- Generating or persisting derivative thumbnail files.

## Decisions

### Use a dedicated event-lab relationship model

Add a small model around `events_labs` that can fetch valid linked reports, fetch eligible reports for an event pet, create a relationship, and remove a relationship. Link creation resolves both event and lab records on the server and verifies matching pet identifiers, non-deleted lab state, and absence of the composite relationship before inserting.

This keeps relationship SQL and the security invariant out of the view and avoids treating a many-to-many relationship as a field on `events`. Adding an ORM pivot declaration alone was considered, but explicit queries are clearer for the eligibility and soft-delete constraints.

### Load embedded labs only for the finalized-event branch

After resolving the event and pet, the finalized branch loads valid linked labs, their result rows, and eligible unlinked labs. The open branch keeps its current data and markup. Linked-lab queries join `lab_report` with the event pet constraint so a deleted or reassigned report is not disclosed even if a stale junction row remains.

Eager-loading all event states was rejected because it expands the critical open-consultation path for a feature that is intentionally out of scope there.

### Share lab-result presentation normalization

Extract the existing transformation of raw `lab_results` rows—numeric versus text value, reference range, low/high/out-of-range status, and display values—into a reusable application-level presenter/helper. Both `Lab::detail()` and the finalized-event panel consume the normalized representation, while each keeps view-specific markup.

Copying the calculations into the event controller was rejected because the two screens could disagree about abnormal values as the lab format evolves.

### Present multiple labs as compact disclosure sections

The former invoice sidebar becomes a lab panel. Linked labs are ordered by `sample_date` and then identifier descending. The newest is expanded initially; older labs are collapsed but show date and source/device. The entire lab header and an explicit accessible action lead to `lab/detail/{id}`. Link and unlink controls remain within the panel, with unlink requiring confirmation.

Showing every result table fully expanded was rejected because a few panels could make the report editor difficult to navigate. Showing only links was rejected because the agreed behavior requires actual results on the event page.

### Use POST mutations and revalidate on every request

Link and unlink actions use authenticated POST handlers and redirect back to the event. The link handler never trusts a pet identifier supplied by the browser; it derives the pet from the persisted event. Unlink verifies that the submitted composite relationship exists. Successful changes are recorded in the existing activity log with event and lab identifiers.

AJAX-only mutations were considered but add unnecessary failure-state UI for short, infrequent actions. Conventional POST/redirect behavior is consistent with the application and remains functional without client scripting.

### Stream previewable images through an authenticated endpoint

Add an inline-preview endpoint that looks up the attachment, validates file existence, permits only a narrow browser-safe raster MIME allowlist, sends the stored MIME type with `Content-Disposition: inline` and `X-Content-Type-Options: nosniff`, and otherwise refuses preview. The view renders the endpoint inside a fixed-size, `object-fit: cover` preview; clicking it opens the inline image. Files outside the allowlist continue through the existing download route.

Streaming the original image avoids new schema and cache lifecycle work. Direct links to `data/upload` were rejected because they bypass the authenticated controller. SVG and formats with inconsistent browser support are deliberately treated as ordinary files.

### Remove drawing creation without removing historical media

Stop including `block_drawing.php` and remove drawing-only styles and scripts from the finalized report composition. Remove or retire the drawing/reset endpoints once repository usage confirms there are no remaining callers. Existing finalized drawing JPEG records remain in `events_upload`, so they naturally appear as previews.

Deleting old drawing files was rejected because it would erase clinical history. Temporary unsaved drawing files are outside this change's migration scope.

### Derive summary values from event and bill records

Use the event creation timestamp for the summary date, not the bill creation timestamp. Use the finalized bill's gross total as cost and render a localized unavailable marker if it cannot be resolved. Keep client and pet links and format date and currency with the application's existing helpers.

Using invoice creation time as the event date was rejected because those timestamps can differ and the requested field is specifically the event date.

## Risks / Trade-offs

- [Large original images consume bandwidth despite small visual dimensions] → Lazy-load previews, keep the safe raster allowlist narrow, and defer generated thumbnail caching until usage shows it is necessary.
- [Many linked labs can make the sidebar long] → Expand only the newest lab by default and keep older reports collapsible.
- [Stale `events_labs` rows can remain after lab reassignment or soft deletion] → Apply the pet and non-deleted constraints when reading as well as linking; allow cleanup separately without exposing mismatched data.
- [Removing drawing endpoints can affect an undiscovered caller] → Search server and client references during implementation, remove UI first, and retain endpoints temporarily if compatibility cannot be established.
- [The current application has global CSRF protection disabled] → Use POST, authenticated veterinary controllers, strict persisted-record validation, and no state changes through GET; enabling global CSRF remains outside this change.

## Migration Plan

1. Add the relationship queries, result normalization, and link/unlink handlers while retaining the existing finalized view.
2. Add authenticated inline image delivery and tests for safe and rejected MIME types.
3. Replace the finalized view composition and introduce the compact header, attachment previews, and embedded lab panel.
4. Remove drawing UI assets and retire server endpoints only after checking for remaining callers.
5. Deploy without a database migration; the existing `events_labs` table is the source of truth.

Rollback restores the former finalized-event templates and drawing client assets. New junction rows are harmless to the old interface and should be retained so linked data is not lost.
