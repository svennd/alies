## Why

The pet fiche currently presents medical history in a DataTable whose paging and responsive row details make the clinical timeline harder to scan. Replacing it with an expandable chronological feed will make recent consultations easier to scan while keeping clinical details compact and easy to filter.

## What Changes

- Replace the DataTable in the pet fiche history block with a Bootstrap-compatible expandable card feed based on the "Medische Historiek" example in `index2.html`.
- Display medical-history entries newest first with every entry collapsed initially.
- Allow users to expand and collapse an entry by activating its header while keeping at most one entry open.
- Add client-side filters for event type (`Alle`, `Ziekte`, and `Operaties`) and veterinarian.
- Ensure every veterinarian displayed in a history entry is represented exactly once in the veterinarian filter and can filter both primary and supporting assignments.
- Keep legacy medicine and laboratory entries visible through `Alle` without offering separate filter choices for those unused types.
- Display the location in the entry header alongside the veterinarian information.
- Hide filters, edit actions, and the separate eye toggle on phone-sized screens while retaining header-based expansion.
- Preserve clinical report content, products, procedures, unfinished-report indication, edit navigation, and attachment access.
- Provide responsive presentation, filter empty states, and incremental display of long histories without DataTables.

## Capabilities

### New Capabilities

- `pet-medical-history`: Defines the chronological, expandable, filterable medical-history experience on the pet fiche.

### Modified Capabilities

None.

## Impact

- Primary view: `application/views/pets/fiche/block_history.php`.
- Dutch and English veterinary language files for new feed, filter, empty-state, and incremental-display labels.
- Existing data source: `Events_model::get_pet_history()` and the `Pets::fiche()` controller payload; veterinarian assignments will be normalized so display and filtering consume the same data.
- Existing Bootstrap 4, jQuery, and Font Awesome assets can support the behavior; no new frontend dependency or API is expected.
- Attachment rendering must use the model's existing `upload_count` field consistently instead of the obsolete `uploads` array assumption in the current view.
