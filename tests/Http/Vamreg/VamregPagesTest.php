<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHelper.php';

use PHPUnit\Framework\Attributes\Group;

/**
 * T280 - Vamreg pages render (Vamreg::index, in, out, out_detail, product_list).
 *
 * Rules: Vamreg is an admin screen. Without the `vamreg_api_key` setting the index shows an error
 * page instead of the dashboard (the other pages do not check the key). The dashboard shows the
 * number of DRAFT rows of each buffer inside the quarter. The quarter comes from the url; a
 * non numeric year or quarter is a TypeError, so a 500.
 * Fixed quarters: the dashboard test owns 2031 Q1 (2031-01-01 to 2031-03-31); the in, out and out_detail tests use the years 2032, 2033 and 2034, so the tests do not see each other's rows.
 */
final class VamregPagesTest extends HttpTestCase
{
    private const CNK = '7654321';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    /** The visible text of a page, tags removed and whitespace collapsed. */
    private function plain(\Psr\Http\Message\ResponseInterface $response): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($this->text($response))));
    }

    private function seedQuarter(): void
    {
        // IN: 2 drafts + 1 sent + 1 invalid in 2031 Q1, 1 draft in Q2 (must not count), 1 on the first and last day
        VamregHelper::in('2031-01-01', ['status' => 'DRAFT']);
        VamregHelper::in('2031-03-31', ['status' => 'DRAFT']);
        VamregHelper::in('2031-02-10', ['status' => 'SENT']);
        VamregHelper::in('2031-02-11', ['status' => 'INVALID']);
        VamregHelper::in('2031-04-01', ['status' => 'DRAFT']);
        // OUT: 3 drafts + 1 sent in Q1, 1 draft in the last quarter of 2030
        VamregHelper::out('2031-01-15', ['cnk' => self::CNK, 'status' => 'DRAFT']);
        VamregHelper::out('2031-02-15', ['cnk' => self::CNK, 'status' => 'DRAFT']);
        VamregHelper::out('2031-03-15', ['cnk' => self::CNK, 'status' => 'DRAFT']);
        VamregHelper::out('2031-03-16', ['cnk' => self::CNK, 'status' => 'SENT']);
        VamregHelper::out('2030-12-31', ['cnk' => self::CNK, 'status' => 'DRAFT']);
    }

    public function testWithoutTheApiKeyTheIndexShowsAnError(): void
    {
        $response = $this->get('vamreg');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'VAMREG API key is not configured');
    }

    public function testWithAnEmptyApiKeyTheIndexStillShowsTheError(): void
    {
        Factory::setting('vamreg_api_key', '');

        $this->assertSee($this->get('vamreg/index/2031/1'), 'VAMREG API key is not configured');
    }

    public function testWithTheKeyTheDashboardShowsTheDraftCountsOfTheQuarter(): void
    {
        Factory::setting('vamreg_api_key', 'test-key');
        $this->seedQuarter();

        $response = $this->get('vamreg/index/2031/1');

        $this->assertStatus($response, 200);
        $this->assertDontSee($response, 'API key is not configured');
        $this->assertSee($response, 'Q1 2031');
        $this->assertSee($response, '2031-01-01');
        $this->assertSee($response, '2031-03-31');
        // IN has 2 drafts, OUT has 3 (the badges are rendered in that order)
        $this->assertMatchesRegularExpression('/\b2 draft\b.*\b3 draft\b/s', $this->plain($response));
        // the sent / invalid rows and rows outside the quarter did not count
        $this->assertDontSee($response, '5 draft');

        // other quarters count their own rows: Q2 2031 has 1 IN draft and no OUT, Q4 2030 has 1 OUT
        $q2 = $this->get('vamreg/index/2031/2');
        $this->assertMatchesRegularExpression('/\b1 draft\b.*\b0 draft\b/s', $this->plain($q2));
        $q4 = $this->get('vamreg/index/2030/4');
        $this->assertMatchesRegularExpression('/\b0 draft\b.*\b1 draft\b/s', $this->plain($q4));
    }

    public function testTheDashboardLinksToThePreviousQuarterAndNextQuarter(): void
    {
        Factory::setting('vamreg_api_key', 'test-key');

        $response = $this->get('vamreg/index/2031/1');

        $this->assertSee($response, 'Q4 2030');
        $this->assertSee($response, 'Q2 2031');
        // the first quarter of the year wraps around
        $this->assertSee($this->get('vamreg/index/2031/4'), 'Q1 2032');
    }

    public function testTheDashboardCountsLinkedProducts(): void
    {
        Factory::setting('vamreg_api_key', 'test-key');
        $total = (int) $this->scalar('SELECT COUNT(*) FROM vamreg_index');
        $linked = (int) $this->scalar('SELECT COUNT(*) FROM vamreg_index v JOIN products p ON p.cnk = v.cnk');
        $this->assertGreaterThanOrEqual(1, $linked);

        $text = $this->plain($this->get('vamreg/index/2031/1'));

        $this->assertMatchesRegularExpression('/Linked products\s*' . $linked . '\b/', $text);
        $this->assertMatchesRegularExpression('/Total products\s*' . $total . '\b/', $text);
    }

    public function testTheInPageListsTheDeliveriesOfTheQuarterOnly(): void
    {
        VamregHelper::in('2032-02-20', ['in_quantity_pack_count' => 7701, 'status' => 'DRAFT']);
        VamregHelper::in('2032-05-20', ['in_quantity_pack_count' => 8802, 'status' => 'DRAFT']);

        $response = $this->get('vamreg/in/2032/1');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Vamreg / in');
        $this->assertSee($response, '7701');
        $this->assertDontSee($response, '8802');
        // linked to the index through the CNK
        $this->assertSee($response, 'Clavubactin 250 mg 20 tabletten');
    }

    public function testTheOutPageSumsTheQuantityPerCnk(): void
    {
        VamregHelper::out('2033-01-15', ['cnk' => '5550001', 'out_quantity_pack_count' => 1.5]);
        VamregHelper::out('2033-02-15', ['cnk' => '5550001', 'out_quantity_pack_count' => 2]);
        VamregHelper::out('2033-06-15', ['cnk' => '5550001', 'out_quantity_pack_count' => 40]);

        $response = $this->get('vamreg/out/2033/1');

        $this->assertStatus($response, 200);
        // 1.5 + 2 = 3.5 packs; the June row is outside the quarter
        $this->assertMatchesRegularExpression('#out_detail/5550001/2033/1">\s*3\.50\s+PACKS#', $this->text($response));
    }

    public function testTheOutDetailPageListsTheLinesOfOneCnk(): void
    {
        $product = Factory::product(['name' => 'Detail Antibiotic', 'cnk' => self::CNK]);
        $owner = Factory::owner(['last_name' => 'Detailowner']);
        $pet = Factory::pet($owner, ['name' => 'Detailpet']);
        $event = Factory::event($pet, 0);
        VamregHelper::out('2034-02-15', ['cnk' => self::CNK, 'event' => $event, 'out_quantity_pack_count' => 2, 'status' => 'DRAFT']);
        VamregHelper::out('2034-02-16', ['cnk' => '1111111', 'out_quantity_pack_count' => 9]);

        $response = $this->get('vamreg/out_detail/' . self::CNK . '/2034/1');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Detail Antibiotic');
        $this->assertSee($response, 'Detailpet');
        $this->assertSee($response, 'Detailowner');
        $this->assertSee($response, '2034-02-15');
        $this->assertDontSee($response, '2034-02-16');
        $this->assertGreaterThan(0, $product);
    }

    public function testTheProductListShowsTheIndexWithTheLinkedProduct(): void
    {
        $response = $this->get('vamreg/product_list');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Clavubactin 250 mg 20 tabletten');
        $this->assertSee($response, 'CTI-0001');
        $this->assertSee($response, '1234567');
    }

    public function testThePagesWorkWithoutAQuarterInTheUrl(): void
    {
        Factory::setting('vamreg_api_key', 'test-key');
        $quarter = (int) ceil(((int) date('n')) / 3);

        foreach (['vamreg/index', 'vamreg/in', 'vamreg/out'] as $url) {
            $response = $this->get($url);
            $this->assertStatus($response, 200, $url);
            $this->assertSee($response, 'Q' . $quarter . ' ' . date('Y'), $url);
        }
    }

    #[Group('known-bug')]
    public function testANonNumericQuarterInTheUrlIsAServerError(): void
    {
        // KNOWN BUG: quarterContext(?int, ?int) gets the url segment: 'abc' is a TypeError - decide in CI4 whether to keep
        Factory::setting('vamreg_api_key', 'test-key');

        $this->assertStatus($this->get('vamreg/in/abc'), 500);
        $this->assertStatus($this->get('vamreg/out/2031/abc'), 500);
        $this->assertStatus($this->get('vamreg/index/abc/1'), 500);
        // positive control: numeric segments work
        $this->assertStatus($this->get('vamreg/in/2031/1'), 200);
    }
}
