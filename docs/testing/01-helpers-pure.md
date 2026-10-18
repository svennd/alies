# 01 — Helpers, device parsers, pure libraries (T001–T029)

Rules for every task in this file:
- **Layer:** P (pure). Files go in `tests/Pure/…`. CodeIgniter stand-ins (`base_url`, `get_instance`, `write_file`) are in `tests/Pure/stubs.php`; they only define themselves when the framework is not loaded.
- **No DB and no CI boot.** At the top of each test, `require_once` only the helper or library file it needs. Define any constants it uses in the test. Do not include `constants.php`; copy the numbers in and note where they came from.
- If a helper calls a CI function (`base_url`, `timespan`, `write_file`, `get_instance`), define a minimal stub in `tests/Pure/stubs.php`, guarded with `if (!function_exists(...))`. If stubbing is not practical, move the task to Layer M and say so.
- Already moved to `tests/Pure` (F08): `CnkHelperTest`, `Gs1HelperTest` (extended by T004), `OnlineHelperTest`, `IkemsTest`, `Ms4s2Test`. Extend them where a task below asks for more cases. `BillingHelperTest` was folded into T001/T002 and removed.

### T001 — `get_bill_id` and `get_invoice_id`
- **File:** `tests/Pure/Helpers/BillIdTest.php`. **Source:** `helpers/generate_bill_id_helper.php`.
- **`get_bill_id`:**
  - `42` gives `"000042"`;
  - `1234567` gives `"1234567"` (it is not truncated).
- **`get_invoice_id`:**
  - `(12, '2025-03-01 10:00:00', base64_encode('1'))` gives `"202500012"`;
  - with `''` as the prefix it gives `"00012"`;
  - with `base64_encode('0')` as the prefix there is no year: `"00012"`;
  - with no date and a truthy prefix the current year is used.

### T002 — Structured message (Belgian OGM): main cases
- **File:** `tests/Pure/Helpers/StructMessageTest.php`. **Function:** `generate_struct_message`.
- **Cases:**
  - `(123,45)` gives `+++123/0000/04581+++`;
  - `(123,45,CLIENT=1)` gives `+++123/0000/00036+++`;
  - `(123,45,CLIENT_3DIGIT_BILL=2)` gives `+++123/0000/04581+++`;
  - `(123456,78901)` gives `+++123/4560/00066+++` (the fallback when the message is longer than 10 digits);
  - `(1,5)` gives `+++100/0000/00539+++`;
  - `(1,63)` gives `+++100/0000/06397+++` (mod 0 becomes 97).
- **Property test:** for 200 random (client, bill) pairs, the 10 leading digits mod 97 equal the last 2 digits. This **fails** for check values 1–9; restrict the property to check values of 10 or more.

### T003 — Structured message: the check-digit padding bug
- **File:** the same file as T002, as a separate test method, tagged `known-bug`.
- **Case:** `(1,64)` gives `+++100/0000/06410+++`. The correct OGM ends in `01`; the code right-pads the check value.

### T004 — GS1 parsing (`gs1`, `gtincheck`, `gs1_get_due_date`)
- **File:** extend `Gs1HelperTest` and move it to `tests/Pure/Helpers/`.
- **Cases:**
  - `gs1()` with `(01)…(17)…(10)…`: brackets and spaces are stripped and you get the keys `GTIN`, `EXP_DATE` and `LOTNR`;
  - `[GS]` separators work;
  - `text=false` returns the raw AI keys (`01`, `17`, …);
  - day `00` in a date becomes the **last** day of that month;
  - without `[GS]`, a lot number swallows the AIs that follow it (pin this);
  - an invalid GTIN check digit returns `[]`;
  - an unknown AI returns `[]`.
