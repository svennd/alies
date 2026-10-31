# 07 — Reports, exports, stats, Vamreg (T260–T299)

All of these are admin-only (`Admin_Controller`), so log in as `admin` (Layer H).

Exports feed external systems: the accountant (Kluwer Expert/M) and the Vamreg authority. Their **byte-level output is a contract**, so use golden files:
1. Store the expected output in `tests/Fixtures/golden/<name>`.
2. Compare it after normalising values that change between runs, such as dates and generated IDs. Do the replacement in the test with clearly named placeholders.
3. When a golden file is created for the first time, a human must check it by eye. Say so in the test docblock.

Date windows in Export use `> from 00:00` and `< to 23:59`. As a result, rows at exactly `from 00:00:00` and at `to 23:59:xx` are left out. Pin this with one boundary row per export.

## Export (`Export.php`)

### T260 — Export index page
- **File:** `tests/Http/Export/ExportIndexTest.php`.
- **Assert:**
  - `export` shows this month's totals from `get_yearly_earnings_by_date`;
  - it shows the number of bills with no `invoice_id` (the "checks");
  - a POST to `search_from`/`search_to` changes the range.

### T261 — Owners XML export
- **File:** `tests/Http/Export/OwnersXmlTest.php`.
- **Assert:** `export/owners/{from}/{to}` returns `text/xml` that parses with `simplexml`. It contains `<owner>` elements only for owners created or updated in the window, with every documented child element.
- **Assert:** empty fields are left out, but a value of `"0"` is kept.
- **Assert:** an empty window returns `<xml><owners></owners></xml>`.
- **Pin (`known-bug`):** an owner whose `street` contains `&` produces a truncated value.
- **Golden file:** `golden/export_owners.xml`.

### T262 — Pets XML export
- **File:** `tests/Http/Export/PetsXmlTest.php`.
- **Assert:** `export/pets` lists all non-deleted pets.
- **Assert:** `export/pets/7` lists only pets created or updated in the last 7 days.
- Check the list of elements.

### T263 — Bills CSV export
- **File:** `tests/Http/Export/BillsCsvTest.php`.
- **Assert:** `export/csv/{from}/{to}` has exactly this header line:
  `invoice_id,invoice_date,total_brut,total_net,BTW_0,BTW_6,BTW_21,cash,card,transfer,modified,owner_id,owner_last_name,vet_first_name,location_name`
- **Assert:** there is one row per invoiced bill in the window, with the values taken from the DB.
- **Pin:** there is no CSV quoting, so a comma in a last name breaks the column count.
- **Pin:** an empty window produces a warning or error. Record the current output.

### T264 — Kluwer (Expert/M) XML export
- **File:** `tests/Http/Export/KluwerXmlTest.php`.
- **Setup:** 2 paid bills in the window:
  - one for a company owner with `btw_nr='BE0123456789'`;
  - one for a private owner.
  - Each bill has product and procedure lines on 2 booking codes, at 6% and 21%.
- **Customer assertions:**
  - the root element is `ImportExpMPlus`, encoded Windows-1252;
  - `VATStatus` is 1 for the company owner and 0 for the private one;
  - `VATNumber` is `0123456789`, produced by `trim` with the characters "BE".
- **Sale assertions:**
  - `DocNumber` = `get_invoice_id(...)`, `DocDate` is `d/m/Y`, `Amount` uses a comma decimal;
  - `Details` has these lines in order:
    1. account 400000 with the gross amount;
    2. account 451000 with the VAT;
    3. one line per booking code, with `Ventil` mapped as 0→1, 6→2, 12→3, 21→4.
- **Golden file:** `golden/kluwer.xml`.
- **Pin (`known-bug`):** a bill with no events causes a TypeError (500).

### T265 — Merged invoice PDF
- **File:** `tests/Http/Export/InvoicesPdfTest.php`.
- **Assert:** when every invoice in the window has its stored PDF, `export/pdf/{from}/{to}` returns one merged PDF and writes `data/stored/.invoices/list{from}_{to}.pdf`.
- **Assert:** when one PDF is missing, the response contains a link to `invoice/get_bill/{id}`.
- Create the stored PDFs first by calling `invoice/get_bill/{id}` (see T146).
- Remove the files you created afterwards.

