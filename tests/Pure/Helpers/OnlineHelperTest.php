<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/online_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T008 - application/helpers/online_helper.php: get_online_status().
 *
 * online  = last login less than 3 hours ago (also a timestamp in the future)
 * away    = less than 7 days ago
 * offline = anything older
 *
 * The clock cannot be frozen. The helper reads time() after the test computed its timestamp, so the
 * measured age can only be equal or a little larger. Cases "just below" a limit therefore keep a
 * safety margin of a minute; cases exactly on a limit are safe as they are.
 */
final class OnlineHelperTest extends TestCase
{
    private const THREE_HOURS = 3 * 60 * 60;
    private const SEVEN_DAYS = 7 * 24 * 60 * 60;

    /** @return array<string, array{int, string}> age in seconds => status */
    public static function ages(): array
    {
        return [
            'just now' => [0, 'online'],
            'a minute ago' => [60, 'online'],
            'a minute short of three hours' => [self::THREE_HOURS - 60, 'online'],
            'exactly three hours' => [self::THREE_HOURS, 'away'],
            'four hours' => [4 * 60 * 60, 'away'],
            'a minute short of seven days' => [self::SEVEN_DAYS - 60, 'away'],
            'exactly seven days' => [self::SEVEN_DAYS, 'offline'],
            'eight days' => [8 * 24 * 60 * 60, 'offline'],
            'a year' => [365 * 24 * 60 * 60, 'offline'],
        ];
    }

    #[DataProvider('ages')]
    public function testStatusForTheAgeOfTheLastLogin(int $ageInSeconds, string $expected): void
    {
        $this->assertSame($expected, get_online_status(time() - $ageInSeconds));
    }

    public function testATimestampInTheFutureIsOnline(): void
    {
        $this->assertSame('online', get_online_status(time() + 3600));
    }

    public function testTimestampZeroMeansNeverLoggedInAndIsOffline(): void
    {
        $this->assertSame('offline', get_online_status(0));
    }
}
