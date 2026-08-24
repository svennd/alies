## Context

See `proposal.md` for motivation. The pet fiche obtains all history rows through `Events_model::get_pet_history()` and renders attachment counts as conditional header actions. Lab relationships use the many-to-many `events_labs` table, while displayable reports come from `lab_report` and are considered valid only when they are non-deleted and still assigned to the event's pet. The finalized-event lab panel currently has no stable fragment identifier.

The change spans history data assembly and two views but does not require schema changes. It must avoid multiplying event rows when several labs are linked and must not expose stale or pet-mismatched relationships.

## Goals / Non-Goals

**Goals:**

- Derive the valid linked-lab count as part of the existing history fetch rather than issuing one query per rendered event.
- Apply the same pet and soft-delete validity rules used by finalized-event lab presentation.
- Make the compact action understandable to assistive technology and usable at phone widths.
- Provide deterministic navigation from the history indicator to the finalized event's lab panel.

**Non-Goals:**

- Display lab result values directly inside the pet-history entry.
- Add a lab-specific history filter or alter legacy laboratory event types.
- Change lab linking, unlinking, ingestion, deletion, or pet assignment.
- Clean up stale junction rows or change the `events_labs` schema.

## Decisions

### Calculate a validity-filtered count in the history query

Extend the history selection with a correlated aggregate that counts `events_labs` rows joined to `lab_report` for the current event, restricted to the event's pet and to reports whose deletion timestamp is null. This follows the existing attachment-count pattern, returns one scalar per event, and cannot duplicate history rows when an event has multiple labs.

Loading linked labs once per event was rejected because it introduces an additional N+1 query path solely to render a count. A direct join with grouping was rejected because the history query selects full event and veterinarian data and would become more fragile under SQL grouping modes.

### Render the lab signal as a counted action

When the count is positive, render an action beside the existing attachment action using the application's established flask icon, the numeric count, and the existing localized lab-results text as a screen-reader label. Suppress the action entirely for zero valid links. Treat either attachments or linked labs as sufficient to retain the compact action area on phones.

A decorative icon in the title was rejected because it would not offer direct navigation, would be ambiguous to screen-reader users, and would not communicate multiple linked reports.

### Navigate with a stable fragment target

Give the finalized-event lab-results section a stable page identifier and point the fiche action to the existing event route with that fragment. This retains server-rendered navigation and works without additional JavaScript.

Linking to the event page without a fragment was rejected because the lab panel may sit below substantial report content. Linking directly to one full lab report was rejected because an event can have several linked reports and the indicator represents the relationship collection.

### Reuse existing localization

Use the existing localized `event_labs` label for accessible text. No new visible wording is necessary because the flask symbol and count form the compact visual presentation.

Adding a new translation key was rejected unless implementation reveals that the existing label is grammatically unsuitable in context.

## Risks / Trade-offs

- [The correlated count adds work to a history query that can return many events] → Keep it within the single history query, rely on the junction table's composite primary key, and verify query behavior with multiple links rather than performing per-event lookups.
- [Stale junction rows could produce misleading indicators] → Join the report record and apply both pet ownership and soft-delete constraints when counting.
- [The compact action row could become crowded on small screens] → Reuse the existing small action styling and hide the action when its count is zero.
- [Fragment navigation depends on the finalized-event layout] → Put the stable identifier on the outer lab-results section and cover the generated URL and target with focused view tests.

## Migration Plan

1. Add the validity-filtered lab count to pet-history data assembly.
2. Add the stable lab-panel identifier and render the counted fiche action.
3. Run focused model/view tests, the relevant existing finalized-event tests, and PHP syntax checks.

No data migration is required. Rollback removes the scalar selection, fiche action, and fragment identifier without changing stored relationships.
