<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * T007 (second half) - time_ago() with CodeIgniter's real timespan(), which needs the framework
 * for its language lines. The wiring with a stand-in is tested in tests/Pure/Helpers/UserDateHelperTest.php.
 * The text is language dependent, so only the shape is asserted: one unit, starting with the amount.
 */
final class TimeAgoTest extends TestCase
{
    public function testTwoHoursAgoIsDescribedWithOneUnit(): void
    {
        $text = time_ago(date('Y-m-d H:i:s', time() - 2 * 3600 - 30));

        $this->assertMatchesRegularExpression('/^2 \S+$/', $text);
    }

    public function testThreeDaysAgoIsDescribedInDays(): void
    {
        $text = time_ago(date('Y-m-d H:i:s', time() - 3 * 86400 - 30));

        $this->assertMatchesRegularExpression('/^3 \S+$/', $text);
    }
}
