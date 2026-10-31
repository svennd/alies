# 03 — Owners, pets, weights, breeds, search, stickies, vet profile (T050–T099)

Defaults for this file unless a task says otherwise: Layer H, files in `tests/Http/Owners/` or `tests/Http/Pets/`, logged in as `vet` at location 1.

Delete behaviour:
- **Soft deletes:** `owners`, `pets`, `sticky`.
- **Hard deletes:** `pets_weight`, `vaccine_pet`, `breeds`.

## Owners

### T050 — Add an owner
- **Source:** `Owners::add`.
- **POST:**
  - `submit`, `last_name`, `first_name`, `street`, `nr`, `zip`, `city`, `main_city`, `province`;
  - `phone` (stored as `telephone`), `mobile`, `phone2`, `phone3`, `mail`;
  - `btw_nr`, `invoice_addr`, `invoice_contact`, `invoice_tel`, `msg`;
  - checkboxes `low_budget`, `debts`, `contact`.
- **Assert the row:**
  - every field is stored;
  - the checkboxes become 1, or 0 when they are left out;
  - `last_bill` is today, `initial_vet` is the vet's ID, `initial_loc` is 1.
- **Assert:** it redirects to `owners/detail/{id}` and writes a `logs` row `add_client`.
- **Negative case:** an empty `last_name` re-renders the form and inserts no row.
- **Post the complete form** (`Forms::owner()`): fields that are not posted are inserted as NULL, and several columns are NOT NULL, so a minimal POST is a **500** database error (`known-bug`).
- **Pin (`known-bug`):** names are stored and printed without escaping (stored XSS); phone numbers are stored exactly as typed.

### T051 — Edit an owner
- **Assert:** a POST to `owners/edit/{id}` updates the fields, including `disabled`, and redirects to `owners/detail/{id}/true`.
- **⚠ BUG (data loss):** unlike "add", a partial form does not fail: omitted fields are blanked (`''`/NULL) and unposted checkboxes switch off. A soft-deleted owner can still be edited. Both pinned as `known-bug`.
- **Assert:** `logs` gets `update_client`.
- **Assert:** a GET shows the current values in the form.

### T052 — Owner detail page
- **Assert:** the page shows the owner's name, all non-transferred pets, and the open bills (status DRAFT, PENDING, UNPAID, INCOMPLETE, OVERDUE or ONHOLD; not PAID, HISTORICAL or INVALID).
- **⚠ BUG:** the intended "youngest pet first" order never worked (`order_by(array(...))`); pets come in the order they were added.
- **Assert:** a transferred pet and a PAID bill are not shown.
- **Record:** what `owners/detail/999999` returns. The current code has no guard. Pin the status.

### T053 — Owner AJAX endpoints
1. `owners/set_email/{id}`: POST `email` stores `mail`. The response body is empty.
2. `owners/phone`: POST `phone="0470 12 34 56"` for a seeded owner returns `{"exists":true,"owner":{…id…}}`. An unknown number returns `{"exists":false}`.
3. `owners/addr` **with** the `X-Requested-With: XMLHttpRequest` header returns JSON with matches on street, nr and zip. **Without** the header the body is empty.
4. `owners/get_zip/2000` with the AJAX header returns the zipcode row. Without the header the body is empty.

### T054 — Owner invoices and products pages
- **Assert:** `owners/invoices/{id}` lists the owner's bills from the last 12 months and leaves out older ones. POST `search_from`/`search_to` widens the range.
- **Assert:** `owners/products/{id}` lists products used on events billed to this owner within the range.

## Pets

### T060 — Add a pet
- **POST:**
  - `submit`, `owner`, `type`, `name`, `gender`, `birth`;
  - `breed`, `breed2`, `color`, `hairtype`;
  - `chip="981-000-123"` (the dashes are stripped);
  - `weight=12.5`, `vacbook`, `msg`.
- **Assert the row:**
  - `chip='981000123'`;
  - `last_weight=12.5`, and a `pets_weight` row exists;
  - `nr_vac_book` and `note` are stored;
  - `location=1`, `init_vet` is the vet;
  - an empty `companion` is stored as NULL.
- **Assert:** it redirects to `pets/fiche/{id}` and writes a `logs` row `add_pet`.
- **Negative case:** a missing `name` redirects to `pets/add/{owner}` and inserts nothing.
- **Pin (`known-bug`):** the owner comes from POST `owner`, not from the URL argument.

### T061 — Edit a pet
- **POST** to `pets/edit/{id}`.
- **Breed:**
  - an empty `breed` keeps `current_breed`;
  - `breed2=-1` stores NULL.
- **Gender:** `gender_custom` overrides `gender`.
- **Weight:** a new weight different from the last one inserts a `pets_weight` row; the same weight inserts nothing.
- **Death:**
  - `dead=1` sets `death=1` and `death_date` = today.
  - **Pin (`known-bug`):** editing the dead pet again resets `death_date` to today. Set it to an older date in the DB before the second edit.
- **Assert:** `lost` is stored. The request redirects to `pets/fiche/{id}`.

