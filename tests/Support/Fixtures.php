<?php

declare(strict_types=1);

/**
 * IDs and values of the rows in tests/Fixtures/seed.sql. Keep both in sync.
 *
 * Rules of thumb for tests:
 *  - Reading seed rows is always fine.
 *  - Do not change seed rows that other tests depend on. Create your own with Factory instead.
 *  - Opening a bill (invoice/get_bill) attaches ALL open events of the owner, so billing tests
 *    should use a fresh Factory::owner(), not OWNER_JANSSENS.
 */
final class Fixtures
{
    // ---- login ------------------------------------------------------------------------------
    public const PASSWORD = 'TestPassword123!';

    public const USER_ADMIN = 1;      // admin@test.local     group admin
    public const USER_VET = 2;        // vet@test.local       group vet, order number 123456
    public const USER_ACCOUNTING = 3; // acc@test.local       group accounting
    public const USER_MEMBER = 4;     // member@test.local    group members only
    public const USER_NOGROUP = 5;    // nogroup@test.local   no group at all
    public const USER_VET2 = 6;       // vet2@test.local      group vet
    public const USER_INACTIVE = 7;   // inactive@test.local  group vet, active = 0

    /** role => [email, user id, may use vet screens] */
    public const USERS = [
        'admin'    => ['admin@test.local', self::USER_ADMIN, true],
        'vet'      => ['vet@test.local', self::USER_VET, true],
        'vet2'     => ['vet2@test.local', self::USER_VET2, true],
        'acc'      => ['acc@test.local', self::USER_ACCOUNTING, true],
        'member'   => ['member@test.local', self::USER_MEMBER, false],
        'nogroup'  => ['nogroup@test.local', self::USER_NOGROUP, false],
        'inactive' => ['inactive@test.local', self::USER_INACTIVE, false],
    ];

    public const GROUP_ADMIN = 1;
    public const GROUP_MEMBERS = 2;
    public const GROUP_VET = 3;
    public const GROUP_ACCOUNTING = 4;

    // ---- locations ----------------------------------------------------------------------------
    public const LOCATION_PRACTICE = 1;
    public const LOCATION_CAR = 2;
    public const LOCATION_DELETED = 3; // soft deleted

    // ---- owners -------------------------------------------------------------------------------
    public const OWNER_JANSSENS = 1001;   // 031234567 / 0470123456, mail, pets 2001 2002 2004 2005 (2009 deleted)
    public const OWNER_PEETERS = 1002;    // low_budget, pet 2003
    public const OWNER_COMPANY = 1003;    // btw_nr BE0123456789, pet 2006
    public const OWNER_DISABLED = 1004;
    public const OWNER_DUPLICATE_A = 1005; // same mobile (0475999999) as B, pet 2007
    public const OWNER_DUPLICATE_B = 1006; //                          pet 2008
    public const OWNER_DELETED = 1007;     // soft deleted
    public const OWNER_WRITE_OFF = 1837;   // the client Logs::write_off_invoice bills

    public const JANSSENS_LANDLINE = '031234567';
    public const JANSSENS_MOBILE = '0470123456';
    public const DUPLICATE_MOBILE = '0475999999';

    // ---- pets ---------------------------------------------------------------------------------
    public const PET_REX = 2001;          // dog, chip, 2 weights, last weight 25.50, breed Labrador, owner 1001
    public const PET_MIMI = 2002;         // cat, owner 1001
    public const PET_BOBBY = 2003;        // dog, owner 1002, chip 981000000000003
    public const PET_DEAD = 2004;         // owner 1001, death = 1
    public const PET_TRANSFERRED = 2005;  // owner 1001, transfered = 1
    public const PET_HORSE = 2006;        // owner 1003
    public const PET_DUPLICATE_A = 2007;  // chip 981000000000099 (also on B)
    public const PET_DUPLICATE_B = 2008;
    public const PET_DELETED = 2009;      // soft deleted
    public const PET_TRANSFER_SOURCE = 2010;     // transfered = 1, note [transfer:send:1002], owner 1001
    public const PET_TRANSFER_SUCCESSOR = 2011;  // the copy owner 1002 received

    public const CHIP_REX = '981000000000001';
    public const CHIP_DUPLICATE = '981000000000099';

    public const BREED_UNKNOWN = 1;       // Breeds::rebuild_frequenty skips this one
    public const BREED_LABRADOR = 2;
    public const BREED_CAT_SHORTHAIR = 5;

    // ---- catalogue ----------------------------------------------------------------------------
    public const BOOKING_PRODUCTS = 1;    // 6 % VAT
    public const BOOKING_SERVICES = 2;    // 21 % VAT
    public const BOOKING_DELETED = 3;     // soft deleted

