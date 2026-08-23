## MODIFIED Requirements

### Requirement: Pet identity is immediately recognizable
The pet fiche profile card SHALL present the pet's avatar when one is available, otherwise its species icon, together with the pet name, record ID, and linked owner identity in its identity area. The avatar or fallback icon SHALL be an accessible control that authenticated veterinary staff can activate with a pointer or keyboard to manage the pet photo.

#### Scenario: Staff opens a pet fiche
- **WHEN** a staff member opens `pets/fiche/{id}`
- **THEN** the profile card displays the pet name, record ID, and an owner link that targets the current owner's detail page
- **AND** it displays either the pet's stored avatar or the species icon fallback

#### Scenario: Staff opens a pet fiche with no avatar
- **WHEN** a staff member opens `pets/fiche/{id}` for a pet without a stored avatar
- **THEN** the profile card displays the pet name, species icon, record ID, and an owner link that targets the current owner's detail page
- **AND** the species-icon control communicates that it can be used to add a pet photo

#### Scenario: Staff opens a pet fiche with an avatar
- **WHEN** a staff member opens `pets/fiche/{id}` for a pet with a stored avatar
- **THEN** the profile card displays the pet name, circular pet avatar, record ID, and linked owner identity
- **AND** the species icon is not displayed in place of the available avatar

#### Scenario: Staff activates the image control with a keyboard
- **WHEN** keyboard focus reaches the pet avatar or species-icon control and the staff member uses standard button activation
- **THEN** the pet photo management interface opens

#### Scenario: Profile card is displayed on a narrow viewport
- **WHEN** the pet identity area is displayed at a supported phone-sized viewport
- **THEN** the avatar control, pet identity, and profile actions reflow without overlap or horizontal scrolling
