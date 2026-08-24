## 1. Lab Data and Presentation Foundations

- [x] 1.1 Add an event-lab relationship model for valid linked reports, eligible pet-scoped reports, duplicate-safe linking, and relationship-only unlinking; verify model tests cover matching pets, another pet, deleted labs, duplicates, multiple links, and stale links.
- [x] 1.2 Extract reusable lab-result normalization for text/numeric values and low/high/reference-range state, update the full lab detail flow to use it, and verify unit tests produce the same display state for normal, abnormal, zero-range, and text results.
- [x] 1.3 Load linked lab reports, normalized result rows, and eligible unlinked choices only for closed/finalized events; verify controller tests show no lab-query/view data requirement on the open consultation branch.

## 2. Secure Lab Relationship Actions

- [x] 2.1 Add an authenticated POST link action that derives the pet from the persisted event, validates finalized state and lab eligibility, logs success, and redirects to the event; verify controller tests reject open events, missing records, deleted labs, duplicates, and labs belonging to another pet.
- [x] 2.2 Add an authenticated POST unlink action that validates the composite relationship, logs success, and removes only the junction row; verify tests confirm the lab report and its pet assignment remain unchanged.
- [x] 2.3 Add confirmation and success/error feedback for link and unlink controls, and verify cancellation leaves the relationship unchanged while accepted actions return to the same finalized event.

## 3. Attachment Preview Delivery

- [x] 3.1 Add an authenticated inline attachment endpoint with file-existence checks, a narrow raster MIME allowlist, inline disposition, and `nosniff`; verify controller tests accept supported images and reject missing, SVG, and non-image attachments.
- [x] 3.2 Render lazy-loaded fixed-size previews for eligible images and filename actions for other files, preserving delete behavior and finalized drawing JPEGs; verify view tests cover image, drawing-image, PDF, SVG, and missing-file metadata cases.

## 4. Finalized Event Interface

- [x] 4.1 Replace the three header cards with a responsive compact summary line using linked client/pet names, the event timestamp, formatted bill total, and an explicit unavailable-cost state; verify rendered output and links for billed and unbilled finalized events.
- [x] 4.2 Remove the Report, Media, and Files tabs and compose the report editor, uploader, and attachment collection in one continuous finalized-event layout; verify report saving, autosaving, upload, download, and deletion remain operational.
- [x] 4.3 Replace the invoice-item sidebar with the lab panel, embedding normalized rows for all valid linked labs newest first, expanding the newest by default, marking abnormal results, and providing one-click full-report navigation; verify view tests cover zero, one, and multiple linked labs.
- [x] 4.4 Add the pet-scoped lab selector and confirmed unlink controls to the lab panel, with localized labels and accessible control names; verify only eligible labs appear and all controls work with keyboard navigation.
- [x] 4.5 Remove drawing view inclusion, drawing-only styling, and drawing client scripts; search for remaining callers before retiring drawing/reset endpoints and verify historical finalized drawing attachments still render as previews.

## 5. Regression and Acceptance Verification

- [x] 5.1 Add integration coverage for link → embedded display → full-report navigation → unlink, including a reassigned or soft-deleted linked lab that must not be disclosed.
- [ ] 5.2 Run the project test suite and PHP syntax checks for every changed PHP file, resolving all failures.
- [x] 5.3 Manually verify the finalized page at desktop and narrow viewport widths, confirming the summary stays compact, large lab sets remain navigable, attachments preview safely, and the open consultation interface is unchanged.
