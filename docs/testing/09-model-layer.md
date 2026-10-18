# 09 — Model layer and MY_Model (T350–T399)

Every test here is **Layer M**. It boots CI3 in-process, extends `CodeIgniterDatabaseTestCase` (each test is rolled back) and uses `Fixtures::` IDs.

The models will be rewritten for CI4 (`CodeIgniter\Model`). When that happens, each of these tests is ported **together with its model**: the inputs and the expected DB state or return value stay the same, and only the call syntax changes. Write the tests with that in mind:
- one behaviour per test method;
- set up state with explicit inserts;
- assert on plain arrays and scalars.

## MY_Model contract

The CI4 models must reproduce these semantics, or every caller has to be changed.

### T350 — MY_Model reads
- **File:** `tests/Models/MyModelReadTest.php`. Use `Sticky_model` (soft deletes) and `Breeds_model` (no soft deletes) as subjects.
- **`get` and `get_all`:**
  - return `false`, not `[]` or `null`, when there are no rows;
  - `get(int)` looks up by primary key;
  - `get(['col'=>v])` filters by column.
- **Soft deletes:** `get_all()` leaves out soft-deleted rows; `with_trashed()` includes them; `only_trashed()` returns only them.
- **`count_rows`.**
- **`fields('a, b')`:** limits the columns, and the selection resets after the read.
- **`where`:** `where('col', [1,2])` becomes an IN clause, and `where('col','like','x')` a LIKE.
- **`order_by` and `limit`.**

### T351 — MY_Model writes
- **File:** `tests/Models/MyModelWriteTest.php`.
- **`insert`:**
  - returns the new ID and sets `created_at`;
  - inserting several rows returns an array of IDs;
  - an unknown column causes `show_error('MY_Model: Unknown column…')`. Check how that surfaces in the CLI (maybe exit). Skip it if it kills the process.
- **`update`:**
  - `update($data, $id)` sets `updated_at` and returns the number of affected rows;
  - `update($data, ['col'=>v])` works.
- **Pin (dangerous):** `update($data)` with no WHERE updates **every row**. Verify this on a temporary table or inside the rollback.
- **`delete` and `restore`:**
  - `delete($id)` on a soft-delete model sets `deleted_at`;
  - on a hard-delete model it removes the row;
  - `restore($id)` clears `deleted_at`.

### T352 — MY_Model relations
- **File:** `tests/Models/MyModelRelationsTest.php`.
- **`has_one`:** `Bills_model->with_owner('fields:last_name')->get($id)` puts the related row under `owner`.
- **Unmatched has_one, same-named key:** if the relation key has the same name as the local column (`vet` and `location` on bills), the scalar value is kept.
- **`has_many`:** `Pets_model->with_pets_weight()` returns a list.
- **Soft deletes on the related model:** the related model's soft-delete filter applies. A soft-deleted owner is **not** attached.
- **`where:` inside `with_`:** rows with no match are dropped.

### T353 — Which models use soft deletes
- **File:** `tests/Models/SoftDeleteInventoryTest.php`.
- **Data-driven:** for every model in `application/models`, assert whether `soft_deletes` is true. The expected list:
  - **true:** Booking_code, Bills, Owners, Pets, Payment, Stock_location, Procedures, Products, Sticky;
  - **false:** all the others.
- **Pin (`known-bug`):** `LabReport_model` declares `$soft_delete`, which is the wrong name, so soft deletes are off for that model.
- This test acts as the checklist for the CI4 `$useSoftDeletes` settings.

## Remaining model methods (not covered by earlier tasks)

### T360 — Owners statistics
- **Methods:** `get_per_city`, `get_per_province`, `last_bill_by_year_month_init_vet(5)`, `duplicate_phone` and `disable(id, newOwner)`.
- **Pin (`known-bug`):** `duplicate_owner_street` throws a DB error.

### T361 — Pets statistics and duplicates
- **Methods:** `get_per_type` counts only alive, non-transferred pets. `duplicate_chips` ignores empty chips and transferred pets.
- **`format_transfer_history`:**
  - quantities `1.50` → `1.5`, `2.00` → `2`, and `-0` → `0`;
  - item names are HTML-escaped;
  - an `<hr>` is added only when the report is not blank.

