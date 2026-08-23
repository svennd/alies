## 1. History Data and Labels

Tasks 1–4 record the completed initial implementation. Section 5 supersedes the affected behavior with the confirmed follow-up requirements.

- [x] 1.1 Prepare a deduplicated, name-sorted veterinarian option list from primary and supporting veterinarian IDs in the existing history payload, excluding unusable missing IDs, and verify a supporting veterinarian appears once and can be matched to their events.
- [x] 1.2 Add Dutch and English language lines for the medical-history heading, all-type and all-veterinarian choices, filtered empty state, reset action, and show-more action, and verify both locale files return non-empty labels for every new control.

## 2. Medical-History Feed

- [x] 2.1 Replace the history DataTable markup and initialization with scoped Bootstrap-compatible history cards ordered by the existing payload, and verify the rendered page contains no DataTable initialization for this block.
- [x] 2.2 Render each card summary with date, type symbol, title, all involved veterinarians, unfinished status, and actions while keeping location out of the collapsed summary; verify complete and unfinished sample entries display the expected metadata.
- [x] 2.3 Render optional expanded sections for location, report HTML, products, procedures, edit/event navigation, and attachments using `upload_count`, and verify empty sections and zero-count attachment controls are omitted without PHP notices.
- [x] 2.4 Add component-scoped desktop and narrow-viewport styling based on the `index2.html` visual example, and verify at mobile width that metadata and actions reflow without horizontal scrolling.

## 3. Interaction and Filtering

- [x] 3.1 Implement accessible single-entry expansion using buttons, unique panel IDs, `aria-controls`, and synchronized `aria-expanded` state; verify the newest entry opens initially, opening another closes the prior entry, and the active entry can be closed without opening another.
- [x] 3.2 Implement combined type and veterinarian filters with `Alle` as the default type, only `Ziekte` and `Operaties` as specific type choices, and matching across primary and supporting vets; verify legacy medicine/laboratory entries appear under `Alle` but have no dedicated filter option.
- [x] 3.3 Reset the visible batch and open the newest match after each filter change, and add a distinct no-match state with a reset action; verify combined filters with zero and multiple matches behave as specified.
- [x] 3.4 Limit initial matching cards to 10 and implement `Toon meer` in batches of up to 10; verify histories with 10, 11, and more than 20 matching entries show and hide the control at the correct points.

## 4. Verification

- [x] 4.1 Run PHP syntax checks on every modified PHP view and language file and verify all commands exit successfully.
- [x] 4.2 Exercise the pet fiche with no history, a short history, and a long mixed history containing legacy types, supporting vets, missing optional details, unfinished reports, and attachments; verify the requirements in `specs/pet-medical-history/spec.md` hold on desktop and mobile layouts.
- [x] 4.3 Verify expansion, filters, reset, show-more, edit, and attachment controls are keyboard reachable and that focus activation and ARIA expanded state remain correct.

## 5. Requested Follow-up

- [x] 5.1 Normalize each event's primary and supporting veterinarian assignments from explicitly aliased IDs and names, including a deterministic fallback token for displayed names without usable IDs, and verify every veterinarian displayed across a fixture history appears exactly once in the name-sorted filter options.
- [x] 5.2 Move location from the expanded details into the entry header, render all entries collapsed initially and after filter changes, and verify neither initial load nor filtering opens an entry automatically.
- [x] 5.3 Restructure each entry header so its metadata is a semantic button that toggles the detail panel while edit and attachment links remain independent actions; verify mouse and keyboard activation toggle one panel and action-link activation does not toggle it.
- [x] 5.4 Hide the filter controls, edit action, and separate eye toggle at phone width while retaining header expansion and attachment access, and verify the phone layout has no horizontal scrolling.
- [x] 5.5 Update combined veterinarian filtering to consume the normalized veterinarian tokens used for display, then verify primary, supporting, legacy fallback, type-plus-veterinarian, and no-match cases in a headless browser.
- [x] 5.6 Run PHP syntax checks, strict OpenSpec validation, responsive desktop/phone browser checks, accessibility-state checks, and the project PHPUnit suite; verify all required checks pass before marking the follow-up complete.
