# Test task status

Smaller models: when you finish an ID, change its status to `done` and fill in the test file.
Status values: `todo`, `wip`, `done`, `blocked` (put the reason in notes).

Total: 10 foundation tasks and 241 test tasks.

## Suggested prompt batches

Each batch is a set of consecutive IDs from the same spec file, sized for one prompt to a smaller model. F01–F10 must be done first.

| Batch | IDs | Spec |
|---|---|---|
| B01 | T001, T002, T003, T004 | [01-helpers-pure.md](01-helpers-pure.md) |
| B02 | T005, T006, T007, T008 | [01-helpers-pure.md](01-helpers-pure.md) |
| B03 | T009, T010, T011, T012 | [01-helpers-pure.md](01-helpers-pure.md) |
| B04 | T013, T014, T015, T016 | [01-helpers-pure.md](01-helpers-pure.md) |
| B05 | T017, T018, T021 | [01-helpers-pure.md](01-helpers-pure.md) |
| B06 | T022, T023, T024, T025 | [01-helpers-pure.md](01-helpers-pure.md) |
| B07 | T030, T031, T032, T033 | [02-auth-access.md](02-auth-access.md) |
| B08 | T034, T035, T036, T037 | [02-auth-access.md](02-auth-access.md) |
| B09 | T038, T039, T040, T041 | [02-auth-access.md](02-auth-access.md) |
| B10 | T042, T043, T044 | [02-auth-access.md](02-auth-access.md) |
| B11 | T050, T051, T052, T053 | [03-owners-pets.md](03-owners-pets.md) |
| B12 | T054, T060, T061, T062 | [03-owners-pets.md](03-owners-pets.md) |
| B13 | T063, T064, T065, T066 | [03-owners-pets.md](03-owners-pets.md) |
| B14 | T067, T068, T069, T075 | [03-owners-pets.md](03-owners-pets.md) |
| B15 | T076, T077, T078, T079 | [03-owners-pets.md](03-owners-pets.md) |
| B16 | T085, T086, T087, T088 | [03-owners-pets.md](03-owners-pets.md) |
| B17 | T090, T091, T092, T093 | [03-owners-pets.md](03-owners-pets.md) |
| B18 | T094 | [03-owners-pets.md](03-owners-pets.md) |
| B19 | T100, T101, T102, T103 | [04-events-billing.md](04-events-billing.md) |
| B20 | T104, T105, T110, T111 | [04-events-billing.md](04-events-billing.md) |
| B21 | T112, T113, T114, T115 | [04-events-billing.md](04-events-billing.md) |
| B22 | T116, T117, T118, T119 | [04-events-billing.md](04-events-billing.md) |
| B23 | T120, T121, T125, T126 | [04-events-billing.md](04-events-billing.md) |
| B24 | T127, T128, T130, T131 | [04-events-billing.md](04-events-billing.md) |
| B25 | T132, T133, T134, T140 | [04-events-billing.md](04-events-billing.md) |
| B26 | T141, T142, T143, T144 | [04-events-billing.md](04-events-billing.md) |
| B27 | T145, T146, T147, T148 | [04-events-billing.md](04-events-billing.md) |
| B28 | T149, T150, T151, T152 | [04-events-billing.md](04-events-billing.md) |
| B29 | T155, T156, T157, T158 | [04-events-billing.md](04-events-billing.md) |
| B30 | T159, T139 | [04-events-billing.md](04-events-billing.md) |
| B31 | T160, T161, T162, T163 | [05-stock-products.md](05-stock-products.md) |
| B32 | T164, T165, T170, T171 | [05-stock-products.md](05-stock-products.md) |
| B33 | T172, T173, T174, T175 | [05-stock-products.md](05-stock-products.md) |
| B34 | T180, T181, T182, T183 | [05-stock-products.md](05-stock-products.md) |
| B35 | T184, T185, T186, T187 | [05-stock-products.md](05-stock-products.md) |
| B36 | T188, T190, T191, T195 | [05-stock-products.md](05-stock-products.md) |
| B37 | T200, T201, T202, T203 | [05-stock-products.md](05-stock-products.md) |
| B38 | T204, T205, T206, T207 | [05-stock-products.md](05-stock-products.md) |
| B39 | T208, T209, T210, T211 | [05-stock-products.md](05-stock-products.md) |
| B40 | T212, T213, T215, T216 | [05-stock-products.md](05-stock-products.md) |
| B41 | T217 | [05-stock-products.md](05-stock-products.md) |
| B42 | T220, T221, T222, T223 | [06-clinical.md](06-clinical.md) |
| B43 | T224, T225, T230, T235 | [06-clinical.md](06-clinical.md) |
| B44 | T236, T240, T241, T242 | [06-clinical.md](06-clinical.md) |
| B45 | T243, T250, T251, T252 | [06-clinical.md](06-clinical.md) |
| B46 | T253, T254, T255 | [06-clinical.md](06-clinical.md) |
| B47 | T260, T261, T262, T263 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B48 | T264, T265, T271 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B49 | T272, T273, T274, T275 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B50 | T276, T280, T281, T282 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B51 | T283, T284, T285, T290 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B52 | T291 | [07-reports-export-vamreg.md](07-reports-export-vamreg.md) |
| B53 | T300, T301, T302, T303 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B54 | T305, T310, T311, T312 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B55 | T313, T320, T321, T322 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B56 | T323, T324, T325, T326 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B57 | T327, T328, T329, T335 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B58 | T336, T340, T345, T346 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B59 | T347, T348, T349 | [08-admin-tools-cli-api.md](08-admin-tools-cli-api.md) |
| B60 | T350, T351, T352, T353 | [09-model-layer.md](09-model-layer.md) |
| B61 | T360, T361, T362, T363 | [09-model-layer.md](09-model-layer.md) |
| B62 | T364, T365, T366, T367 | [09-model-layer.md](09-model-layer.md) |
| B63 | T368, T369, T370, T371 | [09-model-layer.md](09-model-layer.md) |
| B64 | T372, T373, T374 | [09-model-layer.md](09-model-layer.md) |

