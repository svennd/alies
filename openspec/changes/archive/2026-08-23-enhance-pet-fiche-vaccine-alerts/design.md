## Context

See `proposal.md` for motivation and `specs/pet-fiche-vaccine-status/spec.md` for observable behavior. The pet fiche receives a vaccine summary array from the existing model and renders it in a narrow Bootstrap 4/SB Admin 2 sidebar card. The current view uses a table and performs a single three-month comparison inline.

## Goals / Non-Goals

**Goals:**

- Derive a deterministic danger, warning, or success state from each rappel date.
- Translate the visual structure demonstrated in `vaccin.html` into the project's existing Bootstrap 4 design system.
- Keep names and date pairs legible at narrow widths.
- Keep the change local to the pet-fiche vaccine partial.

**Non-Goals:**

- Changing vaccine storage, selection, ordering, or reminder scheduling.
- Redesigning the full vaccine fiche or the surrounding pet fiche.
- Introducing Tailwind CSS, Material Symbols, JavaScript behavior, or a new dependency from the reference mock-up.

## Decisions

### Use Bootstrap alert variants as persistent status blocks

Each vaccine will render as its own compact block using the existing `alert-success`, `alert-warning`, or `alert-danger` visual variant, with spacing and flex/wrapping utilities already available in Bootstrap 4. The vaccine name will be the primary line and the formatted injection-to-rappel date pair the secondary line.

This reuses the project's established CSS and captures the reference design without importing its Tailwind implementation. Keeping the table and merely coloring a cell was rejected because it does not provide the requested alert-like scanability.

### Compare calendar-day boundaries explicitly

The view will establish a current-day boundary and a separate boundary three calendar months later. Each rappel value will be converted to a date value before classification:

- rappel before the current day: danger;
- rappel from the current day through the three-month boundary, inclusive: warning;
- rappel after the three-month boundary: success.

Explicit branching prevents an expired vaccine from being absorbed into the broader “within three months” condition. Calendar-day comparison also avoids the display changing partway through a day because of time components.

### Preserve the existing view contract

The implementation will consume the existing `name`, `max_injection`, and `max_rappel` fields and continue using `user_format_date` with the user's configured date format. It will retain the heading link and localized empty-state string. Dynamic vaccine names will be escaped when rendered.

Changing the model to return a computed status was considered but rejected because this is currently a fiche-specific presentation rule and the existing data already supports it.

## Risks / Trade-offs

- **[Dense content in the extra-narrow XL sidebar]** → Allow the date row and long names to wrap rather than forcing fixed columns or truncating essential dates.
- **[Month arithmetic differs near month ends]** → Use the runtime's established calendar-month date behavior consistently and cover the inclusive computed boundary in verification.
- **[Bootstrap alerts can imply transient notifications]** → Use their visual variants as static status blocks without dismissal behavior or unnecessary live-announcement semantics.
- **[Unexpected or invalid date values]** → Preserve the existing data contract; verify representative records and avoid expanding this visual change into data-repair behavior.

## Migration Plan

1. Update only the pet-fiche vaccine partial.
2. Verify the three urgency states, empty state, link target, localized date formatting, and responsive wrapping.
3. Roll back by restoring the prior partial; no persisted data or schema migration is involved.
