## 1. Type-Only History Filtering

- [x] 1.1 Remove veterinarian option preparation, filter markup, per-entry veterinarian filter attributes, and veterinarian matching state from the pet history view, and verify the rendered block contains the type filter but no veterinarian filter.
- [x] 1.2 Update the type-filter change and reset handlers to operate independently while preserving collapsed entries, 10-entry batching, no-match feedback, and `Toon meer`; verify disease, operation, all-type, reset, and zero-match cases behave as specified.
- [x] 1.3 Preserve primary and supporting veterinarian names in every entry summary while removing only filter-specific presentation data, and verify a fixture with multiple veterinarians still renders their names.

## 2. Localization and Verification

- [x] 2.1 Remove the unused Dutch and English `history_all_vets` language lines and verify no application or test reference remains.
- [x] 2.2 Add or update focused pet-history view tests for the absent veterinarian filter, retained veterinarian summary, and type-filter contract, and verify the focused PHPUnit suite passes.
- [x] 2.3 Run PHP syntax checks for each modified PHP file and strict OpenSpec validation for `remove-veterinarian-history-filter`, and resolve any failures.
