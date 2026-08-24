## 1. History Lab Count

- [x] 1.1 Extend pet-history data assembly with a scalar valid linked-lab count that joins the event relationship to non-deleted reports belonging to the event's pet, and verify multiple links do not duplicate history entries.
- [x] 1.2 Add focused model/integration coverage for zero, one, and multiple valid links plus deleted and pet-mismatched reports, and verify the returned counts satisfy every data-validity scenario.

## 2. Pet Fiche Indicator and Navigation

- [x] 2.1 Add a stable fragment identifier to the finalized-event linked-lab results section, and verify the rendered page contains exactly one matching navigation target.
- [x] 2.2 Render a conditional flask action with the linked-lab count and localized accessible label in each applicable pet-history entry, point it to the event lab-panel fragment, and verify events without valid labs render no lab action.
- [x] 2.3 Update compact action visibility so a lab action remains available on phone-sized layouts with or without attachments, and verify existing attachment-only and no-action behavior remains unchanged.
- [x] 2.4 Add focused view coverage for conditional rendering, counts, accessible text, fragment URLs, and compact-action CSS behavior, and verify the new pet-history view tests pass.

## 3. Regression Verification

- [x] 3.1 Run the relevant pet-history and finalized-event tests and verify existing lab linking, embedded results, and attachment actions still pass unchanged.
- [x] 3.2 Run PHP syntax checks for every changed PHP file and verify all checks complete without errors.
