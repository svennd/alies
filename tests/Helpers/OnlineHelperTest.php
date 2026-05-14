<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OnlineHelperTest extends TestCase
{
    public function testRecentLoginsAreOnline(): void
    {
        $this->assertSame('online', get_online_status(time() - 60));
    }

    public function testLoginsWithinSevenDaysAreAway(): void
    {
        $this->assertSame('away', get_online_status(time() - (4 * 60 * 60)));
    }

    public function testOlderLoginsAreOffline(): void
    {
        $this->assertSame('offline', get_online_status(time() - (8 * 24 * 60 * 60)));
    }
}