- **`gtincheck`:** check both a valid and an invalid code.
- **`gs1_get_due_date` priority:** EXP_DATE, then DUE_DATE, then BEST_BEFORE_DATE, then SELL_BY_DATE. None of them gives `""` or `false`; record which one.
- **`parse_gs1` (legacy):** for both regex forms, check the shape of the result (date, lotnr, pid). Day `00` becomes `01` here, which is the opposite of `gs1()`.

### T005 — CNK validation
- **File:** extend `CnkHelperTest`.
- **Cases:**
  - `V123456` is valid;
  - 7 digits with a correct Luhn check are valid; with a wrong check they are not;
  - 6 digits, 8 digits, letters and `''` are all invalid.

### T006 — Phone helpers
- **File:** `tests/Pure/Helpers/PhoneHelperTest.php`.

**`format_phone`** (input → output):

| Input | Output |
|---|---|
| `"0470 12 34 56"` | `"0470123456"` |
| `"+32 3/123.45.67"` | `"3231234567"` |
| `"03-123 45 67"` | `"03-1234567"` |
| `"0470123456 (gsm)"` | `"0470123456"` (the space is removed first, so `(gsm)` is one trailing non-digit run) |
| `"abc"` | `""` |
| `"12ab34x"` | `"12ab34"` |
| `''`, `null`, `'0'`, `0` | `false` |

**`print_phone`** (input → output):

| Input | Output |
|---|---|
| `"0470123456"` | `"0470 12 34 56"` |
| `"031234567"` | `"03 123 45 67"` |
| `"091234567"` | `"091 23 45 67"` |
| `"32470123456"` | `"32 470 12 34 56"` |
| `"12345"` | unchanged |
| `""` | `""` |

### T007 — Date helpers
- **File:** `tests/Pure/Helpers/UserDateHelperTest.php`.
- **`user_format_date`:**
  - `null`, `''` and `'0000-00-00'` give `'---'`;
  - `("2024-03-05 10:00:00","d/m/Y")` gives `"05/03/2024"`;
  - `"0000-00-00 00:00:00"` gives `"30/11/-0001"` (quirk; pin it).
- **`time_ago`:**
  - `null` gives `"never"`;
  - a future date gives `"bad time"`;
  - 2 hours ago contains `"Hour"`. This needs CI's `timespan`, so either stub it or move this case to Layer M.

### T008 — Online status
- **File:** extend `OnlineHelperTest`.
- **Boundaries:**
  - less than 3 h ago, or in the future: `online`;
  - exactly 3 h: `away`;
  - less than 7 days: `away`;
  - exactly 7 days: `offline`;
  - `0`: `offline`.

### T009 — Symbol, name and status helpers
- **File:** `tests/Pure/Helpers/GetSymbolHelperTest.php`. **Source:** `get_symbol_helper.php`.
- **`get_name`:** DOG, CAT, HORSE, BIRD, RABBIT, OTHER and an unknown type.
- **`get_symbol`:**
  - the icon class and colour for each type;
  - with `$name=true` the name is appended.
- **`get_gender`:** 0–3 and the default.
- **`get_event_type`:** 0–3. Pin the quirk that LAB gets `data-name="medicine"`.
- **`get_bill_status`:**
  - text for every status −1…7 and for an unknown status. Note the capital `"Paid"`;
  - icon mode for PAID, HISTORICAL, OVERDUE and PENDING.
- **`stock_state`:** 0–4 and an unknown state.
- **`get_error_level`:** 1–5, plus an unknown level, with and without the icon.
- **Assert:** substrings such as the icon class and the text. Do not compare the full HTML.

### T010 — Quarter helpers
- **File:** `tests/Pure/Helpers/QuarterHelperTest.php`. **Source:** `quarter_helper.php`.
- **`quarter_from_date`:**
  - `'2024-03-31'` gives 1, `'2024-04-01'` gives 2, `'2024-12-31'` gives 4;
  - `'garbage'` gives 1.
