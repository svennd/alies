## Purpose

Enable veterinary staff to review laboratory payloads that automatic matching could not associate with a patient, recover them safely for a selected pet, or dismiss them without destroying their audit history.

## ADDED Requirements

### Requirement: Active unmatched-result queue
The system SHALL provide authorized veterinary staff with a Lab page containing every pending lab result that is neither resolved nor soft-deleted. Each entry SHALL expose its receipt time, source or device, matching-failure reason, available patient-identifying hints, and an action to inspect the pending result in detail.

#### Scenario: Unmatched results are available
- **WHEN** one or more pending lab results are unresolved and not soft-deleted
- **THEN** the unmatched-results page lists each active result
- **AND** each entry presents the identifiers available to help staff recognize the intended pet and owner

#### Scenario: Queue contains no active results
- **WHEN** every pending lab result is resolved or soft-deleted
- **THEN** the unmatched-results page presents an empty-state message

#### Scenario: Resolved and deleted results are excluded
- **WHEN** a pending result has been resolved or soft-deleted
- **THEN** it is absent from the active unmatched-results page
- **AND** it is excluded from the active unmatched-result navigation count

#### Scenario: An old result remains unresolved
- **WHEN** an unmatched result remains active beyond the existing recent-result period
- **THEN** it remains available in the queue
- **AND** it remains included in the active unmatched-result navigation count

### Requirement: Detailed pending-result inspection
The system SHALL provide authorized veterinary staff with a detailed view of an active pending result. The view SHALL parse supported payloads and present the available report metadata, measurements, values, reference limits, units, plots, source details, and patient-identifying hints in a form comparable to a normal lab report. It SHALL also make the complete retained JSON available in a collapsed technical section.

#### Scenario: Supported payload is inspected
- **WHEN** authorized staff open an active pending result whose payload is supported
- **THEN** the system presents all report details that can be parsed from the payload
- **AND** the retained JSON is available in a collapsed technical section
- **AND** the JSON is not expanded by default

#### Scenario: Payload is only partially understood
- **WHEN** the system cannot parse all or part of an active pending payload
- **THEN** the detail view remains available
- **AND** it clearly indicates which structured details could not be produced
- **AND** the complete retained JSON remains available for investigation

#### Scenario: Raw payload is not exposed outside the detail view
- **WHEN** an unmatched result is shown in the queue or an operation produces a routine user-facing error
- **THEN** the complete retained payload is not included in that output

### Requirement: Separate owner and pet assignment
The system SHALL require authorized veterinary staff to select an existing owner first and then select one eligible pet belonging to that owner when assigning an active pending result. Owner and pet selections SHALL be validated independently and together by the server.

#### Scenario: Staff select an owner
- **WHEN** authorized staff select an existing owner for an active pending result
- **THEN** the interface enables pet selection for that owner
- **AND** offers only pets currently associated with that owner

#### Scenario: Staff select a matching pet
- **WHEN** authorized staff select an eligible pet after selecting its owner
- **THEN** the interface shows the selected owner and pet as separate choices
- **AND** the selected pet can be submitted for recovery

#### Scenario: Staff change the selected owner
- **WHEN** staff change the owner after selecting a pet
- **THEN** the previous pet selection is cleared
- **AND** the interface offers pets belonging to the newly selected owner

#### Scenario: Selected pet is no longer valid
- **WHEN** recovery is submitted for a pet that no longer exists, is not eligible for association, or does not belong to the selected owner
- **THEN** the system refuses the recovery
- **AND** the pending result remains active and unchanged
- **AND** the user receives an actionable error message

#### Scenario: Owner or pet is missing
- **WHEN** recovery is submitted without both a valid owner and a valid pet selection
- **THEN** the system refuses the recovery
- **AND** prompts the user to complete the missing selection

### Requirement: Atomic pending-result recovery
The system SHALL recover a supported active pending result into the normal lab report data associated with the selected pet. Report metadata, individual results, plots when present, and source identity SHALL be preserved, and the recovery SHALL either complete in full or leave both pending and normal report data unchanged.

#### Scenario: Current API payload is recovered
- **WHEN** authorized staff assign a supported current API pending payload to a valid pet
- **THEN** the system creates the normal report and all associated result and plot data for that pet
- **AND** the recovered report becomes available through the existing Lab and pet interfaces
- **AND** the pending record is marked resolved with the report, selected pet, acting user, and resolution time

#### Scenario: Legacy pending payload is recovered
- **WHEN** authorized staff assign a supported legacy-backfill pending payload to a valid pet
- **THEN** the system converts its retained report and detail data into the normal report structure for that pet
- **AND** marks the pending record resolved with the same audit information as a current payload

