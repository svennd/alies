<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/ExcelExporter.php';

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * T023 - application/libraries/ExcelExporter.php: exportToExcel().
 *
 * The method sends download headers, streams the xlsx to the output and then calls exit, so it runs in
 * a separate php process (a small script written to the temp directory). The bytes it prints are read
 * back with PhpSpreadsheet. Headers are not inspectable from the command line.
 */
final class ExcelExporterTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/alies-excel-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);
    }

    /**
     * Runs exportToExcel($data) in its own process and returns the worksheet as rows (cell values).
     *
     * @return array{rows: array<int, array<string, mixed>>, sheet: \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet, bytes: string}
     */
    private function export(array $data): array
    {
        $script = $this->dir . '/run.php';
        file_put_contents($script, '<?php
            define("BASEPATH", "x");
            require ' . var_export(APPPATH . 'libraries/ExcelExporter.php', true) . ';
            (new ExcelExporter())->exportToExcel(unserialize(base64_decode($argv[1])), "test.xlsx");
            echo "NOT REACHED";
        ');

        $process = proc_open(
            [PHP_BINARY, $script, base64_encode(serialize($data))],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $bytes = (string) stream_get_contents($pipes[1]);
        $errors = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $this->assertSame('', $errors, 'the export wrote errors');
        $this->assertStringStartsWith('PK', $bytes, 'an xlsx file is a zip archive');
        $this->assertStringNotContainsString('NOT REACHED', $bytes, 'exportToExcel() ends the request with exit');

        file_put_contents($this->dir . '/out.xlsx', $bytes);
        $sheet = IOFactory::load($this->dir . '/out.xlsx')->getActiveSheet();

        return ['rows' => $sheet->toArray(null, true, false, true), 'sheet' => $sheet, 'bytes' => $bytes];
    }

    public function testNothingToExportIsAnException(): void
    {
        $this->expectExceptionObject(new Exception('No data provided for export.'));

        (new ExcelExporter())->exportToExcel([], 'empty.xlsx');
    }

    public function testTheHeaderRowIsTheKeysOfTheFirstRowWithACapital(): void
    {
        $result = $this->export([
            ['name' => 'aspirin', 'volume' => 2, 'last_bill' => '2026-01-01'],
        ]);

        $this->assertSame(['A' => 'Name', 'B' => 'Volume', 'C' => 'Last_bill'], $result['rows'][1]);
    }

    public function testDataStartsOnRowTwoInTheOrderOfTheInput(): void
    {
        $result = $this->export([
            ['name' => 'aspirin', 'volume' => 2],
            ['name' => 'ibuprofen', 'volume' => 3.5],
            ['name' => 'paracetamol', 'volume' => 10],
        ]);

        $this->assertCount(4, $result['rows']);
        $this->assertSame(['A' => 'aspirin', 'B' => 2], $result['rows'][2]);
        $this->assertSame(['A' => 'ibuprofen', 'B' => 3.5], $result['rows'][3]);
        $this->assertSame(['A' => 'paracetamol', 'B' => 10], $result['rows'][4]);
    }

    public function testNumbersStayNumbersAndTextStaysText(): void
    {
        $sheet = $this->export([['text' => '007', 'int' => 7, 'float' => 1.25]])['sheet'];

        $this->assertSame('007', $sheet->getCell('A2')->getValue());
        $this->assertSame(7, $sheet->getCell('B2')->getValue());
        $this->assertSame(1.25, $sheet->getCell('C2')->getValue());
    }

    public function testMissingAndNullValuesAreEmptyCellsFollowingTheColumnsOfTheFirstRow(): void
    {
        $result = $this->export([
            ['a' => 1, 'b' => 2],
            ['a' => null, 'b' => 5],
            ['b' => 6, 'extra' => 'ignored'],
        ]);

        $this->assertSame(['A' => null, 'B' => 5], $result['rows'][3]);
        $this->assertSame(['A' => null, 'B' => 6], $result['rows'][4]);
        $this->assertArrayNotHasKey('C', $result['rows'][4], 'columns that are not in the first row are not exported');
    }

    public function testUnicodeSurvives(): void
    {
        $this->assertSame('Café — naïve', $this->export([['t' => 'Café — naïve']])['sheet']->getCell('A2')->getValue());
    }

    public function testRowsAreWrittenAtTheirArrayKeyPlusTwoSoTheListMustStartAtZero(): void
    {
        // quirk: the row number is taken from the array key. A filtered list (keys 5 and 6) leaves
        // an empty gap of rows between the header and the data
        $result = $this->export([5 => ['x' => 'five'], 6 => ['x' => 'six']]);

        $this->assertSame('five', $result['sheet']->getCell('A7')->getValue());
        $this->assertSame('six', $result['sheet']->getCell('A8')->getValue());
        $this->assertNull($result['sheet']->getCell('A2')->getValue());
    }

    #[Group('known-bug')]
    public function testTextThatStartsWithAnEqualsSignBecomesAFormula(): void
    {
        // KNOWN BUG: formula injection. A client or product name starting with "=" is stored as a formula
        // and evaluated when the sheet is opened - decide in CI4 whether to escape it
        $cell = $this->export([['name' => '=1+1']])['sheet']->getCell('A2');

        $this->assertTrue($cell->isFormula());
        $this->assertSame('=1+1', $cell->getValue());
    }
}
