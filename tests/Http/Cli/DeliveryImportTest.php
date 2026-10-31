<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T325 - Cli::delivery <filename>: imports a pipe separated Covetrus delivery file from data/stored/delivery/.
 *
 * Columns are found by their (Dutch) header names. Per line: one `delivery` row (dates j/m/Y -> Y-m-d, decimal
 * comma -> point, wholesale_id from `wholesale.vendor_id = Art. nr.` or NULL); when the CNK is in `vamreg_index`
 * also a `vamreg_in_buffer` DRAFT row (BE / DIST_BE, in_quantity_pack_count = aantal). The file is then moved to
 * processed/, "in <file> lines : N" is printed and a log row `import_delivery` written. A missing file prints
 * "File not found". Nothing prevents importing the same file twice.
 */
final class DeliveryImportTest extends HttpTestCase
{
    private const DIR = 'data/stored/delivery/';
    private const HEADER = 'Besteldatum|Bestelbonnr|Mijn Referentie|Art. nr.|Omschrijving|CNK nummer|Leveringsdatum|'
        . 'Leveringsbon nummer|bruto prijs|netto prijs|BTW|aantal|Lotnummer|Vervaldatum|Facturatie';

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $name) {
            foreach ([self::DIR . $name, self::DIR . 'processed/' . $name] as $path) {
                $path = dirname(__DIR__, 3) . '/' . $path;
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
        parent::tearDown();
    }

    private function write(string $name, array $lines): string
    {
        $this->files[] = $name;
        file_put_contents(dirname(__DIR__, 3) . '/' . self::DIR . $name, implode("\n", array_merge([self::HEADER], $lines)) . "\n");

        return $name;
    }

    private function path(string $name, bool $processed = false): string
    {
        return dirname(__DIR__, 3) . '/' . self::DIR . ($processed ? 'processed/' : '') . $name;
    }

    /** @return array{string, string, int, int, int, int, string} file, vendor, cnk row1, cnk row2, nr1, nr2 */
    private function scenario(): array
    {
        $vendor = 'DLV-' . uniqid();
        $wholesale = Factory::wholesale(['vendor_id' => $vendor]);
        $cnk1 = random_int(3000000, 4999999);
        $cnk2 = random_int(5000000, 9999999);
        Factory::insert('vamreg_index', [
            'cti' => 'cti-' . uniqid(), 'cnk' => (string) $cnk1, 'ppnNl' => 'x', 'susage' => 'VETERINARY',
        ]);
        $nr1 = random_int(100000, 400000000);
        $nr2 = $nr1 + 1;
        $name = 'test_delivery_' . uniqid() . '.txt';
        $this->write($name, [
            "5/03/2026|1001|ref one|$vendor|Amoxi|$cnk1|7/03/2026|$nr1|12,50|10,25|6|3|LOTA|15/11/2027|F1",
            "20/12/2025|1002|ref two|NOPE-" . uniqid() . "|Other|$cnk2|22/12/2025|$nr2|4,00|3,50|21|2|LOTB|01/01/2028|F2",
        ]);

        return [$name, $vendor, $cnk1, $cnk2, $nr1, $nr2, (string) $wholesale];
    }

    public function testEveryLineBecomesADeliveryRowWithConvertedValues(): void
    {
        [$name, $vendor, $cnk1, $cnk2, $nr1, $nr2, $wholesale] = $this->scenario();

        [$exit, $out] = $this->cli(['cli', 'delivery', $name]);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString("in $name lines : 2", $out);

        $one = $this->row('SELECT * FROM delivery WHERE delivery_nr = ?', [$nr1]);
        $this->assertNotNull($one);
        $this->assertSame('2026-03-05', $one['order_date']);
        $this->assertSame('2026-03-07', $one['delivery_date']);
        $this->assertSame('2027-11-15', $one['due_date']);
        $this->assertSame(1001, (int) $one['order_nr']);
        $this->assertSame('ref one', $one['my_ref']);
        $this->assertSame($vendor, $one['wholesale_artnr']);
        $this->assertSame($wholesale, (string) $one['wholesale_id']);
        $this->assertSame($cnk1, (int) $one['CNK']);
        $this->assertEqualsWithDelta(12.50, (float) $one['bruto_price'], 0.001);
        $this->assertEqualsWithDelta(10.25, (float) $one['netto_price'], 0.001);
        $this->assertSame(6, (int) $one['btw']);
        $this->assertSame(3, (int) $one['amount']);
        $this->assertSame('LOTA', $one['lotnr']);
        $this->assertSame('F1', $one['billing']);

        $two = $this->row('SELECT * FROM delivery WHERE delivery_nr = ?', [$nr2]);
        $this->assertNotNull($two);
        $this->assertNull($two['wholesale_id'], 'an unknown article number leaves wholesale_id NULL');
        $this->assertSame('2025-12-20', $two['order_date']);
        $this->assertSame($cnk2, (int) $two['CNK']);
        $this->assertEqualsWithDelta(4.00, (float) $two['bruto_price'], 0.001);
        $this->assertSame(21, (int) $two['btw']);
    }

    public function testOnlyACnkKnownToVamregGetsADraftBufferRow(): void
    {
        [$name, , $cnk1, $cnk2, , , $wholesale] = $this->scenario();

        $this->cli(['cli', 'delivery', $name]);

        $buffer = $this->rows('SELECT * FROM vamreg_in_buffer WHERE cnk = ?', [(string) $cnk1]);
        $this->assertCount(1, $buffer);
        $this->assertSame('DRAFT', $buffer[0]['status']);
        $this->assertSame(3, (int) $buffer[0]['in_quantity_pack_count']);
        $this->assertSame($wholesale, (string) $buffer[0]['wholesale_id']);
        $this->assertSame('2026-03-07', $buffer[0]['delivery']);
        $this->assertSame('BE', $buffer[0]['product_type']);
        $this->assertSame('DIST_BE', $buffer[0]['provider_type']);
        $this->assertCount(0, $this->rows('SELECT * FROM vamreg_in_buffer WHERE cnk = ?', [(string) $cnk2]));
    }

    public function testTheFileIsMovedToProcessedAndTheImportIsLogged(): void
    {
        [$name] = $this->scenario();
        $this->assertFileExists($this->path($name));

        [, $out] = $this->cli(['cli', 'delivery', $name]);

        $this->assertFileDoesNotExist($this->path($name));
        $this->assertFileExists($this->path($name, true));
        $this->assertStringNotContainsString('issue moving file', $out);
        $log = $this->row("SELECT * FROM log WHERE event = 'import_delivery' AND msg = ?", ["file: $name lines: 2"]);
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level']);
    }

    public function testAMissingFileIsReportedAndNothingIsImported(): void
    {
        $before = $this->countWhere('delivery');
        $logs = $this->logCount('import_delivery');

        [$exit, $out] = $this->cli(['cli', 'delivery', 'does-not-exist-' . uniqid() . '.txt']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('File not found : data/stored/delivery/does-not-exist-', $out);
        $this->assertSame($before, $this->countWhere('delivery'));
        $this->assertSame($logs, $this->logCount('import_delivery'));
    }

    #[Group('known-bug')]
    public function testImportingTheSameFileAgainDuplicatesTheRows(): void
    {
        // KNOWN BUG: there is no duplicate check on delivery number - decide in CI4 whether to keep
        [$name, , , , $nr1] = $this->scenario();
        $this->cli(['cli', 'delivery', $name]);
        $this->assertSame(1, $this->countWhere('delivery', ['delivery_nr' => $nr1]));

        // put the same content back, as a second upload of the same file would
        copy($this->path($name, true), $this->path($name));
        $this->cli(['cli', 'delivery', $name]);

        $this->assertSame(2, $this->countWhere('delivery', ['delivery_nr' => $nr1]));
    }
}
