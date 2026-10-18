# 05 — Products, prices, stock, limits, wholesale, delivery, logs (T160–T219)

Constants used throughout this file:
- **Stock states:** CHECK=0, IN_USE=1, HISTORY=2, ERROR=3, MERGE=4.
- **Dead volume:** `products.dead_volume` is **added** to the volume removed by `reduce`, `fallback_reduce`, `reduce_stock` and `increase_stock`. It is **not** added by `move`, `write_off` or `add_stock`.
- **Soft deletes:** `products`, `procedures`, `booking_codes`, `stock_location`.
- **Hard deletes:** `stock`, `products_price`, `products_type`, `products_label`.

The stock maths needs fine-grained checks, so it is covered twice: through HTTP for each flow, and through Layer M (T200–T209) for the edge cases.

## Stock intake (adding stock, then verifying it)

### T160 — Add stock (new lot)
- **Layer:** H. **File:** `tests/Http/Stock/AddStockTest.php`. **Source:** `Stock::add_stock`.
- **POST:** `submit`, `pid`, `new_volume=10`, `eol=<+1y>`, `lotnr=L1`, `in_price=2.5`, `cat_price=3`, `supplier`.
- **Assert:** a `stock` row is inserted with `state=0` and `location=1`, and the response renders the add page with the row listed.
- **Assert:** a `log_stock` row `add_stock` exists.
- **Assert:** `products.backorder` goes from 1 to 0.
- **Assert:** when `products.wholesale=0` and `buy_price < cat_price`, `products.buy_price` becomes the cat price and a log row `update_buy_price` is written.

### T161 — Add stock to an existing CHECK row
- **Assert:** posting the same pid, eol, lotnr, in_price and location again **adds** the volume to the existing CHECK row instead of inserting a new one. A log row `add_stock_re` is written.
- **Pin (`known-bug`):** with an empty `eol`, the first add stores `''`, so the second add does not match it and inserts a new row.

### T162 — Add-stock validation
- **Assert:**
  - `new_volume=6000` shows the error "Invalid volume (>5000)";
  - `new_volume=5000` shows "Not a valid product or no volume…";
  - an empty `pid` shows the same message;
  - in all three cases no row is inserted and a `bad_stock_entry` log row is written.
- **Pin:** a negative volume is accepted.

### T163 — Add stock with a new GS1 barcode
- **Assert:** posting `new_barcode_input=1` together with `barcode_gs1=<GTIN>` stores `products.input_barcode` and writes a `new_gs1` log row.

### T164 — Delete a CHECK row
- **Assert:** `stock/delete_stock/{id}` removes a row in `state=0` and redirects to `stock/add_stock`.
- **Assert:** a row in `state=1` is **not** deleted.

### T165 — Verify stock (delivery slip)
- **Layer:** H. **File:** `tests/Http/Stock/VerifyStockTest.php`.
- **Setup:** 2 CHECK rows at location 1 and 1 CHECK row at location 2.
- **Assert:** a POST with `submit`, `delivery_slip=note` and `regdate` does all of the following:
  - inserts 1 `delivery_slip` row;
  - inserts 3 `register_in` rows (one per CHECK row, with product, eol, volume, in_price, cat_price, supplier and lotnr);
  - moves **all 3** rows to `state=1`, including the row at location 2 (pin this);
  - writes a log row `stock_verify` with the text "products verified: 3";
  - redirects to `stock/add_stock`.
- **Assert:** a GET lists the pending CHECK rows.

## Stock movement and write-off

### T170 — Move stock between locations
- **Layer:** H. **File:** `tests/Http/Stock/MoveStockTest.php`.
- **Step 1:** `stock/move` with POST `submit`, `move_volume[<sid>]=3` and `location_hidden=2` renders the overview with the selection.
- **Step 2:** logged in at location 1, `stock/move_verification` with POST `submit`, `move_volume[]`, `max_move[]`, `from=2` and `move_prod[]`:
  - the source row (at location 2) loses 3;
  - the destination (location 1) has a row with the same lotnr, in_price and eol and +3. It is either created or added to an existing row;
  - it redirects to `stock/move/1`.
- **Case:** moving the full volume puts the source row in state 2.
- **Case:** `max_move < volume` records `info='OVERDRAW_MOVE'`.

### T171 — Write off a whole stock row (expired)
- **Assert:** `stock/write_off_full/{sid}` does all of the following:
  - inserts a `liquidate` row with `reason='EXPIRED'`, the full volume, eol, lotnr and `stock_id`;
  - leaves the stock row with volume 0 and state 2;
  - writes a `write_off` row to `log_stock`;
  - redirects to `stock/expired_stock`.

