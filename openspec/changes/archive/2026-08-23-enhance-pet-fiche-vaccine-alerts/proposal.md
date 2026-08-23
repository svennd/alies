## Why

The vaccine summary on the pet fiche currently uses a dense table and only distinguishes vaccines due within three months by red text. A compact alert-style presentation will make each vaccine and its urgency easier to scan in the fiche sidebar.

## What Changes

- Replace the vaccine summary table with one Bootstrap 4 alert-style block per vaccine.
- Display the vaccine name together with its injection and rappel dates in each block.
- Classify expired vaccines as danger, vaccines due within the next three months as warning, and later vaccines as success.
- Preserve the existing vaccine-detail link, localized labels and empty-state behavior.
- Keep the summary usable in the narrow and responsive pet-fiche sidebar.

## Capabilities

### New Capabilities

- `pet-fiche-vaccine-status`: Defines the compact vaccine summary and its three visual urgency states on the pet fiche.

### Modified Capabilities

None.

## Impact

- Primary view: `application/views/pets/fiche/vaccines.php`.
- Reference design: `vaccin.html`.
- Existing vaccine summary data and Bootstrap 4/SB Admin 2 styles are reused; no database, controller, model, API, or dependency changes are expected.
