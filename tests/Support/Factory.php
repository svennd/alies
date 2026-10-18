<?php

declare(strict_types=1);

/**
 * Creates database rows for tests and returns their IDs. Every creator takes an array of column
 * overrides as its last argument; everything not mentioned gets a sensible default.
 *
 *   $owner = Factory::owner();
 *   $pet   = Factory::pet($owner, ['name' => 'Fluffy']);
 *   $event = Factory::event($pet, 0);
 *   Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 2.0);
 *
 * Where the rows go depends on the layer:
 *  - HTTP tests: PDO connection to the test database (default, HttpTestCase does not need to do anything).
 *  - Model tests: Factory::useMysqli($this->ci->db->conn_id), so the rows live in the test's transaction.
 *
 * Use Factory::raw('NOW()') for SQL expressions.
 */
final class Factory
{
    private static ?FactorySink $sink = null;

    // ---- plumbing -----------------------------------------------------------------------------

    public static function usePdo(PDO $pdo): void
    {
        self::$sink = new PdoSink($pdo);
    }

    public static function useMysqli(mysqli $mysqli): void
    {
        self::$sink = new MysqliSink($mysqli);
    }

    public static function reset(): void
    {
        self::$sink = null;
    }

    public static function raw(string $sql): RawSql
    {
        return new RawSql($sql);
    }

    public static function unique(string $prefix = 'T'): string
    {
        static $counter = 0;
        $counter++;

        return sprintf('%s%s%d', $prefix, substr(bin2hex(random_bytes(3)), 0, 5), $counter);
    }

    /** Inserts one row and returns its auto increment id (0 for tables without one). */
    public static function insert(string $table, array $row): int
    {
        $sink = self::sink();
        $columns = [];
        $values = [];
        foreach ($row as $column => $value) {
            $columns[] = '`' . $column . '`';
            $values[] = self::literal($sink, $value);
        }

        $sink->execute(sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, implode(', ', $columns), implode(', ', $values)));

