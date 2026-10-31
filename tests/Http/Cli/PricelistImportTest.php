<?php

declare(strict_types=1);

/**
 * T326 - Cli::pricelist <filename>: imports a pipe separated price list from data/stored/pricelist/.
 *
 * First line is a header and ignored; 11 positional columns: art nr, description, bruto, btw, sell price,
 * distributor, CNK, VHB, distributor id, group, created (ignored). Wholesale_model::update_record: bruto '-'
 * is skipped; a known vendor_id is updated and, when bruto changed, the OLD bruto is stored in
 * `wholesale_price`; an unknown one is inserted with last_bruto = bruto. The group name is looked up in
 * `wholesale_type` and created when missing; an empty group is "unknown". The file moves to processed/.
 */
final class PricelistImportTest extends HttpTestCase
{
    private const DIR = 'data/stored/pricelist/';

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

    private function path(string $name, bool $processed = false): string
    {
        return dirname(__DIR__, 3) . '/' . self::DIR . ($processed ? 'processed/' : '') . $name;
    }

    private function write(array $lines): string
    {
        $name = 'test_pricelist_' . uniqid() . '.txt';
        $this->files[] = $name;
        $header = 'Art. nr.|Omschrijving|Bruto|BTW|Verk pr apotheek|Verdeler|CNK|VHB|Distr id|Groep|Aangemaakt';
        file_put_contents($this->path($name), implode("\n", array_merge([$header], $lines)) . "\n");

        return $name;
    }

    public function testExistingNewAndSkippedRowsAreHandled(): void
    {
        $vendorOld = 'PL-' . uniqid();
        $vendorNew = 'PL-' . uniqid();
        $vendorSkipped = 'PL-' . uniqid();
        Factory::wholesale(['vendor_id' => $vendorOld, 'description' => 'Oud', 'bruto' => 10.00, 'last_bruto' => 10.00, 'btw' => 6]);
        $name = $this->write([
            "$vendorOld|Nieuwe naam|12,50|21|17,25|DistriX|1111111|VHB-9|DX9|Medicatie|2026-01-01",
            "$vendorNew|Nieuw artikel|7,25|6|9,90|DistriY|2222222||DY1||2026-01-01",
            "$vendorSkipped|Niet leverbaar|-|6|0|DistriZ|3333333||DZ1|Medicatie|2026-01-01",
        ]);

        [$exit, $out] = $this->cli(['cli', 'pricelist', $name]);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('lines : 3', $out);

        // 1. the existing article is updated, its old bruto goes to the price history
        $row = $this->row('SELECT * FROM wholesale WHERE vendor_id = ?', [$vendorOld]);
        $this->assertSame('Nieuwe naam', $row['description']);
        $this->assertEqualsWithDelta(12.50, (float) $row['bruto'], 0.001);
        $this->assertSame(21, (int) $row['btw']);
        $this->assertEqualsWithDelta(17.25, (float) $row['sell_price'], 0.001);
        $this->assertSame('DistriX', $row['distributor']);
        $this->assertSame('1111111', $row['CNK']);
        $this->assertSame('VHB-9', $row['VHB']);
        $this->assertSame('DX9', $row['distributor_id']);
        $history = $this->rows('SELECT * FROM wholesale_price WHERE art_nr = ?', [$vendorOld]);
        $this->assertCount(1, $history);
        $this->assertEqualsWithDelta(10.00, (float) $history[0]['bruto'], 0.001);
        $medicatie = $this->scalar("SELECT id FROM wholesale_type WHERE name = 'Medicatie'");
        $this->assertSame((string) $medicatie, (string) $row['type']);

        // 2. the new article: last_bruto = bruto, an empty group becomes "unknown"
        $row = $this->row('SELECT * FROM wholesale WHERE vendor_id = ?', [$vendorNew]);
        $this->assertNotNull($row);
        $this->assertSame('Nieuw artikel', $row['description']);
        $this->assertEqualsWithDelta(7.25, (float) $row['bruto'], 0.001);
        $this->assertEqualsWithDelta(7.25, (float) $row['last_bruto'], 0.001);
        $this->assertSame(date('Y-m-d'), $row['last_bruto_date']);
        $this->assertEqualsWithDelta(9.90, (float) $row['sell_price'], 0.001);
        $this->assertSame('2222222', $row['CNK']);
        $unknown = $this->rows("SELECT id FROM wholesale_type WHERE name = 'unknown'");
        $this->assertCount(1, $unknown);
        $this->assertSame((string) $unknown[0]['id'], (string) $row['type']);
        $this->assertSame(0, $this->countWhere('wholesale_price', ['art_nr' => $vendorNew]));

        // 3. bruto '-' is skipped entirely (but still counted as a line)
        $this->assertSame(0, $this->countWhere('wholesale', ['vendor_id' => $vendorSkipped]));

        // file and log
        $this->assertFileDoesNotExist($this->path($name));
        $this->assertFileExists($this->path($name, true));
        $log = $this->row("SELECT * FROM log WHERE event = 'import_pricelist' AND msg = 'lines: 3'");
        $this->assertNotNull($log);
        $this->assertSame(0, $this->countWhere('log', ['event' => 'import_pricelist', 'msg' => 'issue moving file']));
    }

    public function testAnUnchangedBrutoWritesNoPriceHistory(): void
    {
        $vendor = 'PL-' . uniqid();
        Factory::wholesale(['vendor_id' => $vendor, 'bruto' => 10.00]);
        $name = $this->write(["$vendor|Zelfde|10,00|6|14,00|D|1|||Medicatie|x"]);

        $this->cli(['cli', 'pricelist', $name]);

        $this->assertSame(0, $this->countWhere('wholesale_price', ['art_nr' => $vendor]));
        $this->assertSame('Zelfde', $this->row('SELECT description FROM wholesale WHERE vendor_id = ?', [$vendor])['description']);
    }

    public function testAMissingFileIsReported(): void
    {
        $logs = $this->logCount('import_pricelist');

        [$exit, $out] = $this->cli(['cli', 'pricelist', 'nope-' . uniqid() . '.txt']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('File not found : data/stored/pricelist/nope-', $out);
        $this->assertSame($logs, $this->logCount('import_pricelist'));
    }
}
