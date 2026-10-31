# 04 — Events (consultations), billing, payment, invoices (T100–T159)

This is the most business-critical area. Money and stock change here, so be precise.

Constants:
- **events.status:** OPEN=0, CLOSED=1, HISTORY=2.
- **events.report:** INIT=0, OPEN=1, DONE=2.
- **bills.status:** INVALID=-1, DRAFT=0, PENDING=1, UNPAID=2, INCOMPLETE=3, PAID=4, OVERDUE=5, ONHOLD=6, HISTORICAL=7.
- **events.payment:** holds the bill id; 0 means "no bill".
- **Line type:** PROCEDURE=0, PRODUCT=1.

Controllers:
- `application/controllers/Events.php`, `Events_report.php` and `Invoice.php` extend Vet_Controller (any of vet, admin or accounting).
- `Admin_invoice.php` and `Accounting.php` extend Admin_Controller.

Delete behaviour:
- `bills` and `payment` use **soft deletes** (`deleted_at`).
- `events`, `events_products` and `events_procedures` are **hard-deleted**.

---

## Events — creating and viewing

### T100 — Start a new event for a pet
- **Layer:** H. **File:** `tests/Http/Events/NewEventTest.php`. **Source:** `Events::new_event`.
- **Cases:**
  1. A pet with no open event: `GET events/new_event/{pet}` inserts an `events` row with `pet`, `location` = the logged-in user's location, `vet` = the user and `status=0`. It redirects to `events/event/{newId}`.
  2. The same pet already has an OPEN event at the same location created 3 days ago: no new row is inserted, `vet` is changed to the current user, and it redirects to the existing ID.
  3. The open event is 20 days old: a new event is created.
  4. The open event is at another location: a new event is created.
  5. A `logs` row is written: `new_event`, or `update_restart_event` for case 2.
- ⚠ BUG: this mutates on GET. Pin it.

### T101 — Event page for an OPEN event
- **Layer:** H. **File:** `tests/Http/Events/ViewOpenEventTest.php`. **Source:** `Events::event`.
- **Setup:** an OPEN event with 1 product line and 1 procedure line. The product line has a lot.
- **Assert:** status 200; the page shows the pet name, owner name, product name, procedure name and lot number.
- **Assert:** an event at status CLOSED with a PAID bill shows the report view. Look for a stable marker in `views/event/main_report.php` and assert it is there and not in the open view.

### T102 — Event page for a CLOSED event shows linked and linkable labs
- **Layer:** H. **File:** `tests/Http/Events/ViewClosedEventLabsTest.php`. **Source:** `Events::event`, `Events_lab_model`.
- **Setup:** a CLOSED event for pet P; a lab report for P linked through `events_labs`; a second lab report for P that is not linked; a lab for another pet.
- **Assert:** the linked lab is shown in the linked section; the unlinked lab for P can be picked; the other pet's lab does not appear.

### T103 — Event page for an event ID that does not exist
- **Layer:** H. **File:** `tests/Http/Events/ViewMissingEventTest.php`.
- **Assert:** record the current status code (likely 500 or a PHP error page) as `known-bug`. CI4 should return 404.

### T104 — Lock an event
- **Layer:** H. **File:** `tests/Http/Events/LockEventTest.php`. **Source:** `Events::lock`.
- **Cases:**
  1. `GET events/lock/{id}` sets `status=1` and redirects to `events/event/{id}`.
  2. There is no state check: locking an already CLOSED event still returns 302 and leaves the status at 1.

### T105 — Link and unlink a lab on an event
- **Layer:** H. **File:** `tests/Http/Events/LinkLabTest.php`. **Source:** `Events::link_lab` and `Events::unlink_lab`.
- **Cases:**
  1. A GET to either endpoint returns 405.
  2. A POST for a missing event returns 404.
  3. A POST to `link_lab` on an OPEN event: no `events_labs` row is created and it redirects to the event.
  4. A POST on a CLOSED event with a valid `lab_id` for the same pet: the row is created, a `logs` row `event_lab_linked` is written, and it redirects. Follow the redirect: the flash success message is shown.
  5. `lab_id=0`, or a lab that belongs to another pet: no row is created.
  6. Linking the same lab twice: still only one row.
  7. `unlink_lab` removes the row only. The `lab_report` row stays.

