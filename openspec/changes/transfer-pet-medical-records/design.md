## Context

See `proposal.md` for motivation and `specs/pet-owner-transfer/spec.md` for the behavioral contract.

The current transfer flow clones the `pets` row, changes its owner, and marks the source row as transferred so old invoice rendering can continue to resolve events through the former owner. All related clinical tables retain the source pet ID. Medical-history queries also select events by one pet ID, so the successor appears to have no earlier history.

The test database currently contains 10 transferred source pets. All 10 source-to-successor pairs can be resolved uniquely by the stored transfer target, stable pet identity fields, transfer markers, and timestamps. Eight source pets retain clinical data; 51 visible events are eligible for history copying, with 101 product lines and 37 procedure lines available for textual summaries. Existing successor data includes vaccinations and weights that must remain untouched.

History-only events already have an application convention: `status = STATUS_HISTORY`, `payment = BILL_INVALID`, `report = REPORT_DONE`, and `no_history = 0`. Invoice collection selects events using the open payment value, so this convention provides a non-billable copy without new schema.

## Goals / Non-Goals

**Goals:**

- Keep the active successor pet clinically complete after a transfer.
- Preserve the original event graph for former-owner invoice and accounting history.
- Reuse one transfer operation for new transfers and historical backfill where practical.
- Make runtime transfer and migration backfill atomic and fail closed.
- Preserve original clinical dates and staff/location attribution in copied history.

**Non-Goals:**

- Redesign ownership history or replace the source/successor pet-record model.
- Add an event source/provenance column or a transfer-lineage table.
- Copy invoice lines, prices, booking codes, stock movements, VAMReg rows, or former-owner data.
- Copy events explicitly hidden from medical history.
- Copy event attachments; none of the currently eligible test-data events has attachments, and attachments are outside the requested transfer set.
- Address the existing GET completion route, authorization granularity, CSRF configuration, or other low-priority transfer defects.

## Decisions

### 1. Use a hybrid move-and-copy operation

Direct patient records will move by changing only their pet foreign-key column:

| Table | Column |
|---|---|
| `vaccine_pet` | `pet` |
| `pets_weight` | `pets` |
| `tooth` | `pet` |
| `tooth_msg` | `pet` |
| `rx` | `pet_id` |
| `lab` | `pet` |
| `lab_report` | `pet_id` |

Rows already associated with the successor are not selected or modified. Moving rather than copying prevents duplicate vaccination reminders and makes the active pet ID the single owner of direct clinical records.

Events will be copied rather than moved because an event's current pet relationship is required by historical invoice rendering. The source event and all of its child rows remain unchanged.

Alternative considered: move all events to the successor. Rejected because it breaks the relationship used to render former-owner bills.

Alternative considered: make every clinical screen lineage-aware. Rejected as unnecessary complexity for a rare operation and because the requested summary-copy approach fits the existing history-event convention.

### 2. Copy only visible events as finalized history summaries

For each source event with `no_history = 0`, create one successor event with:

- source `title`, `anamnese`, `type`, `location`, primary/supporting veterinarians, and timestamps;
- `pet` set to the successor pet;
- `status = STATUS_HISTORY`;
- `payment = BILL_INVALID`;
- `report = REPORT_DONE`;
- `no_history = 0`.

The insert must explicitly preserve the source `created_at` and `updated_at` instead of allowing the model's automatic timestamps to replace the clinical date.

Events with `no_history = 1` are skipped. No `events_products`, `events_procedures`, `events_upload`, or other invoice/event child rows are copied.

Alternative considered: retain products and procedures as child rows with zero prices. Rejected because those rows feed financial, stock, and usage reporting even when their price is zero.

### 3. Render products and procedures into the report body

Before inserting a history copy, load its product and procedure lines with their catalog names and units. Append non-empty sections to the original `anamnese` containing:

- item type;
- display name;
- signed recorded quantity;
- unit when one is available.

Names and units must be escaped before adding them to stored HTML. The summary excludes prices, tax, discounts, booking data, stock/lot data, invoice IDs, and owner data. Empty sections are omitted, and the source report is retained unchanged ahead of the appended summary.

