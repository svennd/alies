## Purpose

Ensure that a pet remains clinically complete after an ownership transfer while preserving the former owner's historical invoices and other accounting relationships.

## ADDED Requirements

### Requirement: Ownership transfer preserves the current patient record
The system SHALL create the successor pet under the selected new owner and SHALL preserve the source pet's identification, medication, nutrition, current weight, vaccination-book number, and avatar data on that successor.

#### Scenario: Pet is transferred successfully
- **WHEN** authorized staff completes an ownership transfer to a valid new owner
- **THEN** the system creates an active successor pet belonging to the new owner
- **AND** the successor contains the source pet's clinical and identification fields supported by the pet record
- **AND** the source pet is retained as a hidden transferred record for historical accounting

### Requirement: Direct medical records move to the successor pet
The system SHALL move the source pet's vaccination records, weight measurements, dental chart, dental history, RX images, legacy laboratory records, and API laboratory reports to the successor pet without duplicating or deleting their clinical content.

#### Scenario: Source pet has direct medical records
- **WHEN** a pet with one or more supported direct medical records is transferred successfully
- **THEN** each supported record is associated with the successor pet
- **AND** it is no longer associated with the hidden source pet
- **AND** records already created for the successor remain present

#### Scenario: Vaccination reminder is evaluated after transfer
- **WHEN** a moved vaccination record is eligible for a reminder
- **THEN** the reminder resolves through the successor pet and its current owner or configured companion
- **AND** the hidden source pet does not produce a duplicate reminder for that vaccination

### Requirement: Visible medical history is copied as non-billable history
The system SHALL copy each source event eligible for medical-history display to the successor pet as a finalized historical event that cannot be attached to an invoice.

#### Scenario: Visible source event is copied
- **WHEN** a successfully transferred pet has an event with medical-history visibility enabled
- **THEN** the successor receives a historical event with the source title, report content, event type, veterinarians, location, and original event date
- **AND** the copied event is finalized
- **AND** the copied event uses the established invalid-payment value so it is not billable

#### Scenario: Hidden source event is encountered
- **WHEN** a source event has medical-history visibility disabled
- **THEN** the system does not create a history copy of that event for the successor

### Requirement: Product and procedure details become clinical summary text
The system SHALL append the source event's product and procedure names, recorded quantities, and units to the copied event's report text while excluding financial and former-owner information.

#### Scenario: Source event contains products and procedures
- **WHEN** a visible source event contains one or more product or procedure lines
- **THEN** the copied event report includes readable product and procedure sections
- **AND** each available item includes its name, recorded quantity, and unit where available
- **AND** the original report content remains present

#### Scenario: Source event has no item lines
- **WHEN** a visible source event has no products or procedures
- **THEN** the copied event preserves the original report without empty product or procedure sections

#### Scenario: Summary event is persisted
- **WHEN** the system saves a copied medical-history event
- **THEN** it does not copy event product rows, event procedure rows, prices, booking codes, stock references, invoice identifiers, or former-owner details

### Requirement: Historical accounting remains unchanged
The system MUST retain each source event and all of its existing invoice and accounting relationships on the hidden source pet.

#### Scenario: Billed event is represented in successor history
- **WHEN** a source event belongs to an existing bill and a history copy is created
- **THEN** the original event remains linked to its original pet and bill
- **AND** the copied event has no billable child lines
- **AND** the copied event is excluded from future invoice collection

### Requirement: Laboratory event context follows copied history
The system SHALL retain laboratory reports as patient records on the successor and SHALL associate an API laboratory report with the corresponding copied history event when that association existed on the source event.

#### Scenario: Source event has a linked API laboratory report
- **WHEN** the laboratory report is moved to the successor and its visible source event is copied
- **THEN** the copied event is linked to that laboratory report
- **AND** the report is available through the successor pet's laboratory views

#### Scenario: Laboratory report has no visible linked event
- **WHEN** a laboratory report is moved but has no link to an event eligible for copying
- **THEN** the report remains available through the successor pet's laboratory views without creating an artificial event association

### Requirement: Transfer is atomic
The system MUST perform successor creation, medical-record movement, history copying, laboratory relinking, and source-pet hiding in one database transaction.

#### Scenario: Every transfer operation succeeds
- **WHEN** all required ownership-transfer operations complete successfully
- **THEN** the system commits the successor and all related changes together

#### Scenario: Any transfer operation fails
- **WHEN** successor creation or any required medical-record or history operation fails
- **THEN** the system rolls back the complete ownership transfer
- **AND** the source pet and its medical records remain in their pre-transfer state

### Requirement: Existing transferred pets are backfilled safely
The system SHALL provide a one-time migration that identifies each historical source pet and its unique successor, then applies the same medical-record movement and non-billable history-copy behavior as a new transfer.

#### Scenario: Historical transfer has one deterministic successor
- **WHEN** the migration finds exactly one successor matching the stored target owner, pet identity, transfer markers, and transfer timing
- **THEN** it moves the supported direct medical records
- **AND** it creates the eligible non-billable history copies
- **AND** it restores eligible API laboratory event associations

#### Scenario: Historical transfer cannot be matched uniquely
- **WHEN** any historical source pet has no deterministic successor or has multiple possible successors
- **THEN** the migration aborts without committing partial backfill changes
- **AND** it reports the unresolved source record for manual investigation

#### Scenario: Backfill executes successfully
- **WHEN** every historical source pet has a unique successor and all backfill operations succeed
- **THEN** the migration commits all backfill changes in one transaction
- **AND** existing medical records already belonging to a successor remain unchanged
