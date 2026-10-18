<?php

declare(strict_types=1);

/**
 * T139 - Accounting::dashboard($month = 0). The controller extends Admin_Controller: admins get in, vets AND
 * accounting-only users are sent to the home page.
 *
 * The numbers are checked on a month far in the past (30 months back) where the seed has no bills or events, so the
 * expectations are exactly what the test creates:
 *  - monthly earnings: sum(total_net) of bills with invoice_date in that month and status PAID(4) or HISTORICAL(7)
 *  - yearly earnings: the same for created_at in that year
 *  - contacts: events created in that month / year (and the operations among them)
 *  - chart: invoiced bills of the 11 months before plus the month itself (last day excluded), per month
 *  - stock errors: count of stock with state 3
 */
final class DashboardTest extends HttpTestCase
{
    private const OFFSET = -30;

    private function target(): DateTime
    {
        return (new DateTime('first day of this month'))->modify(self::OFFSET . ' months');
    }

    public function testAdminGetsInOnTheCurrentPreviousAndNextMonth(): void
    {
        $this->loginAs('admin');

        foreach (['accounting/dashboard', 'accounting/dashboard/-1', 'accounting/dashboard/1'] as $route) {
            $response = $this->get($route);
            $this->assertStatus($response, 200, $route);
            $this->assertNotRedirect($response, $route);
        }
    }

    public function testAVetAndAnAccountingUserAreSentToTheHomePage(): void
    {
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('accounting/dashboard'), '');

        // the class extends Admin_Controller, so accounting-only users are refused too
        $this->cookies->clear();
        $this->loginAs('acc');
        $this->assertRedirectsTo($this->get('accounting/dashboard'), '');

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStatus($this->get('accounting/dashboard'), 200);
    }

    public function testTheStockErrorCountIsShown(): void
    {
        Factory::stock(Fixtures::PRODUCT_TIERED, ['state' => 3]);
        $expected = (int) $this->scalar('SELECT COUNT(*) FROM stock WHERE state = 3');
        $this->assertGreaterThanOrEqual(2, $expected);
        $this->loginAs('admin');

        $page = $this->get('accounting/dashboard');

        $this->assertMatchesRegularExpression('/\(' . $expected . '\)/', $this->text($page));
        $this->assertSee($page, '(' . $expected . ')');
    }

    public function testEarningsContactsAndChartOfAnEarlierMonth(): void
    {
        $target = $this->target();
        $year = $target->format('Y');
        $targetMonth = (int) $target->format('n');
        $extraMonth = $targetMonth === 1 ? 2 : 1;
        $inMonth = $target->format('Y-m-15 12:00:00');
        $inYear = sprintf('%s-%02d-15 12:00:00', $year, $extraMonth);
        $owner = Factory::owner();
        $invoice = 880000 + random_int(0, 9000);

        $paid = fn (float $net, int $status, string $date, ?int $invoiceId = null) => Factory::bill($owner, $status, [
            'total_net' => $net, 'total_brut' => $net, 'invoice_date' => $date, 'created_at' => $date, 'invoice_id' => $invoiceId,
        ]);
        $paid(1000, 4, $inMonth, $invoice + 1);   // PAID
        $paid(234, 4, $inMonth, $invoice + 2);    // PAID
        $paid(100, 7, $inMonth, $invoice + 3);    // HISTORICAL
        $paid(500, 1, $inMonth, null);            // PENDING: not counted
        $paid(66, 4, $inYear, $invoice + 4);      // other month of the same year

        $pet = Factory::pet($owner);
        Factory::event($pet, 0, 0, ['created_at' => $inMonth]);
        Factory::event($pet, 0, 0, ['created_at' => $inMonth]);
        Factory::event($pet, 0, 0, ['created_at' => $inMonth, 'type' => 1]);   // operation
        Factory::event($pet, 0, 0, ['created_at' => $inYear]);
        Factory::event($pet, 0, 0, ['created_at' => $inYear]);

        $this->loginAs('admin');
        $page = $this->get('accounting/dashboard/' . self::OFFSET);

        $this->assertStatus($page, 200);
        $body = (string) $page->getBody();
        // month: 1000 + 234 + 100 = 1334 ; year: 1334 + 66 = 1400
        $this->assertMatchesRegularExpression('#reports/revenue">&euro; 1\.334</a>#', $body);
        $this->assertStringContainsString('&euro; 1.400', $body);
        $this->assertStringNotContainsString('1.834', $body);
        // events: 3 in the month of which 1 operation, 5 in the year
        $this->assertMatchesRegularExpression('#fw-bold">3</div>\s*<span[^>]*><i[^>]*></i>\s*1\s*</span>#', $body);
        $this->assertMatchesRegularExpression('#fw-bold">5</div>#', $body);
        // chart: 66 only falls in the window when its month lies before the selected month
        $label = $target->format('F');
        if ($extraMonth < $targetMonth) {
            $this->assertStringContainsString("labels: ['" . DateTime::createFromFormat('!n', (string) $extraMonth)->format('F') . "','" . $label . "',]", $body);
            $this->assertStringContainsString('data: [66,1334,]', $body);
        } else {
            $this->assertStringContainsString("labels: ['" . $label . "',]", $body);
            $this->assertStringContainsString('data: [1334,]', $body);
        }
    }
}
