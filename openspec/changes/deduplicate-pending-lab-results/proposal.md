## Why

Repeated imports of the same unrecognized laboratory report currently create a new active `lab_report_pending` row every time the upstream source retries or is polled. MediLab report `2652672`, for example, appears hourly even though its stable source identity has not changed, producing queue clutter and repeated manual work.

## What Changes

- Make unmatched lab ingestion idempotent when the parsed payload provides a stable source identity.
- Refresh the retained payload, matching identifiers, reason, and last-received timestamp of an existing active pending report instead of adding another queue entry.
- Refresh an already recovered normal report by its existing patient assignment when the same source identity arrives again, even if the repeated payload still cannot be matched automatically.
- Resolve any matching active pending entry when a later delivery of the same report can be matched automatically and persisted normally.
- Keep a dismissed pending identity dismissed while repeated imports remain unmatched, rather than recreating it in the active queue.
- Continue creating independent pending rows when no stable source identity is available, because those payloads cannot be deduplicated safely.
- Consolidate pre-existing active duplicates so one current pending row remains per stable source identity.
- Protect the invariant against concurrent deliveries and cover active, recovered, dismissed, missing-identity, and historical-duplicate cases with automated tests.

## Capabilities

### New Capabilities

- `pending-lab-ingestion-idempotency`: Defines stable source identity handling for repeated unmatched imports, recovered reports, dismissed reports, and existing duplicate pending data.

### Modified Capabilities

None.

## Impact

- Lab ingestion orchestration in `LabResultService`.
- Pending-report lookup/update behavior in `LabReportPending_model` and existing normal-report source lookup in `LabReport_model`.
- `lab_report_pending` schema, indexing, migration cleanup, queue timestamps, and potentially queue ordering.
- API responses remain compatible: unmatched imports continue returning `status: pending`, and matched or recovered report refreshes continue returning `status: ok`.
- No device payload contract changes and no content-based deduplication for reports lacking a source identity.