## Tasks

| ID | Title | Layer | Spec | Status | Test file | Notes |
|---|---|---|---|---|---|---|
| F01 | Reproducible test database schema | infra | [00](00-foundation.md) | done |  | |
| F02 | `testing` environment and an app server | infra | [00](00-foundation.md) | done |  | |
| F03 | Deterministic seed data (`tests/Fixtures/seed.sql`) | infra | [00](00-foundation.md) | done |  | |
| F04 | `HttpTestCase` base class (`tests/Support/HttpTestCase.php`) | infra | [00](00-foundation.md) | done |  | |
| F05 | Factories (`tests/Support/Factory.php`) | infra | [00](00-foundation.md) | done |  | |
| F06 | PHPUnit test suites and composer scripts | infra | [00](00-foundation.md) | done |  | |
| F07 | External service stubs | infra | [00](00-foundation.md) | not built | | documented in 00-foundation.md |
| F08 | Reorganise existing tests | infra | [00](00-foundation.md) | done |  | |
| F09 | `docs/testing/STATUS.md` tracker | infra | [00](00-foundation.md) | done |  | |
| F10 | CI job | infra | [00](00-foundation.md) | done |  | |
| T001 | `get_bill_id` and `get_invoice_id` | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/BillIdTest.php` |  |
| T002 | Structured message (Belgian OGM): main cases | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/StructMessageTest.php` | old BillingHelperTest folded in and removed |
| T003 | Structured message: the check-digit padding bug | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/StructMessageTest.php` | known-bug group; also pins the old BillingHelperTest expectation (1234,56 -> 05660, correct is 06) |
| T004 | GS1 parsing (`gs1`, `gtincheck`, `gs1_get_due_date`) | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/Gs1HelperTest.php` | legacy parse_gs1 day 00 -> 1st vs gs1() -> last day pinned |
| T005 | CNK validation | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/CnkHelperTest.php` |  |
| T006 | Phone helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/PhoneHelperTest.php` | spec example "(gsm)" corrected: result is 0470123456 |
| T007 | Date helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/UserDateHelperTest.php` | real timespan in tests/Models/TimeAgoTest.php; helper has PHP 8.4 deprecations |
| T008 | Online status | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/OnlineHelperTest.php` | clock cannot be frozen: margins of 60 s below the limits |
| T009 | Symbol, name and status helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/GetSymbolHelperTest.php` |  |
| T010 | Quarter helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/QuarterHelperTest.php` | two known-bug tests (quarter 5, post-quarter day) |
| T011 | Vamreg helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/VamregHelperTest.php` |  |
| T012 | Lab percentage helper | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/LabHelperTest.php` | helper is dead code in the app |
| T013 | CSV download helpers | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/FileDownloadHelperTest.php` | headers not testable in CLI, body only |
| T014 | `move_file` and `base64_to_image` | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/FileHelpersTest.php` | uses stubs for get_instance/write_file (tests/Pure/stubs.php) |
| T015 | `javascript` and `inject_trumbowyg` | P | [01](01-helpers-pure.md) | done | `tests/Pure/Helpers/AssetHelpersTest.php` | host of base_url never asserted |
| T016 | Lmscan adapter | P | [01](01-helpers-pure.md) | done | `tests/Pure/Devices/LmscanTest.php` | fixture tests/Fixtures/lmscan_payload.json from API.md |
| T017 | Medilab adapter | P | [01](01-helpers-pure.md) | done | `tests/Pure/Devices/MedilabTest.php` | fixture tests/Fixtures/medilab_payload.json; 2 known quirks pinned (numeric value without text_value is lost, chip row stays in results) |
| T018 | Ikems and Ms4s2 (extend the existing tests) | P | [01](01-helpers-pure.md) | done | `tests/Pure/Devices/IkemsTest.php, tests/Pure/Devices/Ms4s2Test.php` | Ikems date parsing rolls impossible dates over (31/02 -> 03-03) |
| T021 | Lab_result_presenter (extend the existing test) | P | [01](01-helpers-pure.md) | done | `tests/Pure/Libraries/LabResultPresenterTest.php` |  |
| T022 | Lab_source_identity, Lab_device_adapter_factory and Event_attachment_preview | P | [01](01-helpers-pure.md) | done | `tests/Pure/Libraries/LabSourceIdentityTest.php, LabDeviceAdapterFactoryCreateTest.php, EventAttachmentPreviewTest.php` | factory source-pattern test left in tests/Libraries as ci3-only |
| T023 | ExcelExporter | P | [01](01-helpers-pure.md) | done | `tests/Pure/Libraries/ExcelExporterTest.php` | runs in a subprocess (method calls exit); formula injection pinned as known-bug |
| T024 | Pdf and Qr libraries | M | [01](01-helpers-pure.md) | done | `tests/Models/PdfQrLibraryTest.php` | Qr returns an SVG data URI, not PNG; known-bug: one Qr instance accumulates data |
| T025 | Vamregclient request building | P | [01](01-helpers-pure.md) | done | `tests/Pure/Libraries/VamregclientTest.php` | real request() never run (curl, hard coded host) - see F07 |
| T030 | Login page renders | H | [02](02-auth-access.md) | done | `tests/Http/Auth/LoginPageTest.php` |  |
| T031 | Successful login, then choosing a location | H | [02](02-auth-access.md) | done | `tests/Http/Auth/LoginAndLocationTest.php` |  |
| T032 | Failed login and lockout | H | [02](02-auth-access.md) | done | `tests/Http/Auth/FailedLoginAndLockoutTest.php` | lockout is per identity and ip; no message is shown after a failed login |
| T033 | Logout | H | [02](02-auth-access.md) | done | `tests/Http/Auth/LogoutTest.php` | logout works over GET |
| T034 | Inactive user cannot log in | H | [02](02-auth-access.md) | done | `tests/Http/Auth/InactiveUserTest.php` | refusal does not count as a failed attempt; active flag is only checked at login |
| T035 | Unauthenticated access to every protected controller | H | [02](02-auth-access.md) | done | `tests/Http/Auth/AnonymousAccessTest.php` | fails when a controller has no entry in ROUTES (CI3 folder layout) |
| T036 | Admin-only controllers reject a vet | H | [02](02-auth-access.md) | done | `tests/Http/Auth/AdminOnlyControllersTest.php` | also covers T038 for admin routes |
| T037 | The accounting role | H | [02](02-auth-access.md) | done | `tests/Http/Auth/AccountingRoleTest.php` | accounting dashboard is closed to accounting (extends Admin_Controller) |
| T038 | Users with the wrong group or no group | H | [02](02-auth-access.md) | done | `tests/Http/Auth/AccountingRoleTest.php` | members/no-group users get a 500, also in AdminOnlyControllersTest |
| T039 | Admin-only actions inside vet controllers | H | [02](02-auth-access.md) | done | `tests/Http/Auth/AdminChecksInsideVetControllersTest.php` | 7 known-bug tests: endpoints without admin check |
| T040 | Change password | H | [02](02-auth-access.md) | done | `tests/Http/Auth/ChangePasswordTest.php` | uses user 6, hash restored |
| T041 | Group admin screens | H | [02](02-auth-access.md) | done | `tests/Http/Auth/GroupAdminTest.php` | edit_group is broken (xss_clean rule missing); group name silently cut at 20 chars |
| T042 | Activate and deactivate a user | H | [02](02-auth-access.md) | done | `tests/Http/Auth/ActivateDeactivateUserTest.php` | token read from the form; activating an active user goes to forgot_password |
| T043 | CLI and upgrade endpoints over HTTP | H | [02](02-auth-access.md) | done | `tests/Http/Auth/CliOverHttpTest.php` | Upgrade is guarded like Cli (spec said it was open) |
| T044 | Session location and the location bar | H | [02](02-auth-access.md) | done | `tests/Http/Auth/SessionLocationTest.php` | current_location is TINYINT: 999 is stored as 255 |
| T050 | Add an owner | H | [03](03-owners-pets.md) | done | `tests/Http/Owners/AddOwnerTest.php` | partial form = 500; stored XSS; phone stored as typed |
| T051 | Edit an owner | H | [03](03-owners-pets.md) | done | `tests/Http/Owners/EditOwnerTest.php` | partial form BLANKS fields (data loss); deleted owner can be edited |
| T052 | Owner detail page | H | [03](03-owners-pets.md) | done | `tests/Http/Owners/OwnerDetailTest.php` | pet sorting never worked (order_by with array) |
| T053 | Owner AJAX endpoints | H | [03](03-owners-pets.md) | done | `tests/Http/Owners/OwnerAjaxTest.php` |  |
| T054 | Owner invoices and products pages | H | [03](03-owners-pets.md) | done | `tests/Http/Owners/OwnerInvoicesAndProductsTest.php` | dates are injectable (known-bug) |
| T060 | Add a pet | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/AddPetTest.php` | owner comes from the form, companion field only for VAT clients |
| T061 | Edit a pet | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/EditPetTest.php` | death_date reset on every save of a dead pet |
| T062 | Pet fiche page | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetFicheTest.php` | other-pets block needs more than one sibling |
| T063 | Delete a pet (admin) | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/DeletePetTest.php` | search still lists soft-deleted pets |
| T064 | Weight endpoints | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetWeightTest.php` | del_weight is a GET; last_weight not recalculated |
| T065 | Pet name autocomplete and chip check | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetLookupTest.php` |  |
| T066 | Pet transfer to a new owner (HTTP) | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetTransferHttpTest.php` |  |
| T067 | Pet history export as PDF | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetExportTest.php` | PDF content is compressed: headers/size only |
| T068 | Medical history shown on the fiche | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetMedicalHistoryTest.php` |  |
| T069 | Pet avatar over HTTP | H | [03](03-owners-pets.md) | done | `tests/Http/Pets/PetAvatarHttpTest.php` |  |
| T075 | Breed list and detail | H | [03](03-owners-pets.md) | done | `tests/Http/Breeds/BreedsTest.php` | merge leaves breed2; edit of unknown id = 500 |
| T076 | Add and edit a breed | H | [03](03-owners-pets.md) | done | `tests/Http/Breeds/BreedsTest.php` | merge leaves breed2; edit of unknown id = 500 |
| T077 | Breed search autocomplete | H | [03](03-owners-pets.md) | done | `tests/Http/Breeds/BreedsTest.php` | merge leaves breed2; edit of unknown id = 500 |
| T078 | Merge breeds | H | [03](03-owners-pets.md) | done | `tests/Http/Breeds/BreedsTest.php` | merge leaves breed2; edit of unknown id = 500 |
| T079 | Rebuild breed frequencies | H | [03](03-owners-pets.md) | done | `tests/Http/Breeds/BreedsTest.php` | merge leaves breed2; edit of unknown id = 500 |
| T085 | Global search | H | [03](03-owners-pets.md) | done | `tests/Http/Search/GlobalSearchTest.php` |  |
| T086 | Owner search methods | M | [03](03-owners-pets.md) | done | `tests/Models/OwnersSearchTest.php` | raw SQL searches return soft-deleted rows |
| T087 | Pet search methods | M | [03](03-owners-pets.md) | done | `tests/Models/PetsSearchTest.php` |  |
| T088 | Auto-death | M | [03](03-owners-pets.md) | done | `tests/Models/PetsAutoDeathTest.php` | [auto-death] suffix lost when note is NULL |
| T090 | Sticky notes | H | [03](03-owners-pets.md) | done | `tests/Http/Sticky/StickyNotesTest.php` | private notes are listed to everyone |
| T091 | Vet profile | H | [03](03-owners-pets.md) | done | `tests/Http/Vet/VetProfileTest.php` |  |
| T092 | Vet list autocomplete | H | [03](03-owners-pets.md) | done | `tests/Http/Vet/VetAutocompleteTest.php` |  |
| T093 | Home page (dashboard) | H | [03](03-owners-pets.md) | done | `tests/Http/Vet/HomeAndHelpTest.php` |  |
| T094 | Help pages | H | [03](03-owners-pets.md) | done | `tests/Http/Vet/HomeAndHelpTest.php` |  |
| T100 | Start a new event for a pet | H | [04](04-events-billing.md) | done | `tests/Http/Events/NewEventTest.php` | GET mutates |
| T101 | Event page for an OPEN event | H | [04](04-events-billing.md) | done | `tests/Http/Events/ViewOpenEventTest.php` |  |
| T102 | Event page for a CLOSED event shows linked and linkable labs | H | [04](04-events-billing.md) | done | `tests/Http/Events/ViewClosedEventLabsTest.php` |  |
| T103 | Event page for an event ID that does not exist | H | [04](04-events-billing.md) | done | `tests/Http/Events/ViewMissingEventTest.php` | 500 for a missing event |
| T104 | Lock an event | H | [04](04-events-billing.md) | done | `tests/Http/Events/LockEventTest.php` | GET mutates |
| T105 | Link and unlink a lab on an event | H | [04](04-events-billing.md) | done | `tests/Http/Events/LinkLabTest.php` |  |
| T110 | Add a procedure line | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddProcedureLineTest.php` |  |
| T111 | Add a product line with tiered pricing | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddProductLineTierTest.php` | product without tiers = 500 |
| T112 | Add a product line with a stock lot | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddProductLineStockTest.php` |  |
| T113 | Booking code overrides VAT | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddLineBookingTest.php` |  |
| T114 | Adding a vaccine creates a reminder | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddVaccineLineTest.php` |  |
| T115 | Adding an antibiotic creates a Vamreg draft | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddAntibioticLineTest.php` |  |
| T116 | `add_line` on a non-open event is ignored | H | [04](04-events-billing.md) | done | `tests/Http/Events/AddLineClosedEventTest.php` |  |
| T117 | Remove product and procedure lines | H | [04](04-events-billing.md) | done | `tests/Http/Events/RemoveLineTest.php` | line id not checked against the event |
| T118 | Change the volume of a product line (`prod_edit`) | H | [04](04-events-billing.md) | done | `tests/Http/Events/ProdEditTest.php` |  |
| T119 | `proc_edit` placeholder | H | [04](04-events-billing.md) | done | `tests/Http/Events/ProcEditTest.php` |  |
| T120 | Edit or disable the vaccine reminder from an event | H | [04](04-events-billing.md) | done | `tests/Http/Events/EditVaccinTest.php` |  |
| T121 | Set the Vamreg indication | H | [04](04-events-billing.md) | done | `tests/Http/Events/SetVamregIndicationTest.php` |  |
| T125 | Price edit page | H | [04](04-events-billing.md) | done | `tests/Http/Events/EditPricePageTest.php` |  |
| T126 | Edit a unit price manually | H | [04](04-events-billing.md) | done | `tests/Http/Events/EditUnitPriceTest.php` | no status check |
| T127 | Apply a percentage reduction to the whole event | H | [04](04-events-billing.md) | done | `tests/Http/Events/EditEventPriceReductionTest.php` | GET mutates |
| T128 | `events_model.is_modified` and the bill's modified flag | M | [04](04-events-billing.md) | done | `tests/Models/EventsModifiedTest.php` |  |
| T130 | Update the report text | H | [04](04-events-billing.md) | done | `tests/Http/Events/UpdateReportTest.php` |  |
| T131 | Autosave the anamnese | H | [04](04-events-billing.md) | done | `tests/Http/Events/AnamneseAutosaveTest.php` |  |
| T132 | History visibility and event type | H | [04](04-events-billing.md) | done | `tests/Http/Events/EventsReportFlagsTest.php` | GET mutates |
| T133 | Delete an event that has no bill | H | [04](04-events-billing.md) | done | `tests/Http/Events/DeleteEventNoBillTest.php` | orphan lines |
| T134 | Delete an event that has a PENDING bill | H | [04](04-events-billing.md) | done | `tests/Http/Events/DeleteEventWithBillTest.php` | paid bill left behind |
| T140 | Full payment flow (happy path) | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/FullPaymentFlowTest.php` |  |
| T141 | Payment outcomes | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/PaymentOutcomesTest.php` | overpay = INCOMPLETE; amounts overwrite |
| T142 | Verify a transfer | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/VerifyTransferTest.php` | works from any status |
| T143 | Reuse an existing bill | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/BillReuseTest.php` |  |
| T144 | Opening a bill attaches all the owner's open events | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/AttachEventsTest.php` | owner without events = 500 |
| T145 | Invalid or deleted bill | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/InvalidBillTest.php` |  |
| T146 | Invoice PDF (print) | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/PrintPdfTest.php` | PDF read by inflating streams |
| T147 | Mail mode | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/MailBillTest.php` | mail end to end skipped: needs F07 mail config |
| T148 | Store the bill message | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/StoreBillMsgTest.php` |  |
| T149 | Bill overview and filters | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/BillOverviewTest.php` |  |
| T150 | Write-off bill rendering | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/WriteOffBillTest.php` |  |
| T151 | Admin edit bill | H | [04](04-events-billing.md) | done | `tests/Http/Invoice/AdminEditBillTest.php` | amount ignored |
| T152 | Open bills shown on a bill | M | [04](04-events-billing.md) | done | `tests/Models/BillsOpenBillsTest.php` |  |
| T155 | `calculate_bill` totals and VAT rounding | H | [04](04-events-billing.md) | done | `tests/Models/BillsCalculateTest.php` | 12% no VAT, VAT>=1000, no events |
| T156 | `set_invoice_id` numbering | H | [04](04-events-billing.md) | done | `tests/Models/BillsInvoiceIdTest.php` | sequence uses current year, not the bill year |
| T157 | Earnings aggregates | H | [04](04-events-billing.md) | done | `tests/Models/BillsEarningsTest.php` |  |
| T158 | Event aggregates | H | [04](04-events-billing.md) | done | `tests/Models/EventsAggregatesTest.php` |  |
| T159 | Event search and export queries | H | [04](04-events-billing.md) | done | `tests/Models/EventsSearchExportTest.php` | FULLTEXT sees committed rows only |
| T139 | Accounting dashboard | H | [04](04-events-billing.md) | done | `tests/Http/Accounting/DashboardTest.php` |  |
| T160 | Add stock (new lot) | H | [05](05-stock-products.md) | done | `tests/Http/Stock/AddStockTest.php` | empty eol stored as empty string |
| T161 | Add stock to an existing CHECK row | H | [05](05-stock-products.md) | done | `tests/Http/Stock/AddStockTest.php` | empty eol stored as empty string |
| T162 | Add-stock validation | H | [05](05-stock-products.md) | done | `tests/Http/Stock/AddStockTest.php` | empty eol stored as empty string |
| T163 | Add stock with a new GS1 barcode | H | [05](05-stock-products.md) | done | `tests/Http/Stock/AddStockTest.php` | empty eol stored as empty string |
| T164 | Delete a CHECK row | H | [05](05-stock-products.md) | done | `tests/Http/Stock/VerifyStockTest.php` |  |
| T165 | Verify stock (delivery slip) | H | [05](05-stock-products.md) | done | `tests/Http/Stock/VerifyStockTest.php` |  |
| T170 | Move stock between locations | H | [05](05-stock-products.md) | done | `tests/Http/Stock/MoveStockTest.php` | OVERDRAW_MOVE never stored |
| T171 | Write off a whole stock row (expired) | H | [05](05-stock-products.md) | done | `tests/Http/Stock/WriteOffTest.php` |  |
| T172 | Partial write-off | H | [05](05-stock-products.md) | done | `tests/Http/Stock/WriteOffTest.php` |  |
| T173 | Expired stock page | H | [05](05-stock-products.md) | done | `tests/Http/Stock/ExpiredStockPageTest.php` |  |
| T174 | Admin stock edit and the stock-problems screen | H | [05](05-stock-products.md) | done | `tests/Http/Stock/AdminStockTest.php` |  |
| T175 | Stock detail and stock autocomplete | H | [05](05-stock-products.md) | done | `tests/Http/Stock/StockDetailTest.php` |  |
| T180 | Product index and catalogue search | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductIndexAndProfileTest.php` |  |
| T181 | Product profile | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductIndexAndProfileTest.php` |  |
| T182 | New product wizard (admin) | H | [05](05-stock-products.md) | done | `tests/Http/Products/NewProductWizardTest.php` |  |
| T183 | Edit a product (admin) | H | [05](05-stock-products.md) | done | `tests/Http/Products/EditProductTest.php` | post without limit = 500 after update |
| T184 | Product lists by type (DataTables) | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductListTest.php` |  |
| T185 | Delete a product | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductListTest.php` |  |
| T186 | Autocomplete for adding stock (`products/get_product`) | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductAutocompleteTest.php` |  |
| T187 | Autocomplete for event lines (`products/get_product_or_procedure`) | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductAutocompleteTest.php` |  |
| T188 | Lot numbers, backorder and monthly usage | H | [05](05-stock-products.md) | done | `tests/Http/Products/ProductAutocompleteTest.php` |  |
| T190 | Product price tiers | H | [05](05-stock-products.md) | done | `tests/Http/Pricing/ProductPricingTest.php` |  |
| T191 | Procedures | H | [05](05-stock-products.md) | done | `tests/Http/Pricing/ProceduresTest.php` |  |
| T195 | Stock limits | H | [05](05-stock-products.md) | done | `tests/Http/Limits/StockLimitPagesTest.php`, `tests/Models/StockLimitTest.php` |  |
| T200 | `reduce` and `fallback_reduce` with dead volume | H | [05](05-stock-products.md) | done | `tests/Models/StockReduceModelTest.php` |  |
| T201 | `reduce_stock` paths | H | [05](05-stock-products.md) | done | `tests/Models/StockReduceModelTest.php` |  |
| T202 | `increase_stock` | H | [05](05-stock-products.md) | done | `tests/Models/StockReduceModelTest.php` |  |
| T203 | `move` edge cases | H | [05](05-stock-products.md) | done | `tests/Models/StockReduceModelTest.php` |  |
| T204 | `write_off` state transitions | H | [05](05-stock-products.md) | done | `tests/Models/StockReduceModelTest.php` |  |
| T205 | `fix_duplicates` | H | [05](05-stock-products.md) | done | `tests/Models/StockMaintenanceModelTest.php` |  |
| T206 | `remove_dead_products_from_stock` and `get_problems` | H | [05](05-stock-products.md) | done | `tests/Models/StockMaintenanceModelTest.php` |  |
| T207 | Stock read queries | H | [05](05-stock-products.md) | done | `tests/Models/StockQueriesModelTest.php` |  |
| T208 | Stock value snapshot | H | [05](05-stock-products.md) | done | `tests/Models/StockQueriesModelTest.php` |  |
| T209 | Products model queries | H | [05](05-stock-products.md) | done | `tests/Models/ProductsModelQueriesTest.php` |  |
| T210 | Wholesale index and search | H | [05](05-stock-products.md) | done | `tests/Http/Wholesale/WholesaleIndexTest.php` |  |
| T211 | Accept, ignore and unignore price changes | H | [05](05-stock-products.md) | done | `tests/Http/Wholesale/WholesalePriceChangesTest.php` |  |
| T212 | Wholesale history and AJAX | H | [05](05-stock-products.md) | done | `tests/Http/Wholesale/WholesaleHistoryTest.php` | VHB row never shown (typo) |
| T213 | Delivery overview | H | [05](05-stock-products.md) | done | `tests/Http/Wholesale/DeliveryOverviewTest.php` | register_in rows never shown |
| T215 | Write-off log and write-off invoice | H | [05](05-stock-products.md) | done | `tests/Http/Logs/LogsWriteOffTest.php` |  |
| T216 | Other log pages | H | [05](05-stock-products.md) | done | `tests/Http/Logs/LogPagesTest.php` |  |
| T217 | Logger | M | [05](05-stock-products.md) | done | `tests/Models/LogsModelTest.php` | logger ignores its user/location arguments |
| T220 | Vaccine reminder overview | H | [06](06-clinical.md) | done | `tests/Http/Vaccine/OverviewTest.php` |  |
| T221 | Vaccine CSV export | H | [06](06-clinical.md) | done | `tests/Http/Vaccine/ExportCsvTest.php` |  |
| T222 | Pet vaccination PDF | H | [06](06-clinical.md) | done | `tests/Http/Vaccine/ExportPetPdfTest.php` |  |
| T223 | Vaccine record pages and edits | H | [06](06-clinical.md) | done | `tests/Http/Vaccine/FicheEditTest.php` |  |
| T224 | External ("martian") vaccine | H | [06](06-clinical.md) | done | `tests/Http/Vaccine/MartianVaccineTest.php` | product_id 0: never in expiry lists |
| T225 | Vaccine model queries | M | [06](06-clinical.md) | done | `tests/Models/VaccineModelTest.php` |  |
| T230 | Dental chart | H | [06](06-clinical.md) | done | `tests/Http/Tooth/ToothTest.php` | chart renders only with short_open_tag=0 (set in serve.sh); dev container default gives a 500 |
| T235 | Rx list page | H | [06](06-clinical.md) | done | `tests/Http/Rx/RxListTest.php` |  |
| T236 | Rx import (CLI) | H | [06](06-clinical.md) | done | `tests/Http/Cli/ImportRxTest.php` | meta leaks between files |
| T240 | Chunked upload to an event | H | [06](06-clinical.md) | done | `tests/Http/Files/EventUploadTest.php` |  |
| T241 | Generic chunked upload (`append` / `file_complete`) | H | [06](06-clinical.md) | done | `tests/Http/Files/GenericUploadTest.php` |  |
| T242 | Delete a file | H | [06](06-clinical.md) | done | `tests/Http/Files/DeleteFileTest.php` |  |
| T243 | Preview of a type that cannot be previewed | H | [06](06-clinical.md) | done | `tests/Http/Files/PreviewTest.php` |  |
| T250 | Lab list and filters | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabIndexTest.php` | soft-deleted reports listed |
| T251 | Lab detail and print | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabDetailTest.php` | 500 without results / with 1-2 plot points |
| T252 | Lab list for one pet | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabListDeleteReassignTest.php` | delete is a hard delete |
| T253 | Delete a lab report (admin) | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabListDeleteReassignTest.php` | delete is a hard delete |
| T254 | Reassign a lab report | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabListDeleteReassignTest.php` | delete is a hard delete |
| T255 | Pending lab results (HTTP) | H | [06](06-clinical.md) | done | `tests/Http/Lab/LabPendingHttpTest.php` |  |
| T260 | Export index page | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/ExportIndexTest.php` |  |
| T261 | Owners XML export | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/OwnersXmlTest.php` | golden file golden_export_owners.xml (not yet reviewed by a human) |
| T262 | Pets XML export | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/PetsXmlTest.php` |  |
| T263 | Bills CSV export | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/BillsCsvTest.php` |  |
| T264 | Kluwer (Expert/M) XML export | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/KluwerXmlTest.php` | golden file golden_export_kluwer.xml (not yet reviewed by a human) |
| T265 | Merged invoice PDF | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Export/InvoicesPdfTest.php` |  |
| T271 | Stock list XLSX | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Reports/StockListXlsxTest.php` |  |
| T272 | Client list XLSX | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Reports/ClientListXlsxTest.php`, `ClientListEmptyXlsxTest.php` |  |
| T273 | Product usage reports | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Reports/UsageReportTest.php` |  |
| T274 | Products report and products CSV | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Reports/ProductsReportTest.php` |  |
| T275 | Register in, register out and revenue | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Reports/RegisterAndRevenueTest.php` |  |
| T276 | Stats (saved SQL queries) | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Stats/StatsTest.php` |  |
| T280 | Vamreg pages render | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/VamregPagesTest.php` |  |
| T281 | Edit IN and OUT buffer rows | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/VamregEditTest.php` |  |
| T282 | Lock, unlock, remove and restore | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/VamregStatusTest.php` |  |
| T283 | Post all drafts (stubbed API) | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/PostAllTest.php`, `tests/Models/VamregPostAllTest.php` | API handling tested in-process with a recording client |
| T284 | Refresh the product index (stubbed) | H | [07](07-reports-export-vamreg.md) | done | `tests/Models/VamregSyncTest.php`, `tests/Http/Vamreg/RefreshTest.php` |  |
| T285 | Reset (debug tool) | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/ResetTest.php`, `tests/Models/VamregPostAllTest.php` |  |
| T290 | Vamreg model logic | M | [07](07-reports-export-vamreg.md) | done | `tests/Models/VamregModelsTest.php` |  |
| T291 | Vamreg deadline banner | H | [07](07-reports-export-vamreg.md) | done | `tests/Http/Vamreg/DeadlineBannerTest.php` |  |
| T300 | Settings page | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Admin/SettingsTest.php` |  |
| T301 | Locations | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Admin/LocationsTest.php` |  |
| T302 | Booking codes | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Admin/BookingCodesTest.php` |  |
| T303 | Product types and labels | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Admin/ProductTypesTest.php` |  |
| T305 | User management | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Admin/MemberTest.php` | created user has empty username; duplicate e-mail allowed |
| T310 | Duplicate finders | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Tools/DuplicateFindersTest.php` |  |
| T311 | Merge clients | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Tools/MergeClientsTest.php` |  |
| T312 | API key management | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Tools/ApiKeysTest.php` |  |
| T313 | Covetrus connection test | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Tools/CovetrusConnectionTest.php` | network part skipped: needs F07 stub |
| T320 | Help, and refusing web access | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/CliHelpTest.php` |  |
| T321 | `cli daily` orchestration | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/DailyOrchestrationTest.php` | missing vamreg key aborts the run |
| T322 | Autoclose | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/AutocloseTest.php` |  |
| T323 | Prune | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/PruneTest.php` |  |
| T324 | Auto-death settings | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/AutoDeathTest.php` |  |
| T325 | Delivery import | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/DeliveryImportTest.php` | re-import duplicates |
| T326 | Price list import | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/PricelistImportTest.php` |  |
| T327 | Stock clean and usage recalculation | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/StockCleanUsageTest.php` |  |
| T328 | Monthly product usage dump | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/MonthlyProductUsageDumpTest.php` |  |
| T329 | Rate-limit cleanup and Vamreg update | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/RateLimitVamregTest.php` | real API part skipped: needs F07 stub |
| T335 | API authentication and rate limit | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Api/ApiAuthTest.php` |  |
| T336 | Lab import endpoint | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Api/LabImportTest.php` |  |
| T340 | Migrating up and down | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Cli/UpgradeTest.php` | rebuilds the database afterwards |
| T345 | Route smoke crawl | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Smoke/RouteSmokeTest.php`, `tests/Fixtures/routes.php` |  |
| T346 | No PHP notices on the main pages | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Smoke/NoPhpNoticesTest.php` | testing env hides notices: checks Severity text and the CI log only |
| T347 | Flash messages survive redirects | H | [08](08-admin-tools-cli-api.md) | done | `tests/Http/Smoke/FlashMessagesTest.php` |  |
| T348 | Every log event name | H | [08](08-admin-tools-cli-api.md) | done | `tests/Pure/LogEventCoverageTest.php` | ratchet: new untested log events fail |
| T349 | Replace the CI3-only contract tests | H | [08](08-admin-tools-cli-api.md) | done | (mapping in 08-admin-tools-cli-api.md) | contract tests stay as ci3-only until the port |
| T350 | MY_Model reads | M | [09](09-model-layer.md) | done | `tests/Models/MyModelReadTest.php` |  |
| T351 | MY_Model writes | M | [09](09-model-layer.md) | done | `tests/Models/MyModelWriteTest.php` |  |
| T352 | MY_Model relations | M | [09](09-model-layer.md) | done | `tests/Models/MyModelRelationsTest.php` |  |
| T353 | Which models use soft deletes | M | [09](09-model-layer.md) | done | `tests/Models/SoftDeleteInventoryTest.php` |  |
| T360 | Owners statistics | M | [09](09-model-layer.md) | done | `tests/Models/OwnersStatisticsTest.php` |  |
| T361 | Pets statistics and duplicates | M | [09](09-model-layer.md) | done | `tests/Models/PetsStatisticsTest.php` |  |
| T362 | Breed statistics | M | [09](09-model-layer.md) | done | `tests/Models/BreedsStatisticsTest.php` |  |
| T363 | Config_model | M | [09](09-model-layer.md) | done | `tests/Models/ConfigUsersRxModelsTest.php` |  |
| T364 | Users_model and Groups | M | [09](09-model-layer.md) | done | `tests/Models/ConfigUsersRxModelsTest.php` |  |
| T365 | Rx_model | M | [09](09-model-layer.md) | done | `tests/Models/ConfigUsersRxModelsTest.php` |  |
| T366 | ApiKey_model and ApiRate_model | M | [09](09-model-layer.md) | done | `tests/Models/ApiAndLabModelsTest.php` |  |
| T367 | LabReport_model and LabResult_model | M | [09](09-model-layer.md) | done | `tests/Models/ApiAndLabModelsTest.php` |  |
| T368 | Liquidate_model | M | [09](09-model-layer.md) | done | `tests/Models/LiquidateModelTest.php` |  |
| T369 | Register_in model | M | [09](09-model-layer.md) | done | `tests/Models/RegisterInDeliveryModelsTest.php` |  |
| T370 | Events products and procedures aggregates | M | [09](09-model-layer.md) | done | `tests/Models/EventsLinesAggregatesTest.php` |  |
| T371 | Wholesale_model | M | [09](09-model-layer.md) | done | `tests/Models/WholesaleModelTest.php` |  |
| T372 | Product_price_model | M | [09](09-model-layer.md) | done | `tests/Models/ProductPriceModelTest.php` |  |
| T373 | Pets_model avatar persistence and transfer | M | [09](09-model-layer.md) | done | `tests/Models/PetTransferFailureReasonsTest.php` |  |
| T374 | Events_lab_model and LabReportPending_model | M | [09](09-model-layer.md) | done | (no new file) | existing lab model tests already cover it; only LabReportPendingModelTest still uses existingId() |
