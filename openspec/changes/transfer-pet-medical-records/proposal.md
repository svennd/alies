## Why

Changing a pet's owner currently creates a successor pet record but leaves vaccinations, measurements, dental records, imaging, laboratory results, and medical history attached to the hidden source record. Although ownership transfers are rare, staff need the successor record to remain clinically complete without moving invoice-bound data away from the former owner.

## What Changes

- Extend pet ownership transfer so patient-owned medical records move to the successor pet.
- Preserve medication, nutrition, identification, and avatar data through the existing pet clone behavior.
- Copy visible medical-history events to the successor as finalized, non-billable historical summaries.
- Append product and procedure names, quantities, and units to copied event report text without copying billable event lines, prices, invoices, stock references, or former-owner information.
- Keep the original events and their accounting relationships unchanged under the historical source pet.
- Reassociate API lab links with the corresponding copied history events where a source event has a linked report.
- Add a transactional, one-time sequential migration that repairs existing transfers and aborts rather than guessing when a source and successor pet cannot be matched uniquely.
- Keep hidden (`no_history = 1`) events out of the copied medical history.

## Capabilities

### New Capabilities

- `pet-owner-transfer`: Defines clinically complete pet ownership transfers, non-billable history copies, preservation of historical accounting, and backfill of existing transfers.

### Modified Capabilities

None.

## Impact

- Affects the pet transfer controller/model flow and models or services responsible for events, vaccinations, weights, dental records, RX images, and laboratory records.
- Adds a CodeIgniter sequential data migration for existing transferred pets; no new event provenance column is introduced.
- Requires transactional database updates, deterministic source-to-successor matching, transfer logging, and integration coverage for success and rollback paths.
- Does not change invoice ownership, bill contents, stock movements, event product/procedure rows, or external dependencies.