### 4. Preserve vaccination provenance without duplicating billing rows

Moving a vaccination changes only `vaccine_pet.pet`. Its existing `event_id` and `event_line` remain attached to the original event/product row. This keeps the vaccination's source trace while reminders and pet vaccine screens resolve it through the successor. The copied event contains the product as summary text and does not receive a duplicate vaccination row.

### 5. Relink API labs to copied events

While copying events, retain an in-memory map from source event ID to copied event ID for the duration of the transaction. After `lab_report.pet_id` moves to the successor, copy applicable `events_labs` associations using that map. The source association remains unchanged; event display filters ensure the moved report appears on the successor copy.

Legacy `lab` records move by pet ID and require no event relinking. API reports without a copied source event remain available from the successor's lab list.

### 6. Centralize the runtime operation and make it transactional

The transfer orchestration will capture the ID returned by pet cloning and execute the following order inside one database transaction:

1. Lock and validate the source pet and target owner.
2. Clone the pet and retain the successor ID.
3. Preload eligible events, item summaries, and API lab associations.
4. Move the direct clinical rows to the successor.
5. Insert non-billable history copies and their applicable lab associations.
6. Mark the source pet transferred and clear its chip and companion link as currently done.
7. Commit only after every required query succeeds; otherwise roll back and return failure.

The controller logs success only after commit and redirects using the confirmed successor owner. The existing clone behavior continues to carry medication, nutrition, identification, current weight, vaccination-book number, and the shared avatar reference.

### 7. Backfill with a one-time sequential data migration

The next sequential migration will perform no DDL. It will construct a complete source-to-successor map before changing data. A candidate successor must match all available transfer evidence:

- target owner parsed from the source `[transfer:send:<owner>]` marker;
- successor owner;
- pet name, type, and nullable birth date;
- successor transfer marker;
- source update and successor creation timestamps within the transfer request window.

Every source must resolve to exactly one successor. The migration preflight also checks dental `(pet, tooth)` uniqueness and any other update constraint that could reject the move. Any missing, ambiguous, or conflicting mapping raises an error before mutation.

After preflight, the migration starts one transaction and applies the same move/copy rules to every mapped pair. It commits only when all pairs succeed. No `events.transfer_source_event_id`, transfer-lineage table, or other persistent idempotency marker is added: rollback protects failed attempts, and the sequential migration registry prevents rerunning a completed migration.

## Risks / Trade-offs

- **[Copied history no longer has structured event lines]** → Include product/procedure names, quantities, and units in clearly separated report sections and retain the original billed event unchanged.
- **[A historical pair cannot be resolved in another database]** → Validate every pair before mutation and abort with the unresolved source ID instead of selecting a likely candidate.
- **[A successor has a conflicting dental chart row]** → Detect composite-key conflicts during preflight and abort for manual resolution rather than overwrite either chart.
- **[A database error occurs partway through transfer or backfill]** → Use one transaction and check every insert/update before source hiding or commit.
- **[Directly moved API labs stop appearing on the original event view]** → Link each moved report to its copied successor event; preserve the original association for accounting/history integrity.
- **[The migration cannot safely be replayed after a successful unregistered commit]** → Accept this narrow operational trade-off per the chosen no-provenance design; run through the normal sequential migration command with a database backup and verify the recorded migration version immediately afterward.
- **[Post-deployment data rollback is ambiguous]** → Do not attempt an automatic reverse data migration after staff may have added successor records; restore the pre-migration backup if rollback is required after commit.

## Migration Plan

1. Back up the target database.
2. Deploy the transactional transfer implementation and sequential backfill migration together.
3. Run migration preflight and stop on any non-unique pair or key conflict.
4. Execute the data backfill transaction and record the sequential migration version.
5. Verify that mapped source pets retain original events and bills, direct clinical rows now reference successors, copied events are history/final/invalid-payment records, and vaccination reminders resolve to successor owners.
6. If execution fails before commit, correct the reported data issue and rerun; the transaction leaves the database unchanged.
7. If a problem is discovered after commit, stop new writes and restore the pre-migration database backup rather than attempting a lossy down migration.
