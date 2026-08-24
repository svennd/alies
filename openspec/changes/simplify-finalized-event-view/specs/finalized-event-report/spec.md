## Purpose

Defines a concise review experience for completed veterinary events, including unified report attachments and secure, pet-scoped access to embedded laboratory results.

## ADDED Requirements

### Requirement: Finalized event summary
The system SHALL show a single compact summary line on a closed or finalized event containing the client name, pet name, event date, and event cost. The client and pet names SHALL navigate to their respective records, the date SHALL represent the event rather than invoice creation, and an unavailable cost SHALL be represented explicitly rather than omitted.

#### Scenario: Completed event has billing information
- **WHEN** a staff user opens a closed or finalized event with billing information
- **THEN** the system displays the linked client name, linked pet name, formatted event date, and formatted total event cost on one compact summary line

#### Scenario: Completed event has no available cost
- **WHEN** a staff user opens a closed or finalized event for which no cost can be resolved
- **THEN** the summary line displays a neutral unavailable-cost indicator

#### Scenario: User follows summary links
- **WHEN** a staff user activates the client or pet name in the summary line
- **THEN** the system opens the corresponding client or pet record

### Requirement: Unified finalized report content
The system SHALL present the report editor, upload control, and existing attachments in one continuous finalized-event view without Report, Media, or Files tabs.

#### Scenario: User opens a finalized event
- **WHEN** a staff user opens a closed or finalized event
- **THEN** the report, upload control, and attachments are visible in the main content flow without changing tabs

#### Scenario: User opens an active consultation
- **WHEN** a staff user opens an event whose consultation is still open
- **THEN** the existing open-consultation interface remains unchanged by this capability

### Requirement: Attachment previews
The system SHALL show a compact preview for an uploaded image only when its stored content type is approved for safe inline browser display. Activating the preview SHALL open the image through an authenticated application request. Other attachments SHALL remain accessible through a filename-based file action.

#### Scenario: Safe image is attached
- **WHEN** an attachment has an approved browser-safe image content type
- **THEN** the finalized event shows a small image preview that can be activated to open the image

#### Scenario: Non-previewable file is attached
- **WHEN** an attachment is not an approved browser-safe image
- **THEN** the finalized event shows its filename and a file action without attempting an inline preview

#### Scenario: Existing finalized drawing is attached
- **WHEN** a drawing previously saved as an event image attachment exists
- **THEN** it remains available through the same preview behavior as other eligible image attachments

### Requirement: Drawing creation is unavailable
The system SHALL NOT present drawing tools, a drawing canvas, or drawing-specific save and reset controls on the finalized-event page.

#### Scenario: User reviews finalized event media
- **WHEN** a staff user opens a closed or finalized event
- **THEN** no control is offered to create, modify, save, or reset a drawing

### Requirement: Pet-scoped lab linking
The system SHALL allow staff to link multiple non-deleted lab reports to a closed or finalized event through the event-to-lab relationship. A lab report SHALL be linkable only when it is assigned to the event's pet and is not already linked to that event, and the system MUST enforce those conditions when processing the link request.

#### Scenario: Eligible lab reports are offered
- **WHEN** a staff user opens the lab-link control for a finalized event
- **THEN** the choices contain only non-deleted, not-yet-linked lab reports assigned to that event's pet

#### Scenario: Staff links an eligible lab
- **WHEN** a staff user submits an eligible lab report for the event
- **THEN** the relationship is saved and the lab results become visible on that event

#### Scenario: Crafted request submits another pet's lab
- **WHEN** a link request identifies a lab report that is not assigned to the event's pet
- **THEN** the system rejects the request and does not create the relationship

#### Scenario: Duplicate or deleted lab is submitted
- **WHEN** a link request identifies a lab that is already linked or has been deleted
- **THEN** the system does not create a new relationship

### Requirement: Embedded linked lab results
The system SHALL replace the finalized event's invoice-item sidebar with a lab-results panel that embeds every currently valid linked lab report and its result values. Each lab SHALL identify its sample date and source or device, preserve the established indication for results outside their reference range, and provide a single-click path to the full lab report.

#### Scenario: One lab is linked
- **WHEN** a staff user opens a finalized event with one valid linked lab
- **THEN** the lab panel displays that lab's identifying information and actual result rows
- **AND** activating its header or full-report action opens the complete lab report

#### Scenario: Several labs are linked
- **WHEN** a finalized event has several valid linked labs
- **THEN** the panel displays all of them newest first
- **AND** the newest lab is expanded by default while older labs can be expanded individually

#### Scenario: Result is outside its reference range
- **WHEN** an embedded numeric result is below its minimum or above its maximum reference value
- **THEN** the panel visibly distinguishes that result using the same interpretation as the full lab report

#### Scenario: No lab is linked
- **WHEN** a finalized event has no valid linked labs
- **THEN** the panel presents an empty state and the pet-scoped lab-link control

#### Scenario: Linked lab no longer belongs to the pet
- **WHEN** a previously linked lab is reassigned away from the event's pet or is deleted
- **THEN** its results are not disclosed on the finalized event

### Requirement: Lab unlinking
The system SHALL allow staff to unlink a lab report from a finalized event after confirmation without deleting the lab report or changing its pet assignment.

#### Scenario: Staff confirms unlinking
- **WHEN** a staff user confirms removal of a linked lab from an event
- **THEN** only the event-to-lab relationship is removed
- **AND** the lab report remains assigned to its pet and available elsewhere

#### Scenario: Staff cancels unlinking
- **WHEN** a staff user cancels the unlink confirmation
- **THEN** the event-to-lab relationship remains unchanged

