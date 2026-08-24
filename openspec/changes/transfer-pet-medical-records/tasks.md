## 1. Transactional Transfer Core

- [ ] 1.1 Refactor pet cloning so the transfer operation retains and validates the successor pet ID, and verify a model test covers successful clone creation and clone failure.
- [ ] 1.2 Wrap successor creation, clinical-data movement, history copying, source hiding, and success logging in a single transaction, and verify an injected failure leaves the source pet active with no successor or partial relationship changes.
- [ ] 1.3 Add source-pet and target-owner validation before mutation, and verify invalid, missing, or already-transferred source records cannot begin the transaction.

## 2. Direct Medical Record Movement

- [ ] 2.1 Move `vaccine_pet.pet`, `pets_weight.pets`, `tooth.pet`, and `tooth_msg.pet` rows from the source to the successor while retaining existing successor rows, and verify record IDs and clinical values remain unchanged.
- [ ] 2.2 Move `rx.pet_id`, `lab.pet`, and `lab_report.pet_id` rows from the source to the successor while retaining source-system identifiers and metadata, and verify RX and both laboratory screens discover the moved records through the successor.
- [ ] 2.3 Verify vaccination summaries and reminder queries resolve moved vaccinations through the successor owner or companion and do not emit reminders through the hidden source pet.

## 3. Non-Billable Medical History Copies

- [ ] 3.1 Add a history-summary formatter that appends escaped product and procedure names, signed quantities, and available units to the original report, and verify tests cover products only, procedures only, both groups, missing units, and no item lines.
- [ ] 3.2 Copy each `no_history = 0` source event to the successor with original clinical metadata and timestamps plus history/final/invalid-payment state, and verify `no_history = 1` events are not copied.
- [ ] 3.3 Ensure copied events have no product, procedure, upload, stock, VAMReg, booking, price, invoice, or owner child data, and verify the original event graph and bill association remain unchanged.
- [ ] 3.4 Recreate eligible `events_labs` associations from source events to their copied successor events after moving API reports, and verify linked reports render on the copied event while unlinked reports remain available from the successor lab list.

## 4. Existing Transfer Backfill

- [ ] 4.1 Add the next sequential data-only migration with deterministic source/successor matching by transfer target, identity fields, transfer markers, and timestamps, and verify all current test-data transfers resolve exactly once without adding schema columns.
- [ ] 4.2 Add migration preflight for missing or ambiguous matches and dental or other update-key conflicts, and verify any failed preflight reports the source record before performing mutation.
- [ ] 4.3 Apply direct-record movement, visible history copying, item summarization, and API lab relinking for every mapped pair in one transaction, and verify existing successor vaccinations and weights remain unchanged.
- [ ] 4.4 Make the migration roll back all pairs when any backfill query fails and document backup-based post-commit recovery in the migration handoff, then verify a forced mid-backfill failure leaves all source and successor data unchanged.

## 5. Controller and Regression Verification

- [ ] 5.1 Update transfer completion handling to log and redirect only after a committed result, and verify failed transfers return visible failure feedback without a success log.
- [ ] 5.2 Add an end-to-end transfer integration test covering pet fields/avatar, all supported direct medical tables, copied visible history, skipped hidden history, product/procedure summary text, and linked API labs.
- [ ] 5.3 Add accounting regression coverage proving original events still render on former-owner bills and copied history events cannot be collected by a new invoice.
- [ ] 5.4 Run the focused transfer/migration tests and the existing project test suite, lint every changed PHP file, and record the commands and passing results in the implementation handoff.
