## Why

The veterinarian filter in the pet fiche medical-history block is no longer needed and adds unnecessary choice to a feed that is now filtered only by clinical event type. Removing it keeps the controls aligned with the current workflow while retaining veterinarian information where it helps users understand each entry.

## What Changes

- Remove the veterinarian filter and its `Alle dierenartsen` option from the pet medical-history block.
- Keep the event-type filter with `Alle`, `Ziekte`, and `Operaties`, with `Alle` selected initially.
- Make filter changes, reset behavior, empty-state handling, and incremental display depend only on the selected event type.
- Continue displaying primary and supporting veterinarian names in each medical-history entry summary.
- Remove veterinarian-filter-specific presentation data and localization that are no longer consumed, without changing the underlying medical-history records.

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `pet-medical-history`: Remove veterinarian filtering and define type-only filtering while preserving veterinarian names in entry summaries.

## Impact

- Primary view: `application/views/pets/fiche/block_history.php`.
- Dutch and English veterinary language files may lose the unused all-veterinarians label.
- The existing `Events_model::get_pet_history()` veterinarian collection remains available for summary display; no database, controller, route, or API change is required.
- Focused pet-history view tests and OpenSpec validation need to cover the absence of the veterinarian filter and continued type-filter behavior.
