<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/file_download_helper.php';

use PHPUnit\Framework\TestCase;

/**
 * T013 - application/helpers/file_download_helper.php: array_to_csv() and array_to_csv_download().
 *
 * Both send a download header and then write to php://output; the output is captured with ob_start().
 * The headers (Content-Type: application/csv, Content-Disposition: attachment; filename="...") cannot
 * be inspected from the command line, so only the body is checked here; the headers are covered by
 * the HTTP tests of the screens that use the helpers (stock list, reports, vaccine export).
 */
final class FileDownloadHelperTest extends TestCase
{
    private function capture(callable $call): string
    {
        ob_start();
        try {
            $call();
        } finally {
            $output = (string) ob_get_clean();
        }

        return $output;
    }

    public function testArrayToCsvWritesOneCommaSeparatedLinePerRow(): void
    {
        $output = $this->capture(fn () => array_to_csv([['name', 'volume'], ['aspirin', 2], ['ibuprofen', 3.5]], 'stock.csv'));

        $this->assertSame("name,volume\naspirin,2\nibuprofen,3.5\n", $output);
    }

    public function testArrayToCsvDoesNotQuoteOrEscapeAnything(): void
    {
        // a comma or a quote inside a value breaks the columns: the helper just implodes
        $output = $this->capture(fn () => array_to_csv([['Janssens, Jan', 'say "hi"', "two\nlines"]]));

        $this->assertSame("Janssens, Jan,say \"hi\",two\nlines\n", $output);
        // one data row, but the newline inside a value makes it two lines for a CSV reader
        $this->assertCount(2, explode("\n", rtrim($output, "\n")));
    }

    public function testArrayToCsvOfNothingWritesNothing(): void
    {
        $this->assertSame('', $this->capture(fn () => array_to_csv([])));
    }

    public function testArrayToCsvWritesNullAsAnEmptyColumn(): void
    {
        $this->assertSame("a,,c\n", $this->capture(fn () => array_to_csv([['a', null, 'c']])));
    }

    public function testDownloadOfAReadyMadeCsvStringEndsEveryLineWithANewline(): void
    {
        $output = $this->capture(fn () => array_to_csv_download("a,b\nc,d", 'list.csv'));

        $this->assertSame("a,b\nc,d\n", $output);
    }

    public function testDownloadOfAStringThatAlreadyEndsWithANewlineGetsOneBlankLineExtra(): void
    {
        // quirk: the string is split on "\n" and every part, also the empty one after the last
        // newline, is written with a newline of its own
        $output = $this->capture(fn () => array_to_csv_download("a,b\nc,d\n", 'list.csv'));

        $this->assertSame("a,b\nc,d\n\n", $output);
    }

    public function testDownloadOfAnEmptyStringWritesASingleNewline(): void
    {
        $this->assertSame("\n", $this->capture(fn () => array_to_csv_download('', 'empty.csv')));
    }
}
