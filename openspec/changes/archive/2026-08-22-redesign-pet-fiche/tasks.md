## 1. Profile Card Implementation

- [x] 1.1 Rebuild the `pet_info.php` card header with the species icon, escaped pet name and owner label, record ID, visible edit action, and Bootstrap overflow menu for export and change-owner; verify each action resolves to its existing pet- or owner-specific route.
- [x] 1.2 Replace the pet information table with the `col-6 col-md-4` fact grid, retaining localized labels, helper-rendered type and gender information, cross-breed formatting, age, chip formatting, unknown markers, and the correctly closed weight-history link; verify complete and incomplete pet records render the expected core facts.
- [x] 1.3 Add conditional fact cells for hair color, hair type, and vaccination-book number, plus a safely escaped note warning and the dental/RX/lab action footer; verify empty optional values and unavailable RX/lab actions produce no empty UI elements.
- [x] 1.4 Add only narrowly scoped profile-card styles needed for stable icon sizing, fact labels, wrapping, and spacing; verify the partial uses the loaded Bootstrap 4, SB Admin, and Font Awesome assets without adding a dependency or changing a global stylesheet.

## 2. Verification

- [x] 2.1 Run PHP syntax validation on `application/views/pets/fiche/pet_info.php` and verify it reports no syntax errors.
- [x] 2.2 Inspect `pets/fiche/{id}` at small, tablet, and desktop widths and verify the fact grid changes from two to three columns without overflow, overlap, or disruption to the vaccines card, medical history, and sidebar blocks.
- [x] 2.3 Exercise a pet with a note, optional facts, RX images, and lab reports and a pet without those values; verify safe note formatting, conditional visibility, and all profile action destinations in both data states.