        return $sink->lastInsertId();
    }

    private static function sink(): FactorySink
    {
        return self::$sink ??= new PdoSink(TestDatabase::connect());
    }

    private static function literal(FactorySink $sink, mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            $value instanceof RawSql => $value->sql,
            is_bool($value) => $value ? '1' : '0',
            is_int($value) => (string) $value,
            is_float($value) => rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.') ?: '0',
            default => $sink->quote((string) $value),
        };
    }

    private static function merge(array $defaults, array $overrides): array
    {
        return array_merge($defaults, $overrides);
    }

    private static function timestamps(): array
    {
        return ['created_at' => self::raw('NOW()'), 'updated_at' => self::raw('NOW()')];
    }

    // ---- people and animals -------------------------------------------------------------------

    public static function owner(array $overrides = []): int
    {
        return self::insert('owners', self::merge([
            'first_name' => 'Test',
            'last_name' => self::unique('Owner'),
            'telephone' => null,
            'mobile' => null,
            'street' => 'Teststraat',
            'nr' => '1',
            'city' => 'Antwerpen',
            'main_city' => 'Antwerpen',
            'province' => 'Antwerpen',
            'zip' => '2000',
            'mail' => null,
            'msg' => '',
            'debts' => 0,
            'disabled' => 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'last_bill' => self::raw('CURDATE()'),
            'initial_vet' => Fixtures::USER_VET,
            'initial_loc' => Fixtures::LOCATION_PRACTICE,
        ] + self::timestamps(), $overrides));
    }

    public static function pet(int $ownerId, array $overrides = []): int
    {
        return self::insert('pets', self::merge([
            'type' => 0,
            'name' => self::unique('Pet'),
            'birth' => self::raw('DATE_SUB(CURDATE(), INTERVAL 3 YEAR)'),
            'death' => 0,
            'breed' => Fixtures::BREED_UNKNOWN,
            'gender' => 0,
            'color' => 'bruin',
            'last_weight' => 10.00,
            'lost' => 0,
            'hairtype' => 'kort',
            'owner' => $ownerId,
            'location' => Fixtures::LOCATION_PRACTICE,
            'init_vet' => Fixtures::USER_VET,
            'transfered' => 0,
        ] + self::timestamps(), $overrides));
    }

    public static function petWeight(int $petId, float $weight, ?string $createdAt = null): int
    {
        return self::insert('pets_weight', [
            'pets' => $petId,
            'weight' => $weight,
            'created_at' => $createdAt ?? self::raw('NOW()'),
            'updated_at' => self::raw('NOW()'),
        ]);
    }

    public static function breed(array $overrides = []): int
    {
        return self::insert('breeds', self::merge([
            'name' => self::unique('Breed'),
            'type' => 0,
            'freq' => 0,
            'male_min_weight' => 1,
            'male_max_weight' => 2,
            'female_min_weight' => 1,
            'female_max_weight' => 2,
        ] + self::timestamps(), $overrides));
    }

    // ---- catalogue ----------------------------------------------------------------------------

    public static function procedure(array $overrides = []): int
    {
        return self::insert('procedures', self::merge([
            'name' => self::unique('Procedure'),
            'price' => 10.00,
            'booking_code' => Fixtures::BOOKING_SERVICES,
        ] + self::timestamps(), $overrides));
    }

    /** A product with one price tier of 10.00 (pass 'price' => null to skip it, or use productPrice()). */
    public static function product(array $overrides = []): int
    {
        $price = array_key_exists('price', $overrides) ? $overrides['price'] : 10.00;
        unset($overrides['price']);

        $id = self::insert('products', self::merge([
            'name' => self::unique('Product'),
            'wholesale_name' => '',
            'short_name' => '',
            'producer' => 'Test',
            'supplier' => 'Test',
            'posologie' => '',
            'toedieningsweg' => '',
            'type' => Fixtures::TYPE_MEDICATION,
            'dead_volume' => 0,
            'buy_volume' => 1,
            'sell_volume' => 1,
            'buy_price' => 1.00,
            'unit_buy' => 'stuk',
            'unit_sell' => 'stuk',
            'btw_buy' => 6,
            'btw_sell' => 6,
            'booking_code' => Fixtures::BOOKING_PRODUCTS,
            'delay' => 0,
            'comment' => '',
            'sellable' => 1,
            'discontinued' => 0,
            'limit_stock' => 0,
            'vaccin' => 0,
            'vaccin_freq' => 0,
            'vhbcode' => '',
            'wholesale' => 0,
            'usage_count' => 0,
            'is_antibiotic' => 0,
        ] + self::timestamps(), $overrides));

        if ($price !== null) {
            self::productPrice($id, 1.0, (float) $price);
        }

        return $id;
    }

    public static function productPrice(int $productId, float $volume, float $price): int
    {
        return self::insert('products_price', [
            'product_id' => $productId,
            'volume' => $volume,
            'price' => $price,
        ] + self::timestamps());
    }

    public static function stock(int $productId, array $overrides = []): int
    {
        return self::insert('stock', self::merge([
            'product_id' => $productId,
            'eol' => self::raw('DATE_ADD(CURDATE(), INTERVAL 1 YEAR)'),
            'location' => (string) Fixtures::LOCATION_PRACTICE,
            'in_price' => 1.00,
            'cat_price' => 1.00,
            'lotnr' => self::unique('LOT'),
            'volume' => 10.00,
            'barcode' => self::unique('BC'),
            'supplier' => 'Test',
            'state' => Fixtures::STATE_IN_USE,
        ] + self::timestamps(), $overrides));
    }

    public static function wholesale(array $overrides = []): int
    {
        return self::insert('wholesale', self::merge([
            'vendor_id' => self::unique('W-'),
            'description' => self::unique('Article'),
            'bruto' => 10.00,
            'last_bruto' => 10.00,
            'last_bruto_date' => self::raw('CURDATE()'),
            'btw' => 6,
            'sell_price' => 14.00,
            'distributor' => 'Test Distri',
            'CNK' => (string) random_int(1000000, 9999999),
            'type' => 1,
            'ignore_change' => 0,
        ] + self::timestamps(), $overrides));
    }

    // ---- consultations and bills --------------------------------------------------------------

    public static function event(int $petId, int $status = 0, int $payment = 0, array $overrides = []): int
    {
        return self::insert('events', self::merge([
            'title' => self::unique('Event'),
            'anamnese' => '',
            'pet' => $petId,
            'type' => 0,
            'status' => $status,
            'payment' => $payment,
            'location' => Fixtures::LOCATION_PRACTICE,
            'vet' => Fixtures::USER_VET,
            'vet_support_1' => 0,
            'vet_support_2' => 0,
            'report' => 0,
            'no_history' => 0,
            'created_at' => self::raw('NOW()'),
            'updated_at' => self::raw('NOW()'),
        ], $overrides));
    }

    /**
     * Adds a product line. Net and gross follow the same rule as the application:
     * net = unit price * volume, gross = net * (1 + vat / 100), both rounded to 2 decimals.
     */
    public static function eventProduct(int $eventId, int $productId, float $volume = 1.0, array $overrides = []): int
    {
        $unit = (float) ($overrides['unit_price'] ?? 10.00);
        $btw = (int) ($overrides['btw'] ?? 6);
        $net = round($unit * $volume, 2);

        return self::insert('events_products', self::merge([
            'product_id' => $productId,
            'event_id' => $eventId,
            'volume' => $volume,
            'price_net' => $net,
            'price_brut' => round($net * (1 + $btw / 100), 2),
            'unit_price' => $unit,
            'btw' => $btw,
            'booking' => $btw === 21 ? Fixtures::BOOKING_SERVICES : Fixtures::BOOKING_PRODUCTS,
            'stock_id' => null,
        ] + self::timestamps(), $overrides));
    }

    public static function eventProcedure(int $eventId, int $procedureId = Fixtures::PROCEDURE_CONSULT, float $volume = 1.0, array $overrides = []): int
    {
        $unit = (float) ($overrides['unit_price'] ?? 25.00);
        $btw = (int) ($overrides['btw'] ?? 21);
        $net = round($unit * $volume, 2);

        return self::insert('events_procedures', self::merge([
            'procedures_id' => $procedureId,
            'event_id' => $eventId,
            'volume' => $volume,
            'price_net' => $net,
            'price_brut' => round($net * (1 + $btw / 100), 2),
            'unit_price' => $unit,
            'btw' => $btw,
            'booking' => $btw === 21 ? Fixtures::BOOKING_SERVICES : Fixtures::BOOKING_PRODUCTS,
        ] + self::timestamps(), $overrides));
    }

    public static function bill(int $ownerId, int $status = 0, array $overrides = []): int
    {
        return self::insert('bills', self::merge([
            'owner_id' => $ownerId,
            'vet' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'total_brut' => 0,
            'total_net' => 0,
            'BTW_0' => 0,
            'BTW_6' => 0,
            'BTW_21' => 0,
            'cash' => 0,
            'card' => 0,
            'transfer' => 0,
            'transfer_verified' => 0,
            'modified' => 0,
            'status' => $status,
            'msg' => '',
            'msg_invoice' => '',
            'mail' => 0,
        ] + self::timestamps(), $overrides));
    }

    /** A cash/card/transfer payment row. The schema wants a deleted_at, the application stores the zero date. */
    public static function payment(int $billId, array $overrides = []): int
    {
        return self::insert('payment', self::merge([
            'card' => 0,
            'cash' => 0,
            'transfer' => 0,
            'bill_id' => $billId,
            'vet' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'deleted_at' => '0000-00-00 00:00:00',
        ] + self::timestamps(), $overrides));
    }

    // ---- clinical -----------------------------------------------------------------------------

    public static function vaccine(int $petId, array $overrides = []): int
    {
        return self::insert('vaccine_pet', self::merge([
            'product_id' => Fixtures::PRODUCT_VACCINE,
            'event_id' => 0,
            'event_line' => null,
            'product' => 'Nobivac Vaccin',
            'pet' => $petId,
            'redo' => self::raw("DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-15'), INTERVAL 1 MONTH)"),
            'no_rappel' => 0,
            'location' => Fixtures::LOCATION_PRACTICE,
            'vet' => Fixtures::USER_VET,
            'created_at' => self::raw('NOW()'),
            'updated_at' => self::raw('NOW()'),
        ], $overrides));
    }

    /**
     * @param list<array{code: string, value_num?: float|null, value_text?: string|null, unit?: string, ref_min?: float|null, ref_max?: float|null}> $results
     */
    public static function labReport(int $petId, array $results = [], array $overrides = []): int
    {
        $id = self::insert('lab_report', self::merge([
            'pet_id' => $petId,
            'device' => 'ms4s2',
            'source' => 'test',
            'source_id' => self::unique('S-'),
            'software_version' => '1.0',
            'metadata' => '{}',
            'sample_date' => self::raw('NOW()'),
            'created_at' => self::raw('NOW()'),
            'updated_at' => self::raw('NOW()'),
        ], $overrides));

        foreach ($results as $result) {
            self::insert('lab_results', $result + [
                'report_id' => $id,
                'value_num' => null,
                'value_text' => null,
                'unit' => null,
                'ref_min' => null,
                'ref_max' => null,
            ]);
        }

        return $id;
    }

    // ---- settings and api keys ----------------------------------------------------------------

    /** Stores a setting the way Config_model::store does (base64 encoded), replacing an existing one. */
    public static function setting(string $name, string $value): void
    {
        $sink = self::sink();
        $sink->execute(sprintf(
            'INSERT INTO `config` (`name`, `value`, `created_at`, `updated_at`) VALUES (%s, %s, NOW(), NOW())
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()',
            $sink->quote($name),
            $sink->quote(base64_encode($value))
        ));
    }

    public static function apiKey(?string $key = null, bool $active = true, string $device = 'ms4s2'): string
    {
        $key ??= self::unique('key-');
        self::insert('api_keys', [
            'key_hash' => $key,
            'device' => $device,
            'active' => $active ? 1 : 0,
            'rate_limit' => 60,
            'created_at' => self::raw('NOW()'),
        ]);

        return $key;
    }
}

/** Marks an SQL expression that must not be quoted. */
final class RawSql
{
    public function __construct(public readonly string $sql)
    {
    }
}

interface FactorySink
{
    public function execute(string $sql): void;

    public function lastInsertId(): int;

    public function quote(string $value): string;
}

final class PdoSink implements FactorySink
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function execute(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function quote(string $value): string
    {
        return $this->pdo->quote($value);
    }
}

final class MysqliSink implements FactorySink
{
    public function __construct(private readonly mysqli $mysqli)
    {
    }

    public function execute(string $sql): void
    {
        if ($this->mysqli->query($sql) === false) {
            throw new RuntimeException('Factory insert failed: ' . $this->mysqli->error . ' in ' . substr($sql, 0, 200));
        }
    }

    public function lastInsertId(): int
    {
        return (int) $this->mysqli->insert_id;
    }

    public function quote(string $value): string
    {
        return "'" . $this->mysqli->real_escape_string($value) . "'";
    }
}
