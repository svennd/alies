<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T157 - earnings aggregates of Bills_model. Source: application/models/Bills_model.php
 *
 *  get_monthly_earning(date)      sum(total_net), invoice_date from the GIVEN day (not the 1st) to the last day
 *                                 of that month, status PAID or HISTORICAL only. Does not look at deleted_at.
 *  get_yearly_earnings(date)      same for created_at in the year of the date; mutates the DateTime
 *  get_yearly_earnings_by_month   per year/month of invoice_date, from date - 11 months up to but NOT including
 *                                 the last day of the month; needs invoice_id, ignores status, skips deleted
 *  get_yearly_earnings_by_date    invoice_date strictly between "from 00:00" and "to 23:59" (both exclusive)
 *
 * All rows live in 2018/2019, the seeded bills are from this year, so they never interfere.
 */
final class BillsEarningsTest extends CodeIgniterDatabaseTestCase
{
    private int $owner;
    private Bills_model $model;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owner = Factory::owner();
        $this->model = $this->model('Bills_model', 'bills_earn');
    }

    private function bill(int $status, string $invoiceDate, float $net, array $extra = []): int
    {
        return Factory::bill($this->owner, $status, $extra + [
            'invoice_date' => $invoiceDate,
            'created_at' => $invoiceDate,
            'total_net' => $net,
            'total_brut' => round($net * 1.21, 2),
        ]);
    }

    // ---- get_monthly_earning ------------------------------------------------------------------

    public function testMonthlyEarningCountsPaidAndHistoricalWithinTheMonth(): void
    {
        $this->bill(BILL_PAID, '2019-03-01 00:00:00', 10.0);        // first instant: in
        $this->bill(BILL_HISTORICAL, '2019-03-31 12:00:00', 20.0);  // last day, noon: in (DATE() compare)
        $this->bill(BILL_PAID, '2019-03-10 09:00:00', 0.5);         // in
        $this->bill(BILL_UNPAID, '2019-03-15 09:00:00', 100.0);     // wrong status
        $this->bill(BILL_PENDING, '2019-03-15 09:00:00', 100.0);    // wrong status
        $this->bill(BILL_PAID, '2019-02-28 23:59:59', 1000.0);      // previous month
        $this->bill(BILL_PAID, '2019-04-01 00:00:00', 2000.0);      // next month

        // 10 + 20 + 0.5 = 30.5
        $this->assertEqualsWithDelta(30.5, $this->model->get_monthly_earning(new DateTime('2019-03-01')), 0.0001);
    }

    public function testMonthlyEarningStartsAtTheGivenDayNotAtTheFirst(): void
    {
        $this->bill(BILL_PAID, '2019-03-01 00:00:00', 10.0);
        $this->bill(BILL_PAID, '2019-03-15 23:00:00', 4.0);
        $this->bill(BILL_PAID, '2019-03-16 00:00:00', 20.0);

        // from the 16th: only 20 ; the 15th is excluded, the 1st too
        $this->assertEqualsWithDelta(20.0, $this->model->get_monthly_earning(new DateTime('2019-03-16')), 0.0001);
        // positive control: from the 1st everything is counted: 10 + 4 + 20 = 34
        $this->assertEqualsWithDelta(34.0, $this->model->get_monthly_earning(new DateTime('2019-03-01')), 0.0001);
    }

    public function testMonthlyEarningWithNothingIsIntegerZeroAndIsRounded(): void
    {
        $this->assertSame(0, $this->model->get_monthly_earning(new DateTime('2019-03-01')));

        $this->bill(BILL_PAID, '2019-03-05 10:00:00', 1.11);
        $this->bill(BILL_PAID, '2019-03-06 10:00:00', 2.22);
        // 1.11 + 2.22 = 3.33
        $this->assertEqualsWithDelta(3.33, $this->model->get_monthly_earning(new DateTime('2019-03-01')), 0.0001);
    }

    #[Group('known-bug')]
    public function testMonthlyEarningAlsoCountsSoftDeletedBills(): void
    {
        $this->bill(BILL_PAID, '2019-03-10 10:00:00', 10.0);
        $this->bill(BILL_PAID, '2019-03-11 10:00:00', 40.0, ['deleted_at' => Factory::raw('NOW()')]);

        // KNOWN BUG: no deleted_at filter, so the deleted 40 is in the total (by_month/by_date do filter).
        $this->assertEqualsWithDelta(50.0, $this->model->get_monthly_earning(new DateTime('2019-03-01')), 0.0001);
    }

    // ---- get_yearly_earnings ------------------------------------------------------------------

    public function testYearlyEarningsUseCreatedAtAndStatusAndTheYearBoundaries(): void
    {
        $this->bill(BILL_PAID, '2019-01-01 00:00:00', 10.0);          // first instant: in
        $this->bill(BILL_HISTORICAL, '2019-12-31 23:59:59', 20.0);    // last second: in
        $this->bill(BILL_PAID, '2019-07-01 12:00:00', 3.0);           // in
        $this->bill(BILL_PAID, '2018-12-31 23:59:59', 1000.0);        // out
        $this->bill(BILL_PAID, '2020-01-01 00:00:00', 2000.0);        // out
        $this->bill(BILL_OVERDUE, '2019-07-01 12:00:00', 500.0);      // wrong status
        // invoice_date is irrelevant: this bill is created in 2019 but invoiced in 2020, still counted
        $this->bill(BILL_PAID, '2020-02-02 12:00:00', 7.0, ['created_at' => '2019-05-05 12:00:00']);

        // 10 + 20 + 3 + 7 = 40
        $this->assertEqualsWithDelta(40.0, $this->model->get_yearly_earnings(new DateTime('2019-06-15')), 0.0001);
        $this->assertSame(0, $this->model->get_yearly_earnings(new DateTime('2017-06-15')));
    }

    public function testYearlyEarningsChangesTheDateTimeItIsGiven(): void
    {
        $date = new DateTime('2019-06-15');

        $this->model->get_yearly_earnings($date);

        // the object is moved to "last day of december"
        $this->assertSame('2019-12-31', $date->format('Y-m-d'));
    }

    // ---- get_yearly_earnings_by_month ---------------------------------------------------------

    public function testByMonthGroupsInvoicesAndOnlyUsesTheWindow(): void
    {
        // window for 2019-06-15: DATE(invoice_date) >= 2018-07-15 and < 2019-06-30
        $this->bill(BILL_PAID, '2018-07-14 23:00:00', 1000.0, ['invoice_id' => 1]);   // before the window
        $this->bill(BILL_PAID, '2018-07-15 10:00:00', 10.0, ['invoice_id' => 2]);     // in (first day)
        $this->bill(BILL_PAID, '2018-07-20 10:00:00', 5.0, ['invoice_id' => 3]);      // in, same month
        $this->bill(BILL_UNPAID, '2019-01-10 10:00:00', 3.0, ['invoice_id' => 4]);    // status is ignored: in
        $this->bill(BILL_PAID, '2019-06-29 23:00:00', 7.0, ['invoice_id' => 5]);      // in
        $this->bill(BILL_PAID, '2019-01-11 10:00:00', 900.0);                         // no invoice_id: out
        $this->bill(BILL_PAID, '2019-01-12 10:00:00', 800.0, ['invoice_id' => 6, 'deleted_at' => Factory::raw('NOW()')]);

        $rows = $this->model->get_yearly_earnings_by_month(new DateTime('2019-06-15'));

        // brut = round(net * 1.21, 2): 10 -> 12.10, 5 -> 6.05, 3 -> 3.63, 7 -> 8.47
        $this->assertCount(3, $rows);
        $this->assertSame([2018, 7], [(int) $rows[0]['y'], (int) $rows[0]['m']]);
        $this->assertEqualsWithDelta(15.0, (float) $rows[0]['total'], 0.0001);
        $this->assertEqualsWithDelta(18.15, (float) $rows[0]['total_brut'], 0.0001);
        $this->assertSame(2, (int) $rows[0]['invoices']);
        $this->assertSame([2019, 1], [(int) $rows[1]['y'], (int) $rows[1]['m']]);
        $this->assertEqualsWithDelta(3.0, (float) $rows[1]['total'], 0.0001);
        $this->assertEqualsWithDelta(3.63, (float) $rows[1]['total_brut'], 0.0001);
        $this->assertSame(1, (int) $rows[1]['invoices']);
        $this->assertSame([2019, 6], [(int) $rows[2]['y'], (int) $rows[2]['m']]);
        $this->assertEqualsWithDelta(7.0, (float) $rows[2]['total'], 0.0001);
        $this->assertSame(1, (int) $rows[2]['invoices']);
    }

    #[Group('known-bug')]
    public function testByMonthLeavesOutTheLastDayOfTheSelectedMonth(): void
    {
        $this->bill(BILL_PAID, '2019-06-29 12:00:00', 7.0, ['invoice_id' => 1]);
        $this->bill(BILL_PAID, '2019-06-30 12:00:00', 100.0, ['invoice_id' => 2]);

        $rows = $this->model->get_yearly_earnings_by_month(new DateTime('2019-06-15'));

        // KNOWN BUG: "< LAST_DAY" instead of "<=": the invoice of 30 June (100) is missing from June.
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(7.0, (float) $rows[0]['total'], 0.0001);
    }

    public function testByMonthChangesTheDateTimeItIsGiven(): void
    {
        $date = new DateTime('2019-06-15');

        $this->model->get_yearly_earnings_by_month($date);

        $this->assertSame('2018-07-15', $date->format('Y-m-d'));
    }

    public function testByMonthWithoutBillsIsAnEmptyArray(): void
    {
        $this->assertSame([], $this->model->get_yearly_earnings_by_month(new DateTime('2015-06-15')));
    }

    // ---- get_yearly_earnings_by_date ----------------------------------------------------------

    public function testByDateExcludesExactlyMidnightAndTheLastMinute(): void
    {
        $this->bill(BILL_PAID, '2019-01-01 00:00:00', 1000.0, ['invoice_id' => 1]);  // exactly 00:00:00: out (>)
        $this->bill(BILL_PAID, '2019-01-01 00:00:01', 1.0, ['invoice_id' => 2]);     // in
        $this->bill(BILL_UNPAID, '2019-01-31 23:58:59', 2.0, ['invoice_id' => 3]);   // in (status ignored)
        $this->bill(BILL_PAID, '2019-01-31 23:59:00', 500.0, ['invoice_id' => 4]);   // exactly 23:59:00: out (<)
        $this->bill(BILL_PAID, '2019-01-31 23:59:30', 100.0, ['invoice_id' => 5]);   // out
        $this->bill(BILL_PAID, '2019-01-15 12:00:00', 900.0);                        // no invoice_id: out
        $this->bill(BILL_PAID, '2019-01-16 12:00:00', 800.0, ['invoice_id' => 6, 'deleted_at' => Factory::raw('NOW()')]);

        $rows = $this->model->get_yearly_earnings_by_date('2019-01-01', '2019-01-31');

        // 1 + 2 = 3 ; brut 1.21 + 2.42 = 3.63 ; two invoices
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(3.0, (float) $rows[0]['total'], 0.0001);
        $this->assertEqualsWithDelta(3.63, (float) $rows[0]['total_brut'], 0.0001);
        $this->assertSame(2, (int) $rows[0]['invoices']);
        $this->assertArrayNotHasKey('y', $rows[0]);
    }

    public function testByDateGroupedGivesOneRowPerMonth(): void
    {
        $this->bill(BILL_PAID, '2019-01-10 10:00:00', 10.0, ['invoice_id' => 1]);
        $this->bill(BILL_PAID, '2019-01-20 10:00:00', 5.0, ['invoice_id' => 2]);
        $this->bill(BILL_PAID, '2019-02-10 10:00:00', 4.0, ['invoice_id' => 3]);

        $rows = $this->model->get_yearly_earnings_by_date('2019-01-01', '2019-02-28', true);

        $this->assertCount(2, $rows);
        $this->assertSame([2019, 1], [(int) $rows[0]['y'], (int) $rows[0]['m']]);
        $this->assertEqualsWithDelta(15.0, (float) $rows[0]['total'], 0.0001);
        $this->assertSame(2, (int) $rows[0]['invoices']);
        $this->assertSame([2019, 2], [(int) $rows[1]['y'], (int) $rows[1]['m']]);
        $this->assertEqualsWithDelta(4.0, (float) $rows[1]['total'], 0.0001);
    }

    public function testByDateWithoutBillsGivesOneRowWithNullTotalAndZeroInvoices(): void
    {
        $rows = $this->model->get_yearly_earnings_by_date('2015-01-01', '2015-01-31');

        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['total']);
        $this->assertSame(0, (int) $rows[0]['invoices']);
    }
}