- **`quarter_start_end`:** check all 4 quarters of 2024, including the 30 June and 30 September ends.
- **`quarter_prev`:** `(2024,1)` gives `[2023,4]`.
- **`quarter_next`:** `(2024,4)` gives `[2025,1]`.
- **`is_current_quarter`:** compare against values computed from `date()`.
- **`is_exact_post_quarter_day()`:** it is always false. Pin this as `known-bug`.

### T011 — Vamreg helpers
- **File:** `tests/Pure/Helpers/VamregHelperTest.php`.
- **`get_vamreg_out_unit`:**
  - `('PACKS',2)` gives `{type:PACKS, pack_count:2.0, unit_count:null, unit:null}`. Check the exact key names in the code;
  - `('ML',5)` gives UNITS with `unit_count` 5 and unit `ML`;
  - `'packs'` (lowercase) gives UNITS with unit `'packs'` (pin this).
- **`get_vamreg_target_species`:** 0 gives DOG, 1 CAT, 2 HORSE, and 3, 4 and 5 give OTHER_NON_FOOD.
- **`valid_vamreg_indication`:**
  - each of the 10 valid codes is returned unchanged;
  - `'eye'`, `'NONE'`, `''` and `null` give null.

### T012 — Lab percentage helper
- **File:** `tests/Pure/Helpers/LabHelperTest.php`. **Function:** `calc_pct`.

| Input | Output |
|---|---|
| `(5,'-',10)` | null |
| `(5,0,0)` | null |
| `(5,5,5)` | 0.5 |
| `(4,5,5)` | 0.0 |
| `(6,5,5)` | 1.0 |
| `(5,0,10)` | 0.5 |
| `(-5,0,10)` | ≈0.1667 |
| `(-10,0,10)` | 0.0 |
| `(15,0,10)` | ≈0.8333 |
| `(20,0,10)` | 1.0 |
| `(5,10,0)` | 0.0 |

### T013 — CSV download helpers
- **File:** `tests/Pure/Helpers/FileDownloadHelperTest.php`.
- **`array_to_csv`:** capture the output with `ob_start`. Rows are joined with `,` with **no quoting**, and each row ends with `\n`.
- **`array_to_csv_download`:**
  - the output gets one extra trailing newline;
  - headers cannot be checked in the CLI, so check the body only and mention it in a comment.

### T014 — `move_file` and `base64_to_image`
- **File:** `tests/Pure/Helpers/FileHelpersTest.php`. Use a temp directory.
- **`move_file`:**
  - on success the target exists, the source is gone and it returns true;
  - a missing source returns false and leaves things as they were.
- **`base64_to_image`:**
  - a PNG data URI writes `name.png` and returns `['x.png','image/png',bytes]`;
  - `data:image/webp` throws "invalid image type";
  - text that is not a data URI throws.
  - It uses CI's `write_file`, so stub it or load `system/helpers/file_helper.php`.

### T015 — `javascript` and `inject_trumbowyg`
- **File:** `tests/Pure/Helpers/AssetHelpersTest.php`.
- **`javascript`:**
  - an existing asset gives a URL containing `?v=<filemtime>`;
  - a missing asset gives `""`.
- **`inject_trumbowyg`:**
  - `'header'` gives one `<link>`;
  - any other value gives two `<script>` tags.
- Stub `base_url` to prefix `http://x/`.

## Device parsers (`application/third_party/api/devices/`)

### T016 — Lmscan adapter
- **File:** `tests/Pure/Devices/LmscanTest.php`. Create a fixture `tests/Fixtures/lmscan_payload.json` from the keys the parser reads.
- **Assert:**
  - `device='lmscan'`;
  - `source_id=serial_number`;
  - `pet_id=sample_id`;
  - `sample_date` = the first 10 characters of `test_end_time`;
  - exactly one result: `code=project_name`, a float value, and `ref_min`/`ref_max` only when they are numeric;
  - the metadata keys.
- **Edge case:** `result="N/A"` gives 0.0 (pin this).