## Reports (`Reports.php`)

### T271 — Stock list XLSX
- **File:** `tests/Http/Reports/StockListXlsxTest.php`.
- **Assert:** `reports/stock_list` shows the location picker.
- **Assert:** `reports/stock_list/1/1/0` returns an xlsx (content type, plus a ZIP magic `PK`).
- **Assert:** after reading it with PhpSpreadsheet, the headers match the keys of `get_list` and there is one row per IN_USE stock group at location 1.
- **Pin:** a location with no stock returns 500.

### T272 — Client list XLSX
- **Assert:** `reports/clients_list` and `reports/clients_list/1` (active only, `last_bill` within 3 years) return xlsx files.
- **Assert:** the row count matches the DB.

### T273 — Product usage reports
- **File:** `tests/Http/Reports/UsageReportTest.php`.
- **Assert:** `reports/usage/{pid}` shows the usage rows for the last 3 months by default.
- **Assert:** a POST with `submit=usage` and a date range filters the rows.
- **Assert:** a POST with `summary=1` shows the per-location summary with its `total` row.
- **Assert:** `reports/usage_csv/{pid}/{from}/{to}` has the header `volume,vet,pet_id,client_id,lotnr,eol,created_at`.

### T274 — Products report and products CSV
- **Assert:** `reports/products` with POST `from`/`to` lists the volume and net total per product and per in_price.
- **Assert:** `reports/products_csv/{from}/{to}` has the header `name,volume,unit,inprice,net_price,barcode`.
- **Pin (`known-bug`):** the `net_price` and `barcode` columns are empty.

### T275 — Register in, register out and revenue
- **Assert:** `reports/register_in` lists the `register_in` rows in the range, with the wholesale bruto.
- **Assert:** `reports/register_out` lists the product usage per event.
- **Assert:** `reports/revenue` shows 12 months of totals. A POST with `submit` and a range shows a per-month breakdown that matches `get_yearly_earnings_by_date(...,true)`.

### T276 — Stats (saved SQL queries)
- **File:** `tests/Http/Stats/StatsTest.php`.
- **Setup:** a `stats` row whose query is `SELECT COUNT(*) AS n FROM owners WHERE created_at > '%s' AND created_at < '%s'`.
- **Assert:** a POST to `stats` with `query=<id>` and a range shows the result `n`.
- **Assert:** a POST to `stats/update/{id}` with `submit`, `query` and `help` updates the row and redirects to `stats`.

## Vamreg (`Vamreg.php`, Vamreg models)

Vamreg is the Belgian antibiotic register. **External HTTP must be stubbed.**
- **Layer H:** point the Vamreg base URL at the stub server from F07. If it is hard-coded, use Layer M with a `Vamregclient` subclass that overrides `request()`.
- **Clock:** the deadline maths uses the real clock, so build expectations from `date()`.

### T280 — Vamreg pages render
- **File:** `tests/Http/Vamreg/VamregPagesTest.php`.
- **Assert:** without the `vamreg_api_key` setting, `vamreg` shows "VAMREG API key is not configured".
- **Assert:** with the key set, each of these returns 200:
  - `vamreg/index/2026/1`;
  - `vamreg/in/2026/1`;
  - `vamreg/out/2026/1`;
  - `vamreg/out_detail/<cnk>/2026/1`;
  - `vamreg/product_list`.
- **Assert:** the draft counts shown equal the DRAFT rows in each buffer for that quarter.
- **Pin:** `vamreg/in/abc` returns 500.

### T281 — Edit IN and OUT buffer rows
- **Assert:** a POST to `vamreg/save/{id}/2026/1` with `new_volume=3.7` stores `in_quantity_pack_count=3`. The values `0`, `-1` and `abc` are ignored.
- **Assert:** it redirects to `vamreg/index/2026/1`.
- **Assert:** a POST to `vamreg/save_out/{id}/2026/1` with `new_volume=2`, `ab_unit=PACKS`, `cnk` and `out_date` updates the quantity fields through `get_vamreg_out_unit` and redirects to `vamreg/out_detail/{cnk}/2026/1`.