### T362 — Breed statistics
- **Methods:**
  - `Breeds_model::search_by_name`: fewer than 4 characters returns `[]`; owners must have a non-empty `last_bill`.
  - `get_breed_stats`: age under 20, `last_weight` not 0, alive, not lost.

### T363 — Config_model
- **Assert:** `store('x','v')` saves `base64('v')`, and a second store updates the same row.
- **Assert:** `get_value('x')` returns `'v'`, and a missing key returns `''`.

### T364 — Users_model and Groups
- **Assert:** `get_active_vets()` returns only active users in group 3, with exactly the columns `id, username, first_name, last_name, last_login, image`.

### T365 — Rx_model
- **Assert:** `get_images` groups rows by `studydate` (descending), with the CSV `group_concat` fields.
- **Assert:** `count_for_pet` and `has_images_for_pet` return the expected values.

### T366 — ApiKey_model and ApiRate_model
- **`findActive`:** matches when `active=1` and returns false for a revoked key.
- **`hit`:** returns true for counts 1 to 60 and false at 61. A different minute starts a new count.
- **`cleanup(2)`:** deletes rows older than 2 days and keeps recent ones.

### T367 — LabReport_model and LabResult_model
- Some coverage already exists. Fill these gaps:
  - `findBySource` with the device or source candidate. Pin that it ignores `deleted_at`.
  - `claimSource`: returns the existing ID for a duplicate source and a new ID otherwise.
  - `get_for_pet`, `count_for_pet` and `has_for_pet`.
  - `LabResult_model::save`, `deleteByReport`, and `get_grouped_by_reports([])`, which returns `[]`.

### T368 — Liquidate_model
- **`get_unbilled`:** ordered by `created_at` ascending; returns false when there are no rows.
- **`count_unbilled`.**
- **`assign_bill`:** an empty array returns false. Rows that already have a bill are not reassigned.
- **`get_bill_rows`:** check the columns, and that `unit_sell` is `''` (COALESCE) when the product has no unit.

### T369 — Register_in, Booking_code and Delivery models
- **`Register_in_model`:**
  - `get_last_net_brut(pid)` returns `[in_price, cat_price, 'd-m-Y']` from the latest slip, or `[0,0,0]`;
  - `date_lookup`;
  - `get_delivery(date)`.
- **`Booking_code_model::get_usage_detail`:** returns `[products, procedures]` for CLOSED events only.
- **Pin (`known-bug`):** `Delivery_model::record` is unused and stores a NULL `created_at`. Document this, and consider removing it in CI4.

### T370 — Events products and procedures aggregates
- **`Events_products_model`:**
  - `get_monthly_earning` returns a per-VAT array plus `total`; with no data it returns the `{6:0, 21:0, total:0}` shape;
  - `get_monthly_usage`;
  - `usage_summary`: the rollup `total` row, and the reduction-reason counts for `TIER_REDUCTION`, `AUTO_REDUCTION` and `MANUAL`.
- **`Events_procedures_model`:** `get_monthly_earning` and `get_net_income` (3-month and 1-year windows).

### T371 — Wholesale_model
- **`get_bruto_increase`:** only price increases, with the percentage rounded to 1 decimal.
- **`update_price`:** returns false when the price is unchanged. Otherwise it inserts a history row with the **old** bruto.
- **`update_record`:** check both the insert path and the update path (see T326).
- **Other methods:** `get_price_diff`, `search_index`, `get_index_stats`, `get_unlinked_items` and `get_wh_id`.

### T372 — Product_price_model
- **Assert:** `add_price` returns the new ID and writes an `add_price` log row.
- **Assert:** `update_price` writes `modify_price`.
- **Assert:** `rm_price` returns the product ID, or 0 and an ERROR log row when the price does not exist.

### T373 — Pets_model avatar persistence and transfer
- **Already covered by:** `PetAvatarPersistenceTest`, `PetTransferWorkflowTest` and `PetTransferRuntimeRollbackTest`.
- **Task:** check that these tests use `Fixtures::` IDs (F08) and cover the four `transfer_pet` failure reasons:
  - source missing or already transferred;
  - owner missing, deleted or disabled;
  - owner already owns the pet;
  - dental conflict.
- Add tests for any reason that is missing.

### T374 — Events_lab_model and LabReportPending_model
- **Already covered.** Check that they use `Fixtures::` IDs instead of `existingId()`, which picks whatever happens to be in the DB.
