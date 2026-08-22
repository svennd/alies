## Purpose

Provide veterinary staff with a responsive, scannable pet profile summary that preserves access to all patient facts and profile-related actions on the pet fiche.

## ADDED Requirements

### Requirement: Pet identity is immediately recognizable
The pet fiche profile card SHALL present the pet's species icon, name, record ID, and linked owner identity together in its identity area.

#### Scenario: Staff opens a pet fiche
- **WHEN** a staff member opens `pets/fiche/{id}`
- **THEN** the profile card displays the pet name, species icon, record ID, and an owner link that targets the current owner's detail page

### Requirement: Core pet facts are presented as a responsive summary
The profile card SHALL present breed, type, gender, weight, birth date, age when applicable, and chip number in a fact grid that uses two columns on small screens and three columns on tablet and desktop screens.

#### Scenario: Profile is viewed on a small screen
- **WHEN** the available viewport is below the tablet breakpoint
- **THEN** the pet facts are arranged in two columns without horizontal overflow or overlapping content

#### Scenario: Profile is viewed on a tablet or desktop screen
- **WHEN** the available viewport is at or above the tablet breakpoint
- **THEN** the pet facts are arranged in three columns for rapid scanning

#### Scenario: A core fact has no recorded value
- **WHEN** weight, chip number, or breed data is unavailable
- **THEN** the corresponding fact remains present and displays the established unknown or empty-state marker

#### Scenario: Weight history is requested
- **WHEN** a staff member activates the displayed weight value
- **THEN** the system navigates to the existing weight-history page for that pet

### Requirement: Optional pet facts remain conditional
The profile card SHALL display hair color, hair type, and vaccination book number only when each corresponding value is present.

#### Scenario: Optional facts are available
- **WHEN** one or more optional pet facts contain values
- **THEN** each available fact is added to the responsive fact grid with its localized label

#### Scenario: Optional facts are absent
- **WHEN** an optional pet fact is empty
- **THEN** no empty grid item is displayed for that fact

### Requirement: Existing profile actions remain available
The profile card SHALL retain actions for editing the pet, exporting the record, changing the owner, and opening dental records, and SHALL retain RX and lab actions when those capabilities are available for the pet.

#### Scenario: Staff views standard profile actions
- **WHEN** the profile card is displayed
- **THEN** edit, export, change-owner, and dental actions target their existing routes and remain accessible at all supported viewport sizes

#### Scenario: Pet has RX images
- **WHEN** the fiche data indicates that RX images exist for the pet
- **THEN** an RX action is displayed and targets the existing RX list route for that pet

#### Scenario: Pet has lab reports
- **WHEN** the fiche data indicates that lab reports exist for the pet
- **THEN** a lab action is displayed and targets the existing lab list route for that pet

#### Scenario: Conditional action is unavailable
- **WHEN** the fiche data does not indicate RX images or lab reports for the pet
- **THEN** the corresponding action is not displayed

### Requirement: Pet notes retain prominence and safe formatting
The profile card SHALL display a non-empty pet note as a visually distinct warning area, preserve its line breaks, and render stored note content as text rather than executable markup.

#### Scenario: Pet has a note
- **WHEN** the pet note contains text
- **THEN** the note is displayed in a warning area below the fact grid with line breaks preserved

#### Scenario: Pet has no note
- **WHEN** the pet note is empty
- **THEN** no warning area is displayed

### Requirement: Redesign remains confined to the pet information card
The redesigned profile card SHALL coexist with the current pet fiche grid and SHALL NOT alter the vaccines, medical history, client, weight, birth, nutrition, medication, or other-pets sections.

#### Scenario: Redesigned fiche is loaded
- **WHEN** a staff member opens a pet fiche after the redesign
- **THEN** all surrounding fiche sections retain their existing placement and behavior
