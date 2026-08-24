## Why

The pet fiche medical-history feed indicates when an event has attachments but gives no equivalent signal for linked laboratory reports. Clinicians therefore have to open individual events to discover whether relevant lab results are connected.

## What Changes

- Add a linked-lab indicator to each pet medical-history entry that has one or more currently valid linked lab reports.
- Show the number of valid linked labs beside a flask icon so events with multiple reports are distinguishable at a glance.
- Make the indicator navigate directly to the linked-lab panel on the finalized event page.
- Keep the indicator available in the compact phone layout alongside the existing attachment action.
- Exclude deleted, stale, or pet-mismatched lab relationships from the indicator and its count.

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `pet-medical-history`: Extend the compact entry summary and responsive actions with an accessible, counted indicator for valid lab reports linked to an event.

## Impact

- Pet-history data assembly in `application/models/Events_model.php`.
- Pet fiche history presentation in `application/views/pets/fiche/block_history.php`.
- Finalized-event lab panel markup in `application/views/event/report/block_lab_results.php` gains a stable target as an implementation detail of the pet-history navigation.
- Existing event-lab relationship semantics and localization are reused; no database migration or external dependency is required.
- Focused model/view tests will cover valid counts, stale relationship filtering, conditional rendering, navigation, accessibility, and narrow-screen availability.
