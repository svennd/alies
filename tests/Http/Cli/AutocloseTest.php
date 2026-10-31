<?php

declare(strict_types=1);

/**
 * T322 - Cli::autoclose.
 *
 * Gates: setting `autoclose` (days) empty or 0 does nothing. Events older than that and not finished
 * (report != 2): with `autodisable` on, an event whose anamnese is '' or equals `autotemplate` gets
 * report 2 + no_history 1 (the anamnese is left alone); every other unfinished event with no_history 0
 * gets report 2 and ' [Auto-Closed]' appended. Events with no_history 1, report 2 or younger than the
 * limit are untouched. A log row `autoclose` ("closed N events") is written when something changed.
 */
final class AutocloseTest extends HttpTestCase
{
    private const TEMPLATE = '<p>tpl</p>';

    private int $pet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pet = Factory::pet(Factory::owner());
        $this->db()->exec("DELETE FROM log WHERE event IN ('autoclose', 'ran_autoclose')");
    }

    private function settings(string $days, string $disable): void
    {
        Factory::setting('autoclose', $days);
        Factory::setting('autodisable', $disable);
        Factory::setting('autotemplate', self::TEMPLATE);
    }

    private function old(array $over = [], int $days = 10): int
    {
        return Factory::event($this->pet, 0, 0, $over + [
            'created_at' => date('Y-m-d H:i:s', strtotime("-$days days")),
        ]);
    }

    public function testEmptyAndTemplateEventsAreHiddenAndRealOnesAreClosedWithAMarker(): void
    {
        $this->settings('7', '1');
        $empty = $this->old(['anamnese' => '']);
        $template = $this->old(['anamnese' => self::TEMPLATE]);
        $real = $this->old(['anamnese' => '<p>Hond kreupel</p>']);
        $recent = Factory::event($this->pet, 0, 0, ['anamnese' => 'nieuw', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))]);
        $done = $this->old(['report' => 2, 'anamnese' => 'klaar']);
        $hidden = $this->old(['no_history' => 1, 'anamnese' => 'verborgen']);

        [$exit] = $this->cli(['cli', 'autoclose']);

        $this->assertSame(0, $exit);
        foreach ([$empty, $template] as $id) {
            $row = $this->find('events', $id);
            $this->assertSame(2, (int) $row['report']);
            $this->assertSame(1, (int) $row['no_history']);
        }
        $this->assertSame('', $this->find('events', $empty)['anamnese']);
        $this->assertSame(self::TEMPLATE, $this->find('events', $template)['anamnese']);

        $row = $this->find('events', $real);
        $this->assertSame(2, (int) $row['report']);
        $this->assertSame(0, (int) $row['no_history']);
        $this->assertSame('<p>Hond kreupel</p> [Auto-Closed]', $row['anamnese']);

        // untouched: too recent, already finished, already hidden
        $row = $this->find('events', $recent);
        $this->assertSame([0, 0, 'nieuw'], [(int) $row['report'], (int) $row['no_history'], $row['anamnese']]);
        $row = $this->find('events', $done);
        $this->assertSame([2, 0, 'klaar'], [(int) $row['report'], (int) $row['no_history'], $row['anamnese']]);
        $row = $this->find('events', $hidden);
        $this->assertSame([0, 1, 'verborgen'], [(int) $row['report'], (int) $row['no_history'], $row['anamnese']]);
    }

    public function testALogRowIsWrittenWithTheNumberOfClosedEvents(): void
    {
        $this->settings('7', '1');
        $this->old(['anamnese' => '']);
        $this->old(['anamnese' => 'tekst']);

        $this->cli(['cli', 'autoclose']);

        $log = $this->row("SELECT * FROM log WHERE event = 'autoclose'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level']);
        // other unfinished old events of the seed may be included, so at least our two
        $this->assertMatchesRegularExpression('/^closed (\d+) events$/', $log['msg']);
        $this->assertGreaterThanOrEqual(2, (int) preg_replace('/\D/', '', $log['msg']));
        $this->assertSame(0, $this->logCount('ran_autoclose'));
    }

    public function testNothingToCloseWritesADebugRow(): void
    {
        $this->settings('7', '1');
        $this->cli(['cli', 'autoclose']);   // closes whatever there is
        $this->db()->exec("DELETE FROM log WHERE event IN ('autoclose', 'ran_autoclose')");

        $this->cli(['cli', 'autoclose']);

        $this->assertSame(0, $this->logCount('autoclose'));
        $log = $this->row("SELECT * FROM log WHERE event = 'ran_autoclose'");
        $this->assertNotNull($log);
        $this->assertSame(5, (int) $log['level']);
        $this->assertSame('no affected', $log['msg']);
    }

    public function testWithAutodisableOffEmptyEventsAreClosedWithTheMarkerToo(): void
    {
        $this->settings('7', '0');
        $empty = $this->old(['anamnese' => '']);

        $this->cli(['cli', 'autoclose']);

        $row = $this->find('events', $empty);
        $this->assertSame(2, (int) $row['report']);
        $this->assertSame(0, (int) $row['no_history']);
        $this->assertSame(' [Auto-Closed]', $row['anamnese']);
    }

    public function testTheLimitIsTheConfiguredNumberOfDays(): void
    {
        $this->settings('20', '0');
        $fifteen = $this->old(['anamnese' => 'a'], 15);
        $twentyFive = $this->old(['anamnese' => 'b'], 25);

        $this->cli(['cli', 'autoclose']);

        $this->assertSame(0, (int) $this->find('events', $fifteen)['report']);
        $this->assertSame(2, (int) $this->find('events', $twentyFive)['report']);
    }

    public function testAnEmptyAutocloseSettingDoesNothing(): void
    {
        $this->settings('', '1');
        $a = $this->old(['anamnese' => '']);
        $b = $this->old(['anamnese' => 'tekst']);

        [$exit] = $this->cli(['cli', 'autoclose']);

        $this->assertSame(0, $exit);
        foreach ([$a, $b] as $id) {
            $this->assertSame(0, (int) $this->find('events', $id)['report']);
        }
        $this->assertSame(0, $this->logCount('autoclose') + $this->logCount('ran_autoclose'));

        // positive control: the same events are closed once the setting has a value
        $this->settings('7', '1');
        $this->cli(['cli', 'autoclose']);
        $this->assertSame(2, (int) $this->find('events', $b)['report']);
    }

    public function testZeroDaysDoesNothingToo(): void
    {
        $this->settings('0', '1');
        $a = $this->old(['anamnese' => 'tekst']);

        $this->cli(['cli', 'autoclose']);

        $this->assertSame(0, (int) $this->find('events', $a)['report']);
        $this->assertSame(0, $this->logCount('ran_autoclose'));
    }
}
