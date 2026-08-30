## Purpose

Ensure repeated deliveries of one laboratory report remain a single manageable item and update the correct retained or recovered record without losing lifecycle decisions.

## ADDED Requirements

### Requirement: Stable laboratory source identity
The system SHALL treat a non-empty source report identifier together with its device identity, or its source identity when no device identity is available, as the stable identity of an imported laboratory report. The system SHALL NOT infer a stable identity solely from payload contents or patient-identifying fields.

#### Scenario: Device and source identifier are available
- **WHEN** a parsed laboratory delivery contains a device identity and a non-empty source report identifier
- **THEN** the system uses that pair as the report's stable source identity

#### Scenario: Only source and source identifier are available
- **WHEN** a parsed laboratory delivery has no device identity but contains a source identity and a non-empty source report identifier
- **THEN** the system uses the source and source-identifier pair as the stable source identity

#### Scenario: No stable source identifier is available
- **WHEN** an unmatched delivery has no non-empty source report identifier
- **THEN** the system retains it as a new pending record without attempting content-based deduplication

### Requirement: Repeated active unmatched delivery
The system SHALL retain at most one active pending record for a stable laboratory source identity. A repeated unmatched delivery SHALL refresh that active record with the newest raw payload, matching identifiers, failure reason, and last-received time while preserving its original first-received time.

#### Scenario: An active pending record receives another delivery
- **WHEN** an unmatched laboratory delivery has the same stable source identity as an active pending record
- **THEN** the existing pending record is refreshed
- **AND** no additional active queue entry is created
- **AND** the ingestion response remains `status: pending`

#### Scenario: Concurrent unmatched deliveries share an identity
- **WHEN** two unmatched deliveries with the same stable source identity are processed concurrently
- **THEN** the active queue contains exactly one pending record for that identity after both requests complete
- **AND** that record contains one of the complete delivered payloads rather than a partial combination

#### Scenario: Staff review a repeatedly received pending report
- **WHEN** an active pending report has been delivered more than once
- **THEN** the unmatched queue and pending detail identify the latest receipt time
- **AND** the retained lifecycle data preserves when the report was first received

### Requirement: Repeated delivery of a recovered report
The system SHALL use the existing patient assignment of a normal report when a repeated delivery has the same stable source identity, even when the repeated delivery cannot independently resolve a patient. It SHALL refresh the existing report and SHALL NOT create an active pending record.

#### Scenario: Recovered report remains unmatchable upstream
- **WHEN** a delivery cannot resolve a patient from its current identifiers
- **AND** a normal report with the same stable source identity already exists
- **THEN** the system refreshes that normal report using its existing patient assignment
- **AND** returns `status: ok`
- **AND** creates no active pending record

#### Scenario: Source identity belongs to one existing report
- **WHEN** a repeated delivery refreshes a normal report by stable source identity
- **THEN** the system preserves the report identifier and current patient assignment
- **AND** atomically replaces its report data with the newly delivered data

### Requirement: Later automatic patient match
When a delivery with a stable source identity can be matched automatically and an active pending record already exists for that identity, the system SHALL persist the normal report and resolve the active pending record to that report as one atomic operation.

#### Scenario: Previously unmatched delivery becomes matchable
- **WHEN** a later delivery of an active pending identity uniquely resolves to a patient
- **THEN** the system creates or refreshes the normal report for that patient
- **AND** marks the pending record resolved to the normal report and patient
- **AND** removes the pending record from the active unmatched queue

#### Scenario: Automatic persistence fails
- **WHEN** persistence of the automatically matched report or resolution of its pending record fails
- **THEN** neither operation is partially committed
- **AND** the pending record remains active with its previously valid retained data

### Requirement: Dismissed identity remains dismissed
The system SHALL NOT recreate an active pending record when an unmatched delivery has the same stable source identity as a dismissed pending record. A later delivery that can be matched automatically SHALL still be eligible for normal report ingestion.

#### Scenario: Dismissed report is delivered unmatched again
- **WHEN** an unmatched delivery has the same stable source identity as a dismissed pending record
- **THEN** the identity remains absent from the active unmatched queue
- **AND** no new active pending record is created
- **AND** the ingestion response remains `status: pending`

#### Scenario: Dismissed report later becomes matchable
- **WHEN** a delivery with a previously dismissed stable source identity now uniquely resolves to a patient
- **THEN** the system ingests it as a normal report
- **AND** retains the original dismissal record for audit history

### Requirement: Existing active duplicate consolidation
Deployment of this behavior SHALL consolidate existing active pending records that share a stable source identity into one active record without discarding the retained audit history of the other records.

#### Scenario: Historical active duplicates exist
- **WHEN** multiple active pending records share one stable source identity during migration
- **THEN** exactly one record remains active for that identity
- **AND** the active record retains the newest complete payload and latest-received time
- **AND** the earliest known first-received time is preserved
- **AND** the superseded records remain inactive and available for audit

#### Scenario: Historical records have no stable identity
- **WHEN** multiple active pending records lack a stable source identity
- **THEN** migration leaves them independent and active