### T172 — Partial write-off
- **Assert:** `stock/write_off/{sid}` GET shows the form.
- **Assert:** a POST with `submit`, `volume=2` and `reason=BROKEN` inserts a `liquidate` row with volume 2 and reason BROKEN, reduces the stock by exactly 2 (no dead volume is added) and redirects to `products/profile/{pid}`.
- **Case:** a write-off larger than the volume left puts the row in state 3 (ERROR).

### T173 — Expired stock page
- **Assert:** `stock/expired_stock` lists IN_USE rows whose eol lies between 360 days ago and 90 days from now, ordered by eol.
- **Assert:** a row with eol a year from now is not listed.

### T174 — Admin stock edit and the stock-problems screen
- **Assert:** `stock/edit/{id}` (admin) with POST `eol`, `in_price`, `lotnr`, `new_volume=7`, `state=1` and `ori_volume=5` updates the row, writes 2 WARN rows to `log_stock` (−5 and +7) and redirects to `stock/stock_detail/{pid}`.
- **Assert:** `stock/stock_check` lists ERROR rows and IN_USE rows with a negative volume.
- **Assert:** `stock/clear_error/{id}` sets `state=2` and `volume=0`, then redirects to `stock/stock_check`.

### T175 — Stock detail and stock autocomplete
- **Assert:** `stock/stock_detail/{pid}` shows only the IN_USE rows. `stock/stock_detail/{pid}/1` shows every state.
- **Assert:** `stock/get_product_stock/1?query=<prefix>` returns JSON shaped `{"query","suggestions":[{"value","data":{"id":[],"volume":[],"lotnr":[],"eol":[],"prod","unit"}}]}`.
- **Pin:** a missing `query` gives a 500.

## Products

### T180 — Product index and catalogue search
- **Assert:** `products` returns 200 and lists the stock at the user's location. `products/index/all` lists all locations.
- **Assert:** `products/search_catalog?q=<prefix>` returns JSON `{"query","count","html"}`. `count` covers both products and procedures, and `html` contains the product name.
- **Assert:** a query shorter than 2 characters returns `count=0`.
- **Assert:** non-sellable products are sorted last.

### T181 — Product profile
- **Assert:** `products/profile/{id}` shows the prices, the stock (local and global), and the usage for 30, 180 and 365 days.
- **Assert:** a POST `message` stores `products.comment`.

### T182 — New product wizard (admin)
- **Steps:**
  1. POST step 1 with `submit`, `name`, `type`, etc.: a product is inserted and the request redirects to `products/new/2/{pid}`.
  2. Step 2 sets the volumes and units. `btw_sell` comes from the chosen booking code.
  3. Step 3: `limit_stock`, plus `limit[1][-1]=5` inserts a `stock_limit` row.
  4. Step 4: `dead_volume`, vaccine fields, antibiotic fields. `default_indication="null"` is stored as NULL.
- **Assert:** after each step the DB has exactly the new columns, and the `new_product` log row exists.

### T183 — Edit a product (admin)
- **Assert:** a POST to `products/product/{id}` with all the fields and `labels[]` updates the product. `btw_sell` is taken from the booking code.
- **Assert:** the labels are replaced (`products_product_label`), and an existing `stock_limit` row is updated through `limit[1][<limitId>]=8`.
- **Assert:** a vet sending the same POST changes nothing.

### T184 — Product lists by type (DataTables)
- **Assert:** `products/product_list/{typeId}` returns 200.
- **Assert:** `products/get/{typeId}` returns `{"aaData":[[nameLink, stockText, icons, "", editLink], …]}`.
- **Assert:** the special type IDs filter as follows: 0 gives non-sellable products, −1 antibiotics, −2 vaccines.
- **Assert:** a type that does not exist returns `{"aaData":[]}`.

### T185 — Delete a product
- **Assert:** `products/delete_product/{id}` soft-deletes the product and redirects to `products/product_list`.
- **Assert:** the product disappears from `get_product_or_procedure`. Its stock rows remain until `cli stock_clean` runs (T327).

### T186 — Autocomplete for adding stock (`products/get_product`)
- **Assert:** `?query=a` (1 character) returns an empty `suggestions` list.
- **Assert:** a name query returns suggestions with `data.{id, buy_volume, unit_buy, supplier, sell_volume, unit_sell, buy_price, last_net, last_brut, last_date}`.
- **Assert:** a GS1 string of 26 or more characters whose GTIN matches `input_barcode` returns that product with `data.gs1`.
- **Assert:** a GS1 string with an unknown GTIN returns empty `suggestions` plus a `gs1` object.

### T187 — Autocomplete for event lines (`products/get_product_or_procedure`)
- **Assert:** a name query returns products with `type:1`, including `stock[]` (only IN_USE rows with volume > 0), `btw`, `booking`, `vaccin` and `is_antibiotic`.
- **Assert:** it also returns procedures with `type:0`.
- **Assert:** a GS1 query matching a lot at the user's location returns a first suggestion with `type:2` and the specific `stock` row.
- **Assert:** non-sellable and deleted products are excluded.