---

## Events — lines (AJAX `add_line`)

POST fields: `line`, `title`, `volume`, `is_antibiotic`, `vaccin`, `vaccin_freq`, `stock`, `btw`, `booking`, `booking_default`.

Price rules:
- VAT factor = `round(1 + btw/100, 2)`.
- `brut = net * factor`.
- Product unit price comes from the **tiers**: use the price of the largest tier volume ≤ the requested volume. If the volume is below the smallest tier, use the first price.

### T110 — Add a procedure line
- **Layer:** H. **File:** `tests/Http/Events/AddProcedureLineTest.php`.
- **Setup:** a procedure priced 25.00 at 21%. POST `line=<procId>`, `volume=2`, `btw=21`, `booking=0`, `booking_default=<code>`.
- **Assert JSON:** `type=0`; `net_price=50`; `brut_price="60.50"` (a string with 2 decimals); `return` is the new ID.
- **Assert DB:** the `events_procedures` row has `unit_price=25`, `price_net=50`, `price_brut=60.5`, `btw=21` and `booking=<booking_default>`.

### T111 — Add a product line with tiered pricing
- **Layer:** H. **File:** `tests/Http/Events/AddProductLineTierTest.php`.
- **Setup:** a product with tiers `{1:10, 5:8, 10:6}` at 6%.
- **Data-driven cases (volume → expected unit price):** 0.5→10, 3→10, 5→8, 7→8, 12→6.
- **For each case, assert:** `unit_price`, `price_net = unit*vol`, `price_brut = net*1.06`, `stock_id` NULL when no stock was posted.
- **Extra cases:**
  - a product with a single tier: that price is used at every volume;
  - a product with no tiers: price 0.

### T112 — Add a product line with a stock lot
- **Layer:** H. **File:** `tests/Http/Events/AddProductLineStockTest.php`.
- **Assert:** posting `stock=<stockId>` puts `stock_lotnr`, `stock_eol` and `stock_volume` in the JSON and stores `events_products.stock_id`.
- **Assert:** the `stock.volume` row does **not** change yet. Stock is only reduced when the bill is paid (T140).

### T113 — Booking code overrides VAT
- **Layer:** H. **File:** `tests/Http/Events/AddLineBookingTest.php`.
- **Cases:**
  1. `booking` ≠ `booking_default` and `booking` ≠ 0: `btw` and `booking` come from the `booking_codes` row, and the posted `btw` is ignored.
  2. `booking=0`: `booking_default` is used together with the posted `btw`.

### T114 — Adding a vaccine creates a reminder
- **Layer:** H. **File:** `tests/Http/Events/AddVaccineLineTest.php`.
- **Assert:** posting `vaccin=1` and `vaccin_freq=365` inserts a `vaccine_pet` row with these fields:
  - `event_line` = the new line ID, `event_id`, and `pet`;
  - `redo` = today + 365 days (Y-m-d), and `no_rappel=0`;
  - `product` = the posted title, plus `location` and `vet`.
- **Assert:** the JSON has `vaccin: true`.

### T115 — Adding an antibiotic creates a Vamreg draft
- **Layer:** H. **File:** `tests/Http/Events/AddAntibioticLineTest.php`.
- **Assert:** posting `is_antibiotic=1` inserts a `vamreg_out_buffer` row with these fields:
  - `event`, `event_line` and `product_type='BE'`;
  - `status='DRAFT'`;
  - `target_species` = mapped from the pet type (see T011);
  - `indication` = `products.default_indication`;
  - `cnk`, and `out_date` = today in Y-m-d;
  - the unit and volume fields from `get_vamreg_out_unit`.
- **Assert:** with `volume=0` the status is `'INVALID'`.

### T116 — `add_line` on a non-open event is ignored
- **Layer:** H. **File:** `tests/Http/Events/AddLineClosedEventTest.php`.
- **Assert:** for a CLOSED event the response body is empty and no row is inserted in either line table.

