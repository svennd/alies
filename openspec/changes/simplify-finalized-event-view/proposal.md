## Why

The finalized event page spreads its core information across oversized header cards and separate Report, Media, and Files tabs, making completed consultations slower to review. Lab reports can already be related to events in the database, but staff cannot manage or inspect those relationships from the event interface.

## What Changes

- Replace the three-part client, pet, and event header on closed/finalized events with one compact line containing linked client and pet names, the event date, and event cost.
- Replace the Report, Media, and Files tabs with one continuous report view containing the clinical report, upload control, and attachment list.
- Show compact previews for browser-safe uploaded images while keeping non-image files available by filename.
- **BREAKING** Remove the event drawing interface and its drawing-specific client behavior; retain previously finalized drawings as ordinary image attachments.
- Add controls to link and unlink lab reports through the existing `events_labs` relationship.
- Restrict linkable lab reports to non-deleted reports assigned to the event's pet, and enforce the same rule server-side.
- Replace the finalized event's invoice-item sidebar with embedded results for every linked lab, including navigation to the full lab report.
- Leave the open consultation interface unchanged.

## Capabilities

### New Capabilities

- `finalized-event-report`: Defines the compact finalized-event layout, unified report and attachment workflow, and pet-scoped lab linking and embedded lab-result presentation.

### Modified Capabilities

- None.

## Impact

- Finalized-event controller data assembly and report views under `application/controllers/Events.php` and `application/views/event/`.
- Event upload presentation and authenticated inline image delivery under `application/controllers/Files.php` and `Events_upload_model`.
- Lab-report queries and a new event-to-lab relationship access layer using `lab_report` and `events_labs`.
- Link/unlink request handlers, authorization and pet-assignment validation, activity logging, localization strings, and focused controller/model/view tests.
- No database migration is expected because the `events_labs` junction table already exists.
