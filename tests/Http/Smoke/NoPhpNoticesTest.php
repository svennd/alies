<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * T346 - the main pages produce no PHP warnings/notices/deprecations.
 *
 * LIMITATION: the testing environment of the application hides PHP errors (index.php: display_errors 0 and
 * E_NOTICE/E_DEPRECATED are not even reported), so a notice can neither be seen in the body nor reach the log.
 * What can be checked:
 *  1. the body has no "Severity: ..." block and no error page (catches display_errors turned on);
 *  2. the CodeIgniter log file (application/logs/log-<date>.php, written for every PHP warning/error that is
 *     reported) gets no new "Severity: Warning|Error|Notice|Deprecated" line from the request.
 * The log is shared with other test runs working at the same time, so a line only counts when it is
 * produced by both of two identical requests (a real offender repeats, someone else's warning does not).
 * Pages that offend today are in KNOWN_OFFENDERS, with the file and reason; the test checks they still do.
 */
final class NoPhpNoticesTest extends HttpTestCase
{
    /** page => why it is listed. */
    private const KNOWN_OFFENDERS = [
        // KNOWN BUG: views/bills/report_print.php uses $str_invoice_or_bill and $fact_invoice_or_bill that the controller never sets (Warning: Undefined variable)
        'invoice/get_bill/7001' => 'undefined variables in bills/report_print.php',
    ];

    private const PATTERN = '/Severity: (Notice|Warning|Deprecated|Error|Parse error)/';

    /** @return array<string, array{string}> */
    public static function pages(): array
    {
        $pages = [
            '', 'owners/detail/1001', 'owners/edit/1001', 'owners/add', 'pets/fiche/2001', 'pets/edit/2001', 'pets/add/1001',
            'events/event/6001', 'events/event/6002', 'products', 'products/profile/4001', 'invoice',
            'invoice/get_bill/7001', 'lab', 'lab/detail/10001', 'vaccine', 'stock/stock_detail/4002', 'reports/revenue',
            'reports/stock_list', 'stats', 'sticky', 'wholesale', 'admin/settings', 'member',
        ];

        return array_combine(array_map(static fn (string $p): string => $p === '' ? '(home)' : $p, $pages), array_map(static fn (string $p): array => [$p], $pages));
    }

    private function logFile(): string
    {
        return dirname(__DIR__, 3) . '/application/logs/log-' . date('Y-m-d') . '.php';
    }

    /** @return list<string> the warning lines added to the log while $fn runs, without the timestamp */
    private function newLogLines(callable $fn): array
    {
        clearstatcache();
        $file = $this->logFile();
        $offset = is_file($file) ? filesize($file) : 0;
        $fn();
        clearstatcache();
        if (!is_file($file) || filesize($file) <= $offset) {
            return [];
        }
        $handle = fopen($file, 'rb');
        fseek($handle, $offset);
        $added = (string) stream_get_contents($handle);
        fclose($handle);

        $lines = [];
        foreach (explode("\n", $added) as $line) {
            if (preg_match(self::PATTERN, $line)) {
                $lines[] = preg_replace('/^\w+ - [\d-]+ [\d:]+ --> /', '', $line);
            }
        }

        return $lines;
    }

    /** @return list<string> */
    private function offences(string $page): array
    {
        $body = '';
        $first = $this->newLogLines(function () use ($page, &$body): void {
            $body = (string) $this->get($page)->getBody();
        });
        $second = $this->newLogLines(function () use ($page): void {
            $this->get($page);
        });

        $offences = array_values(array_unique(array_intersect($first, $second)));
        if (preg_match(self::PATTERN, $body) || str_contains($body, 'A PHP Error was encountered')) {
            $offences[] = 'displayed in the page';
        }

        return $offences;
    }

    #[DataProvider('pages')]
    public function testThePageRaisesNoPhpWarnings(string $page): void
    {
        $this->loginAs('admin');
        $offences = $this->offences($page);

        if (isset(self::KNOWN_OFFENDERS[$page])) {
            $this->assertNotSame([], $offences, $page . ' is listed (' . self::KNOWN_OFFENDERS[$page] . ') but is clean now');

            return;
        }
        $this->assertSame([], $offences, $page . ': ' . implode(' | ', $offences));
    }

    public function testTheLogCheckNamesTheUndefinedVariableOfTheBillView(): void
    {
        $this->loginAs('admin');
        $offences = $this->offences('invoice/get_bill/7001');

        $this->assertNotSame([], $offences);
        $this->assertStringContainsString('Undefined variable $str_invoice_or_bill', implode("\n", $offences));
    }
}
