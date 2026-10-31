<?php

declare(strict_types=1);

/**
 * T324 - Cli::auto_death (Pets_model::auto_death).
 *
 * Gate: setting `autdeath` truthy. For each type DOG 0, CAT 1, HORSE 2, BIRD 3, OTHER 4, RABBIT 5 with
 * setting `auto_dead_<type>` > 0: living pets of that type whose age in whole years is MORE than the
 * setting and that have no event in the last 2 years get death = 1, death_date = today - 14 days and
 * ' [auto-death]' appended to the note. A log row `auto_death` "killed: N of type: T" is written per
 * type that killed something.
 */
final class AutoDeathTest extends HttpTestCase
{
    private int $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = Factory::owner();
        $this->db()->exec("DELETE FROM log WHERE event = 'auto_death'");
    }

    private function settings(string $gate, array $years): void
    {
        Factory::setting('autdeath', $gate);
        for ($type = 0; $type <= 5; $type++) {
            Factory::setting('auto_dead_' . $type, (string) ($years[$type] ?? 0));
        }
    }

    private function pet(int $type, int $ageYears, array $over = []): int
    {
        return Factory::pet($this->owner, $over + [
            'type' => $type, 'note' => '',
            'birth' => date('Y-m-d', strtotime("-$ageYears years -2 days")),
        ]);
    }

    public function testOldPetsWithoutRecentEventsOfAConfiguredTypeAreMarkedDead(): void
    {
        $this->settings('1', [0 => 15]);
        $old = $this->pet(0, 20);
        $young = $this->pet(0, 10);
        $exactly = $this->pet(0, 15);      // 15 whole years: not more than 15
        $oneOver = $this->pet(0, 16);
        $busy = $this->pet(0, 20);
        Factory::event($busy, 0, 0, ['created_at' => date('Y-m-d H:i:s', strtotime('-1 year'))]);
        $quiet = $this->pet(0, 20);
        Factory::event($quiet, 0, 0, ['created_at' => date('Y-m-d H:i:s', strtotime('-3 years'))]);
        $alreadyDead = $this->pet(0, 20, ['death' => 1, 'death_date' => '2020-01-01', 'note' => 'x']);

        [$exit] = $this->cli(['cli', 'auto_death']);

        $this->assertSame(0, $exit);
        foreach ([$old, $oneOver, $quiet] as $id) {
            $row = $this->find('pets', $id);
            $this->assertSame(1, (int) $row['death'], "pet $id");
            $this->assertSame(date('Y-m-d', strtotime('-14 days')), $row['death_date']);
            $this->assertSame(' [auto-death]', $row['note']);
        }
        $this->assertSame(0, (int) $this->find('pets', $young)['death']);
        $this->assertSame(0, (int) $this->find('pets', $busy)['death'], 'a recent event keeps the pet alive');
        $this->assertSame('2020-01-01', $this->find('pets', $alreadyDead)['death_date'], 'dead pets are not touched');
        $this->assertSame('x', $this->find('pets', $alreadyDead)['note']);
        // age 15 years + 2 days is 15 whole years: not more than 15
        $this->assertSame(0, (int) $this->find('pets', $exactly)['death']);

        $log = $this->row("SELECT * FROM log WHERE event = 'auto_death'");
        $this->assertNotNull($log);
        $this->assertSame(4, (int) $log['level']);
        $this->assertMatchesRegularExpression('/^killed: [3-9]\d* of type: 0$/', $log['msg']);
    }

    public function testTypesWithASettingOfZeroAreSkipped(): void
    {
        $this->settings('1', [0 => 15, 1 => 0, 2 => 0]);
        $dog = $this->pet(0, 20);
        $cat = $this->pet(1, 30);
        $horse = $this->pet(2, 40);

        $this->cli(['cli', 'auto_death']);

        $this->assertSame(1, (int) $this->find('pets', $dog)['death']);
        $this->assertSame(0, (int) $this->find('pets', $cat)['death']);
        $this->assertSame(0, (int) $this->find('pets', $horse)['death']);
        $this->assertSame(0, $this->countWhere('log', ['event' => 'auto_death', 'msg' => 'killed: 1 of type: 1']));
        $this->assertSame(0, (int) $this->scalar("SELECT COUNT(*) FROM log WHERE event = 'auto_death' AND msg LIKE '% of type: 1'"));
    }

    public function testEachTypeUsesItsOwnLimitAndLogRow(): void
    {
        $this->settings('1', [1 => 10, 5 => 8]);
        $cat = $this->pet(1, 12);
        $youngCat = $this->pet(1, 9);
        $rabbit = $this->pet(5, 9);
        $dog = $this->pet(0, 30);

        $this->cli(['cli', 'auto_death']);

        $this->assertSame(1, (int) $this->find('pets', $cat)['death']);
        $this->assertSame(0, (int) $this->find('pets', $youngCat)['death']);
        $this->assertSame(1, (int) $this->find('pets', $rabbit)['death']);
        $this->assertSame(0, (int) $this->find('pets', $dog)['death'], 'dogs have no limit here');
        $messages = array_column($this->rows("SELECT msg FROM log WHERE event = 'auto_death'"), 'msg');
        // seeded pets of these types may be killed as well, so only the shape of the rows is pinned
        $this->assertCount(2, $messages);
        $all = implode("\n", $messages);
        $this->assertMatchesRegularExpression('/^killed: [1-9]\d* of type: 1$/m', $all);
        $this->assertMatchesRegularExpression('/^killed: [1-9]\d* of type: 5$/m', $all);
    }

    public function testTheGateSettingTurnsEverythingOff(): void
    {
        $this->settings('0', [0 => 15]);
        $dog = $this->pet(0, 20);

        [$exit] = $this->cli(['cli', 'auto_death']);

        $this->assertSame(0, $exit);
        $this->assertSame(0, (int) $this->find('pets', $dog)['death']);
        $this->assertSame(0, $this->logCount('auto_death'));

        // positive control
        $this->settings('1', [0 => 15]);
        $this->cli(['cli', 'auto_death']);
        $this->assertSame(1, (int) $this->find('pets', $dog)['death']);
    }
}