### T062 — Pet fiche page
- **Assert:** `pets/fiche/{id}` shows:
  - the pet name, owner, breed and last weight;
  - its vaccines, with expiry;
  - "other pets" of the owner (at most 5, alive and not lost);
  - the medical history events (T068);
  - Rx and lab indicators when the pet has `rx` or `lab_report` rows.
- **Assert:** a missing pet returns 404.

### T063 — Delete a pet (admin)
- **Assert:** as admin, `pets/delete/{id}` sets `deleted_at` and redirects to `owners/detail/{owner}`.
- **Assert:** the pet disappears from the owner's detail page and from search.
- **Assert:** as vet it redirects to `/` and nothing changes.

### T064 — Weight endpoints
- **Assert:** `pets/push_weight/{id}` (AJAX POST `weight`) inserts a row and updates `last_weight`. `weight=0` inserts nothing.
- **Assert:** `pets/add_weight/{id}` (POST `submit`, `weight`) does the same and redirects to `pets/history_weight/{id}`.
- **Assert:** `pets/history_weight/{id}` lists the weights in ascending order.
- **Assert:** `pets/del_weight/{wid}/{pid}` hard-deletes the row and redirects.
- **Pin (`known-bug`):** `last_weight` is not recalculated after a delete.

### T065 — Pet name autocomplete and chip check
1. `pets/get_pet_name?term=` returns `{"results":[]}`.
2. `?term=<pet id>` returns `{"results":[{"id":…,"text":"Name(#id) - LastName"}]}`.
3. `?term=<name prefix>` returns at most 5 results, with no dead pets.
4. `pets/check_chip/0/<existing chip>` returns `{"status":"200","pet_id":…,"pet_name":…,"owner_id":…,"owner_name":…}`.
5. `check_chip/<that same pet id>/<its chip>` returns `{"status":"404"}`.
6. An unknown chip returns `{"status":"404"}`.

### T066 — Pet transfer to a new owner (HTTP)
- The existing `tests/Integration/PetTransferWorkflowTest.php` covers the model. This task covers the **HTTP flow**.
- **Assert:** `pets/change_owner/{pet}` with POST `submit=name`, `name=<prefix>` lists the matching owners. `submit=client`, `client=<id>` selects one.
- **Assert:** `GET pets/change_owner_complete/{pet}/{newOwner}`:
  - redirects to `owners/detail/{newOwner}`;
  - the source pet has `transfered=1` and `chip=NULL`;
  - a successor pet exists for the new owner;
  - the source's events are copied to the successor with `status=2`;
  - a `transfer_pet` log row is written.
- **Negative case:** transferring to the current owner, or to a disabled owner, redirects back to `pets/change_owner/...` with a flash failure. Nothing changes.

### T067 — Pet history export as PDF
- **Assert:** `pets/export/{id}` (GET) returns 200 and shows a form.
- **Assert:** POST `submit` + `history_to_take` returns a PDF attachment: `Content-Disposition` contains `export_{id}_`, and the body starts with `%PDF`.
- **Assert:** a missing pet redirects to `/`.

### T068 — Medical history shown on the fiche
- **Source:** `Events_model::get_pet_history`.
- **Setup:** 3 events for the pet:
  - one with 2 vets;
  - one with `no_history=1`;
  - one with an upload and a linked lab.
- **Assert:**
  - the hidden event is not shown;
  - the remaining events are newest first;
  - each event lists its products and procedures;
  - the upload and lab counts are shown;
  - the vet names are de-duplicated.
- **Cross-reference:** the existing `tests/Models/PetMedicalHistoryModelTest.php` covers the model. Keep this test on the HTTP output.

### T069 — Pet avatar over HTTP
- The existing `tests/Integration/PetAvatarWorkflowTest.php` and the library tests cover the internals.
- **Assert:**
  - a GET to `pets/save_avatar/{id}` returns 405;
  - a valid POST multipart upload (`pet_avatar_source` + `pet_avatar_crop` data URI, using a small JPEG fixture) redirects to the fiche;
  - `pets.avatar` then matches `pet_[a-f0-9]{32}.jpg`;
  - `pets/avatar_file/{id}` returns `image/jpeg` with `X-Content-Type-Options: nosniff`;
  - `remove_avatar` (POST) clears it, and `avatar_file` returns 404 afterwards;
  - an invalid crop shows a flash error and leaves the avatar unchanged.

## Breeds

### T075 — Breed list and detail
- **Assert:** `breeds` lists the breeds with their pet counts.
- **Assert:** `breeds/index/{id}` shows the alive, not-lost pets of that breed with their owner street and city.

### T076 — Add and edit a breed
- **Assert:** `breeds/add` POST (`name`, `type`, the 4 weight fields) inserts a row with `freq=0` and redirects to `breeds`. A vet is allowed to do this.
- **Assert:** `breeds/edit/{id}` as admin updates the row. As vet it redirects to `/`.
- **Pin (`known-bug`):** `breeds/edit/999999` gives a fatal error, status 500.

