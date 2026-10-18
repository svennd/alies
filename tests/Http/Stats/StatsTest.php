<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/../Reports/ReportsHelper.php';

/**
 * T276 - Stats::index and Stats::update (saved SQL queries).
 *
 * The stats table holds SQL with two %s placeholders (from, to) that is run as it is (sprintf, no escaping).
 * stats (POST query=<id>, search_from, search_to) shows the rows of the result as a table (header = column names)
 * and the SQL that was run; the default range is the current month. stats/update/{id} shows the query and help
 * in textareas; a POST with submit saves both and redirects to stats.
 *
 * Seeded stat 1 ("Nieuwe klanten") counts owners created in the range. Own stats are created per test group.
 * Owners for the range 2016-06: three created on 2016-06-10 (and one at exactly "from 00:00:00" and one outside).
 */
final class StatsTest extends HttpTestCase
{
    private static int $countStat;
    private static int $listStat;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (['2016-06-10 09:00:00', '2016-06-10 10:00:00', '2016-06-20 10:00:00', '2016-06-01 00:00:00', '2016-07-05 10:00:00'] as $i => $created) {
            Factory::owner(['last_name' => 'Statsowner' . $i, 'created_at' => $created]);
        }
        $now = Factory::raw('NOW()');
        self::$countStat = Factory::insert('stats', [
            'title' => 'Owners in range',
            'query' => "SELECT COUNT(*) AS n FROM owners WHERE created_at > '%s' AND created_at < '%s'",
            'help' => 'Counts owners',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        self::$listStat = Factory::insert('stats', [
            'title' => 'Owner names',
            'query' => "SELECT last_name AS name, zip FROM owners WHERE last_name LIKE 'Statsowner%%' AND created_at > '%s' AND created_at < '%s' ORDER BY last_name",
            'help' => 'Lists owner names',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testThePageListsTheStatsAndShowsTheCurrentMonthByDefault(): void
    {
        $page = $this->get('stats');

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringContainsString('<option value="1" >Nieuwe klanten', $body);
        $this->assertStringContainsString('<option value="' . self::$countStat . '" >Owners in range', $body);
        $this->assertStringContainsString('value="' . date('Y-m-01') . '"', $body);
        $this->assertStringContainsString('value="' . date('Y-m-t') . '"', $body);
        $this->assertSee($page, 'no results;');
        $this->assertDontSee($page, 'results for');
    }

    public function testAQueryIsRunWithTheRangeAndItsResultIsShown(): void
    {
        $page = $this->post('stats', ['query' => self::$countStat, 'search_from' => '2016-06-01', 'search_to' => '2016-06-30', 'submit' => 'usage']);

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('results for <strong>Owners in range</strong>', (string) $page->getBody());
        $this->assertSee($page, 'Counts owners');
        // created 06-10 (2x) and 06-20 inside; 06-01 00:00:00 is not > '2016-06-01'; 07-05 is outside
        $this->assertSame([['n'], ['3']], ReportsHelper::tableRows($page));
        // the SQL that was run, with the dates filled in
        $this->assertSee($page, "created_at > '2016-06-01' AND created_at < '2016-06-30'");
        $this->assertStringContainsString('<option value="' . self::$countStat . '" selected>', (string) $page->getBody());
        $this->assertStringContainsString('stats/update/' . self::$countStat, (string) $page->getBody());
    }

    public function testTheSeededStatCountsOwnersToo(): void
    {
        $page = $this->post('stats', ['query' => 1, 'search_from' => '2016-06-01', 'search_to' => '2016-07-31']);

        // 06-10 x2, 06-20, 07-05 (the 06-01 00:00:00 one is excluded)
        $this->assertSame([['n'], ['4']], ReportsHelper::tableRows($page));
        $this->assertStringContainsString('results for <strong>Nieuwe klanten</strong>', (string) $page->getBody());
    }

    public function testAResultWithSeveralColumnsAndRowsIsATable(): void
    {
        $page = $this->post('stats', ['query' => self::$listStat, 'search_from' => '2016-05-01', 'search_to' => '2016-07-31']);

        // %% in the stored SQL is a literal % after sprintf; the zip comes from the factory default
        $this->assertSame([['name', 'zip'], ['Statsowner0', '2000'], ['Statsowner1', '2000'], ['Statsowner2', '2000'], ['Statsowner3', '2000'], ['Statsowner4', '2000']], ReportsHelper::tableRows($page));
    }

    public function testAQueryWithoutRowsShowsNoResults(): void
    {
        $page = $this->post('stats', ['query' => self::$listStat, 'search_from' => '2001-01-01', 'search_to' => '2001-01-31']);

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('results for <strong>Owner names</strong>', (string) $page->getBody());
        $this->assertSee($page, 'no results;');
    }

    public function testWithoutADateTheCurrentMonthIsUsed(): void
    {
        $page = $this->post('stats', ['query' => self::$countStat]);

        $this->assertStatus($page, 200);
        $this->assertSee($page, "created_at > '" . date('Y-m-01') . "' AND created_at < '" . date('Y-m-t') . "'");
    }

    #[Group('known-bug')]
    public function testDatesAreInsertedIntoTheSqlWithoutEscaping(): void
    {
        // KNOWN BUG: sprintf puts the posted dates in the SQL as they are: an injected quote changes the query.
        // Here it turns the range condition into "... OR '1'='1'", so the count is of ALL owners.
        $all = (int) $this->scalar('SELECT COUNT(*) FROM owners');
        $this->assertGreaterThan(3, $all);

        $page = $this->post('stats', ['query' => self::$countStat, 'search_from' => '2016-06-01', 'search_to' => "2016-06-30' OR '1'='1"]);

        $this->assertSame([['n'], [(string) $all]], ReportsHelper::tableRows($page));
    }

    #[Group('known-bug')]
    public function testAQueryThatFailsIsADatabaseError(): void
    {
        // KNOWN BUG: a broken saved query (or a broken date) ends in the CodeIgniter database error page (500).
        $broken = Factory::insert('stats', ['title' => 'Broken', 'query' => 'SELECT nothing FROM nowhere WHERE a > \'%s\' AND b < \'%s\'', 'help' => '', 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);

        $response = $this->post('stats', ['query' => $broken, 'search_from' => '2016-06-01', 'search_to' => '2016-06-30']);

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Database Error');
    }

    // ---- update -----------------------------------------------------------------------------------

    public function testTheUpdatePageShowsQueryAndHelp(): void
    {
        $page = $this->get('stats/update/' . self::$countStat);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringContainsString('action="' . \rtrim(self::baseUrl(), '/') . '/stats/update/' . self::$countStat . '"', $body);
        $this->assertSee($page, "SELECT COUNT(*) AS n FROM owners WHERE created_at > '%s' AND created_at < '%s'");
        $this->assertSee($page, 'Counts owners');
    }

    public function testAPostedUpdateSavesTheQueryAndRedirectsToTheStats(): void
    {
        $id = Factory::insert('stats', ['title' => 'To update', 'query' => 'SELECT 1 AS one', 'help' => 'old help', 'created_at' => Factory::raw('NOW()'), 'updated_at' => '2000-01-01 00:00:00']);
        $new = "SELECT COUNT(*) AS total FROM pets WHERE created_at > '%s' AND created_at < '%s'";

        $response = $this->post('stats/update/' . $id, ['submit' => 'usage', 'query' => $new, 'help' => 'new help']);

        $this->assertRedirectsTo($response, 'stats');
        $row = $this->find('stats', $id);
        $this->assertSame($new, $row['query']);
        $this->assertSame('new help', $row['help']);
        $this->assertSame('To update', $row['title'], 'the title is not editable');
        $this->assertNotSame('2000-01-01 00:00:00', $row['updated_at']);
        // only that row changed
        $this->assertSame('Counts owners', $this->find('stats', self::$countStat)['help']);
        // and the new query is what runs
        $run = $this->post('stats', ['query' => $id, 'search_from' => '2016-06-01', 'search_to' => '2016-06-30']);
        $this->assertSame('total', ReportsHelper::tableRows($run)[0][0]);
    }

    public function testAnUpdateWithoutSubmitChangesNothing(): void
    {
        $id = Factory::insert('stats', ['title' => 'Keep', 'query' => 'SELECT 2 AS two', 'help' => 'keep help', 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);

        $response = $this->post('stats/update/' . $id, ['query' => 'DROP', 'help' => 'x']);

        $this->assertStatus($response, 200);
        $this->assertNotRedirect($response);
        $this->assertSame('SELECT 2 AS two', $this->find('stats', $id)['query']);
        $this->assertSame('keep help', $this->find('stats', $id)['help']);
    }
}
