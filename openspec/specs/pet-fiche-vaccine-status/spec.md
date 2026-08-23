# Pet Fiche Vaccine Status Specification

## Purpose

Define a compact, responsive vaccine summary that communicates each vaccine's injection and rappel dates together with its current urgency on the pet fiche.

## Requirements

### Requirement: Vaccine summary presents individual status blocks
The pet fiche SHALL present every vaccine in its summary as an individual, visually distinct status block containing the vaccine name, injection date, and rappel date.

#### Scenario: Pet has vaccine records
- **WHEN** a user opens the fiche for a pet with one or more current vaccine summary records
- **THEN** the system displays one status block for each record
- **AND** each block displays the vaccine name, formatted injection date, and formatted rappel date

#### Scenario: Pet has no vaccine records
- **WHEN** a user opens the fiche for a pet with no current vaccine summary records
- **THEN** the system displays the existing localized no-vaccines message

### Requirement: Vaccine status reflects rappel urgency
The pet fiche SHALL assign exactly one urgency state to each vaccine by comparing its rappel date with the current date: danger when the rappel date is before the current date, warning when it is from the current date through three months after the current date inclusive, and success when it is later than that three-month boundary.

#### Scenario: Rappel date has passed
- **WHEN** a vaccine's rappel date is before the current date
- **THEN** its status block is presented in the danger state

#### Scenario: Rappel is due today
- **WHEN** a vaccine's rappel date is the current date
- **THEN** its status block is presented in the warning state

#### Scenario: Rappel is due within three months
- **WHEN** a vaccine's rappel date is after the current date and on or before the date three months from the current date
- **THEN** its status block is presented in the warning state

#### Scenario: Rappel is due later
- **WHEN** a vaccine's rappel date is later than the date three months from the current date
- **THEN** its status block is presented in the success state

### Requirement: Vaccine summary remains navigable and responsive
The pet fiche SHALL retain access from the vaccine summary heading to the pet's full vaccine fiche, and the status blocks SHALL remain legible without horizontal scrolling at supported pet-fiche viewport widths.

#### Scenario: User opens the full vaccine fiche
- **WHEN** a user activates the vaccine summary heading link
- **THEN** the system opens the full vaccine fiche for the current pet

#### Scenario: Summary is displayed in a narrow sidebar
- **WHEN** the available width causes a vaccine name or its dates to exceed one line
- **THEN** the content wraps or adapts within its status block without horizontal scrolling or overlap
