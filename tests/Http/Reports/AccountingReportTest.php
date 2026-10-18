<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T270 - Reports::accounting (product and procedure usage on CLOSED events per booking code).
 *
 * Intended rules: a POST with search_from, search_to and booking lists the usage of that booking code; the url form
 * reports/accounting/{from}/{to}/{id}/1 downloads accounting_code_{id}.csv (header product,in_price,netto,netto_ori,
 * location,event_id, see views/reports/accounting_csv.php). Url arguments are ignored unless the csv flag is given.
 *
 * KNOWN BUG (whole report): Booking_code_model::get_usage_detail selects ep.net_price, ep.calc_net_price and
 * ep.barcode, columns that events_products no longer has (it has price_net and price_ori_net, and no barcode).
 * Every search and every csv download therefore ends in a "Database Error" 500. Only the empty form can be shown.
 * The tests pin that, so the CI4 port notices when it is repaired; the data it should list is created here so a
 * fixed report can be checked by turning the pins into assertions.
 */
final class AccountingReportTest extends HttpTestCase
{
    private static int $code;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$code = Factory::insert('booking_codes', ['category' => 'ACC Cat', 'code' => '799001', 'btw' => 6, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);
        $pet = Factory::pet(Factory::owner(['last_name' => 'Accowner']));
        $product = Factory::product(['name' => 'ACC Product']);
        $stamp = '2017-03-10 12:00:00';
        // closed event in March 2017: product line 12.50 and procedure line 25.00 on the code
        $event = Factory::event($pet, 1, 0, ['created_at' => $stamp]);
        Factory::eventProduct($event, $product, 1.0, ['booking' => self::$code, 'unit_price' => 12.50, 'created_at' => $stamp]);
        Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['booking' => self::$code, 'unit_price' => 25.00, 'created_at' => $stamp]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testTheFormListsTheBookingCodesThatAreNotDeleted(): void
    {
        $page = $this->get('reports/accounting');

        $this->assertStatus($page, 200);
        $this->assertSee($page, '700000 Producten 6%');
        $this->assertSee($page, '701000 Diensten 21%');
        $this->assertSee($page, '799001 ACC Cat 6%');
        $this->assertDontSee($page, '709999', 'soft deleted code');
        $this->assertStringContainsString('<option value="' . self::$code . '"', (string) $page->getBody());
        $this->assertDontSee($page, 'Export to CSV', 'no usage, no export button');
    }

    public function testUrlArgumentsAreIgnoredWithoutTheCsvFlag(): void
    {
        $page = $this->get('reports/accounting/2017-03-01/2017-03-31/' . self::$code);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        $this->assertStringNotContainsString('2017-03-01', $body, 'the form is not prefilled from the url');
        $this->assertStringNotContainsString('<option value="' . self::$code . '" selected', $body);
        $this->assertDontSee($page, 'ACC Product');
    }

    #[Group('known-bug')]
    public function testSearchingARangeFailsWithADatabaseError(): void
    {
        // KNOWN BUG: see the class docblock (unknown column ep.net_price).
        $response = $this->post('reports/accounting', ['search_from' => '2017-03-01', 'search_to' => '2017-03-31', 'booking' => self::$code, 'submit' => 'usage']);

        $this->assertStatus($response, 500);
        $this->assertSee($response, 'Database Error');
        // positive control: the page itself works without a booking choice
        $this->assertStatus($this->post('reports/accounting', ['search_from' => '2017-03-01']), 200);
    }

    #[Group('known-bug')]
    public function testTheCsvDownloadFailsWithADatabaseError(): void
    {
        // KNOWN BUG: the intended download is accounting_code_{id}.csv; today the query fails before any header is sent.
        $response = $this->get('reports/accounting/2017-03-01/2017-03-31/' . self::$code . '/1');

        $this->assertStatus($response, 500);
        $this->assertStringNotContainsString('attachment', $response->getHeaderLine('Content-Disposition'));
        $this->assertSee($response, 'Database Error');
    }
}
