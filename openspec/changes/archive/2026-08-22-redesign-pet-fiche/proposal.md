## Why

The current pet information partial presents core patient data as a plain table with actions split across the header and footer, making the profile harder to scan than the reference profile card in `index2.html`. Redesigning this partial will give veterinary staff a clearer patient identity and fact summary while retaining the application's existing Bootstrap 4 and SB Admin visual language.

## What Changes

- Redesign only the `pets/fiche/pet_info.php` partial as a compact pet profile card inspired by the pet profile section in `index2.html`.
- Present the pet identity, owner link, species icon, name, and record ID together in a prominent card header.
- Replace the information table with a responsive two-column mobile and three-column tablet/desktop fact grid.
- Preserve all currently available pet facts, conditional fields, links, notes, and actions.
- Reorganize actions so editing is immediately available while export, owner transfer, dental, RX, and lab actions remain discoverable and responsive.
- Use only the project's existing Bootstrap 4, SB Admin, and Font Awesome assets; do not introduce a new frontend framework or external visual dependency.
- Keep the surrounding fiche layout, controller/model data loading, vaccines, history, and sidebar blocks unchanged.

## Capabilities

### New Capabilities

- `pet-fiche-profile-card`: Defines the responsive presentation and retained behavior of the pet identity, facts, notes, and actions on the pet fiche.

### Modified Capabilities

None.

## Impact

- Primary affected view: `application/views/pets/fiche/pet_info.php`.
- Existing data contract from `Pets::fiche()` remains unchanged.
- Existing routes for pet editing, export, owner transfer, weight history, dental records, RX, and lab records remain unchanged.
- No database, model, controller, API, or dependency changes are expected.