### T077 — Breed search autocomplete
- **Assert:** `breeds/search_breed/lab?type=0` returns `{"results":[{"id","text"}]}`, ordered by `freq` descending.
- **Pin (`known-bug`):** no match returns an **empty body**, not JSON.

### T078 — Merge breeds
- **Assert:** as admin, POST `new_breed=B` to `breeds/merge/{A}` moves `pets.breed` from A to B, deletes breed A and redirects to `breeds`.
- **Pin (`known-bug`):** `pets.breed2=A` is **not** updated.
- **Assert:** merging A into A echoes "You are trying to merge the same breed".

### T079 — Rebuild breed frequencies
- **Setup:** 6 young, alive pets of breed X and 2 of breed Y.
- **Assert:** after `breeds/rebuild_frequenty`, `freq(X)=6` and `freq(Y)=0`. Every other breed is reset to 0. Breed 1 is skipped.

## Search

### T085 — Global search
- **Source:** `Search::index`. The route is `search?search_query=…`.
- **Cases:**
  1. An empty query redirects to `/`.
  2. A last-name prefix finds the owner.
  3. A first-name prefix finds the owner.
  4. Part of a street name finds the owner.
  5. A phone number with spaces finds the owner.
  6. A pet name prefix finds the pet but not a dead pet.
  7. `!<name>` finds the dead pet as well.
  8. A chip of 10 or more digits finds the pet.
  9. A short number (the pet ID) finds the pet, and also the owner with that ID.
  10. A breed name of 4 or more characters finds pets of that breed.
  11. A query containing `<script>` is escaped in the output.

### T086 — Owner search methods
- **Layer:** M. **File:** `tests/Models/OwnersSearchTest.php`.
- **Cover:**
  - `search_by_first_name`, `search_by_last_name`, `search_by_street_ex`;
  - `search_by_phone_ex` (prefix match across 4 columns, limit);
  - `search_by_addr`, `search_by_name` (includes at most 2 pets);
  - `search_for_lab_assignment` (by ID and by name);
  - `is_assignable`.
- **Pin:** the raw-SQL searches do **not** filter soft-deleted or disabled owners. Document this with a deleted owner that still shows up.

### T087 — Pet search methods
- **Layer:** M. **File:** `tests/Models/PetsSearchTest.php`.
- **Cover:**
  - `search_by_name`: dead pets are excluded unless `dead_allowed`; transferred pets are excluded; lost pets are **included**;
  - `search_by_chip_ex` and `search_by_id`. **Pin:** the `id` column they return is the **owner** ID;
  - `other_pets`, `get_all_pets`;
  - `search_assignable_for_owner`, `is_assignable_to_owner`;
  - `findByOwnerPhoneAndPet`, `findByOwnerAndPet`, `findByChipNumber`, `findByPetName`;
  - `does_pet_exist`.

### T088 — Auto-death
- **Layer:** M. **File:** `tests/Models/PetsAutoDeathTest.php`.
- **Assert:** `auto_death(DOG, 15)` marks a dog older than 15 with no events in the last 2 years as dead, with `death_date` = today−14 days and the note suffix `[auto-death]`.
- **Assert:** a dog of the same age that had an event a year ago is untouched. A young dog and a cat are untouched.
- **Assert:** it returns the number of affected rows.

## Sticky notes, vet profile, dashboard

### T090 — Sticky notes
- **Assert:** `sticky/add` (POST `content="<b>x</b>"`, `private=1`) stores `note` escaped as `&lt;b&gt;x&lt;/b&gt;`, together with `user_id` and `location`.
- **Assert:** `sticky` lists all stickies. **Pin:** private ones are shown too.
- **Assert:** `sticky/delete/{id}` soft-deletes the vet's own note. Deleting another user's note as vet does nothing; as admin it soft-deletes it. Both redirect to `sticky`.

### T091 — Vet profile
- **Assert:** `vet/profile` returns 200.
- **Assert:** `vet/profile_change` POST (`color`, `first_name`, `last_name`, `search_config`, `user_date`, `phone`, `order_nr`) updates `users` (`color` is stored as `sidebar`) and redirects to `vet/profile`.
- **Assert:** `vet/pub/{id}` shows the public profile of any user.

### T092 — Vet list autocomplete
- **Assert:** `vet/ajax_get_vets?term=adm` returns `{"results":[{"id","text"}]}` and leaves out the current user.
- **Assert:** without `term` it returns the active vets (group 3).
- **Pin:** no results gives an empty body.

### T093 — Home page
- **Assert:** `GET /` for a vet with a location returns 200 and shows the search form (`name="search_query"`) and a logout link.
- **Assert:** the header counters match the DB: sticky count, open reports of the user (`events` of this vet with `report` not 2 and updated in the last 7 days), stock error count (`state=3`), and recent pending lab items. For the seed: "Plakbriefjes 1", "Verslagen 2".
- **Assert:** the page does not print the user's name (the public profile `vet/pub/{id}` does).
- **Assert:** the call runs the migrations, and `migrations.version` is unchanged afterwards.

### T094 — Help pages
- **Assert:** `help/vet` returns 200.
- **Assert:** `help/admin` as admin returns 200.
