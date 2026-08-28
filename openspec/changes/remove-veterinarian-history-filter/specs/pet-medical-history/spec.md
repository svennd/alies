## MODIFIED Requirements

### Requirement: Event-type filtering
The system SHALL provide event type as the only medical-history filter, with the choices `Alle`, `Ziekte`, and `Operaties` and with `Alle` selected initially.

#### Scenario: History filtering controls are displayed
- **WHEN** a user views medical history on a viewport where filtering controls are available
- **THEN** the system displays the event-type filter
- **AND** it does not display a veterinarian filter

#### Scenario: All types are selected
- **WHEN** the `Alle` filter is active
- **THEN** all medical-history event types are eligible for display, including legacy medicine and laboratory entries

#### Scenario: Disease is selected
- **WHEN** the user selects `Ziekte`
- **THEN** only disease entries are displayed

#### Scenario: Operations are selected
- **WHEN** the user selects `Operaties`
- **THEN** only operation entries are displayed

#### Scenario: Legacy event types exist
- **WHEN** medicine or laboratory history entries exist
- **THEN** those entries remain available through `Alle`
- **AND** the system does not offer separate medicine or laboratory filter choices

### Requirement: Filter state updates the expanded entry
After the event-type filter changes, the system SHALL evaluate the complete history collection, reset incremental display, and leave every matching entry collapsed.

#### Scenario: Filters return matching entries
- **WHEN** a user changes the event-type filter and one or more entries match the selected type
- **THEN** the first batch of matching entries is displayed newest first
- **AND** every matching entry remains collapsed

#### Scenario: Filters return no entries
- **WHEN** no entries match the selected event type
- **THEN** the system shows a filtered empty-state message
- **AND** it offers a way to restore the default event type

### Requirement: Incremental display of long histories
The system SHALL initially display at most 10 entries matching the selected event type and SHALL allow the user to reveal subsequent matching entries in batches of at most 10.

#### Scenario: More than 10 entries match
- **WHEN** more than 10 entries match the selected event type
- **THEN** only the newest 10 matching entries are initially visible
- **AND** a `Toon meer` control is available

#### Scenario: User requests more entries
- **WHEN** the user activates `Toon meer`
- **THEN** up to the next 10 matching entries become visible in chronological order
- **AND** already visible entries retain their order

#### Scenario: All matching entries are visible
- **WHEN** every entry matching the selected event type is visible
- **THEN** the `Toon meer` control is hidden

### Requirement: Empty and responsive presentation
The history feed SHALL distinguish a pet with no medical history from an event-type filter with no matches and SHALL remain usable on narrow viewports and by keyboard.

#### Scenario: Pet has no medical history
- **WHEN** the pet has no medical-history entries
- **THEN** the existing no-history message is displayed without filter or pagination controls

#### Scenario: User views history on a narrow viewport
- **WHEN** the available width cannot accommodate the desktop summary layout
- **THEN** summary metadata and actions reflow without horizontal scrolling or loss of controls

#### Scenario: User views history on a phone-sized viewport
- **WHEN** the history feed is displayed on a phone-sized viewport
- **THEN** the event-type filter is hidden
- **AND** the edit action and separate eye toggle are hidden
- **AND** the entry header remains available to expand or collapse the entry
- **AND** any attachment action remains available

#### Scenario: User navigates with a keyboard
- **WHEN** keyboard focus reaches an entry header toggle
- **THEN** the user can expand or collapse the entry using standard button activation
- **AND** the header toggle exposes whether its panel is expanded
- **AND** activating an edit or attachment action does not also toggle the entry

## REMOVED Requirements

### Requirement: Veterinarian filtering
**Reason**: Medical history is now filtered only by event type, so veterinarian filtering is no longer part of the workflow.

**Migration**: Remove the veterinarian filter control and its client-side matching state. Veterinarian names remain visible in entry summaries and require no data migration.
