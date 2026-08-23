## Context

See `proposal.md` for motivation and `specs/pet-medical-history/spec.md` for observable behavior. The current pet fiche receives the full medical history from `Events_model::get_pet_history()` in descending creation order. The view renders every record in a DataTable, including clinical details in responsive child content, and initializes DataTables locally with custom type buttons.

The project uses CodeIgniter 3 views, Bootstrap 4, jQuery, and Font Awesome. The model payload contains event type, raw primary and supporting veterinarian fields, location, report text, products, procedures, and `upload_count`. The initial implementation independently derives displayed veterinarian names and filter options from those raw fields; because option creation requires both a usable ID and name while display only requires a name, the two can diverge. No network request is needed for filtering or incremental display.

## Goals / Non-Goals

**Goals:**

- Implement the feed as a self-contained enhancement of the pet history view using assets already loaded by the application.
- Keep server-rendered content and links functional when JavaScript-enhanced filtering and accordion behavior are applied.
- Make filtering deterministic across all history records while limiting the number of visible cards.
- Ensure veterinarian display, filter options, and filter matching consume one normalized assignment collection.
- Keep CSS and DOM identifiers scoped so this block cannot interfere with other fiche components.

**Non-Goals:**

- Removing DataTables assets globally; other application pages still use them.
- Changing event types, database schema, report authoring, or medical-history retrieval APIs.
- Adding server-side pagination or asynchronous history loading.
- Redesigning the rest of the pet fiche to match the Tailwind prototype.
- Sanitizing or restructuring historical report HTML beyond the application's existing trust model.

## Decisions

### Render a server-side card feed with client-side enhancement

PHP will render one semantic card per history entry. Each card will carry escaped data attributes for its type and all assigned veterinarian IDs. A small block-local jQuery script will manage filters, visibility limits, and accordion state.

This preserves the current request lifecycle and avoids a new endpoint. A server-side filtered or AJAX feed was considered, but it would add controller/API complexity while the page already retrieves the complete collection.

### Use a semantic metadata button with an explicit accordion controller

Each entry will start collapsed. Its date, type, title, veterinarian names, and location will be grouped in a semantic full-width metadata button with `aria-expanded`, `aria-controls`, and a uniquely identified detail panel. Header actions remain siblings of that button so edit and attachment links are never nested inside another interactive element. The desktop eye button invokes the same toggle behavior, while activating an action link does not toggle the entry.

The script will centrally open or close panels, keeping at most one open. Bootstrap's `data-parent` accordion and a clickable non-semantic container were considered, but the explicit controller and native button preserve keyboard behavior and coordinate filtered and incrementally hidden cards.

### Apply filters to the complete rendered collection before the 10-item limit

The script will calculate matches from both filters, preserve model order, display the first 10 matches, and reveal 10 more per activation. Changing a filter resets the limit to 10 and closes all panels. The initial page render also leaves all panels closed.

This replaces DataTables' visual paging without changing model retrieval. Rendering only 10 records in PHP was rejected because client-side filters must consider all available records and no server API is planned.

### Keep the type filter deliberately limited

The type filter will expose only `Alle`, `Ziekte`, and `Operaties`. `Alle` matches every numeric event type so legacy medicine and laboratory history is not lost. The other choices match the existing disease and operation constants.

This avoids presenting unused workflow choices while retaining historical completeness.

### Normalize veterinarian assignments before rendering

The history query will explicitly alias the IDs and names of the primary and both supporting veterinarians. Each returned event will then receive one normalized `veterinarians` collection. A veterinarian entry uses an ID-based filter token when a positive ID is available and a normalized name-based fallback token when legacy or inconsistent data provides a display name without a usable ID.

The view will use that same collection to render header names, build the deduplicated name-sorted dropdown, and populate each card's filter tokens. This guarantees that every displayed veterinarian is filterable. Matching only raw ID fields was rejected because it produced the observed mismatch; matching only display names was rejected because names are not guaranteed to be unique or stable.

### Preserve actions while correcting attachment payload usage

Edit/event navigation and the unfinished-report indicator remain available in the card summary or expanded actions. Attachment access will be rendered only when numeric `upload_count` is greater than zero and will link to the event's files anchor. This aligns the view with the current model payload instead of relying on the obsolete `uploads[0].counted_rows` shape.

### Put location in the summary and scope responsive styling to the history component

The visual language will approximate the `index2.html` example using Bootstrap cards, spacing utilities, borders, subtle background differences, and Font Awesome icons. Location will appear in the summary metadata and no longer be repeated in the expanded panel. Component-prefixed classes and a narrow-viewport media query will move date/title, veterinarian/location metadata, and remaining actions into stacked rows.

At phone width the filter container, edit action, and separate eye button will be hidden. The semantic metadata button remains the expansion target, and attachment access remains visible. Hiding all actions or removing keyboard semantics from the clickable header was rejected.

Copying the prototype's Tailwind utility classes was rejected because Tailwind is not part of the production view stack.

### Keep user-facing controls localized

Existing language lines will be reused for shared concepts such as veterinarian, location, disease, operation, and no history. Missing labels for the medical-history heading, all-type choice, all-veterinarian choice, filtered empty state, reset action, and show-more action will be added to both Dutch and English veterinary language files.

Hardcoding all labels in Dutch was considered but rejected because the surrounding fiche already follows the active application language.

## Risks / Trade-offs

- [All history records and their detail content remain in the DOM] → Preserve the current retrieval model, cap visible cards to 10, and keep detail panels collapsed; consider server-side loading only if real pet histories demonstrate a performance problem.
- [The model currently performs per-event product and procedure queries] → Do not expand query scope in this UI change; verify representative large histories and record follow-up optimization separately if needed.
- [Rich report content can contain complex HTML] → Place it inside a dedicated detail container and retain the existing rendering/trust behavior so clinical formatting is not lost.
- [Unknown or deleted veterinarians can lack identifiers or names] → Use a name-based filter token when a displayed name lacks an ID; render the existing unknown label without a filter option only when no name is available.
- [A name-based fallback token can conflate legacy veterinarians with identical names] → Prefer explicit aliased IDs whenever present and use the fallback only when a displayed name otherwise could not be filtered.
- [Inline component CSS/script can grow] → Keep both strictly scoped; extraction into shared assets is only warranted if a second screen adopts the component.

## Migration Plan

1. Normalize primary and supporting veterinarian assignments in the existing history payload.
2. Revise the existing feed header, initial accordion state, filtering behavior, and phone controls.
3. Verify the feed with no history, fewer than 10 entries, more than 10 entries, legacy event types, multiple displayed veterinarians, unfinished reports, and attachments.
4. Verify desktop and narrow responsive layouts and keyboard/ARIA state.
5. Deploy without a database migration or cache conversion.

Rollback consists of restoring the previous history view; controller and model contracts remain compatible apart from the view correcting its use of the already-present `upload_count` field.
