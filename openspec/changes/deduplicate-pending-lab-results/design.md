## Context

See `proposal.md` for motivation and `specs/pending-lab-ingestion-idempotency/spec.md` for required behavior. `LabResultService::ingest()` currently parses a delivery, attempts patient resolution, and unconditionally inserts `lab_report_pending` when resolution fails. The pending table has no source-identity constraint, while normal reports already use device/source plus `source_id` uniqueness and `persist_report()` refreshes an existing normal report.

Pending rows now have resolved and deleted lifecycle states. Any deduplication scheme must therefore distinguish a repeated active delivery from a previously resolved or deliberately dismissed identity, retain audit rows, allow multiple identity-less payloads, and prevent concurrent requests from recreating duplicates.

## Goals / Non-Goals

**Goals:**

- Establish one canonical pending lifecycle record for each stable source identity.
- Make active pending refresh, normal-report refresh, automatic resolution, and dismissal suppression transactional and repeat-safe.
- Preserve the earliest receipt, expose the latest receipt, and retain superseded rows for audit.
- Reuse the current normal-report persistence and source lookup semantics.

**Non-Goals:**

- Deduplicate payloads by patient names, result values, dates, or raw-payload hashes.
- Change device adapters or upstream API payloads.
- Reopen dismissed reports automatically while they remain unmatched.
- Merge normal reports that already violate source uniqueness or repair unrelated pending records without a source identifier.

## Decisions

### Derive one canonical identity hash

Introduce a shared identity derivation routine used by normal-report lookup and pending persistence. After trimming values, it produces a SHA-256 hash from a versioned canonical string containing `device + source_id`; only when device is absent does it use `source + source_id`. It returns null when `source_id`, and therefore a safe stable identity, is absent.

The version/tag and length-prefixed components prevent ambiguous concatenation and allow a future derivation change to be explicit. The hash keeps the unique index small even though existing device columns permit long values. The original device, source, and source ID remain stored and displayed; the hash is an internal key, not provenance.

Using a raw-payload hash was rejected because a laboratory can add results to the same report over time. Patient names and sample dates were rejected because they are neither reliably unique nor stable.

### Keep one canonical pending row per identity across its lifecycle

Add nullable `identity_hash`, `last_received_at`, and `superseded_by_id` fields to `lab_report_pending`, with a unique index on `identity_hash`. SQL unique indexes allow multiple null values, so reports without a stable identity remain independent. A row keeps its identity hash after recovery or dismissal; consequently, a later unmatched delivery collides with the same lifecycle row and cannot create a new active item.

An atomic pending upsert updates payload, identifiers, reason, and `last_received_at` only when the canonical row is active. For a dismissed or resolved canonical row, the unmatched delivery is acknowledged as pending but leaves the lifecycle and retained audit payload unchanged. `created_at` remains the first-received time. Queue and detail presentation use `last_received_at`, falling back to `created_at` for legacy rows.

Using a uniqueness constraint only across active rows was rejected because it would permit a dismissed identity to be inserted as a new active row. Deleting old rows was rejected because it would discard the lifecycle audit introduced by the unmatched-result recovery feature.

### Resolve source identity before patient fallback

After parsing, ingestion computes the stable identity and first looks for a normal report using the existing device/source rules. If one exists, its current `pet_id` is authoritative for this delivery: ingestion calls the shared transactional persistence path to refresh the same report even when current payload identifiers no longer resolve a patient.

If no normal report exists, ingestion attempts automatic patient resolution as today:

```text
parsed delivery
      │
stable identity ── existing report ──▶ refresh with existing pet ──▶ ok
      │ no
      ▼
automatic patient match
      ├─ matched ─▶ persist report + resolve matching active pending ─▶ ok
      └─ unmatched ─▶ upsert/suppress canonical pending ─────────────▶ pending
```

When automatic matching succeeds, report persistence and pending resolution occur in one transaction. System-driven resolution records the report and pet while leaving the human actor nullable, which distinguishes it from manual recovery. A final locking source lookup before committing unmatched persistence handles a concurrent request that created the normal report; in that case the pending row is resolved rather than left active. Existing database uniqueness remains the final guard for concurrent normal-report creation, with duplicate-key recovery reloading and refreshing the winning report.

Running patient matching before existing-report lookup was rejected because that is the current defect: a report already assigned by manual recovery can return to the pending queue whenever upstream identifiers remain unrecognized.

### Consolidate historical identities deterministically

The migration derives identity hashes for existing rows and groups non-null identities before adding the unique index. For each group it selects one canonical row using this precedence:

1. If a matching normal report exists, prefer an already resolved row, otherwise the newest row, and ensure the canonical row references that report and pet.
2. Otherwise prefer the newest active row.
3. Otherwise retain the newest lifecycle row, including its resolved or deleted state.

The canonical row receives the earliest `created_at` and the latest known receipt as `last_received_at`. Other rows are made inactive when necessary, point to the canonical row through `superseded_by_id`, and keep their raw payload, source columns, identifiers, reason, and lifecycle audit fields. Their `identity_hash` remains null so the canonical row alone owns the unique key; their original source columns still make their identity auditable.

The migration adds the unique index only after consolidation and verifies there is no remaining duplicate non-null hash. Identity-less rows are untouched.

### Keep API compatibility while improving queue recency

The import endpoint retains its existing response shapes: a delivery that remains unmatched or suppressed by a prior dismissal returns `status: pending`; a delivery persisted to a normal report returns `status: ok`. No new response field is required by clients.

The unmatched queue continues to show one row per active pending record but orders by latest receipt and labels that timestamp as the latest receipt. The detail page exposes first and latest receipt when they differ. This makes an updated item discoverable without misrepresenting its original arrival.

## Risks / Trade-offs

- [An identity hash implementation could drift from normal-report lookup rules] → Centralize identity normalization and add shared contract tests for device precedence, source fallback, trimming, and null identity.
- [A negligible SHA-256 collision would conflate unrelated reports] → Retain and compare the original identity columns before updating; treat a hash match with different canonical components as an error rather than overwriting.
- [Migration may encounter contradictory lifecycle rows] → Use deterministic precedence, retain superseded payloads, log consolidation counts and conflicts, and fail before creating the unique index if canonicalization is incomplete.
- [Concurrent matched and unmatched deliveries can cross table boundaries] → Use transactions, database unique constraints, locking reads, and a final normal-report recheck before leaving a pending row active.
- [Suppressing a dismissed identity can hide new upstream content for that same report] → Preserve the explicit dismissal decision while unmatched; if identifiers later resolve successfully, normal ingestion is allowed, and staff can still inspect the retained audit record.
- [Replacing pending payloads loses intermediate upstream versions] → Preserve the first and latest timestamps and current complete payload; retaining every hourly copy is intentionally avoided because the queue is operational state rather than a delivery log.

## Migration Plan

1. Add nullable identity, last-received, and supersession fields without changing ingestion behavior.
2. Backfill `last_received_at`, derive identities, consolidate duplicate groups, and connect groups that already have a matching normal report.
3. Verify canonical identity uniqueness, then add the unique index.
4. Deploy the shared identity routine, transactional ingestion flow, pending upsert, automatic-resolution behavior, and queue timestamp changes together.
5. Verify MediLab-style repeated imports, normal-report refresh, dismissal suppression, automatic matching, missing identity, concurrent requests, and migrated duplicates.

Rollback application code before reversing the migration. Dropping the unique index and new fields restores the prior schema but does not reactivate consolidated rows automatically; their retained source and supersession data must be exported or deliberately restored before dropping fields if operational rollback requires the former queue shape.
