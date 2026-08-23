## Why

The pet fiche currently identifies a patient only with a generic species icon, which makes visually distinguishing pets slower for veterinary staff. Allowing staff to attach a pet photo makes the fiche more recognizable while preserving the existing icon as a reliable fallback.

## What Changes

- Make the pet profile image area an accessible interactive control for authenticated veterinary staff.
- Allow staff to select, crop, rotate, validate, and save a pet photo from the fiche.
- Display the saved photo as a circular avatar in place of the species icon.
- Allow staff to replace or remove a saved photo, restoring the species icon after removal.
- Persist a normalized image reference with the pet and preserve that reference when a pet is transferred to a new owner.
- Serve stored pet photos through an authenticated application endpoint and provide localized success and error feedback.

## Capabilities

### New Capabilities

- `pet-avatar-management`: Defines secure pet-photo upload, normalization, replacement, removal, retrieval, and transfer behavior.

### Modified Capabilities

- `pet-fiche-profile-card`: Changes the pet identity area to display an interactive pet photo when available while retaining the species icon as the fallback.

## Impact

- Pet persistence and migrations for the stored avatar reference.
- `Pets` controller actions and pet-transfer behavior in `Pets_model`.
- Pet fiche profile-card markup, styling, client-side crop interaction, and asset loading.
- Authenticated image storage and delivery under a dedicated pet-image location.
- English and Dutch language resources, validation feedback, action logging, and upload-focused tests.
- The profile-card view is also in scope for the active `enhance-pet-fiche-vaccine-alerts` change, so implementation should preserve or reconcile concurrent fiche edits.
