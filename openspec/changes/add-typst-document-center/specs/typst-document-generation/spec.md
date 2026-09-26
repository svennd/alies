## Purpose

Provide independent Typst PDF downloads for existing invoice and vaccination data, with predictable rendering and no disruption to established PDF workflows.

## ADDED Requirements

### Requirement: Additive generation actions preserve legacy behavior
The system SHALL add clearly labelled Typst generation buttons alongside the existing invoice and per-pet vaccination PDF actions and a reminder-generation entry alongside vaccination export. Existing PDF classes, generation actions, automatic invoice generation, cached files, email behavior, PDF merging, and CSV export MUST remain functional and retain their current behavior. New actions SHALL NOT switch the default renderer or silently fall back to the old renderer.

#### Scenario: User chooses an existing export
- **WHEN** a user uses an existing invoice, vaccine overview, other PDF, or CSV export action after this change
- **THEN** the existing generation route and output behavior remain in use

#### Scenario: User chooses a Typst download
- **WHEN** an authorized user selects a new Typst button
- **THEN** the requested document is generated with Typst using current document-center settings
- **AND** its download does not create, replace, or reuse a file from the legacy invoice archive

### Requirement: Generation authorization matches source access
New generation actions SHALL require the existing authorization for their source records, and reminder actions SHALL enforce the existing month-access restrictions. Direct requests MUST receive the same checks as requests from visible buttons.

#### Scenario: Unauthorized generation request
- **WHEN** a user without the required role or month access submits a direct generation request
- **THEN** no source document is disclosed and no generation occurs

#### Scenario: Missing source record
- **WHEN** an authorized user requests an invoice or pet that does not exist
- **THEN** a controlled not-found or validation response is returned without generating a PDF

### Requirement: Noto Sans and reliable document layout
Generated documents SHALL use Noto Sans by default, support the application's accented names and currency symbols, and paginate A4 portrait content without overlapping or clipped required information. Multi-page tables SHALL repeat identifying column headers and documents SHALL include page numbering.

#### Scenario: Long invoice
- **WHEN** an invoice has long descriptions and enough rows to span multiple pages
- **THEN** every line and total remains readable with repeated table headers and page numbers
- **AND** body text uses Noto Sans

#### Scenario: Runtime prerequisite unavailable
- **WHEN** Typst cannot be executed or Noto Sans cannot be resolved by the application runtime
- **THEN** the Typst action reports an actionable setup error without silently substituting another renderer or font
- **AND** existing PDF actions remain usable

### Requirement: Invoice generation is read-only and preserves business values
The new invoice document SHALL represent existing bill/invoice identifiers, dates, recipient billing address, line items, stored tax and totals, payment status, and configured payment information. It SHALL support unnumbered bills, numbered invoices, and write-off records with sufficient persisted data. Rendering MUST NOT recalculate bills, link events, allocate numbers, change stock or payments, mark mail sent, or alter stored invoice PDFs.

#### Scenario: Numbered invoice with alternate address
- **WHEN** an existing invoice with an alternate billing address is generated through Typst
- **THEN** it shows that address and the existing invoice identity, items, tax, totals, and payment information
- **AND** the underlying records and archived PDFs remain unchanged

#### Scenario: Unnumbered or write-off bill
- **WHEN** a supported unnumbered bill or write-off record is generated
- **THEN** the document uses its appropriate bill identity and stored line-item context
- **AND** no invoice number is allocated and no business state is changed

#### Scenario: Insufficient persisted bill data
- **WHEN** the requested bill lacks the data necessary for a coherent invoice document
- **THEN** generation reports that the existing bill workflow must prepare the record
- **AND** generation does not perform that preparation itself

### Requirement: Per-pet overview retains existing inclusion rules
The new overview SHALL show the selected pet and owner information plus qualifying vaccination names and administration dates, applying configured optional columns. It SHALL preserve the existing PDF's exclusion of externally recorded vaccinations identified by an absent associated event.

#### Scenario: Mixed vaccination origins
- **WHEN** a pet has both clinic-administered and externally recorded vaccinations
- **THEN** the Typst overview includes the qualifying clinic-administered vaccinations
- **AND** it excludes the externally recorded rows, matching the existing PDF's inclusion behavior

#### Scenario: No qualifying vaccination rows
- **WHEN** a pet has no vaccinations qualifying for the overview
- **THEN** the PDF includes the pet information and an explicit no-vaccinations message

### Requirement: Literal data and private temporary output
Record values and configurable wording MUST be rendered as data, not executable document instructions. Rendering SHALL have access only to its required inputs and approved assets. Temporary inputs/outputs MUST NOT be publicly accessible and SHALL be cleaned after success or failure; user-facing errors and logs MUST NOT expose full record payloads or private filesystem details.

#### Scenario: Text contains Typst metacharacters
- **WHEN** a name or wording field contains characters that have meaning in Typst source
- **THEN** they appear as text without executing instructions or loading unrelated files

#### Scenario: Generation completes or fails
- **WHEN** a generation request finishes successfully or fails
- **THEN** temporary document data is removed
- **AND** the response contains only the intended download or a controlled error

### Requirement: Bound compiler execution and concurrency
Typst generation SHALL enforce finite per-compilation and total-request deadlines, terminate stalled compilation, and initially permit only one active generation request per PHP container. Concurrent attempts SHALL receive a retry response rather than queue inside application workers.

#### Scenario: Compiler stalls or exits unsuccessfully
- **WHEN** compilation exceeds a configured deadline or exits with an error
- **THEN** the request ends with a controlled error and releases its resources
- **AND** no invalid or incomplete PDF is delivered

#### Scenario: Another request is already generating
- **WHEN** a second Typst request arrives while the container is generating a document or batch
- **THEN** it receives a busy/retry response without starting another compiler
