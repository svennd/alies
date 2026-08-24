## ADDED Requirements

### Requirement: Linked lab indication
Each pet medical-history entry SHALL indicate when its event has one or more currently valid linked lab reports. The indicator SHALL use a recognizable laboratory symbol, display the number of valid linked reports, provide an accessible text label, and navigate to the event's linked-lab results.

#### Scenario: Event has one valid linked lab
- **WHEN** a medical-history event has one linked lab report that is assigned to the event's pet and is not deleted
- **THEN** its entry header displays a linked-lab indicator with a count of `1`
- **AND** the indicator identifies itself as lab results without relying on the icon alone

#### Scenario: Event has several valid linked labs
- **WHEN** a medical-history event has several valid linked lab reports
- **THEN** its linked-lab indicator displays the total number of those reports
- **AND** the entry remains represented only once in the history feed

#### Scenario: User follows the linked-lab indicator
- **WHEN** a user activates the linked-lab indicator
- **THEN** the system opens that event at its linked-lab results panel

#### Scenario: Event has no valid linked labs
- **WHEN** a medical-history event has no valid linked lab reports
- **THEN** its entry header does not display a linked-lab indicator

#### Scenario: Event has stale lab relationships
- **WHEN** an event-to-lab relationship points to a deleted lab report or a report no longer assigned to the event's pet
- **THEN** that relationship is excluded from the indicator count
- **AND** the relationship does not by itself cause the indicator to appear

#### Scenario: Linked lab indicator on a phone-sized viewport
- **WHEN** an entry with valid linked labs is displayed on a phone-sized viewport
- **THEN** the linked-lab indicator remains available with the entry's compact actions

