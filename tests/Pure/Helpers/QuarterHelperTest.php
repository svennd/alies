<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/quarter_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * T010 - application/helpers/quarter_helper.php (used by the Vamreg screens).
 *
 * Q1 = Jan-Mar, Q2 = Apr-Jun, Q3 = Jul-Sep, Q4 = Oct-Dec. The two "exact day" helpers and
 * is_current_quarter() read the real clock, so their expectations are derived from date() and an
 * explicit table of quarter end dates, never from the helper's own logic.
 */
final class QuarterHelperTest extends TestCase
{
    /** @return array<string, array{string, int}> */
    public static function dates(): array
    {
        return [
            'first day of the year' => ['2024-01-01', 1],
            'last day of march' => ['2024-03-31', 1],
            'first day of april' => ['2024-04-01', 2],
            'last day of june' => ['2024-06-30', 2],
            'first day of july' => ['2024-07-01', 3],
            'last day of september' => ['2024-09-30', 3],
            'first day of october' => ['2024-10-01', 4],
            'last day of the year' => ['2024-12-31', 4],
            'with a time' => ['2024-05-15 23:59:59', 2],
            // strtotime() fails, the helper then reads timestamp 0 = 1 january 1970
            'garbage' => ['garbage', 1],
        ];
    }

    #[DataProvider('dates')]
    public function testQuarterFromDate(string $date, int $expected): void
    {
        $this->assertSame($expected, quarter_from_date($date));
    }

    public function testQuarterFromDateWithoutDateIsTheCurrentQuarter(): void
    {
        $expected = [1 => 1, 2 => 1, 3 => 1, 4 => 2, 5 => 2, 6 => 2, 7 => 3, 8 => 3, 9 => 3, 10 => 4, 11 => 4, 12 => 4];

        $this->assertSame($expected[(int) date('n')], quarter_from_date());
        $this->assertSame($expected[(int) date('n')], quarter_from_date(null));
    }

    /** @return array<string, array{int, int, string, string}> */
    public static function quarters(): array
    {
        return [
            '2024 Q1' => [2024, 1, '2024-01-01', '2024-03-31'],
            '2024 Q2' => [2024, 2, '2024-04-01', '2024-06-30'],
            '2024 Q3' => [2024, 3, '2024-07-01', '2024-09-30'],
            '2024 Q4' => [2024, 4, '2024-10-01', '2024-12-31'],
            '2023 Q1' => [2023, 1, '2023-01-01', '2023-03-31'],
            '1999 Q4' => [1999, 4, '1999-10-01', '1999-12-31'],
        ];
    }

    #[DataProvider('quarters')]
    public function testQuarterStartAndEnd(int $year, int $quarter, string $start, string $end): void
    {
        $this->assertSame([$start, $end], quarter_start_end($year, $quarter));
    }

    #[Group('known-bug')]
    public function testQuarterStartEndForAQuarterThatDoesNotExistGivesNonsense(): void
    {
        // KNOWN BUG: no validation - quarter 5 is month 13, strtotime() fails and the end becomes 1970
        $this->assertSame(['2024-13-01', '1970-01-31'], quarter_start_end(2024, 5));
    }

    /** @return array<string, array{int, int, array{int, int}}> */
    public static function previous(): array
    {
        return [
            'within the year' => [2024, 3, [2024, 2]],
            'q2' => [2024, 2, [2024, 1]],
            'q4' => [2024, 4, [2024, 3]],
            'over the year boundary' => [2024, 1, [2023, 4]],
        ];
    }

    #[DataProvider('previous')]
    public function testQuarterPrev(int $year, int $quarter, array $expected): void
    {
        $this->assertSame($expected, quarter_prev($year, $quarter));
    }

    /** @return array<string, array{int, int, array{int, int}}> */
    public static function next(): array
    {
        return [
            'within the year' => [2024, 1, [2024, 2]],
            'q3' => [2024, 3, [2024, 4]],
            'over the year boundary' => [2024, 4, [2025, 1]],
        ];
    }

    #[DataProvider('next')]
    public function testQuarterNext(int $year, int $quarter, array $expected): void
    {
        $this->assertSame($expected, quarter_next($year, $quarter));
    }

    public function testPrevAndNextDoNotValidateTheirInput(): void
    {
        $this->assertSame([2023, 4], quarter_prev(2024, 0));
        $this->assertSame([2025, 1], quarter_next(2024, 7));
    }

    public function testPrevAndNextAreEachOthersInverse(): void
    {
        foreach ([[2024, 1], [2024, 2], [2024, 3], [2024, 4]] as [$year, $quarter]) {
            $this->assertSame([$year, $quarter], quarter_next(...quarter_prev($year, $quarter)));
            $this->assertSame([$year, $quarter], quarter_prev(...quarter_next($year, $quarter)));
        }
    }

    public function testIsCurrentQuarter(): void
    {
        $year = (int) date('Y');
        $current = quarter_from_date();
        $other = $current === 4 ? 1 : $current + 1;

        $this->assertTrue(is_current_quarter($year, $current));
        $this->assertFalse(is_current_quarter($year, $other));
        $this->assertFalse(is_current_quarter($year - 1, $current));
        $this->assertFalse(is_current_quarter($year + 1, $current));
    }

    /** Days between today and the last day of the current quarter (0 on that last day). */
    private function daysUntilQuarterEnd(): int
    {
        $endOfQuarter = [3 => '03-31', 6 => '06-30', 9 => '09-30', 12 => '12-31'];
        $month = (int) ceil((int) date('n') / 3) * 3;
        $end = strtotime(date('Y') . '-' . $endOfQuarter[$month]);

        return (int) round(($end - strtotime(date('Y-m-d'))) / 86400);
    }

    public function testQuarterEndTriggerDayIsTheGivenNumberOfDaysBeforeTheEndOfTheQuarter(): void
    {
        $days = $this->daysUntilQuarterEnd();

        $this->assertTrue(is_exact_quarter_end_trigger_day($days));
        $this->assertFalse(is_exact_quarter_end_trigger_day($days + 1));
        if ($days > 0) {
            $this->assertFalse(is_exact_quarter_end_trigger_day($days - 1));
        }
    }

    #[Group('known-bug')]
    public function testPostQuarterDayIsNeverTrue(): void
    {
        // KNOWN BUG: it adds the days to the end of the CURRENT quarter, which is never in the past
        // (and unused). Today is on or before that end, so for 1 or more days it cannot match.
        foreach ([1, 7, 30, 100, 365] as $daysAfter) {
            $this->assertFalse(is_exact_post_quarter_day($daysAfter), "daysAfter $daysAfter");
        }
    }
}