### T282 — Lock, unlock, remove and restore
- **Assert:** `vamreg/lock/{id}` sets the IN row status to `INVALID`, and `vamreg/unlock/{id}` sets it to `DRAFT`.
- **Pin:** `unlock` on a `SENT` row sets it back to `DRAFT`.
- **Assert:** `remove` and `restore` do the same on OUT rows.

### T283 — Post all drafts (stubbed API)
- **File:** `tests/Http/Vamreg/PostAllTest.php`, or Layer M if the API cannot be stubbed over HTTP.
- **With no drafts:** the response is JSON `{status:-1}`.
- **IN, stub returns 200:** the stub answers `[{id:"a"},{id:"b"}]` and the call is `vamreg/post_all/2026/1/IN`.
  - Check the request the stub received: the body is a JSON array where each element has `register:"IN"`, `dateTime` in `Y-m-d\TH:i:s.000\Z` format, `cnk` and `inQuantityPackCount`.
  - Each row becomes `SENT` with its `api_declaration_id` and `sent_at` set.
  - The response is `{status:200}`.
- **OUT, stub returns 200:**
  - check the body has `register:"OUT"`, `targetSpecies`, `indication` and `veterinarianOrderNumber`;
  - check the rows are updated.
- **Stub returns 400:** the stub answers `[{"field":"declarations[1].cnk",…}]`.
  - Row 1 becomes `ERROR` with `api_error` set, and row 0 stays `DRAFT`.
  - Pin the wrong-message bug as `known-bug`.
- **Stub returns 403:** `{status:403}`.
- **Connection refused:** `{status:500}`.

### T284 — Refresh the product index (stubbed)
- **Assert:** with the stub returning 2 medicinal products for `GET /medicinal-product`, `vamreg/refresh`:
  - upserts 2 `vamreg_index` rows;
  - sets `is_antibiotic` on the matching products (see T290);
  - returns `{status:"success"}`.
- **Assert:** when the stub returns 500, the response is `{status:"failure"}` with HTTP 500.

### T285 — Reset (debug tool)
- **Assert:** with SENT rows that have an `api_declaration_id` and a stubbed DELETE, the stub receives the DELETE calls.
- **Pin (`known-bug`):** the IN rows always reset to DRAFT.
- **Pin (`known-bug`):** the OUT reset logic is inverted.

### T290 — Vamreg model logic
- **Layer:** M. **File:** `tests/Models/VamregModelsTest.php`.
- **`Vamreg_index_model::set_ab_status_on_product()`:**
  - buy_volume 10 with pack 20 gives `ab_unit_volume=2` and `ab_unit=PIECE`;
  - buy_volume 1 gives `1` and `PACKS`;
  - unit_sell `ml` gives `ML`;
  - `default_indication` NULL becomes `'NONE'`;
  - products that already have `is_antibiotic=1` are untouched.
- **`get_linked_stats()`.**
- **`Vamreg_in_buffer_model`:** `get_all_drafts_by_date` uses inclusive bounds; `send_draft_aggregate`.
- **`Vamreg_out_buffer_model`:**
  - `summary`: the ERROR > DRAFT > SENT priority, `total_quantity` and `has_negative`;
  - pin the duplication bug when 2 wholesale rows share a CNK;
  - `get_by_cnk` and `get_simplified`.

### T291 — Vamreg deadline banner
- **Layer:** H. Test it through `vamreg/index/{Y}/{Q}`.
- **Setup:** set `vamreg_auto_send_months` to `base64('1')`.
- **Assert:** for a quarter that has not ended (the current one), the page shows "Quarter open".
- **Assert:** for a quarter that ended more than 2 months ago, the page shows "Edit window closed".
- **Assert:** for Q1 2026 the page shows the auto-send date 2026-05-01, which comes from PHP month overflow (pin this).
