## Purpose

Let administrators configure and preview invoice and vaccination document presentation through simple fields while keeping document content tied to application records.

## ADDED Requirements

### Requirement: Admin-only document center
The system SHALL provide an administration entry for invoices, vaccination reminder letters, and per-pet vaccination overviews. Access to document settings, previews, saves, logo management, and default restoration MUST require administrator authorization on every endpoint.

#### Scenario: Administrator opens the center
- **WHEN** an administrator opens the document center
- **THEN** all three document types and shared branding settings are available

#### Scenario: Unauthorized direct request
- **WHEN** an unauthenticated or non-admin user directly requests a document-center action
- **THEN** access is denied or redirected according to existing authentication conventions
- **AND** no settings change, document preview, or branding asset disclosure occurs

### Requirement: Simple presentation controls
The center SHALL expose shared practice branding and document-specific wording and bounded layout settings, with Noto Sans as the default font. It SHALL NOT provide raw template editing. Invoice financial values and identifiers MUST remain supplied by application records rather than editable document settings.

#### Scenario: Configure the three document types
- **WHEN** an administrator edits document settings
- **THEN** shared practice name, address/contact lines, logo, and accent color can be configured
- **AND** each document offers font-size, margin, and spacing settings within supported ranges
- **AND** invoices offer footer/payment wording and QR visibility
- **AND** reminders offer subject, greeting, body, and closing with documented supported placeholders
- **AND** overviews offer title, introduction, footer, and optional veterinarian, location, and next-due columns

#### Scenario: Invalid settings or asset
- **WHEN** submitted settings contain an unsupported placeholder, out-of-range layout value, invalid color, oversized text, or invalid/oversized logo
- **THEN** the submission is rejected with field-level feedback
- **AND** previously saved settings remain unchanged

### Requirement: Persist and restore settings with explicit scope
The center SHALL persist validated settings across requests and provide built-in defaults when no custom values exist. Restoration SHALL target either shared branding or one selected document type and MUST leave other sections and unrelated application settings unchanged. New setting-changing requests MUST be protected against forged submissions.

#### Scenario: Saved settings are reused
- **WHEN** an administrator saves valid settings and subsequently generates a Typst document
- **THEN** the saved settings are applied to that document
- **AND** existing legacy PDF presentation and unrelated configuration remain unchanged

#### Scenario: Restore one document type
- **WHEN** an administrator restores defaults for vaccination reminders
- **THEN** reminder-specific settings return to built-in defaults
- **AND** invoice, overview, and shared branding settings remain unchanged

#### Scenario: Forged settings submission
- **WHEN** a setting-changing request fails request-forgery validation
- **THEN** it does not change persisted settings or assets

### Requirement: Preview without saving or business mutations
The center SHALL preview unsaved settings using representative sample data by default and allow an authorized existing invoice, pet, or eligible reminder row as the preview source. Preview SHALL NOT persist settings, mutate business records, send mail, or modify existing PDF files.

#### Scenario: Preview unsaved wording
- **WHEN** an administrator previews changed reminder wording before saving
- **THEN** the PDF reflects that wording using the selected preview data
- **AND** subsequent ordinary generation still uses the previously saved settings

#### Scenario: Preview an existing invoice
- **WHEN** an administrator previews an existing bill or invoice
- **THEN** its available record data is used without recalculation, event linking, numbering, payment changes, or archive writes

#### Scenario: Invalid preview source
- **WHEN** the selected record is missing, ineligible, or lacks required document data
- **THEN** the center shows a useful validation message instead of generating a misleading document

#### Scenario: Reminder preview exceeds one page
- **WHEN** the submitted wording, branding, layout, and preview data cannot fit a complete reminder letter on one A4 page
- **THEN** the center reports the fit problem and asks the administrator to adjust wording or layout
- **AND** it does not deliver a clipped or multi-page reminder preview