### T117 — Remove product and procedure lines
- **Layer:** H. **File:** `tests/Http/Events/RemoveLineTest.php`. **Source:** `prod_remove` and `proc_remove`.
- **Cases:**
  1. `prod_remove` on an open event deletes the `events_products` row **and** its `vaccine_pet` and `vamreg_out_buffer` rows (matched by `event_line`). It bumps `events.updated_at` and redirects to the event.
  2. `proc_remove` deletes the `events_procedures` row.
  3. On a CLOSED event, both endpoints echo "cannot change due to status" and delete nothing.
  4. ⚠ BUG `known-bug`: the line ID is not checked against the event. Calling `prod_remove/{openEventA}/{lineOfEventB}` deletes event B's line.

### T118 — Change the volume of a product line (`prod_edit`)
- **Layer:** H. **File:** `tests/Http/Events/ProdEditTest.php`.
- **Cases:**
  1. POST `pid`, `volume=6`, `btw=6` and `event_product_id` on an open event: the tier is recalculated (unit 8, net 48, brut 50.88) and it redirects to the event.
  2. A non-numeric volume echoes "You entered a non-numeric value!" and changes nothing.
  3. A closed event echoes "cannot change due to status".
- **Note:** `vaccine_pet` and `vamreg_out_buffer` are not updated. Pin that.

### T119 — `proc_edit` placeholder
- **Layer:** H. **File:** `tests/Http/Events/ProcEditTest.php`.
- **Assert:** an open event echoes "not implemented yet"; a closed event echoes "cannot change due to status".

### T120 — Edit or disable the vaccine reminder from an event
- **Layer:** H. **File:** `tests/Http/Events/EditVaccinTest.php`. **Source:** `Events::edit_vaccin`.
- **Assert:** POST `redo=<date>` sets `redo` and `no_rappel=0`; POST `disable=1` sets `no_rappel=1`. Both redirect to the event.

### T121 — Set the Vamreg indication
- **Layer:** H. **File:** `tests/Http/Events/SetVamregIndicationTest.php`.
- **Assert:** a valid indication is stored on the matching `vamreg_out_buffer` row.
- **Assert:** an invalid indication is stored as whatever `valid_vamreg_indication()` returns (see T011).

---

## Events — price editing

### T125 — Price edit page
- **Layer:** H. **File:** `tests/Http/Events/EditPricePageTest.php`.
- **Assert:** `GET events/edit_price/{id}` returns 200 and lists every product and procedure line with its current unit price.

### T126 — Edit a unit price manually
- **Layer:** H. **File:** `tests/Http/Events/EditUnitPriceTest.php`. **Source:** `Events::edit_unit_price`.
- **Cases:**
  1. A product (type=1): POST `unit_price=7.456`, `volume=2`, `btw=21`, `reason=MANUAL` and `price_ori_net=20`. Expect `unit_price=7.46`, `price_net=14.92`, `price_brut=18.0532` (the factor is **not** rounded) and `price_ori_net=20`. It redirects to `events/edit_price/{event}`.
  2. The same for a procedure (type=0) in `events_procedures`.
  3. ⚠ BUG: this also works on a CLOSED or paid event. Pin it as `known-bug`.

### T127 — Apply a percentage reduction to the whole event
- **Layer:** H. **File:** `tests/Http/Events/EditEventPriceReductionTest.php`. **Source:** `edit_event_price/{id}/{pct}`.
- **Cases:**
  1. With `price_ori_net=0` on a line (unit 10, vol 2, 21%), a 10% reduction gives unit 9, net 18 and brut 21.78. `price_ori_net` becomes 20 and `reduction_reason='AUTO_REDUCTION'`.
  2. Applying 20% afterwards is calculated from the **original** price (unit 8), not compounded.
  3. Both products and procedures are changed.
- **Known bugs:** this is a GET mutation, and volume=0 divides by zero. Do not test the division by zero; just note it.

### T128 — `events_model.is_modified` and the bill's modified flag
- **Layer:** M. **File:** `tests/Models/EventsModifiedTest.php`.
- **Assert:** `is_modified` returns true when any line has `price_ori_net ≠ 0`.
- **Assert:** `Bills_model::is_bill_modified` returns true when any event on the bill is modified.

---

## Events — report and deletion

