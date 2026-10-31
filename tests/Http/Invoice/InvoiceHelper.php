<?php

declare(strict_types=1);

/**
 * Helpers for the bill tests (tests/Http/Invoice): builds owners with open consultations and
 * removes the PDF files a test caused. Files of other agents are never touched, only the ones
 * registered here.
 */
final class InvoiceHelper
{
    /** @var list<string> */
    private static array $files = [];

    public static function storage(): string
    {
        return dirname(__DIR__, 3) . '/data/stored/.invoices';
    }

    /** Path of a stored pdf without the extension check: Y/W/<name>.pdf */
    public static function pdfPath(string $date, string $name): string
    {
        $time = strtotime($date);

        return self::storage() . '/' . date('Y', $time) . '/' . date('W', $time) . '/' . $name . '.pdf';
    }

    /** Remember a path (it is removed now and again at cleanUp). */
    public static function register(string $path): string
    {
        self::$files[] = $path;
        if (is_file($path)) {
            @unlink($path);
        }

        return $path;
    }

    public static function cleanUp(): void
    {
        foreach (self::$files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        self::$files = [];
    }

    /**
     * A fresh owner with one pet and one open event that has the lines of the standard example:
     * product A 2 x 10.00 at 6 % (from lot $lot), product B 1 x 100.00 at 21 % (no stock) and a
     * procedure of 25.00 at 21 %. Net 145.00, BTW_6 base 20, BTW_21 base 125, gross 172.45.
     *
     * @return array{owner: int, pet: int, event: int, productA: int, productB: int, lot: int, lineA: int, lineB: int}
     */
    public static function standardOwner(float $deadVolume = 0.5, float $lotVolume = 50.0): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $productA = Factory::product(['dead_volume' => $deadVolume]);
        $productB = Factory::product(['btw_sell' => 21]);
        $lot = Factory::stock($productA, ['volume' => $lotVolume]);
        $event = Factory::event($pet, 0, 0);
        $lineA = Factory::eventProduct($event, $productA, 2.0, ['unit_price' => 10.00, 'btw' => 6, 'stock_id' => $lot]);
        $lineB = Factory::eventProduct($event, $productB, 1.0, ['unit_price' => 100.00, 'btw' => 21]);
        Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['unit_price' => 25.00, 'btw' => 21]);

        return compact('owner', 'pet', 'event', 'productA', 'productB', 'lot', 'lineA', 'lineB');
    }

    /** Text of the pdf: pdftotext when installed, otherwise the inflated content streams. */
    public static function pdfText(string $pdf): string
    {
        if (trim((string) shell_exec('command -v pdftotext 2>/dev/null')) !== '') {
            $tmp = tempnam(sys_get_temp_dir(), 'pdf');
            file_put_contents($tmp, $pdf);
            $text = (string) shell_exec('pdftotext -layout ' . escapeshellarg($tmp) . ' - 2>/dev/null');
            @unlink($tmp);

            return $text;
        }
        $text = '';
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdf, $streams);
        foreach ($streams[1] as $stream) {
            $inflated = @gzuncompress($stream);
            if ($inflated === false) {
                continue;
            }
            // every text line is an array of strings: [(Dier : )] TJ
            preg_match_all('/\[(.*?)\]\s*TJ/s', $inflated, $arrays);
            foreach ($arrays[1] as $array) {
                preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $array, $pieces);
                $text .= implode('', $pieces[1]) . "\n";
            }
        }

        return $text;
    }
}
