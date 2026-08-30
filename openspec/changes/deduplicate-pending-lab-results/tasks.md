## 1. Source Identity Foundation

- [x] 1.1 Add a shared stable lab source-identity normalizer/hash using device precedence, source fallback, trimmed non-empty values, and null for unsafe identities; verify focused tests cover equivalent identities, component boundaries, whitespace, missing source IDs, and device/source precedence.
- [x] 1.2 Route normal-report source lookup and pending identity derivation through the shared rules without changing existing device/source lookup outcomes; verify model/service contract tests pass for both lookup forms.

## 2. Pending Identity Migration

- [x] 2.1 Add migration 056 fields for canonical identity, latest receipt, and supersession metadata, including a unique canonical-identity index and a reversible schema down path; verify the migration test confirms field and index creation/removal.
- [x] 2.2 Implement deterministic migration consolidation for duplicate identified rows, preserving the earliest receipt, newest payload/latest receipt, lifecycle precedence, and superseded audit rows; verify migration fixtures cover active duplicates, existing normal reports, resolved/deleted-only groups, and conflicting lifecycle rows.
- [x] 2.3 Leave identity-less pending rows independent during backfill and fail safely before index creation when canonicalization is incomplete; verify migration tests cover multiple null identities and the validation/failure path.

## 3. Idempotent Pending Persistence

- [x] 3.1 Add atomic create-or-refresh behavior for canonical pending identities so active rows receive the newest complete payload, identifiers, reason, and latest receipt while retaining their original creation time; verify repeated and concurrent model tests leave exactly one active canonical row.
- [x] 3.2 Make repeated unmatched deliveries against resolved or dismissed canonical rows a lifecycle-preserving no-op, while identity-less deliveries continue creating separate rows; verify model tests cover active, resolved, dismissed, collision-mismatch, and null-identity cases.
- [x] 3.3 Support system-driven pending resolution with a nullable human actor and retained report/pet audit references; verify lifecycle model tests distinguish automatic resolution from manual recovery.

## 4. Transactional Ingestion Flow

- [x] 4.1 Reorder ingestion to refresh an existing normal report by stable source identity and its current patient before attempting automatic patient fallback; verify repeated unmatchable MediLab-style deliveries update one report, preserve its report/pet IDs, return `status: ok`, and create no pending row.
- [x] 4.2 Persist a newly automatically matched report and resolve any canonical active pending row in one transaction; verify success and forced-failure integration tests cover report/result/plot persistence, pending resolution, rollback, and API response compatibility.
- [x] 4.3 Use the idempotent pending path for still-unmatched deliveries and preserve dismissal suppression while allowing a later automatically matchable delivery; verify integration tests cover hourly active refresh, dismissed repeats, later matching, and identity-less imports.
- [x] 4.4 Add the locking recheck and duplicate-key recovery needed for concurrent matched/unmatched and matched/matched deliveries; verify multi-connection integration tests leave one normal report, no contradictory active pending row, and complete result/plot collections.

## 5. Queue Presentation and Regression Coverage

- [x] 5.1 Order active pending results by latest receipt and show latest receipt in the queue plus first/latest receipt in detail when they differ, with legacy fallback to creation time; verify controller and view tests cover refreshed and legacy rows without exposing raw payloads in the queue.
- [ ] 5.2 Run focused lab model, migration, service, controller, view, and end-to-end workflow suites and the project's broader automated test command; verify all suites pass and the import endpoint retains only the existing `pending` and `ok` response shapes.
