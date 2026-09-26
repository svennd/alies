## Purpose

Generate vaccination reminder letters from eligible reminder rows in small selectable batches, delivered as one PDF with exactly one letter per page while preserving current eligibility and recipient rules.

## ADDED Requirements

### Requirement: Select reminder rows using existing eligibility
The reminder PDF action SHALL provide a selection screen for the requested month and product exclusions. It SHALL preserve existing rules excluding deceased, lost, transferred pets, disabled recipients, suppressed reminders, excluded products, and vaccinations outside the requested month. Recipient selection SHALL retain the existing companion-over-owner rule. Rows SHALL have stable unique identities and a deterministic order by due date and row identity.

#### Scenario: Eligible and excluded vaccinations coexist
- **WHEN** a user opens the reminder selection for a month with excluded products
- **THEN** only rows passing the existing month, pet, recipient, suppression, and product rules are selectable
- **AND** the recipient address belongs to the existing resolved recipient

#### Scenario: No eligible rows
- **WHEN** no vaccination rows pass the selected filters
- **THEN** the screen explains that no reminder letters are available
- **AND** no empty PDF is generated

### Requirement: Exactly one letter per selected row
Each selected eligible vaccination row SHALL produce exactly one complete letter on its own page in the batch PDF, with the recipient postal address, pet, vaccine/disease, due date, and configured reminder wording. Rows MUST NOT be consolidated by recipient or pet. Generation SHALL NOT mark reminders sent, send email, or modify clinical records.

#### Scenario: Several rows share a recipient and pet
- **WHEN** two eligible rows for the same pet and recipient are selected
- **THEN** one two-page PDF is generated with one letter per page containing the corresponding row's details
- **AND** recipient and clinical records remain unchanged

### Requirement: Initial batch maximum of ten rows
Reminder generation SHALL enforce a code-defined maximum of 10 selected rows per request and expose the maximum and selected count in the selection UI. The batch limit SHALL NOT be editable through document-center settings. The screen SHALL initially select at most the first 10 eligible rows and allow users to select later rows for subsequent batches.

#### Scenario: More than ten candidates
- **WHEN** 23 eligible rows are listed
- **THEN** no more than the first 10 are initially selected
- **AND** the user can see that the other rows are excluded from this batch and select them for a subsequent request

#### Scenario: Exactly ten selected rows
- **WHEN** a user submits 10 distinct currently eligible rows
- **THEN** the successful batch download is one PDF with exactly 10 pages, one per selected row

#### Scenario: Forged oversized or duplicate selection
- **WHEN** a request supplies more than 10 rows or repeated row identities
- **THEN** the request is rejected before compilation
- **AND** it is not silently truncated or deduplicated

### Requirement: Revalidate requested rows before generation
The server SHALL validate selection size, unique row identities, authorized month range, product exclusions, and current eligibility before compiling any letter. Missing or no-longer-eligible rows SHALL cause a useful validation error without silently replacing them with other candidates.

#### Scenario: Row changes after selection
- **WHEN** a selected row becomes suppressed, moves outside the filter, or disappears before generation
- **THEN** the request asks the user to refresh the selection
- **AND** no partial batch is generated

#### Scenario: Empty submitted selection
- **WHEN** no rows are submitted
- **THEN** the user is asked to select at least one eligible row
- **AND** no compilation starts

### Requirement: Complete batch download as one PDF
A successful reminder batch SHALL download one PDF with a safe filename and exactly one complete letter per A4 page, in the selection screen's due-date/row-identity order. The page count MUST equal the selected row count, without blank separator or trailing pages. A failed or timed-out compilation SHALL fail the whole batch without returning an incomplete PDF. Existing vaccination CSV export SHALL remain available and unchanged.

#### Scenario: Successful batch
- **WHEN** all selected rows compile successfully
- **THEN** one PDF contains exactly the selected number of pages in selection-screen order
- **AND** each page contains the corresponding row's complete letter with no content from another letter

#### Scenario: One selected row
- **WHEN** a batch contains one selected row and generation succeeds
- **THEN** the download is a one-page PDF containing that row's letter

#### Scenario: Letter would overflow
- **WHEN** a letter cannot fit on one page using the selected settings and supported font sizes
- **THEN** the batch reports the affected letter and asks the user to shorten wording or adjust layout
- **AND** no clipped, overflowing, or partial PDF is downloaded

#### Scenario: One letter fails
- **WHEN** any selected letter fails to compile or the request deadline expires
- **THEN** no incomplete PDF is downloaded
- **AND** temporary batch files are removed and the user receives a retryable error

#### Scenario: Legacy CSV export
- **WHEN** the user chooses the existing vaccination CSV export
- **THEN** its existing columns, filters, and download behavior remain unchanged
