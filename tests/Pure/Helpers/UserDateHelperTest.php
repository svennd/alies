<?php

declare(strict_types=1);

/*
 * time_ago() calls CodeIgniter's timespan(), which needs the framework (language files). Here it is
 * replaced by a recording stand-in; the real one is exercised by tests/Models/TimeAgoTest.php.
 */
if (!function_exists('timespan')) {
    define('USER_DATE_TEST_TIMESPAN_STUB', true);

    function timespan(int $seconds, int $time, int $units): string
    {
        $GLOBALS['timespan_calls'][] = [$seconds, $time, $units];

        return 'timespan-result';
    }
}

require_once APPPATH . 'helpers/user_date_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T007 - application/helpers/user_date_helper.php: user_format_date() and time_ago().
 */
final class UserDateHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['timespan_calls'] = [];
    }

    /** @return array<string, array{?string, string, string}> */
    public static function formatCases(): array
    {
        return [
            'datetime with the user format' => ['2024-03-05 10:00:00', 'd/m/Y', '05/03/2024'],
            'date only' => ['2024-03-05', 'd-m-Y', '05-03-2024'],
            'other format' => ['2024-03-05 10:00:00', 'Y.m.d H:i', '2024.03.05 10:00'],
            'null' => [null, 'd/m/Y', '---'],
            'empty string' => ['', 'd/m/Y', '---'],
            'mysql zero date' => ['0000-00-00', 'd/m/Y', '---'],
        ];
    }

    #[DataProvider('formatCases')]
    public function testUserFormatDate(?string $datetime, string $format, string $expected): void
    {
        $this->assertSame($expected, user_format_date($datetime, $format));
    }

    public function testMysqlZeroDatetimeIsNotRecognisedAndGivesAnImpossibleDate(): void
    {
        // quirk: only the zero DATE is special cased, the zero DATETIME becomes day 0 of year 0
        $this->assertSame('30/11/-0001', user_format_date('0000-00-00 00:00:00', 'd/m/Y'));
    }

    public function testUnparsableDateIsATypeError(): void
    {
        // date_create() returns false and date_format() refuses it
        $this->expectException(TypeError::class);

        user_format_date('not a date', 'd/m/Y');
    }

    public function testTimeAgoWithoutDateIsNever(): void
    {
        $this->assertSame('never', time_ago(null));
        $this->assertSame('never', time_ago());
    }

    public function testTimeAgoInTheFutureIsBadTime(): void
    {
        $this->assertSame('bad time', time_ago(date('Y-m-d H:i:s', time() + 3600)));
    }

    public function testTimeAgoDelegatesToTimespanWithOneUnit(): void
    {
        if (!defined('USER_DATE_TEST_TIMESPAN_STUB')) {
            $this->markTestSkipped('CodeIgniter is loaded; the real timespan() is tested in tests/Models/TimeAgoTest.php');
        }

        $input = date('Y-m-d H:i:s', time() - 7200);
        $before = time();
        $result = time_ago($input);
        $after = time();

        $this->assertSame('timespan-result', $result);
        $this->assertCount(1, $GLOBALS['timespan_calls']);
        [$seconds, $now, $units] = $GLOBALS['timespan_calls'][0];
        $this->assertSame(strtotime($input), $seconds);
        $this->assertGreaterThanOrEqual($before, $now);
        $this->assertLessThanOrEqual($after, $now);
        $this->assertSame(1, $units);
    }
}
