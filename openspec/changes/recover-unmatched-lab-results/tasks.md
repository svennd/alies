## 1. Pending lifecycle storage (completed baseline)

- [x] 1.1 Add the next CodeIgniter migration for pending resolution/deletion audit columns and an active-queue index, and verify its up/down SQL and existing-row null defaults with a focused migration test.
- [x] 1.2 Extend `LabReportPending_model` with active list/count, active-row locking, resolved, and soft-delete operations, and verify model tests exclude resolved/deleted rows while retaining records and audit fields.
- [x] 1.3 Persist `source` and `source_id` on new pending API records when available, and verify an ingestion test captures those fields without changing the public API response.

## 2. Shared payload recovery (completed baseline)

- [x] 2.1 Extract device adapter selection into a shared factory used by the API and recovery paths, and verify every currently supported device resolves to its existing adapter while unknown devices are rejected.
- [x] 2.2 Add a legacy-pending mapper that converts migration-047 report/detail wrappers into normalized report data, and verify representative numeric, textual, metadata, source, date, and comment fields with focused tests.
- [x] 2.3 Refactor normal report/result/plot persistence behind one transactional service path while preserving automatic API matching behavior, and verify successful ingestion plus rollback on a result or plot write failure.
- [x] 2.4 Add explicit-pet pending recovery with row locking, pet validation, lifecycle audit updates, and safe payload errors, and verify current and legacy payload success, malformed payload rollback, inactive-row replay, and a same-source/same-pet replay.
- [x] 2.5 Enforce source-identity ownership during recovery, and verify an existing report for another pet is not reassigned, overwritten, or duplicated and the pending row remains active.
- [x] 2.6 Add concurrency-safe pending soft deletion that records acting user and time without clearing payload data, and verify active deletion succeeds while resolved, already-deleted, and competing lifecycle submissions make no contradictory state change.

## 3. Lab controller workflow and authorization (completed baseline)

- [x] 3.1 Add the Lab unmatched-results page action and provide only decoded display identifiers—not raw payloads—to its view, and verify active, empty, legacy, resolved, and deleted queue states with controller/view tests.
- [x] 3.2 Add POST-only recovery and soft-delete actions with veterinarian/administrator authorization, server-side pending/pet validation, redirect flash messages, and application audit logging, and verify GET and accounting/unauthorized mutation attempts cannot change data.
- [x] 3.3 Verify successful controller recovery logs pending, report, pet, and acting-user identifiers, and successful soft deletion logs pending and acting-user identifiers without logging raw payload content.

## 4. Staff interface (completed baseline)

- [x] 4.1 Build the unmatched-results view with source, received time, reason, safe matching hints, the existing pet-and-owner remote search, recovery feedback, an empty state, and confirmed soft-delete controls; verify rendered controls and confirmation behavior with focused view tests.
- [x] 4.2 Change the shared Lab badge to count all active pending rows and link to the unmatched queue, add a discoverable queue action to the Lab index, and verify old active records remain counted while resolved/deleted records do not.
- [x] 4.3 Remove the obsolete nonfunctional assignment and reset controls from normal lab detail output, and verify detail-page tests no longer render dead recovery links while existing report navigation and printing remain intact.

## 5. Integrated verification (completed baseline)

- [x] 5.1 Add an end-to-end recovery workflow test covering queue display, pet/owner selection, report/result/plot creation, pending resolution audit, normal Lab visibility, and eligibility for the existing event-lab linking workflow.
- [x] 5.2 Add an end-to-end dismissal workflow test covering confirmation, retained payload/audit data, queue removal, count update, and repeated-request safety.
- [x] 5.3 Run the focused lab, event-lab, pet-history, migration, controller, model/service, and view test suites plus PHP syntax checks for every changed PHP file, and resolve all regressions.

## 6. Detailed pending-result inspection

- [x] 6.1 Add a reusable pending-preview service that parses current device payloads through the shared adapter factory and legacy payloads through the legacy mapper, returns normalized report/result/plot/source data, and preserves a partial preview when parsing fails; verify supported current, supported legacy, partial, malformed, and unsupported payload cases with focused service tests.
- [x] 6.2 Add an authorized Lab pending-detail action for active records that supplies the normalized preview and safely encoded retained JSON without exposing either to unauthorized or accounting-only users; verify active, inactive, missing, malformed, and unauthorized requests with controller tests.
- [x] 6.3 Change the unmatched queue entries to link to the pending-detail page while keeping raw payload content out of the queue, and verify queue tests cover the detail link and absence of literal payload data.
- [x] 6.4 Build the pending-detail view with normal-report-style metadata, measurements, values, limits, units, plots, source details, matching hints, parse warnings, and collapsed escaped JSON; verify complete, partial, malformed, and HTML-bearing payload rendering with focused view tests.

## 7. Owner-first pending recovery

- [x] 7.1 Add or adapt authorized remote owner lookup and owner-filtered pet lookup behavior, and verify searches return eligible records while missing, invalid, or mismatched owner filters cannot expose an unrestricted pet selection.
- [x] 7.2 Replace the combined pet-and-owner control with separate owner and pet controls on the pending-detail page, enable pet search only after owner selection, and clear the selected pet whenever the owner changes; verify the rendered control state and client-side reset/filter behavior.
- [x] 7.3 Require both owner and pet IDs in pending recovery and validate that the pet currently belongs to the selected owner inside the recovery transaction; verify missing owner, missing pet, invalid records, relationship mismatch, stale selection, and valid current/legacy recovery outcomes.
- [x] 7.4 Extend recovery audit verification to cover the selected owner and pet while retaining the original pending-resolution audit record, and verify rejected submissions make no audit or persistence change.

## 8. Recovered report reassignment

- [x] 8.1 Add a transactional normal-report reassignment service that validates the replacement owner/pet relationship, treats the current association as a no-op, updates a changed association, and removes only event links incompatible with the former pet; verify valid change, no-op, invalid pair, unrelated event-link preservation, and rollback on update or cleanup failure.
- [x] 8.2 Add a POST-only authorized Lab reassignment action that invokes the transaction, reports actionable validation/no-op/failure outcomes, and audits the acting user plus previous and replacement owner/pet identifiers; verify GET, accounting-only, unauthorized, missing-report, invalid, successful, and failed requests.
- [x] 8.3 Add the owner-first, pet-second reassignment controls to the normal lab detail page with the current association preselected and an explicit warning that changing the pet removes incompatible event links; verify confirmation, owner-change pet reset, printing/navigation preservation, and absence of obsolete controls.
- [x] 8.4 Add focused event-lab integration tests proving reassignment removes former-pet links in the same transaction, does not move them to another event, and leaves links unchanged when validation or persistence fails.

## 9. Revised workflow verification

- [x] 9.1 Add an end-to-end unmatched-result workflow test covering queue-to-detail navigation, parsed and raw inspection, owner-first pet selection, recovery, pending audit state, and normal report visibility.
- [x] 9.2 Add an end-to-end correction workflow test covering normal detail display, replacement owner/pet selection, confirmation, report reassignment, incompatible event-link cleanup, old/new association auditing, and repeated no-op submission.
- [x] 9.3 Run the focused lab, pending-preview, owner/pet lookup, recovery, reassignment, event-lab, pet-history, controller, model/service, and view test suites plus PHP syntax checks for every changed PHP file, and resolve regressions introduced by the revised workflow.
