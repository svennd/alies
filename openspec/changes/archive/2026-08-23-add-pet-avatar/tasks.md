## 1. Persistence and Image Processing

- [x] 1.1 Add the next sequential reversible migration with a nullable `pets.avatar` filename column, and verify the migration applies and rolls back against the test database without changing existing pet rows.
- [x] 1.2 Implement the dedicated pet-avatar storage/processing component with unique immutable filenames, JPEG/PNG content validation, 8 MB and pixel-count limits, square re-encoding up to 512×512, temporary-file cleanup, and GD resource cleanup; verify automated tests cover valid JPEG/PNG input plus empty, corrupt, disguised, oversized, and over-dimensioned input.
- [x] 1.3 Implement serialized pet avatar association, removal, and unfiltered reference checks so a prior file is deleted only when no current, transferred, or soft-deleted pet row references it; verify model tests cover replacement, removal, shared references, database failure cleanup, and concurrent-current-reference behavior.

## 2. Authenticated Avatar Operations

- [x] 2.1 Add POST-only upload/replacement and removal actions to the authenticated pet controller, including unknown-pet handling, localized flash feedback, fiche redirects, and successful activity logging; verify controller tests show failed changes preserve the current reference and successful changes produce the expected log action.
- [x] 2.2 Add authenticated avatar delivery resolved exclusively from the requested pet record, with safe image, no-sniff, private revalidation, not-found, and missing-file responses; verify integration tests confirm authenticated image retrieval and no image disclosure to unauthenticated requests.
- [x] 2.3 Preserve the avatar reference through the existing owner-transfer clone and protect each shared record during later replacement/removal; verify a transfer integration test can retrieve the original avatar from both records and replacing one record does not break the other.

## 3. Pet Fiche Interaction

- [x] 3.1 Add English and Dutch labels and feedback for adding, changing, rotating, saving, cancelling, removing, and validating pet photos, and verify every new language key resolves in both locales.
- [x] 3.2 Update the pet fiche identity area to render a circular authenticated avatar URL with a version token or the existing species fallback as a semantic button, and add the Bootstrap modal and current-avatar removal action; verify rendered-view tests cover pets with and without an avatar and preserve all existing identity data and actions.
- [x] 3.3 Load the existing Croppie assets only on the fiche and implement file selection, 8 MB client feedback, square preview, rotation, cancellation, cropped-result submission, modal reset, and keyboard/focus behavior; verify the interaction works with both pointer and keyboard and cancellation submits no change.
- [x] 3.4 Reconcile the profile-card edits with the active vaccine-alert change and tune responsive avatar/modal styling, then verify the full fiche at phone, tablet, and desktop widths has no overlap or horizontal scrolling.

## 4. End-to-End Verification

- [x] 4.1 Run PHP syntax checks and the relevant PHPUnit test suites, and verify they complete successfully without regressions in pet fiche, pet transfer, or existing core workflow coverage.
- [x] 4.2 Perform an authenticated smoke test for fallback, JPEG upload, PNG upload, crop, rotation, replacement, removal, invalid input, missing storage directory recovery, cache refresh, and transfer continuity; verify stored paths are not exposed publicly and record the observed results.

## Verification Record

- Migration 052 was applied forward from 051; its isolated up/down test preserved existing rows and confirmed the nullable column is reversible.
- The automated workflow accepted JPEG and PNG sources, generated 512×512 normalized JPEGs, preserved the current reference after invalid input, replaced and removed files safely, recreated a missing storage directory, and recorded upload/replace/remove actions.
- Transfer tests confirmed both source and successor retain a shared avatar and later replacement does not break the historical record.
- Render and interaction checks confirmed the species fallback, private versioned URL, crop/rotation/cancel wiring, keyboard button semantics, responsive crop boundary, and English/Dutch labels.
- Access-control checks confirmed all endpoints inherit authenticated `Vet_Controller`, stored paths are absent from rendered markup, direct Apache access to `data/stored/pets` is denied, and controller delivery uses private/no-sniff headers.
