## Purpose

Enable authenticated veterinary staff to manage a safe, recognizable photo for each pet while preserving privacy, data integrity, and a dependable species-icon fallback.

## ADDED Requirements

### Requirement: Staff can prepare and save a pet photo
The system SHALL allow authenticated veterinary staff to select a JPEG or PNG image of no more than 8 MB from the pet fiche, preview a square crop, rotate the crop, and explicitly save or cancel the change. The existing avatar SHALL remain unchanged until a valid selection is successfully saved.

#### Scenario: Staff saves a valid photo
- **WHEN** an authenticated staff member selects a JPEG or PNG image within the size limit, adjusts the square crop, and activates save
- **THEN** the system stores the selected crop as the pet's avatar
- **AND** the fiche displays localized success feedback

#### Scenario: Staff cancels preparation
- **WHEN** an authenticated staff member selects or adjusts an image and then cancels
- **THEN** the system closes the photo editor without changing the pet's stored avatar

#### Scenario: Staff rotates the crop
- **WHEN** an authenticated staff member rotates the selected image before saving
- **THEN** the saved avatar reflects the selected orientation and square crop

### Requirement: Uploaded pet photos are validated and normalized
The system MUST validate image content on the server independently of the filename or browser-provided media type. It SHALL reject unsupported, corrupt, empty, or oversized uploads and SHALL store accepted content as a newly encoded square image no larger than 512 by 512 pixels, without retaining embedded metadata.

#### Scenario: File extension disguises invalid content
- **WHEN** an uploaded file has a permitted extension but its content is not a valid supported image
- **THEN** the system rejects the upload
- **AND** it displays localized error feedback without changing the existing avatar

#### Scenario: Upload exceeds the size limit
- **WHEN** an uploaded image exceeds 8 MB
- **THEN** the system rejects the upload
- **AND** it displays localized size-limit feedback without changing the existing avatar

#### Scenario: Valid image is normalized
- **WHEN** a valid supported image is saved
- **THEN** the stored avatar is a newly encoded square image no larger than 512 by 512 pixels
- **AND** metadata from the source image is not retained

### Requirement: Staff can replace or remove a pet photo
The system SHALL allow authenticated veterinary staff to replace an existing pet avatar through the same preparation flow or remove it explicitly. Failed replacement attempts SHALL preserve the previous avatar.

#### Scenario: Staff replaces an existing avatar
- **WHEN** an authenticated staff member successfully saves a new valid image for a pet that already has an avatar
- **THEN** the new avatar becomes the pet's current avatar
- **AND** the previous avatar is no longer displayed for that pet

#### Scenario: Replacement fails validation
- **WHEN** an authenticated staff member attempts to replace an avatar with an invalid image
- **THEN** the current avatar remains associated with and displayed for the pet

#### Scenario: Staff removes an avatar
- **WHEN** an authenticated staff member confirms removal of a pet's current avatar
- **THEN** the pet no longer has a stored avatar reference
- **AND** the fiche returns to its species-icon fallback

### Requirement: Pet photos remain restricted to authenticated staff
The system MUST deliver stored pet avatars only through an authenticated application request and SHALL NOT expose their storage paths as directly accessible public asset URLs. Upload, replacement, and removal operations MUST reject unauthenticated requests and requests for pets that do not exist.

#### Scenario: Authenticated staff requests a stored avatar
- **WHEN** an authenticated staff member requests the avatar for a pet that has one
- **THEN** the system returns the normalized image with an image media type

#### Scenario: Unauthenticated visitor requests a stored avatar
- **WHEN** an unauthenticated visitor requests a pet avatar
- **THEN** the system does not disclose the image content or its storage path

#### Scenario: Operation targets an unknown pet
- **WHEN** an avatar upload, replacement, or removal targets a pet record that does not exist
- **THEN** the system rejects the operation without creating, changing, or deleting an avatar file

### Requirement: Avatar continuity is preserved during pet transfer
When transferring a pet creates a successor pet record, the system SHALL preserve the current avatar for both the historical source record and the successor record. Later replacement or removal on either record SHALL NOT break avatar retrieval for the other record.

#### Scenario: Pet with an avatar is transferred
- **WHEN** a pet with a current avatar is transferred to a new owner
- **THEN** the successor pet record displays the same avatar
- **AND** the historical source pet record can still retrieve that avatar

#### Scenario: Successor avatar is replaced after transfer
- **WHEN** staff replaces the successor pet record's avatar after transfer
- **THEN** the successor displays the replacement
- **AND** the historical source record retains access to its prior avatar

### Requirement: Avatar changes are auditable
The system SHALL record successful pet avatar uploads, replacements, and removals in the existing staff activity log with the acting staff member and target pet identifiable through the log context.

#### Scenario: Avatar change succeeds
- **WHEN** an authenticated staff member successfully uploads, replaces, or removes a pet avatar
- **THEN** the system records the action in the staff activity log for that pet

#### Scenario: Avatar change is rejected
- **WHEN** an avatar operation fails validation or targets an unknown pet
- **THEN** the system does not record a successful avatar-change action