#### Scenario: Recovery processing fails
- **WHEN** parsing, validation, or persistence fails during recovery
- **THEN** no partial report, result, plot, or resolved-state change remains committed
- **AND** the pending result remains active
- **AND** the user receives an error message without exposing sensitive payload content

#### Scenario: Source identity conflicts with another pet
- **WHEN** the pending payload's source identity already belongs to a normal report associated with a different pet
- **THEN** the system refuses to overwrite or reassign that report
- **AND** leaves the pending result active for further review or soft deletion

#### Scenario: Repeated recovery submission
- **WHEN** a recovery request is repeated after that pending result has already been resolved
- **THEN** the system does not create a duplicate report or duplicate result data
- **AND** informs the user that the pending result is no longer active

### Requirement: Correct recovered report assignment
The system SHALL let authorized veterinary staff change the owner and pet associated with a recovered normal lab report from its detail view by selecting the replacement owner first and then one of that owner's pets. The correction SHALL either update the complete association and related audit data or leave the report unchanged.

#### Scenario: Staff correct an assignment
- **WHEN** authorized staff select a valid replacement owner and pet and confirm the correction
- **THEN** the report is associated with the replacement pet and owner
- **AND** the previous and replacement owner and pet identifiers are recorded in the application audit log

#### Scenario: Correction changes the pet
- **WHEN** a confirmed correction assigns the report to a different pet
- **THEN** every existing event link for that report that belongs to the former pet is removed in the same transaction
- **AND** the confirmation shown before submission warns staff that incompatible event links will be removed

#### Scenario: Correction keeps the same association
- **WHEN** staff submit the report's current owner and pet as the replacement association
- **THEN** the system makes no association or event-link change
- **AND** informs the user that the report is already assigned to that patient

#### Scenario: Replacement pet does not belong to replacement owner
- **WHEN** a correction is submitted with an invalid owner, invalid pet, or owner-pet mismatch
- **THEN** the system refuses the correction
- **AND** the report and its event links remain unchanged
- **AND** the user receives an actionable error message

#### Scenario: Correction persistence fails
- **WHEN** updating the report association, removing incompatible event links, or recording required state fails
- **THEN** no partial correction or event-link removal remains committed
- **AND** the report retains its previous association

### Requirement: Soft deletion of irrecoverable results
The system SHALL let authorized veterinary staff soft-delete an active pending result after explicit confirmation. Soft deletion SHALL retain the payload and record who deleted it and when, while preventing later recovery through the active queue.

#### Scenario: Staff confirm soft deletion
- **WHEN** authorized staff confirm deletion of an active pending result
- **THEN** the system records its deletion time and acting user
- **AND** removes it from the active queue and active count
- **AND** retains its stored payload and audit data

#### Scenario: Staff cancel soft deletion
- **WHEN** staff are shown the deletion confirmation and cancel it
- **THEN** the pending result remains active and unchanged

#### Scenario: Resolved result is submitted for deletion
- **WHEN** a deletion request targets a pending record that has already been resolved
- **THEN** the system refuses to delete the recovered report through this operation
- **AND** leaves the pending record and normal report unchanged

#### Scenario: Repeated deletion submission
- **WHEN** a deletion request targets an already soft-deleted pending record
- **THEN** the system makes no further state change
- **AND** informs the user that the record is no longer active

### Requirement: Restricted lifecycle actions
The system MUST restrict detailed pending-result inspection, recovery, reassignment, and soft deletion to authenticated users authorized to modify veterinary laboratory records, and it SHALL record every successful lifecycle action in the application audit log.

#### Scenario: Authorized lifecycle action
- **WHEN** an authorized authenticated user successfully recovers, reassigns, or soft-deletes a result
- **THEN** the action is recorded with the relevant pending-result or report identifier, action type, acting user, and previous or resulting association identifiers when applicable

#### Scenario: Unauthorized lifecycle action
- **WHEN** a user without permission attempts to inspect a pending payload, recover or soft-delete a pending result, or reassign a recovered report
- **THEN** the system denies the action
- **AND** neither pending nor normal lab data is changed

### Requirement: Single functional recovery entry point
The Lab interface SHALL direct unmatched-result management to the dedicated unmatched-results page and SHALL NOT present assignment or reset controls that lack a functioning server-side action.

#### Scenario: Staff navigate from the Lab area
- **WHEN** active unmatched results exist and staff use the Lab navigation or Lab index
- **THEN** a visible action leads to the unmatched-results page
- **AND** communicates the number of active unmatched results

#### Scenario: Staff inspect a normal report
- **WHEN** staff open an existing normal lab report
- **THEN** the interface provides the supported owner-first, pet-second reassignment action to authorized staff
- **AND** obsolete nonfunctional assignment or reset controls are not displayed
