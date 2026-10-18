<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

/**
 * Helpers shared by the tests in tests/Http/Reports: read an xlsx download with PhpSpreadsheet
 * (only used as a reader) and pick the table rows out of an HTML report.
 */
final class ReportsHelper
{
    /**
     * @return list<list<mixed>> all rows of the first sheet, header row first
     */
    public static function xlsxRows(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        if (substr($body, 0, 2) !== 'PK') {
            throw new RuntimeException('Not a ZIP/xlsx body: ' . substr($body, 0, 100));
        }
        $file = tempnam(sys_get_temp_dir(), 'rep') . '.xlsx';
        file_put_contents($file, $body);
        try {
            $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet();

            return $sheet->toArray(null, false, false, false);
        } finally {
            @unlink($file);
        }
    }

    /**
     * Text of the cells of every table row (td/th) of an HTML page, whitespace collapsed.
     *
     * @return list<list<string>>
     */
    public static function tableRows(ResponseInterface $response): array
    {
        $html = (string) $response->getBody();
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        $rows = [];
        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $cells = [];
            foreach ($tr->childNodes as $cell) {
                if ($cell instanceof DOMElement && in_array($cell->tagName, ['td', 'th'], true)) {
                    $cells[] = trim((string) preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $cell->textContent)));
                }
            }
            if ($cells !== []) {
                $rows[] = $cells;
            }
        }

        return $rows;
    }

    /**
     * The table rows whose cells contain $needle in the first cell.
     *
     * @return list<list<string>>
     */
    public static function rowsStartingWith(ResponseInterface $response, string $needle): array
    {
        return array_values(array_filter(self::tableRows($response), static fn (array $r): bool => str_contains($r[0], $needle)));
    }
}
