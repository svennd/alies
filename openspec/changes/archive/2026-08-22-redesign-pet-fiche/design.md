## Context

See `proposal.md` for motivation. The current `pet_info.php` partial is a single SB Admin card containing a header, an HTML table, an optional nested warning card, and two groups of actions. It receives all required data from `Pets::fiche()` and is placed in the wide column beside the vaccines partial. Bootstrap 4, SB Admin 2, and Font Awesome are already loaded globally.

The `index2.html` reference uses a prominent identity row followed by a responsive fact grid. Its Tailwind and Material Symbols implementation cannot be copied directly because this project uses Bootstrap 4 and its existing icon helpers.

## Goals / Non-Goals

**Goals:**

- Translate the reference card's identity and fact hierarchy into existing Bootstrap 4 and SB Admin conventions.
- Preserve the partial's current data contract, routes, conditional behavior, and localization.
- Keep all actions usable on small screens and prevent long values or action labels from disrupting the card layout.
- Keep implementation and any narrowly required styles within `pet_info.php`.

**Non-Goals:**

- Redesigning the containing fiche grid or any sibling partial.
- Adding pet photos, new patient fields, routes, controller data, or JavaScript behavior.
- Reproducing the Tailwind theme, typography, or Material Symbols from `index2.html`.
- Refactoring shared helpers or global styles.

## Decisions

### Use an SB Admin card with a Bootstrap grid

The existing outer `card shadow mb-4` remains the visual container. The table is replaced with a Bootstrap `row` whose fact items use `col-6 col-md-4`, yielding the specified two- and three-column layouts without custom layout JavaScript.

Alternative considered: copy the reference Tailwind classes. This was rejected because Tailwind is not part of the application and adding it for one partial would create conflicting conventions and unnecessary payload.

### Build a single identity header

The header groups the existing species icon helper with the pet name, ID, and owner link. The species icon is placed in a stable circular icon area using existing utilities plus narrowly scoped sizing where Bootstrap utilities are insufficient. This creates the visual anchor from the reference without requiring a pet photo or new asset.

Alternative considered: retain the owner/pet breadcrumb in the card header. This was rejected because the breadcrumb-like presentation competes with the pet identity; the same owner navigation is clearer as supporting metadata under the name.

### Establish a primary and secondary action hierarchy

Edit remains directly visible in the identity header. Export and change-owner move into a compact overflow dropdown beside it because they are record-management actions used less frequently. Dental, RX, and lab remain as visible secondary actions in a wrapping footer below the note. No action is hidden solely because of viewport width.

Alternative considered: keep every action as a full labeled button in the header. This was rejected because it crowds the profile identity and becomes unstable at the width allocated by the existing fiche grid.

### Preserve field semantics while improving formatting

The fact grid retains current localized labels, helper-generated species and gender markup, unknown-value markers, cross-breed formatting, formatted chip number, linked weight, formatted birth date, and age. Optional hair and vaccination-book facts remain conditional. The malformed current weight anchor is corrected as part of rebuilding the markup.

### Treat database text as text

Pet, owner, breed, color, hair type, vaccination-book, and note values are escaped at output. Trusted HTML from `get_symbol()` and `get_gender()` remains unescaped. For notes, escaping occurs before `nl2br()` so line breaks remain visible without interpreting stored markup.

Alternative considered: preserve the current raw output exactly. This was rejected because reorganizing the partial is an appropriate point to avoid carrying forward markup injection risk in user-entered fields.

### Keep styles local and narrowly scoped

Bootstrap and SB Admin utilities provide most spacing, typography, borders, and responsiveness. Any required rules for the fixed icon size, fact-label treatment, or wrapping are scoped under a unique profile-card class in `pet_info.php`, avoiding changes to global CSS and sibling views.

## Risks / Trade-offs

- [Optional fields can produce an incomplete final grid row] -> Allow natural Bootstrap wrapping; consistent labels and spacing are more important than artificial filler cells.
- [Long chip, breed, owner, or translated action text can wrap] -> Permit wrapping and use stable grid gutters rather than truncating medically relevant data.
- [An overflow dropdown adds one interaction for export and owner transfer] -> Keep edit prominent and use familiar icons and labels inside the dropdown.
- [Escaping stored values could reveal previously hidden literal markup] -> This is intentional safe rendering; preserve only note line breaks and trusted helper output.
- [Local styles embedded in the partial are less reusable] -> Keep them minimal and uniquely scoped because the requested ownership boundary is this partial only.

## Migration Plan

1. Replace the markup in `pet_info.php` while keeping all current route targets and conditions.
2. Verify the card with pets that have complete, missing, and optional data, including notes, RX, and lab states.
3. Check the existing fiche at small, tablet, and desktop widths alongside the vaccines card and sidebar.
4. Roll back by restoring the previous partial; no data or schema rollback is required.
