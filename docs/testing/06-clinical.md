# 06 — Vaccines, teeth, Rx, files/uploads, lab (T220–T259)

Default layer is H. Lab internals already have a lot of Layer M coverage (see `tests/Integration/Lab*`, `tests/Models/LabReport*`). Lab tasks here focus on the HTTP surface, which has no coverage today.

## Vaccines (`Vaccine.php`, `Vaccine_model.php`; table `vaccine_pet`, hard delete)

### T220 — Vaccine reminder overview
- **File:** `tests/Http/Vaccine/OverviewTest.php`.
- **Assert:** `vaccine/index/1` lists the vaccines whose `redo` falls in next month.
- **Excluded** from the list:
  - a dead pet's vaccine;
  - a lost or transferred pet's vaccine;
  - a vaccine with `no_rappel=1`;
  - a vaccine whose owner is disabled.
- **Assert:** `vaccine/index/0` shows the current month.
- **Boundary guard:** see T039.

### T221 — Vaccine CSV export
- **File:** `tests/Http/Vaccine/ExportCsvTest.php`.
- **Assert:** a GET of `vaccine/export/1` shows the list of products that can be excluded.
- **Assert:** a POST with `submit` and `excluded_products[]=<pid>` returns a download named `vaccines_{M_Y}.csv` and writes a `downloaded_vaccine_list` log row.
- **Assert:** the body contains the owner name and pet name of an included vaccine, and does not contain the excluded product.
- **Pin the column order** from `views/vaccine/export.php`. The CSV layout is a contract with external tooling.

### T222 — Pet vaccination PDF
- **File:** `tests/Http/Vaccine/ExportPetPdfTest.php`.
- **Assert:** `vaccine/export_vaccine/{pet}` returns a PDF attachment whose filename contains `export_{pet}_vaccines_`.
- **Assert:** a missing pet redirects to `/`.

### T223 — Vaccine record pages and edits
- **File:** `tests/Http/Vaccine/FicheEditTest.php`.
- **Assert:** `vaccine/fiche/{pet}` lists the pet's vaccines, and `vaccine/detail/{id}` shows one record.
- **Update** with `vaccine/update/{vid}/{pet}`:
  - POST `date_redo` and `no_rappel=on` store `no_rappel=0`. The checkbox is inverted: checked means "remind me";
  - leaving the checkbox out stores `no_rappel=1`;
  - either way the request redirects to `vaccine/fiche/{pet}`.
- **Remove:** `vaccine/remove/{vid}/{pet}` hard-deletes the row.

### T224 — External ("martian") vaccine
- **File:** `tests/Http/Vaccine/MartianVaccineTest.php`.
- **Assert:** a POST to `vaccine/add_martian_vaccine/{pet}` with `submit`, `vaccine=Rabies ext`, `date_redo`, `no_rappel=on` and `created_at=<date>` inserts a `vaccine_pet` row with these values:
  - `product='Rabies ext'`;
  - `event_id=0`;
  - `created_at` = the posted date;
  - `vet` = the logged-in user.
- **Pin (`known-bug`):** `product_id` is NULL, so the vaccine does **not** appear in `vaccine/fiche` summaries or the expiry lists.

### T225 — Vaccine model queries
- **Layer:** M. **File:** `tests/Models/VaccineModelTest.php`.
- **`view(pet)`:** grouped by product, with `max_rappel` taken only from rows where `no_rappel=0`.
- **`get_expiring_vaccines_product_list(date)`.**
- **`get_expiring_vaccines(date, excluded)`:**
  - check the full column list (it is the export contract);
  - the companion-owner rule: when `pets.companion` is set, the companion's `disabled` flag decides.

## Teeth

### T230 — Dental chart
- **File:** `tests/Http/Tooth/ToothTest.php`.
- **Chart:** `tooth/fiche/{pet}` returns 200.
- **Set a tooth:** `tooth/update/{pet}` with POST `tooth=11`, `color=red`:
  - echoes `{"tooth":"11","color":"red"}`;
  - upserts a `tooth` row for `(pet, 11)`;
  - calling it again with another colour updates the same row, so there is still exactly one row.
- **Message:** `tooth/store/{pet}` with POST `message` inserts a `tooth_msg` row and redirects to `tooth/fiche/{pet}/update_text`. The fiche then shows the newest message.
- **History:** `tooth/history/{pet}` lists every message, newest first.

## Rx (X-ray)

### T235 — Rx list page
- **File:** `tests/Http/Rx/RxListTest.php`.
- **Assert:** with 3 seeded `rx` rows over 2 study dates, `rx/list/{pet}` shows 2 groups, newest first, with the descriptions.

