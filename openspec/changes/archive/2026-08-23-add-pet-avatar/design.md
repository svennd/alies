## Context

See `proposal.md` for motivation and the delta specs for required behavior. The pet fiche is rendered by `Pets::fiche()` and its identity card lives in `application/views/pets/fiche/pet_info.php`. Pets currently have no image reference. The project already ships Croppie and uses GD to generate veterinarian profile images, but that flow stores public assets and trusts a browser-generated base64 result more than is appropriate for patient photos.

`Pets_model::transfer_pet()` clones the source pet before hiding it, so an avatar filename added to the row will naturally be copied. That shared reference affects replacement and file cleanup. The project uses authenticated `Vet_Controller` endpoints, a sequential migration scheme, English and Dutch language files, and an existing activity logger.

## Goals / Non-Goals

**Goals:**

- Keep original pet photos out of public asset storage and retain only a normalized square derivative.
- Preserve the existing species-icon fallback and the current profile-card layout.
- Make file replacement safe when transferred pet records share an avatar reference.
- Reuse the installed crop UI and server image facilities without adding a package.

**Non-Goals:**

- Retaining original uploads, maintaining a photo gallery, or attaching photos to medical events.
- Allowing owners or unauthenticated visitors to manage or retrieve pet avatars.
- Retroactively assigning photos to existing pets.
- Redesigning other pet-fiche sections or the veterinarian profile-avatar feature.

## Decisions

### Persist an immutable filename on the pet row

Add a nullable `avatar` filename column to `pets`. Each accepted image receives an opaque, unique filename; stored names never contain the client filename. The current reference is returned with the normal pet query and copied by the existing transfer clone.

An alternative pet-avatar table would support galleries and richer metadata but adds joins and lifecycle complexity for a single-current-image feature. A database BLOB would make authenticated access easy but increases database and backup size and couples image streaming to database throughput.

### Store normalized files outside public assets

Store derivatives beneath `data/stored/pets/` and serve them through an authenticated `Pets` action. Delivery resolves only the filename already associated with the requested pet; callers cannot submit a storage path. Responses set the normalized image media type, `X-Content-Type-Options: nosniff`, and private cache/revalidation headers. A missing record or missing file produces a non-disclosing not-found response.

Using `assets/public` would match veterinarian avatars but would expose patient photos to anyone who guesses a URL. Storing originals was rejected because they are unnecessary and may retain metadata.

### Validate the source and regenerate the derivative server-side

The form sends the original selected file plus the cropped preview result. The server enforces the 8 MB source limit, recognizes JPEG or PNG from decoded content rather than its extension, rejects unreasonable pixel dimensions before full processing, validates the crop payload, and uses GD to decode and re-encode it as a square image up to 512×512. Re-encoding strips source metadata and active or trailing content.

Croppie remains responsible for preview, rotation, and producing the selected square crop. Sending the original alongside the crop lets the server enforce the selected-file contract even though only the derivative is retained. Reusing only the existing base64 flow would prevent reliable server enforcement of the original-file size.

### Use write-before-associate with serialized reference updates

Image processing writes to a unique temporary filename and atomically renames the completed derivative into the pet storage directory. The pet row is then locked and updated within a database transaction so concurrent replacements observe the actual current reference. If association fails, the newly written file is removed.

After a successful commit, the previous file is deleted only when a direct database count confirms that no pet row—including transferred or soft-deleted historical rows—references it. Removal uses the same locked update and reference check. This preserves transfer continuity without requiring a separate reference-count table.

### Keep operations explicit and return to the fiche

The fiche renders the icon/photo as a semantic button opening a Bootstrap modal with a hidden file input, Croppie preview, rotation controls, save/cancel controls, and a remove action only when an avatar exists. Save and remove are POST-only operations. They set localized flash feedback, write successful activity-log entries, and redirect to the same fiche, preventing accidental resubmission on refresh.

An immediate AJAX upload was considered but adds response-state and fallback complexity without improving this small interaction materially. The redirect flow matches existing CodeIgniter controllers while the modal still provides an immediate local preview.

### Load crop assets only on the fiche that needs them

`Pets::fiche()` supplies Croppie's stylesheet and script through the existing extra-header/footer mechanism. Pet-specific JavaScript stays with the fiche partial or a dedicated project asset and initializes only when the avatar modal is present. The control retains the card's four-rem footprint, uses `object-fit: cover`, and exposes localized labels and focus behavior.

## Risks / Trade-offs

- **Large or adversarial images may exhaust GD memory** → Enforce byte and pixel-count limits before creating image resources, handle decode failures, and destroy all GD resources.
- **Database and filesystem changes cannot share one atomic transaction** → Write the immutable file first, remove it on database failure, serialize reference updates, and delete prior files only after commit and an unfiltered reference check.
- **A server interruption can leave an unreferenced derivative** → Unique immutable names prevent incorrect display; cleanup can safely identify files absent from all pet avatar references.
- **The storage directory may be missing or unwritable in a deployment** → Check/create the narrowly scoped directory during the operation, fail without changing the current avatar, and report a localized error.
- **Browser cache may show a replaced photo briefly** → Render a version token derived from the current avatar reference and require private revalidation on the delivery response.
- **The active vaccine-alert change also affects the fiche** → Re-read the shared fiche files during apply and merge only the identity-card changes without overwriting concurrent work.

## Migration Plan

1. Add the nullable avatar-reference column with the next sequential migration; existing pets remain on the species-icon fallback.
2. Deploy the authenticated delivery and mutation actions, image-processing support, translations, and fiche UI together.
3. Confirm the application process can create and write only the dedicated pet-image directory.
4. Smoke-test fallback, upload, replacement, removal, authenticated retrieval, invalid input, and ownership transfer.

Rollback removes the UI and endpoints before dropping the nullable column. Stored derivatives can remain inert during rollback so a forward redeploy can restore references; operational cleanup may remove them only after confirming the feature will not be re-enabled from the rolled-back database state.
