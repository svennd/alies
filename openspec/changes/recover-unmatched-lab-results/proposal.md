## Why

Lab payloads that cannot be matched automatically are retained in `lab_report_pending`, but staff need enough clinical and source detail to identify the intended patient before resolving them. Recovery must also support a deliberate owner-then-pet workflow and allow an incorrect patient assignment to be corrected safely afterward.

## What Changes

- Add an unmatched-results page under the Lab controller that lists all unresolved, non-deleted pending lab payloads and links each entry to a detailed preview.
- Parse pending payloads for the detail page and present the report similarly to a normal full lab view, including measurements, values, limits, units, plots, source metadata, and patient-identifying hints when available.
- Provide the literal retained JSON in a collapsed technical section for authorized staff, while keeping it out of the queue listing and routine user-facing errors.
- Let authorized staff select the intended owner first and then select one of that owner's pets before recovering a pending result.
- Recover the pending payload into the normal lab report, result, and plot records while preserving source identity and preventing partial recovery.
- Support recovery of both current API pending payloads and legacy pending payloads created by the lab backfill.
- Let authorized staff correct an owner/pet mistake after recovery from the normal lab detail page using the same owner-first, pet-second workflow.
- When reassignment changes the pet, remove any event links belonging to the former pet in the same transaction, require confirmation, and audit the previous and replacement associations.
- Let authorized staff soft-delete an unmatched result that cannot or should not be resolved, with an explicit confirmation step.
- Retain resolution and deletion audit information and exclude resolved or deleted entries from the active queue and its navigation count.
- Replace obsolete lab-detail controls with supported reassignment actions and keep unmatched recovery centered on the pending-result detail page.

## Capabilities

### New Capabilities

- `unmatched-lab-result-recovery`: Inspect, assign, recover, reassign, and soft-delete lab results with transactional persistence and auditability.

### Modified Capabilities

None.

## Impact

- Lab controller routes, pending-result model queries, lab ingestion/recovery and reassignment service behavior, owner/pet lookup endpoints, and Lab views/navigation.
- `lab_report_pending` schema gains lifecycle and audit fields for resolved and soft-deleted records.
- Existing device adapters are reused for preview and recovery, with a dedicated path for legacy backfill payloads.
- Existing `events_labs` relationships are affected when a recovered report is reassigned to another pet.
- Focused controller, model/service, migration, view, authorization, and event-link cleanup tests are required; no public API request format changes are expected.
