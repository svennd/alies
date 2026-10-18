# 02 — Authentication and access control (T030–T049)

Every test here is Layer H, in `tests/Http/Auth/`.

How sign-in works today:
- Ion_auth uses the email address as the identity. Passwords are 12–40 characters.
- After 3 failed attempts within 600 s the account is locked, tracked per IP.
- After a successful login the session location is 0. The user must then choose a location (`welcome/change_location/{id}`).

## Base-controller matrix

| Base | Not logged in | Logged in, wrong group |
|---|---|---|
| `Vet_Controller` | redirect to `auth/login` | not vet, admin or accounting: **500** "Not part of the correct group;" |
| `Admin_Controller` | redirect to `auth/login` | vet check first, then a non-admin is **redirected to `/`** |
| `Accounting_Controller` | redirect to `auth/login` | not admin or accounting: **500** |
| `Auth` | no guard | — |
| `Cli` | web access: `show_error('Direct access is not allowed')` | — |
| `API_Controller` | API key header required: 401 JSON | — |

### T030 — Login page renders
- **Assert:** `GET auth/login` returns 200 and shows the identity and password fields. The header and footer layout is **not** used.
- **Assert:** `GET auth/login/2` sets the cookie `alies_location=2`.

### T031 — Successful login, then choosing a location
- **Assert:** POST valid credentials redirects to `/`.
- **Assert:** `GET /` shows the location picker. Pick a stable marker from `views/blocks/location.php` and check that it links to `welcome/change_location/1`.
- **Assert:** `GET welcome/change_location/1` redirects to `/` and sets `users.current_location=1`.
- **Assert:** `/` no longer shows the picker.
- **Assert:** a `logs` row `login_success` is written, and `users.last_login` is updated.

### T032 — Failed login and lockout
- **Assert:** a wrong password redirects back to `auth/login` and writes a `login_failed` log row.
- **Assert:** after 3 failures, even the correct password fails, because of the `login_attempts` table.
- **Clean up:** empty `login_attempts` afterwards.

### T033 — Logout
- **Assert:** `auth/logout` redirects to `auth/login`, and `users.current_location` becomes 0.
- **Assert:** after logout, `GET /` redirects to `auth/login`.

### T034 — Inactive user cannot log in
- **Assert:** a user with `active=0` and the correct password is redirected back to `auth/login` and is not logged in.

### T035 — Unauthenticated access to every protected controller
- **Data-driven:** use one representative GET route per controller. For example:
  - `owners/add`, `pets/fiche/1`, `events/event/1`, `invoice`, `stock/add_stock`, `products`;
  - `pricing/proc`, `admin/settings`, `member`, `reports/revenue`, `export`, `vamreg`;
  - `wholesale`, `logs/nlog`, `tools`, `restore/booking`, `stats`, `lab`;
  - `vaccine`, `tooth/fiche/1`, `breeds`, `rx/list/1`, `sticky`, `vet/profile`;
  - `search?search_query=x`, `report`, `limits/global`, `accounting/dashboard`, `help/vet`.
- **Assert:** every one redirects to `auth/login`.
- **Generate the list** from `application/controllers/*.php` (excluding `Auth`, `Cli` and `Upgrade`), and make the test fail when a new controller has no entry.

### T036 — Admin-only controllers reject a vet
- **Routes:** `admin/settings`, `member`, `reports/revenue`, `export`, `vamreg`, `wholesale`, `logs/nlog`, `tools`, `restore/booking`, `stats`, `accounting/dashboard`, `admin_invoice/edit_bill/1`.
- **Assert:** logged in as vet, each one redirects to `/`.
- **Assert:** logged in as admin, each one returns 200.

### T037 — The accounting role
- **Assert:** an accounting user gets 200 on `pricing/proc` (Accounting_Controller).
- **Assert:** a vet gets **500** on `pricing/proc`.
- **Assert:** an accounting user is redirected to `/` on `accounting/dashboard`, because that controller extends Admin_Controller. Pin this as an inconsistency.

### T038 — Users with the wrong group or no group
- **Assert:** `member@test` (members group only) and `nogroup@test` get **500** with the text "Not part of the correct group" on `owners/add`.

### T039 — Admin-only actions inside vet controllers
Some vet controllers check `in_group('admin')` inside individual methods.
- **Assert:** a vet is redirected to `/` and an admin passes for:
  - `pets/delete/{id}`,
  - `breeds/edit/{id}`,
  - `breeds/merge/{id}`,
  - `stock/edit/{id}`,
  - `stock/stock_check`,
  - `products/new`,
  - `products/set_backorder/{id}` and `products/unset_backorder/{id}`.
- **Assert:** `products/product/{id}` with a vet POST is accepted but **silently not saved**.
- **Assert:** `vaccine/index/5` as vet logs `boundary_vaccine_attack` and redirects to `/`; as admin it returns 200.
- **Assert:** `help/admin` as vet redirects to `vet/dashboard`, which then returns 404 (`known-bug`).
- **Pin as `known-bug`:** these work for a vet **without** an admin check: `stock/delete_stock`, `stock/write_off_full`, `stock/clear_error`, `products/delete_product`, `breeds/add` and `breeds/rebuild_frequenty`.

### T040 — Change password
- **Assert:** `vet/change_password` with a mismatched or too short `new` value shows the form again with an error.
- **Assert:** with a correct `old` and a valid `new`/`new_confirm`, the user is logged out and redirected to `auth/login`, and the new password works.
- **Restore the seed password** at the end.

### T041 — Group admin screens
- **Assert:** `auth/create_group` as admin with a valid `group_name` creates a row in `groups`.
- **Assert:** as vet it redirects to `auth`, which returns 404 (`known-bug`).
- **Assert:** `auth/edit_group/{id}` shows the form with the current name. **⚠ BUG:** saving never works: the form is validated with the rule `xss_clean`, which this CodeIgniter version no longer has, so the page shows "unspecified error (xss_clean)" and the group is not changed. Pin as `known-bug`.
- **Assert:** `groups.name` is `varchar(20)`: a longer name is silently cut (`known-bug`).

### T042 — Activate and deactivate a user
- **Assert:** `auth/deactivate/{id}` as admin, GET, shows the confirmation form.
- **Assert:** POST `confirm=yes` with the CSRF nonce taken from the form deactivates the user.
- **Assert:** `auth/activate/{id}` as admin activates the user again.
- **Pin:** both redirect to `auth`, which returns 404.

### T043 — CLI and upgrade endpoints over HTTP
- **Assert:** `GET cli/daily` over HTTP returns an error page containing "Direct access is not allowed", and **runs nothing**: no new `logs` rows and `stock_value` is unchanged.
- **Assert:** `GET upgrade/up`, `upgrade/down` and `upgrade/retry_medilab_codes` give the same error. `Upgrade` has the same `is_cli()` guard as `Cli` (checked in the constructor), so it is **not** open over HTTP. Also for an admin; the migration version does not change.

### T044 — Session location and the location bar
- **Assert:** after `change_location/2`, a new event created via `events/new_event/{pet}` has `location=2`.
- **Pin (`known-bug`):** `change_location/200` (a location that does not exist) is accepted. `users.current_location` is a TINYINT UNSIGNED, so `999` is stored as `255`.