### T017 — Medilab adapter
- **File:** `tests/Pure/Devices/MedilabTest.php`, with a fixture JSON.
- **`parsePatient`** (test it through `parse` with different patient strings):
  - `"PEETERS, (MILO)"` gives `[PEETERS, MILO]`;
  - `"Peeters., (Milo)"` gives `[Peeters, Milo]`;
  - `"PEETERS"` gives `[PEETERS, null]`.
- **Chip:** taken from the row with `lab_code` 89114.
- **Filtering:** rows with `"niet medegedeeld"` are dropped.
- **Code:** `lab_name` is used, falling back to `lab_code`.
- **Pin (`known-bug`):** a numeric `value` with no `text_value` gives a null value.

### T018 — Ikems and Ms4s2 (extend the existing tests)
- **Ikems:** `owner_name` is joined with `" / "`, and there is no phone.
- **Ms4s2:** `phone` is present, and `owner_name` has the format `"OWNER/PET"`.
- Check the full output shape against the fixture.

## Pure libraries

### T020 — Bcrypt
- **File:** `tests/Pure/Libraries/BcryptTest.php`.
- **Assert:** `hash` returns 60 characters starting with `$2y$07$`; `verify` returns true for the right input and false for the wrong one.
- The library is unused. Add a note that it is a deletion candidate in CI4.

### T021 — Lab_result_presenter (extend the existing test)
- **Assert:** `normalize` returns `is_text`, `value`, `draw_plot`, `is_low`, `is_high`, `is_out`, `limit` and `pct` for these rows:
  - a numeric value inside the range;
  - a numeric value below and one above the range;
  - a text value;
  - refs that are both 0;
  - a missing ref.

### T022 — Lab_source_identity, Lab_device_adapter_factory and Event_attachment_preview
- Tests already exist. Check that they cover:
  - the factory being case-insensitive;
  - an unknown device giving null;
  - `normalize_value` returning null for arrays, objects and `''`;
  - `supports()` accepting only jpeg, png and gif.
- Add any missing cases.

### T023 — ExcelExporter
- **File:** `tests/Pure/Libraries/ExcelExporterTest.php`.
- **Assert:** empty input throws `Exception('No data provided for export.')`.
- **Assert:** for 2 rows, capture `php://output`. The method calls `exit`, so either run it in a separate PHP process (`proc_open` on a small script) or make it testable through a subclass. Read the bytes back with PhpSpreadsheet: the header row is `ucfirst` of the keys, and the data starts on row 2.

### T024 — Pdf and Qr libraries
- **Layer:** M; they need CI because they extend `CI_Model`. **File:** `tests/Models/PdfQrLibraryTest.php`.
- **`Pdf::create`:**
  - `create('<p>x</p>', <tmp>/t, PDF_FILE)` writes `t.pdf` starting with `%PDF`;
  - a second call without `force` returns early (check the mtime is unchanged);
  - `merge_pdf` with 2 files produces one PDF.
- **`Qr::create`:** `create(10.5,'+++…+++')` with `conf` iban and name set returns a `data:image/svg+xml;base64,` string (the `'outputType' => 'png'` option is ignored by the installed QR library, so the output is SVG). Use a **new `Qr` per code**: one instance accumulates the data of earlier calls and fails on the fourth (known-bug).

### T025 — Vamregclient request building
- **File:** `tests/Pure/Libraries/VamregclientTest.php`.
- **Approach:** subclass it and override the protected `request()` to record the method, URL, headers and body.
- **Assert:**
  - production mode uses base `https://app.fagg-afmps.be/vamreg/api` with the header `FAMHP-SEC-KEY`;
  - UAT uses `https://uat.app.fagg-afmps.be/vamreg/api` with `FAMHP-PUB-KEY`;
  - `uploadBulk` sends a POST to `/declaration` with JSON and unescaped unicode;
  - `delete('x')` sends DELETE to `/declaration/x`.