### T188 — Lot numbers, backorder and monthly usage
- **Assert:** `products/get_lot_nr` (POST `pid`) returns a JSON array of `{lotnr, eol, volume, barcode, location}`.
- **Assert:** `products/set_backorder/{pid}` sets `backorder=1` and redirects to `limits/global`. `unset_backorder` sets 0 and redirects to `limits/order`. Both are admin only.
- **Assert:** `products/get_monthly_usage/{pid}/12` returns `[{month_year:"YYYY-MM", total_volume}]`.
- **Assert:** `products/search_by_booking/{id}` lists the products and procedures that use that booking code.

## Pricing (Accounting_Controller: admin or accounting)

### T190 — Product price tiers
- **Assert:** `pricing/prod` (id 0) lists the sellable products.
- **Assert:** a POST to `pricing/prod/{pid}` with `submit`, `volume[]=[1,5,0]` and `price[]=[10,8,7]`:
  - deletes the old tiers and inserts 2 rows; the pair with volume 0 is skipped;
  - inserts a `price_log` row whose JSON is `{"1":"10","5":"8"}`. Check the exact format the code produces.
- **Assert:** with `accept_wholesale=1` and `wholesale=<id>`, `wholesale.last_bruto` is set to `bruto`.
- **Assert:** `pricing/rm_prod_price/{ppid}` deletes the tier and redirects to `pricing/prod/{pid}`. An unknown ID redirects to `pricing/prod/0`.
- **Assert:** `pricing/man/{pid}` with POST `buy_price` and `buy_price_date` updates the product.

### T191 — Procedures
- **Assert:** `pricing/proc` POST with `submit=add_proc`, `name`, `booking_code` and `price` inserts a procedure and writes a `new_procedure` log row.
- **Assert:** POST `action=edit_proc` with `id` updates it.
- **Assert:** `pricing/proc_edit/{id}` shows the income stats.
- **Assert:** `pricing/delete_proc/{id}` soft-deletes the procedure. `restore/procedures/{id}` (admin) restores it, and `restore/procedures` lists the trashed ones.

## Limits

### T195 — Stock limits
- **Assert:** `limits/global` lists products whose total IN_USE volume is below `limit_stock` and that are not on backorder.
- **Assert:** `limits/local/1` lists products whose `stock_limit` for location 1 is above the local volume.
- **Assert:** `limits/order` lists the products with `backorder=1`.
- **Layer M:** add `tests/Models/StockLimitTest.php` covering `global_shortage()` and `local_shortage()`, including the NULL-group bug (a limit for a product with no stock rows).

## Stock_model edge cases (Layer M, `tests/Models/Stock*Test.php`)

### T200 — `reduce` and `fallback_reduce` with dead volume
- **Assert:** `reduce(sid, pid, 2)` on a product with `dead_volume=0.5` and a row of volume 10 leaves volume 7.5 in state 1. Reducing to exactly 0 gives state 2; below 0 gives state 3.
- **Assert:** `reduce(..., dead=false)` leaves the dead volume out.
- **Assert:** `fallback_reduce(pid, 2, 1, 'NO_STOCK')` inserts a row with volume −2.5, state 3 and info `NO_STOCK`.

### T201 — `reduce_stock` paths
- **Assert:** volume 0 returns false.
- **Assert:** with a barcode and a location, the matching IN_USE row is reduced.
- **Assert:** with a barcode that matches no row, an ERROR row containing the barcode is inserted.
- **Assert:** with a location only and exactly one row at that location, that row is reduced.
- **Assert:** with a location only and several rows, an ERROR row is inserted.
- **Assert:** with neither, an ERROR row is inserted with no location.
- **Assert:** `maintain_stock_state` then puts IN_USE rows at 0 into state 2, and puts any negative row into state 3.

### T202 — `increase_stock`
- **Assert:** it adds to the matching IN_USE row.
- **Assert:** it falls back to the smallest row with the same barcode and location in any state, reviving it to state 1.
- **Assert:** it otherwise inserts a new row.
- **Assert:** dead volume is added when the flag is set.

### T203 — `move` edge cases
- **Assert:** volume 0 returns false.
- **Assert:** it merges into a matching destination row with null-safe eol and supplier.
- **Assert:** it creates a new destination row when nothing matches.
- **Pin (`known-bug`):** a full move matches a HISTORY row at the destination.
- **Pin (`known-bug`):** a source with a NULL `lotnr` throws a TypeError.

### T204 — `write_off` state transitions
- **Assert:** remaining volume above 0 gives state 1, exactly 0 gives state 2, below 0 gives state 3.
- **Pin:** a CHECK row becomes IN_USE.

