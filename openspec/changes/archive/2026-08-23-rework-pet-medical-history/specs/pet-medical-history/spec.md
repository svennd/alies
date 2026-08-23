## Purpose

Provide clinicians with a chronological, expandable medical-history feed on the pet fiche that makes recent records easy to scan and supports focused filtering without relying on a DataTable.

## ADDED Requirements

### Requirement: Chronological expandable history feed
The system SHALL present pet medical-history entries as a card-based feed ordered from newest to oldest, initially collapsed, with at most one entry expanded at a time.

#### Scenario: Pet has medical history
- **WHEN** a user opens the fiche of a pet that has one or more medical-history entries
- **THEN** the system displays the entries from newest to oldest
- **AND** every visible entry is collapsed

#### Scenario: User opens another entry
- **WHEN** a user activates the header of a collapsed medical-history entry
- **THEN** the selected entry is expanded
- **AND** any previously expanded entry is collapsed

#### Scenario: User closes the expanded entry
- **WHEN** a user activates the header of the currently expanded entry
- **THEN** the entry is collapsed
- **AND** no other entry is opened automatically

### Requirement: Compact entry summary
Each entry header SHALL show its date, event-type symbol, title, involved veterinarian names, location when available, report status, and available actions.

#### Scenario: Entry contains a location
- **WHEN** a medical-history entry has a location
- **THEN** its header displays that location alongside the other summary metadata

#### Scenario: Entry report is unfinished
- **WHEN** a medical-history entry does not have the completed report status
- **THEN** the summary visibly identifies the report as unfinished

### Requirement: Expanded clinical details
An expanded entry SHALL reveal its clinical report, used products, and performed procedures when those values are available, while edit navigation and attachment access remain available as header actions where permitted by the viewport.

#### Scenario: Entry with full clinical data is expanded
- **WHEN** a user expands an entry containing report text, products, procedures, and attachments
- **THEN** the expanded panel displays the report text
- **AND** it lists the products and procedures with their recorded quantities and units where available
- **AND** the entry header provides navigation to edit or inspect the event when that action is available at the current viewport
- **AND** the entry header provides attachment access with the attachment count

#### Scenario: Optional clinical data is absent
- **WHEN** a user expands an entry that has no report text, products, procedures, or attachments
- **THEN** the panel omits empty detail groups and attachment action without producing broken controls or layout

### Requirement: Event-type filtering
The system SHALL provide the type choices `Alle`, `Ziekte`, and `Operaties`, with `Alle` selected initially.

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

### Requirement: Veterinarian filtering
The system SHALL provide an `Alle dierenartsen` choice and exactly one choice for each distinct veterinarian displayed in the pet's available medical-history entry headers.

#### Scenario: Multiple veterinarians are displayed
- **WHEN** the available history headers collectively display multiple distinct primary or supporting veterinarians
- **THEN** the veterinarian filter contains exactly one option for each displayed veterinarian
- **AND** no displayed veterinarian is omitted because of a missing or inconsistently shaped raw identifier field

#### Scenario: User filters by veterinarian
- **WHEN** the user selects a veterinarian
- **THEN** the system displays entries where that person is the primary veterinarian or a supporting veterinarian
- **AND** it combines this constraint with the active event-type filter

#### Scenario: All veterinarians are selected
- **WHEN** `Alle dierenartsen` is active
- **THEN** the veterinarian filter does not exclude any entry

### Requirement: Filter state updates the expanded entry
After a filter changes, the system SHALL evaluate the complete history collection, reset incremental display, and leave every matching entry collapsed.

#### Scenario: Filters return matching entries
- **WHEN** a user changes either filter and one or more entries match both active filters
- **THEN** the first batch of matching entries is displayed newest first
- **AND** every matching entry remains collapsed

#### Scenario: Filters return no entries
- **WHEN** no entries match the active filters
- **THEN** the system shows a filtered empty-state message
- **AND** it offers a way to restore the default filters

### Requirement: Incremental display of long histories
The system SHALL initially display at most 10 matching entries and SHALL allow the user to reveal subsequent matching entries in batches of at most 10.

#### Scenario: More than 10 entries match
- **WHEN** more than 10 entries match the active filters
- **THEN** only the newest 10 matching entries are initially visible
- **AND** a `Toon meer` control is available

#### Scenario: User requests more entries
- **WHEN** the user activates `Toon meer`
- **THEN** up to the next 10 matching entries become visible in chronological order
- **AND** already visible entries retain their order

#### Scenario: All matching entries are visible
- **WHEN** every matching entry is visible
- **THEN** the `Toon meer` control is hidden

### Requirement: Empty and responsive presentation
The history feed SHALL distinguish a pet with no medical history from a filter with no matches and SHALL remain usable on narrow viewports and by keyboard.

#### Scenario: Pet has no medical history
- **WHEN** the pet has no medical-history entries
- **THEN** the existing no-history message is displayed without filter or pagination controls

#### Scenario: User views history on a narrow viewport
- **WHEN** the available width cannot accommodate the desktop summary layout
- **THEN** summary metadata and actions reflow without horizontal scrolling or loss of controls

#### Scenario: User views history on a phone-sized viewport
- **WHEN** the history feed is displayed on a phone-sized viewport
- **THEN** the type and veterinarian filters are hidden
- **AND** the edit action and separate eye toggle are hidden
- **AND** the entry header remains available to expand or collapse the entry
- **AND** any attachment action remains available

#### Scenario: User navigates with a keyboard
- **WHEN** keyboard focus reaches an entry header toggle
- **THEN** the user can expand or collapse the entry using standard button activation
- **AND** the header toggle exposes whether its panel is expanded
- **AND** activating an edit or attachment action does not also toggle the entry