### T130 — Update the report text
- **Layer:** H. **File:** `tests/Http/Events/UpdateReportTest.php`. **Source:** `Events_report::update_report`.
- **Cases:**
  1. POST `submit=report`, `title`, `anamnese` and `sup_vet[]=<vetId>`: the row is updated, `report=1`, `vet_support_1` is set, and it redirects to `events/event/{id}/report`.
  2. `submit=finished_report` with `pet_id`: `report=2` and it redirects to `pets/fiche/{pet_id}`.
  3. `submit=report` with `finished=1`: `report=2`.
  4. No `submit`: echoes "no post data".
  5. An event with status HISTORY (2): echoes "cannot change due to status : status_history".
  6. Allowed on a CLOSED event.

### T131 — Autosave the anamnese
- **Layer:** H. **File:** `tests/Http/Events/AnamneseAutosaveTest.php`.
- **Assert:** a POST with a non-empty `anamnese` updates the row when `report≠2`.
- **Assert:** with `report=2` the row is unchanged.
- **Assert:** an empty anamnese changes nothing. The response body is empty.

### T132 — History visibility and event type
- **Layer:** H. **File:** `tests/Http/Events/EventsReportFlagsTest.php`.
- **Assert:** `disable_history` sets `no_history=1` and `enable_history` sets it back to 0.
- **Assert:** `set_type/{id}/3` sets `type=3`. Each one redirects to the event.
- **Assert:** an event with `no_history=1` disappears from the pet history (cross-check with T068).

### T133 — Delete an event that has no bill
- **Layer:** H. **File:** `tests/Http/Events/DeleteEventNoBillTest.php`.
- **Assert:** for an event with `payment=0`, `events/del/{id}/{owner}` deletes the row, writes a `logs` row `remove_event` and redirects to `owners/detail/{owner}`.
- **Pin (known-bug):** the `events_products` rows are left behind as orphans.

### T134 — Delete an event that has a PENDING bill
- **Layer:** H. **File:** `tests/Http/Events/DeleteEventWithBillTest.php`.
- **Setup:** a PENDING bill B without an invoice_id; event E1 (to delete) and event E2, both with `payment=B`.
- **Assert:**
  - B is soft-deleted (`deleted_at` set);
  - E2 gets `payment=0`;
  - E1 is deleted;
  - a `logs` row `remove_event_with_bill` is written.
- **Case:** with a PAID bill that has an invoice_id, the bill stays, E1 is still deleted (⚠ BUG) and E2 is unchanged.
- **Case:** an event with status=2 and a bill: nothing is deleted.

---

## Bills and invoices

### T140 — Full payment flow (happy path)
- **Layer:** H. **File:** `tests/Http/Invoice/FullPaymentFlowTest.php`.
- This is the **most important test in the suite**.
- **Setup:** owner O has 1 pet, 1 OPEN event and 2 product lines:
  - product A, unit 10, vol 2, 6%, with `stock_id` = lot L (volume 50);
  - product B, unit 100, vol 1, 21%, no stock.
  - Also add 1 procedure at 25.00 and 21%.