### T236 — Rx import (CLI)
- **File:** `tests/Http/Cli/ImportRxTest.php`. This is a CLI test; see 08 for how to run CLI commands.
- **Setup:** create `data/stored/rx/<YYYY-MM-DD_x>/a.jpg` together with a sidecar `a.jpg.txt`. Check the exact sidecar name in `Cli::import_rx`. The sidecar holds `PatientID:<pet>`, `PatientName:CLIENT^PET`, `PatientSex:F`, `StudyDate`, `SeriesDescription`, `BodyPartExamined` and `SeriesNumber`.
- **Assert:** running `php index.php cli import_rx 1` inserts an `rx` row with all the mapped fields and `gender=FEMALE`.
- **Assert:** running it again inserts nothing.
- **Pin (`known-bug`):** metadata from one file leaks into the next when the second sidecar is missing a key.
- **Clean up** the files afterwards.

## Files and uploads

### T240 — Chunked upload to an event
- **File:** `tests/Http/Files/EventUploadTest.php`.
- **Upload steps:**
  1. POST `files/new_file_event/{event}` with multipart `data`, in 2 chunks.
  2. POST `files/new_file_event_complete/{event}` with `file_name=scan.png` and `file_size`.
- **Expected result:**
  - the response is `{"success":true}`;
  - the file exists at `data/upload/e{event}_scan.png`;
  - an `events_upload` row exists with filename, size, mime `image/png`, user and location.
- **Fetching it back:**
  - `files/get_file/{id}` returns the same bytes as an attachment;
  - `files/preview/{id}` returns them inline with `X-Content-Type-Options: nosniff`.
- **Wrong type:** completing an upload of a `.php` or `.exe` file returns `{"success":false,"error":"wrong mime type"}` and deletes the temp file.
- **Pin (`known-bug`):** a `file_name` containing spaces is saved on disk under the raw name, while the DB stores the sanitised name. `get_file` then fails.

### T241 — Generic chunked upload (`append` / `file_complete`)
- **Assert:** `files/append/{id}` followed by `files/file_complete/{id}` with `file_name` returns `{"success":true,"file":"f{id}_name"}` and the file exists under `data/upload/stored/`.
- **Assert:** completing an upload that has no temp file returns `{"success":false,"error":"no file"}`.

### T242 — Delete a file
- **Assert:** `files/delete_file/{id}` by the uploading user removes both the file and the DB row.
- **Pin (`known-bug`):** when another vet calls it, the **file is deleted** but the DB row stays.

### T243 — Preview of a type that cannot be previewed
- **Assert:** `files/preview/{id}` for a PDF upload returns 404, and so does `preview/999999`.

## Lab (web, `Lab.php`)

### T250 — Lab list and filters
- **File:** `tests/Http/Lab/LabIndexTest.php`.
- **Assert:** `lab` lists reports from the last 2 months, and a POST with `search_from`/`search_to` filters the list.
- **Pin (`known-bug`):** a report with `deleted_at` set is still listed.

### T251 — Lab detail and print
- **File:** `tests/Http/Lab/LabDetailTest.php`.
- **Assert:** `lab/detail/{id}` shows the pet name, owner, and each result code with its value and range.
- **Assert:** `lab/print/{id}` returns `application/pdf` inline.
- **Pin (`known-bug`):** a report with no results returns 500.
- **Pin (`known-bug`):** a report with exactly 1 or 2 numeric points for a plot returns a 500 from the chart code.

### T252 — Lab list for one pet
- **Assert:** `lab/list_lab/{pet}` lists that pet's reports, newest `sample_date` first.
- **Assert:** a missing pet redirects to `/`.

### T253 — Delete a lab report (admin)
- **Assert:** as admin, `lab/delete/{id}` redirects to `lab`.
- **Pin (`known-bug`):** the `lab_report` row is **hard-deleted** (because of the `$soft_delete` typo) and its `lab_results` rows are left behind.
- **Assert:** as vet it redirects to `lab` and nothing is deleted.

### T254 — Reassign a lab report
- **Assert:** a GET returns 405.
- **Assert:** a POST to `lab/reassign/{id}` with `owner_id`/`pet_id` of another pet:
  - updates `lab_report.pet_id`;
  - removes the `events_labs` links to the old pet's events;
  - writes a `lab_report_reassigned` log row;
  - redirects to `lab/detail/{id}` with a success flash.
- **Assert:** reassigning to the same pet shows the info flash `lab_reassign_noop`.

### T255 — Pending lab results (HTTP)
- **File:** `tests/Http/Lab/LabPendingHttpTest.php`.
- This replaces the coverage of `LabPendingControllerContractTest`.
- **Assert:** `lab/pending` lists the active pending rows, and `lab/pending_detail/{id}` shows the identifiers and the raw JSON. A missing ID returns 404.
- **Assert:** `lab/search_owners?term=<name>` returns `{"results":[{"id","text":"First Last (#id) - street nr zip"}]}`.
- **Assert:** `lab/search_pets?owner_id=&term=` returns that owner's assignable pets.
- **Assert:** a POST to `lab/recover_pending/{id}` with `owner_id`/`pet_id`:
  - creates a `lab_report` linked to that pet;
  - marks the pending row resolved;
  - redirects to `lab/detail/{reportId}`.
- **Assert:** a GET to `recover_pending` returns 405.
- **Assert:** a POST to `lab/delete_pending/{id}` soft-deletes the row (`deleted_at`, `deleted_by`) and redirects to `lab/pending`.
