<?php

declare(strict_types=1);

/**
 * T291 - the deadline block of the Vamreg dashboard (Vamreg::buildSendDeadlineData, shown by
 * vamreg/index/{year}/{quarter}).
 *
 * Rules, with E = last day of the quarter at 23:59:59 and N = setting `vamreg_auto_send_months`
 * (1 to 6, anything else means 1):
 *  - auto send  = E + N months, at 02:00;
 *  - window end = E + 1 month + 14 days, at 23:59:59;
 *  - label: now <= E "Quarter open"; E < now < auto send "Edit window"; auto send <= now <= window
 *    end "Auto-send due"; later "Edit window closed".
 * "+ N month" is PHP month arithmetic: 31 March + 1 month is 31 April, which PHP turns into 1 May.
 * The page uses the real clock, so the labels are checked for quarters far in the past and the
 * future, and for the current and previous quarter relative to now; the dates are fixed.
 */
final class DeadlineBannerTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
        Factory::setting('vamreg_api_key', 'test-key');
        Factory::setting('vamreg_auto_send_months', '1');
    }

    private function plain(string $url): string
    {
        $response = $this->get($url);
        $this->assertStatus($response, 200, $url);

        return trim(preg_replace('/\s+/', ' ', strip_tags($this->text($response))));
    }

    public function testTheCurrentQuarterIsOpen(): void
    {
        $quarter = (int) ceil(((int) date('n')) / 3);

        $page = $this->plain('vamreg/index/' . date('Y') . '/' . $quarter);

        $this->assertStringContainsString('Quarter open', $page);
        $this->assertStringContainsString('quarter is still open', $page);
        $this->assertStringNotContainsString('Edit window closed', $page);
    }

    public function testAFutureQuarterIsOpen(): void
    {
        $page = $this->plain('vamreg/index/2040/1');

        $this->assertStringContainsString('Quarter open', $page);
        $this->assertStringContainsString('2040-03-31 Quarter closes', $page);
    }

    public function testAQuarterThatEndedMoreThanTwoMonthsAgoIsClosed(): void
    {
        // 2020 is long gone, and so is the quarter before the previous one (it ended at least 3 months ago)
        $page = $this->plain('vamreg/index/2020/3');
        $this->assertStringContainsString('Edit window closed', $page);
        $this->assertStringContainsString('edit window is closed for this quarter', $page);
        $this->assertStringNotContainsString('Quarter open', $page);

        $quarter = (int) ceil(((int) date('n')) / 3);
        $year = (int) date('Y');
        $quarter -= 2;
        if ($quarter < 1) {
            $quarter += 4;
            $year--;
        }
        $this->assertStringContainsString('Edit window closed', $this->plain("vamreg/index/$year/$quarter"));
    }

    public function testThePreviousQuarterIsInOneOfTheStatesAfterTheEnd(): void
    {
        $quarter = (int) ceil(((int) date('n')) / 3);
        $year = (int) date('Y');
        if (--$quarter < 1) {
            $quarter = 4;
            $year--;
        }
        // expected state from the number of days since the quarter ended (E + 1 month is 28-31 days later
        // and E + 1 month + 14 days is 42-45 days later; inside those ranges either label is possible)
        $end = new DateTimeImmutable(date('Y-m-t', strtotime(sprintf('%04d-%02d-01 +2 months', $year, $quarter * 3 - 2))) . ' 23:59:59');
        $days = (int) $end->diff(new DateTimeImmutable('now'))->format('%a');

        $page = $this->plain("vamreg/index/$year/$quarter");

        $this->assertStringNotContainsString('Quarter open', $page);
        if ($days < 28) {
            $this->assertStringContainsString('edit window active', $page, "$days days after the quarter end");
        } elseif ($days > 45) {
            $this->assertStringContainsString('edit window is closed', $page, "$days days after the quarter end");
        } else {
            $this->assertMatchesRegularExpression('/edit window active|auto-send point reached|edit window is closed/', $page);
        }
    }

    public function testTheAutoSendDateOfQ1IsTheFirstOfMayBecauseOfMonthOverflow(): void
    {
        // KNOWN QUIRK: 31 March + 1 month = 31 April = 1 May; the same sum for Q2 (30 June) gives 30 July.
        $q1 = $this->plain('vamreg/index/2026/1');

        $this->assertStringContainsString('2026-03-31 Quarter closes', $q1);
        $this->assertStringContainsString('2026-05-01 Auto send', $q1);
        // window end: 31 March + 1 month = 1 May, + 14 days = 15 May
        $this->assertStringContainsString('2026-05-15 Edit window closes', $q1);
        // no overflow for a 30-day quarter end: 30 June + 1 month = 30 July, window end 30 July + 14 days = 13 August
        $q2 = $this->plain('vamreg/index/2026/2');
        $this->assertStringContainsString('2026-07-30 Auto send', $q2);
        $this->assertStringContainsString('2026-08-13 Edit window closes', $q2);
    }

    public function testTheAutoSendMonthsSettingMovesTheAutoSendDateOnly(): void
    {
        // N = 3: 31 March + 3 months = 31 June = 1 July
        Factory::setting('vamreg_auto_send_months', '3');
        $page = $this->plain('vamreg/index/2026/1');

        $this->assertStringContainsString('2026-07-01 Auto send', $page);
        $this->assertStringContainsString('Quarter end + 3 month(s)', $page);
        $this->assertStringContainsString('2026-05-15 Edit window closes', $page, 'the window end does not depend on the setting');

        // N = 2 on Q4: 31 December 2026 + 2 months = 31 February 2027 = 3 March 2027 (2027 is not a leap year)
        Factory::setting('vamreg_auto_send_months', '2');
        $this->assertStringContainsString('2027-03-03 Auto send', $this->plain('vamreg/index/2026/4'));
    }

    public function testASettingOutsideOneToSixFallsBackToOne(): void
    {
        foreach (['0', '7', '-2', 'abc', ''] as $bad) {
            Factory::setting('vamreg_auto_send_months', $bad);
            $page = $this->plain('vamreg/index/2026/1');

            $this->assertStringContainsString('2026-05-01 Auto send', $page, "setting '$bad'");
            $this->assertStringContainsString('Quarter end + 1 month(s)', $page, "setting '$bad'");
        }
        // positive control: 6 is the largest accepted value (31 March + 6 months = 31 September = 1 October)
        Factory::setting('vamreg_auto_send_months', '6');
        $this->assertStringContainsString('2026-10-01 Auto send', $this->plain('vamreg/index/2026/1'));
    }
}
