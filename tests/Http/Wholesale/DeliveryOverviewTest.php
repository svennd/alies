<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T213 - Wholesale::delivery_overview and Wholesale::delivery. Admin only.
 *
 * Rules: the overview groups the automatic `delivery` rows by delivery_date (count of rows as
 * "products", sum of amount as "packages"), newest first, between search_from (exclusive of the
 * day before) and search_to. Without POST the range is today minus one month until today. The
 * detail page lists the delivery rows of exactly that date with the wholesale description.
 */
final class DeliveryOverviewTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function day(int $offset): string
    {
        return date('Y-m-d', strtotime(($offset >= 0 ? '+' : '') . $offset . ' days'));
    }

    private function delivery(string $date, int $amount, array $overrides = []): int
    {
        return Factory::insert('delivery', array_merge([
            'order_date' => $this->day(-60),
            'order_nr' => 1,
            'my_ref' => 'REF',
            'wholesale_artnr' => 'W-1001',
            'wholesale_id' => 5001,
            'CNK' => 1234500,
            'delivery_date' => $date,
            'delivery_nr' => random_int(100000, 999999),
            'bruto_price' => 10.00,
            'netto_price' => 8.50,
            'amount' => $amount,
            'lotnr' => Factory::unique('LOT'),
            'due_date' => $this->day(365),
            'btw' => 6,
            'billing' => 'Test',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));
    }

    public function testNonAdminsAreSentHome(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('wholesale/delivery_overview'), '');
        $this->assertRedirectsTo($this->get('wholesale/delivery/' . $this->day(-7)), '');

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('wholesale/delivery_overview'), 200);
    }

    public function testRowsAreGroupedByDateWithProductAndPackageCounts(): void
    {
        $date = $this->day(-3);
        $this->delivery($date, 2);
        $this->delivery($date, 3);
        $this->delivery($date, 4);

        $response = $this->get('wholesale/delivery_overview');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertSame(1, substr_count($body, 'wholesale/delivery/' . $date . '"'), 'one row per date');
        // 3 rows, 2 + 3 + 4 = 9 packages
        $this->assertMatchesRegularExpression('#wholesale/delivery/' . $date . '"[^<]*>[^<]*</a></td>\s*<td>3</td>\s*<td>9</td>#', $body);
    }

    public function testTheDefaultRangeIsTheLastMonthAndTheSeededDeliveryIsInIt(): void
    {
        $inside = $this->day(-29);
        $outside = $this->day(-45);
        $this->delivery($inside, 1);
        $this->delivery($outside, 1);

        $body = (string) $this->get('wholesale/delivery_overview')->getBody();

        $this->assertStringContainsString('wholesale/delivery/' . $inside . '"', $body);
        $this->assertStringContainsString('wholesale/delivery/' . $this->day(-7) . '"', $body, 'seeded delivery 1');
        $this->assertStringNotContainsString('wholesale/delivery/' . $outside . '"', $body);
        $this->assertStringContainsString('value="' . $this->day(0) . '" id="search_to"', $body);
        $this->assertStringContainsString('value="' . date('Y-m-d', strtotime('-1 month')) . '" id="search_from"', $body);
    }

    public function testTheNewestDateComesFirst(): void
    {
        $this->delivery($this->day(-12), 1);
        $this->delivery($this->day(-2), 1);
        $this->delivery($this->day(-20), 1);

        $body = (string) $this->get('wholesale/delivery_overview')->getBody();

        $a = strpos($body, 'wholesale/delivery/' . $this->day(-2) . '"');
        $b = strpos($body, 'wholesale/delivery/' . $this->day(-12) . '"');
        $c = strpos($body, 'wholesale/delivery/' . $this->day(-20) . '"');
        $this->assertNotFalse($a);
        $this->assertLessThan($b, $a);
        $this->assertLessThan($c, $b);
    }

    public function testAPostedRangeReplacesTheDefault(): void
    {
        $old = $this->day(-100);
        $recent = $this->day(-4);
        $this->delivery($old, 6);
        $this->delivery($recent, 1);

        $response = $this->post('wholesale/delivery_overview', ['search_from' => $this->day(-110), 'search_to' => $this->day(-90), 'submit' => 'usage']);

        $body = (string) $response->getBody();
        $this->assertStringContainsString('wholesale/delivery/' . $old . '"', $body);
        $this->assertStringNotContainsString('wholesale/delivery/' . $recent . '"', $body);
        $this->assertStringContainsString('value="' . $this->day(-110) . '" id="search_from"', $body);
        $this->assertStringContainsString('value="' . $this->day(-90) . '" id="search_to"', $body);
    }

    public function testTheLowerBoundIsExclusiveOfMidnightOfTheFromDate(): void
    {
        // delivery_date is a DATE: "> from 00:00" drops the from date itself, "< to 23:59" keeps the to date.
        $from = $this->day(-80);
        $to = $this->day(-70);
        $this->delivery($from, 1);
        $this->delivery($to, 1);

        $body = (string) $this->post('wholesale/delivery_overview', ['search_from' => $from, 'search_to' => $to])->getBody();

        $this->assertStringContainsString('wholesale/delivery/' . $to . '"', $body);
        $this->assertStringNotContainsString('wholesale/delivery/' . $from . '"', $body);
    }

    public function testAnEmptyRangeShowsNoTable(): void
    {
        $response = $this->post('wholesale/delivery_overview', ['search_from' => $this->day(-400), 'search_to' => $this->day(-399)]);

        $this->assertStatus($response, 200);
        $this->assertStringNotContainsString('id="deliveries"', (string) $response->getBody());
        $this->assertStringNotContainsString('wholesale/delivery/20', (string) $response->getBody());
    }

    // ---- detail -----------------------------------------------------------------------------

    public function testTheDetailPageListsTheDeliveriesOfThatDateOnly(): void
    {
        $date = $this->day(-15);
        $wh = Factory::wholesale(['description' => 'Detail article ' . Factory::unique('d')]);
        $this->delivery($date, 7, ['wholesale_id' => $wh, 'lotnr' => 'DET-LOT-1', 'delivery_nr' => 654321, 'bruto_price' => 11.11, 'netto_price' => 9.99]);
        $this->delivery($date, 8, ['wholesale_id' => 5001, 'lotnr' => 'DET-LOT-2']);
        $this->delivery($this->day(-16), 9, ['lotnr' => 'DET-OTHER-DAY']);

        $response = $this->get('wholesale/delivery/' . $date);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'DET-LOT-1');
        $this->assertSee($response, 'DET-LOT-2');
        $this->assertDontSee($response, 'DET-OTHER-DAY');
        $this->assertSee($response, '654321');
        $this->assertSee($response, '11.11');
        $this->assertSee($response, '9.99');
        $this->assertSee($response, 'Detail article');
        $this->assertSee($response, 'Amoxi Tabs 500 mg 100 tabletten');
        $this->assertStringContainsString('wholesale/get_history/' . $wh, (string) $response->getBody());
    }

    public function testADeliveryWithoutAWholesaleArticleShowsQuestionMarks(): void
    {
        $date = $this->day(-18);
        $this->delivery($date, 1, ['wholesale_id' => 99999999, 'lotnr' => 'ORPHAN-LOT']);

        $response = $this->get('wholesale/delivery/' . $date);

        $this->assertSee($response, 'ORPHAN-LOT');
        $this->assertSee($response, '???');
    }

    public function testADateWithoutDeliveriesShowsNoTable(): void
    {
        $response = $this->get('wholesale/delivery/' . $this->day(-500));

        $this->assertStatus($response, 200);
        $this->assertStringNotContainsString('id="deliveries"', (string) $response->getBody());
    }

    #[Group('known-bug')]
    public function testTheManualRegisterInRowsOfThatDateAreLoadedButNeverShown(): void
    {
        // KNOWN BUG: Wholesale::delivery passes "regin" (register_in rows of the slip date) to the view,
        // but views/wholesale/delivery.php never prints it; the spec expects them on this page.
        $date = $this->day(-21);
        $slip = Factory::insert('delivery_slip', [
            'vet' => 2, 'note' => 'manual', 'regdate' => $date, 'location' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Factory::insert('register_in', [
            'product' => 4001, 'volume' => 3.00, 'eol' => $this->day(300), 'in_price' => 1.00, 'cat_price' => 1.00,
            'lotnr' => 'RI-ONLY-LOT', 'supplier' => 'S', 'delivery_slip' => $slip,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->delivery($date, 1, ['lotnr' => 'AUTO-SAME-DAY']);

        $response = $this->get('wholesale/delivery/' . $date);

        $this->assertSee($response, 'AUTO-SAME-DAY');
        $this->assertDontSee($response, 'RI-ONLY-LOT');
    }
}
