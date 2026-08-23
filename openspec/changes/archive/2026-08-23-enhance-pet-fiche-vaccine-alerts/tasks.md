## 1. Status Classification

- [x] 1.1 Add calendar-day and inclusive three-month boundaries to the vaccine partial, classify each rappel as danger, warning, or success, and verify expired, due-today, exactly-three-months, and later-than-three-months cases select the expected state.

## 2. Alert-Style Vaccine Summary

- [x] 2.1 Replace the vaccine table rows with compact Bootstrap 4 alert-style blocks that escape the vaccine name and display the existing formatted injection and rappel dates; verify each rendered vaccine contains all three values and the expected alert variant.
- [x] 2.2 Preserve the vaccine fiche heading link and localized no-vaccines state, and verify the link targets the current pet while an empty vaccine collection still shows the existing message.
- [x] 2.3 Apply Bootstrap 4 spacing and wrapping utilities so long names and date pairs adapt to the narrow sidebar, and verify representative long content does not overlap or introduce horizontal scrolling at desktop and mobile fiche widths.

## 3. Verification

- [x] 3.1 Run PHP syntax validation on `application/views/pets/fiche/vaccines.php` and verify it reports no syntax errors.
- [x] 3.2 Render a pet fiche with vaccines in all three urgency states and verify the card layout, date formatting, status colors, navigation, and empty-state regression against the specification and `vaccin.html` reference.