### T205 — `fix_duplicates`
- **Setup:** two IN_USE rows with the same product, location, lotnr, in_price and eol (volumes 3 and 4).
- **Assert:** the result is one new IN_USE row with volume 7, and the originals have state 4 with volume 0.
- **Assert:** it returns `lines_merged` and `new_merged`.

### T206 — `remove_dead_products_from_stock` and `get_problems`
- **Assert:** IN_USE rows with volume above 0 for a soft-deleted product go to state 2, and the volume is kept.
- **Assert:** `get_problems` returns ERROR rows and negative IN_USE rows, but not ERROR rows with a NULL location (pin this).

### T207 — Stock read queries
- **Cover:** `get_stock_levels` (`[local, total]`, or `[0,0]` when there is no stock), `get_all_products`, `get_all_products_count`, `get_list` (with `procent` and location filters; pin the 0.01 vs 0.1 fallback prices), `gs1_lookup` (location first, then the fallback), and `get_product_stock`.

### T208 — Stock value snapshot
- **Assert:** `Stock_value_model::record_value()` inserts one `stock_value` row equal to `ROUND(SUM(in_price/(buy_volume/volume)),2)` over IN_USE rows that are not expired.
- Work out the expected value by hand from 3 seeded rows, one of them expired.

### T209 — Products model queries
- **Cover:** `search_product`, `search_product_with_stock` (escapes `%` and `_`), `get_products_stocks` (sellable only, ordered by `usage_count`, LIKE wildcards are **not** escaped), `get_products_by_type_with_stock` (special types 0, −1, −2, and a parent type that includes its children), `get_product_id`, `update_comment`, `set_backorder_filled`, `product_monthly_use` (none / per_vet / per_location), and `usage_detail`.
- **Also cover:** `Product_type_model::normalize_root` (all the null cases), `get_type_options` display names, `get_descendant_ids`, and `Product_label_model::replace_product_labels` (removes duplicates and zeros, ignores unknown IDs).

## Wholesale (admin)

### T210 — Wholesale index and search
- **Assert:** `wholesale` shows the counts and the recent items. `?search=<term>` finds by description, vendor_id, CNK and VHB.
- **Assert:** `wholesale/unlinked` lists only the items not linked to a product.

### T211 — Accept, ignore and unignore price changes
- **Assert:** `wholesale/pricing` lists only the articles whose `bruto` went up, with the percentage change.
- **Assert:** `wholesale/accept/{id}` sets `last_bruto=bruto` and redirects to `wholesale/index`. `accept/{id}/history` redirects to `wholesale/get_history/{id}`.
- **Assert:** `accept_all` updates every row.
- **Assert:** `ignore` and `unignore` toggle `ignore_change`.

### T212 — Wholesale history and AJAX
- **Assert:** `wholesale/get_history/{id}` shows deliveries, earlier prices, and manual `register_in` rows for the linked product.
- **Assert:** `wholesale/ajax_get_articles?term=x` returns `{"results":[{id,text,distr,bruto,vhb,cnk}]}`. **Pin:** an empty result gives an empty body.
- **Assert:** `wholesale/ajax_get_history/{id}` returns JSON with the AJAX header and an empty body without it.

### T213 — Delivery overview
- **Assert:** `wholesale/delivery_overview` groups `delivery` rows by date for the last month. A POST sets the range.
- **Assert:** `wholesale/delivery/{date}` lists that day's deliveries and its `register_in` rows.

## Logs (admin)

### T215 — Write-off log and write-off invoice
- **Assert:** `logs/write_off` lists the `liquidate` rows and the unbilled count.
- **Assert:** `logs/write_off_invoice` with unbilled rows creates a bill for owner **1837** (seed this owner) with status 4, totals 0, an `invoice_id` and the fixed `msg_invoice`. It sets `liquidate.bill_id` and redirects to `invoice/get_bill/{id}`.
- **Assert:** with no unbilled rows it redirects to `logs/write_off`.
- **Assert:** with owner 1837 missing it returns 500 with "client #1837 was not found".

### T216 — Other log pages
- **Assert:** `logs/product/{pid}` lists the `log_stock` rows for that product.
- **Assert:** `logs/nlog` lists the last 3 days of the `log` table, and a POST range filters it.
- **Assert:** `logs/delivery` lists the delivery slips, and `logs/delivery/{id}` lists their `register_in` products.
- **Assert:** `logs/software_version` shows the `migrations` version.

### T217 — Logger
- **Layer:** M. **File:** `tests/Models/LogsModelTest.php`.
- **Assert:** `logger(INFO, 'evt', 'msg')` inserts into `log` with the session `user_id` (or −1 in CLI) and `location`.
- **Assert:** `stock(DEBUG, 'evt', pid, vol, loc)` inserts into `log_stock`, and location 0 falls back to the session.
- Check the actual table names in the model: `log`/`log_stock` versus `logs`.