- **Steps:**
  1. `GET invoice/bill/{O}` creates a DRAFT bill and redirects to `invoice/get_bill/{bid}`. `owners.last_bill` = today.
  2. `GET invoice/get_bill/{bid}`:
     - the event now has `payment=bid`;
     - the bill is PENDING with `total_net=145`, `BTW_6=20`, `BTW_21=125`;
     - `total_brut = 145 + 1.20 + 26.25 = 172.45`.
  3. POST `invoice/bill_pay/{bid}` with `submit=1`, `cash_value=100`, `card_value=72.45`, `transfer_value=0`, `msg` and `msg_invoice`:
     - the event status is 1;
     - a `payment` row is inserted;
     - the bill is PAID with cash and card stored;
     - `invoice_id` = (max invoice_id this year) + 1, and `invoice_date` = today;
     - lot L volume = 50 − 2 − (A's dead volume);
     - for B, a new `stock` row with `state=3`, `info='NO_STOCK'` and a negative volume;
     - it redirects to `invoice/get_bill/{bid}`.
  4. `GET invoice/get_bill/{bid}` shows the stored totals and the invoice number formatted by `get_invoice_id`.

### T141 — Payment outcomes
- **Layer:** H. **File:** `tests/Http/Invoice/PaymentOutcomesTest.php`. Each case starts from a PENDING bill with `total_brut=100`.
- **Cases:**
  1. Exact cash: PAID.
  2. Exact amount with `transfer_value>0`: ONHOLD (6).
  3. Underpaid: INCOMPLETE (3). Stock **is** reduced and an `invoice_id` **is** assigned (pin it).
  4. ⚠ Overpaid by 0.01: INCOMPLETE (known-bug).
  5. Paying an INCOMPLETE bill again in full: PAID. Stock is **not** reduced a second time, `invoice_id` stays the same, and the `cash`/`card` columns hold the **second** POST values, not a sum (pin it).
  6. A PAID bill: the POST changes nothing and still redirects.
  7. `submit` missing: nothing changes.

### T142 — Verify a transfer
- **Layer:** H. **File:** `tests/Http/Invoice/VerifyTransferTest.php`.
- **Assert:** an ONHOLD bill becomes PAID with `transfer_verified=1` and the request redirects to `invoice`.
- **known-bug:** it also works from any other status, for example DRAFT.

### T143 — Reuse an existing bill
- **Layer:** H. **File:** `tests/Http/Invoice/BillReuseTest.php`.
- **Assert:** if the owner already has a PENDING bill, `invoice/bill/{O}` reuses it and creates no new row.
- **Assert:** if the owner only has a DRAFT, a **new** DRAFT is created. Pin this.

### T144 — Opening a bill attaches all the owner's open events
- **Layer:** H. **File:** `tests/Http/Invoice/AttachEventsTest.php`.
- **Setup:** the owner has 2 pets, each with an event with `payment=0` (one OPEN, one CLOSED, at different locations). Another owner's event must not be attached.
- **Assert:** after `get_bill`, both of the owner's events have `payment=bid` and the other owner's event is unchanged.

### T145 — Invalid or deleted bill
- **Layer:** H. **File:** `tests/Http/Invoice/InvalidBillTest.php`.
- **Assert:** `get_bill/-1`, `get_bill/999999` and `get_bill/<soft-deleted id>` each render the "invalid" view. Assert on a marker text taken from `views/bills/invalid.php`.

### T146 — Invoice PDF (print)
- **Layer:** H. **File:** `tests/Http/Invoice/PrintPdfTest.php`.
- **Assert:** `get_bill/{paidBill}/1` returns `Content-Type: application/pdf` and a body starting with `%PDF`.
- **Assert:** the file `data/stored/.invoices/{Y}/{W}/bill_{invoiceId}.pdf` exists, with Y and W taken from `invoice_date`.
- **Assert:** for an unpaid bill the file is named `check_{000042}`-style instead.
- If `pdftotext` is available, assert the text contains the structured message `+++…+++` (see T002) and the IBAN from the settings.

### T147 — Mail mode
- **Layer:** H. **File:** `tests/Http/Invoice/MailBillTest.php`.
- **Assert:** `get_bill/{id}/2` returns `{"result":true}`.
- ⚠ BUG `known-bug`: **every** bill in the table gets `mail=1`. Assert this on a bill that was not involved.
- **Assert:** no mail is sent.

### T148 — Store the bill message
- **Layer:** H. **File:** `tests/Http/Invoice/StoreBillMsgTest.php`.
- **Assert:** POST `msg` and `msg_invoice` are stored on the bill.

### T149 — Bill overview and filters
- **Layer:** H. **File:** `tests/Http/Invoice/BillOverviewTest.php`. **Source:** `Invoice::index`.
- **Cases:**
  1. By default, bills from the last 7 days are listed and an older bill is not.
  2. POST `search_from` and `search_to` select the range.
  3. URL timestamps work: `invoice/index/{ts}/{ts}`.
  4. `unpaid=1` lists only INCOMPLETE and ONHOLD bills.
  5. `modified=1` lists only bills with `modified=1`.
  6. Soft-deleted bills never appear.
  7. The `RestrictBills` setting limits the number of rows.

### T150 — Write-off bill rendering
- **Layer:** H. **File:** `tests/Http/Invoice/WriteOffBillTest.php`.
- **Setup:** `liquidate` rows linked to bill X.
- **Assert:**
  - the totals are 0 and no event is attached;
  - each product line shows "name (lot X | EOL dd-mm-YYYY)";
  - the location label shows one location, or "multiple locations" when there are several.

### T151 — Admin edit bill
- **Layer:** H. **File:** `tests/Http/Invoice/AdminEditBillTest.php`. **Source:** `Admin_invoice::edit_bill`.
- **Assert:** admin can GET the form (200).
- **Assert:** a POST with `submit`, `vet`, `location`, `cash`, `card`, `transfer`, `status` and `created` updates those columns and writes a `logs` row `edit_bill`.
- **Assert:** `amount` is ignored and `total_brut` is unchanged (pin it).
- **Assert:** a vet gets a redirect to `/`.

### T152 — Open bills shown on a bill
- **Layer:** M. **File:** `tests/Models/BillsOpenBillsTest.php`.
- **Assert:** `get_open_bills(owner, current)` returns bills with status in {0,1,2,3,5,6}, leaves out the current bill and PAID ones, and returns false when there are none.

---

## Bills_model calculations (Layer M, precise money maths)

### T155 — `calculate_bill` totals and VAT rounding
- **File:** `tests/Models/BillsCalculateTest.php`.
- **Cases:**
  1. A mix of rates (0/6/21) across products and procedures: check the `total_net`, `BTW_0`, `BTW_6`, `BTW_21` and `total_brut` columns and the returned array shape `[net, brut, ["0"=>…, "6"=>…, "21"=>…]]`.
  2. VAT is rounded per rate: `BTW_6=0.25` gives VAT 0.02, and `BTW_21=0.10` gives 0.02.
  3. A DRAFT bill becomes PENDING; an UNPAID one stays UNPAID.
  4. A line at 12%: counted in net with no VAT (known-bug).
  5. ⚠ VAT ≥ 1000 on one rate (`BTW_21=5000`, VAT 1050): `total_brut` comes out wrong because `number_format` adds a thousands separator. `known-bug`.
  6. A bill with no events: record the TypeError as `known-bug` (`expectException(TypeError::class)`).

### T156 — `set_invoice_id` numbering
- **File:** `tests/Models/BillsInvoiceIdTest.php`.
- **Assert:**
  - the first bill of the year gets max+1;
  - calling it again does not change the number;
  - a bill created in a previous year is numbered within **that** year's sequence (it is keyed on `created_at`);
  - soft-deleted bills still count towards max;
  - `invoice_date` is set.

### T157 — Earnings aggregates
- **File:** `tests/Models/BillsEarningsTest.php`.
- **Cover:** `get_monthly_earning`, `get_yearly_earnings`, `get_yearly_earnings_by_month` and `get_yearly_earnings_by_date`.
- **Assert:** only PAID and HISTORICAL bills are counted. Use bills dated on the boundaries: the first day of the month, the last day at 12:00, and 00:00:00.
- **Pin:**
  - the last day of the month is excluded by `by_month` (known-bug);
  - `by_date` excludes times exactly at 00:00:00;
  - the DateTime passed in is changed by the call.

### T158 — Event aggregates
- **File:** `tests/Models/EventsAggregatesTest.php`.
- **Cover:** `get_contacts` (pin the 23:59 first-day bug), `get_contacts_year`, `get_open_reports`, `get_event_count`, `get_current_events` (admin sees all; a vet sees only their own or support events; `no_history` is excluded) and `all_bill_products` (false for 0).

### T159 — Event search and export queries
- **File:** `tests/Models/EventsSearchExportTest.php`.
- **Cover:** `search_event` on title and anamnese (FULLTEXT, so check the seed creates the index); `get_booking_export` grouped by booking; `get_all_items` sums per VAT rate; `get_printable_items` columns; `get_products_owner`.

---

## Accounting dashboard

### T139 — Accounting dashboard
- **Layer:** H. **File:** `tests/Http/Accounting/DashboardTest.php`.
- **Assert:** admin gets 200 on `accounting/dashboard`, `accounting/dashboard/-1` and `accounting/dashboard/1`.
- **Assert:** the stock-error count shown equals `SELECT COUNT(*) FROM stock WHERE state=3`.
- **Assert:** a vet is redirected to `/`.
- **Note:** the class extends `Admin_Controller`, so an accounting-only user is also redirected. Pin this, because `Accounting_Controller` would allow them.
