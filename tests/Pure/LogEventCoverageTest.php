<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * T348 - which log events does the application write that no test mentions?
 *
 * The application writes an audit trail through Logs_model::logger($level, 'event', ...) and
 * Logs_model::stock($level, 'event', ...). Every event name is a small promise ("when X happens, a row
 * 'x' is written"). This test scans the application for the literal event names and the tests for any
 * mention of them, and compares the untested ones with tests/Fixtures/log_events_untested.txt.
 *
 *  - a NEW untested event (not in the baseline) fails: add a test that asserts the log row, or, if it
 *    really cannot be tested, add the name to the baseline with a reason in the commit message;
 *  - an event in the baseline that a test mentions now fails too: remove it from the baseline, so the
 *    list only ever shrinks.
 *
 * Events built from variables or concatenated strings are not found (a known blind spot).
 * Uses the CodeIgniter 3 folder layout; for the CI4 port point $directory at the new source folders.
 */
final class LogEventCoverageTest extends TestCase
{
    private const BASELINE = __DIR__ . '/../Fixtures/log_events_untested.txt';

    /** @return array<string, list<string>> event name => files that write it */
    private function eventsInTheApplication(): array
    {
        $root = dirname(__DIR__, 2) . '/application';
        $events = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/migrations/') || str_contains($file->getPathname(), '/language/')) {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            // ->logger(INFO, "event", ...)   ->stock(DEBUG, 'event', ...)
            if (preg_match_all('/->(?:logger|stock)\(\s*(?:FATAL|ERROR|WARN|INFO|DEBUG)\s*,\s*(["\'])([A-Za-z0-9_ ]+)\1/', $source, $m)) {
                foreach ($m[2] as $event) {
                    $events[$event][] = str_replace($root . '/', '', $file->getPathname());
                }
            }
        }
        ksort($events);

        return $events;
    }

    private function mentionedInTests(string $event): bool
    {
        static $corpus = null;
        if ($corpus === null) {
            $corpus = '';
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/..', FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                $path = $file->getPathname();
                if ($file->getExtension() === 'php' && $path !== __FILE__) {
                    $corpus .= (string) file_get_contents($path) . "\n";
                }
            }
        }

        return str_contains($corpus, "'" . $event . "'") || str_contains($corpus, '"' . $event . '"');
    }

    /** @return list<string> */
    private function baseline(): array
    {
        $lines = file(self::BASELINE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $l): bool => $l !== '' && $l[0] !== '#'));
    }

    public function testTheApplicationWritesLogEventsAndTheScannerFindsThem(): void
    {
        $events = $this->eventsInTheApplication();

        // sanity of the scanner itself: events that are known to exist
        foreach (['login_success', 'add_client', 'new_event', 'write_off', 'update_buy_price'] as $known) {
            $this->assertArrayHasKey($known, $events, $known);
        }
        $this->assertGreaterThan(50, count($events));
    }

    public function testNoNewLogEventIsWrittenWithoutATestThatMentionsIt(): void
    {
        $untested = [];
        foreach (array_keys($this->eventsInTheApplication()) as $event) {
            if (!$this->mentionedInTests($event)) {
                $untested[] = $event;
            }
        }

        $new = array_values(array_diff($untested, $this->baseline()));

        $this->assertSame([], $new, "log events without any test (add a test that checks the log row, or add them to tests/Fixtures/log_events_untested.txt):\n  " . implode("\n  ", $new));
    }

    public function testTheBaselineOnlyContainsEventsThatAreStillUntested(): void
    {
        $events = $this->eventsInTheApplication();
        $stale = [];
        foreach ($this->baseline() as $event) {
            if (!isset($events[$event]) || $this->mentionedInTests($event)) {
                $stale[] = $event;
            }
        }

        $this->assertSame([], $stale, "remove these from tests/Fixtures/log_events_untested.txt (now tested, or no longer in the code):\n  " . implode("\n  ", $stale));
    }
}