    public const PROCEDURE_CONSULT = 3001;    // 25.00, booking 2 (21 %)
    public const PROCEDURE_NAILS = 3002;      // 10.00, booking 1 (6 %)
    public const PROCEDURE_DELETED = 3003;    // soft deleted

    public const TYPE_MEDICATION = 1;     // root
    public const TYPE_FOOD = 2;           // root
    public const TYPE_ANTIBIOTICS = 3;    // child of 1

    public const PRODUCT_TIERED = 4001;       // tiers {1: 10.00, 5: 8.00, 10: 6.00}, 6 %, dead volume 0, GTIN, wholesale 5001
    public const PRODUCT_VACCINE = 4002;      // vaccin, yearly, price 20.00, 6 %
    public const PRODUCT_ANTIBIOTIC = 4003;   // is_antibiotic, indication RESP, ab_unit PACKS, CNK 1234567, price 12.50
    public const PRODUCT_DEAD_VOLUME = 4004;  // dead volume 0.50, unit ml, price 2.00
    public const PRODUCT_NO_STOCK = 4005;     // price 5.00, 21 %, limit_stock 5, no stock rows
    public const PRODUCT_DELETED = 4006;      // soft deleted, still has stock row 5008
    public const PRODUCT_NOT_SELLABLE = 4007;

    public const GTIN_TIERED = '05414567000017';
    public const GTIN_VACCINE = '05414567000024';
    public const CNK_ANTIBIOTIC = '1234567';

    public const LABEL_PAIN = 1;
    public const LABEL_SKIN = 2;

    // ---- stock (stock.location holds the location id as a string) ----------------------------
    public const STOCK_TIERED_PRACTICE = 5001;   // product 4001, loc 1, lot AMX-001, volume 100, in use
    public const STOCK_TIERED_CAR = 5002;        // product 4001, loc 2, lot AMX-001, volume 20, in use
    public const STOCK_VACCINE = 5003;           // product 4002, loc 1, lot VAC-001, volume 10
    public const STOCK_ANTIBIOTIC = 5004;        // product 4003, loc 1, lot AB-001, volume 30
    public const STOCK_DEAD_VOLUME = 5005;       // product 4004, loc 1, lot DV-001, volume 50
    public const STOCK_EXPIRED = 5006;           // product 4001, loc 1, lot AMX-OLD, volume 5, eol 30 days ago
    public const STOCK_ERROR = 5007;             // product 4001, loc 1, state 3, volume -2
    public const STOCK_DELETED_PRODUCT = 5008;   // product 4006, loc 1, volume 7, in use

    public const STATE_CHECK = 0;
    public const STATE_IN_USE = 1;
    public const STATE_HISTORY = 2;
    public const STATE_ERROR = 3;
    public const STATE_MERGE = 4;

    // ---- wholesale ----------------------------------------------------------------------------
    public const WHOLESALE_LINKED = 5001;     // linked to product 4001
    public const WHOLESALE_UNLINKED = 5002;
    public const WHOLESALE_INCREASED = 5003;  // bruto 12.00, last_bruto 10.00

    // ---- consultations, bills ----------------------------------------------------------------
    public const EVENT_OPEN = 6001;       // pet 2001, status 0, no bill: product 4001 x2 (line 8001) + consult (line 8101)
    public const EVENT_CLOSED = 6002;     // pet 2002, status 1, bill 7001: vaccine (line 8002) + consult (line 8102)
    public const EVENT_ON_PENDING = 6003; // pet 2003, status 0, bill 7002: consult (line 8103)

    public const BILL_PAID = 7001;        // owner 1001, invoice_id 1, net 45.00, BTW_6 20.00, BTW_21 25.00, gross 51.45
    public const BILL_PENDING = 7002;     // owner 1002, net 25.00, BTW_21 25.00, gross 30.25
    public const BILL_DELETED = 7003;     // owner 1001, soft deleted

    public const LINE_PRODUCT_OPEN_EVENT = 8001;
    public const LINE_PRODUCT_CLOSED_EVENT = 8002;
    public const LINE_PROCEDURE_OPEN_EVENT = 8101;

    // ---- clinical -----------------------------------------------------------------------------
    public const VACCINE_DUE_NEXT_MONTH = 11001;  // pet 2002, redo on the 15th of next month
    public const VACCINE_EXPIRED = 11002;         // pet 2001, redo two months ago
    public const VACCINE_REMINDER_OFF = 11003;    // pet 2003, no_rappel = 1

    public const LAB_REPORT = 10001;              // pet 2001, results ALT, CREA, GLU

    // ---- misc ---------------------------------------------------------------------------------
    public const API_KEY_ACTIVE = 'test-active-api-key';
    public const API_KEY_REVOKED = 'test-revoked-api-key';

    public const STAT_NEW_CLIENTS = 1;
    public const STICKY = 1;
    public const VAMREG_INDEX_ANTIBIOTIC = 1;
}
