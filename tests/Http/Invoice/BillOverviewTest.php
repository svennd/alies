<?php

declare(strict_types=1);

/**
 * T149 - Invoice::index (the bill overview). Each bill row has a link invoice/get_bill/{id}
 * which is how the tests see whether a bill is listed.
 *
 * Rules: created_at between "from 00:00" and "to 23:59" (strictly after / before), default range
 * the last 7 days; POST search_from / search_to or URL timestamps invoice/index/{from}/{to}
 * (URL wins over POST); unpaid=1 keeps INCOMPLETE (3) and ONHOLD (6); modified=1 keeps bills with
 * modified = 1; soft-deleted bills never; the number of rows is limited by the RestrictBills
 * setting (250 when 0), newest first.
 */
final class BillOverviewTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        Factory::setting('RestrictBills', '0');
        parent::tearDown();
    }

    private function daysAgo(int $days, int $hour = 12): RawSql
    {
        return Factory::raw('DATE_ADD(DATE_SUB(CURDATE(), INTERVAL ' . $days . ' DAY), INTERVAL ' . $hour . ' HOUR)');
    }

    private function bill(int $status = 4, int $daysAgo = 1, array $extra = []): int
    {
        return Factory::bill(Factory::owner(), $status, $extra + ['created_at' => $this->daysAgo($daysAgo)]);
    }

    private function listed($page, int $bill): bool
    {
        return str_contains((string) $page->getBody(), 'invoice/get_bill/' . $bill . '"');
    }

    private function assertListed($page, int $bill, string $message = ''): void
    {
        $this->assertTrue($this->listed($page, $bill), $message ?: 'bill ' . $bill . ' should be listed');
    }

    private function assertNotListed($page, int $bill, string $message = ''): void
    {
        $this->assertFalse($this->listed($page, $bill), $message ?: 'bill ' . $bill . ' should not be listed');
    }

    private function range(int $fromDays, int $toDays): array
    {
        return ['search_from' => date('Y-m-d', strtotime('-' . $fromDays . ' days')), 'search_to' => date('Y-m-d', strtotime('-' . $toDays . ' days'))];
    }

    public function testTheLastSevenDaysAreListedByDefault(): void
    {
        $recent = $this->bill(4, 3);
        $today = Factory::bill(Factory::owner(), 1);
        $old = $this->bill(4, 12);
        $this->loginAs('vet');

        $page = $this->get('invoice');

        $this->assertStatus($page, 200);
        $this->assertListed($page, $recent);
        $this->assertListed($page, $today);
        $this->assertNotListed($page, $old, 'older than 7 days');
        $this->assertSee($page, date('Y-m-d', strtotime('-7 days')), 'the default range is in the form');
    }

    public function testAPostedRangeSelectsTheBills(): void
    {
        $old = $this->bill(4, 12);
        $older = $this->bill(4, 30);
        $recent = $this->bill(4, 2);
        $this->loginAs('vet');

        $page = $this->post('invoice/index', $this->range(15, 10));

        $this->assertListed($page, $old);
        $this->assertNotListed($page, $older);
        $this->assertNotListed($page, $recent);
    }

    public function testTheRangeIncludesBothEndDays(): void
    {
        $first = $this->bill(4, 20, ['created_at' => $this->daysAgo(20, 1)]);
        $last = $this->bill(4, 18, ['created_at' => $this->daysAgo(18, 22)]);
        $before = $this->bill(4, 21, ['created_at' => $this->daysAgo(21, 22)]);
        $after = $this->bill(4, 17, ['created_at' => $this->daysAgo(17, 1)]);
        $this->loginAs('vet');

        $page = $this->post('invoice/index', $this->range(20, 18));

        $this->assertListed($page, $first);
        $this->assertListed($page, $last);
        $this->assertNotListed($page, $before);
        $this->assertNotListed($page, $after);
    }

    public function testUrlTimestampsSelectTheBills(): void
    {
        $old = $this->bill(4, 40);
        $recent = $this->bill(4, 2);
        $this->loginAs('vet');

        $page = $this->get('invoice/index/' . strtotime('-45 days') . '/' . strtotime('-35 days'));

        $this->assertListed($page, $old);
        $this->assertNotListed($page, $recent);
        $this->assertSee($page, date('Y-m-d', strtotime('-45 days')));
    }

    public function testTheUrlWinsOverThePostedRange(): void
    {
        $old = $this->bill(4, 40);
        $recent = $this->bill(4, 2);
        $this->loginAs('vet');

        $page = $this->post('invoice/index/' . strtotime('-45 days') . '/' . strtotime('-35 days'), $this->range(5, 0));

        $this->assertListed($page, $old);
        $this->assertNotListed($page, $recent);
    }

    public function testUnpaidListsOnlyIncompleteAndOnHoldBills(): void
    {
        $bills = [];
        foreach ([0, 1, 2, 3, 4, 5, 6, 7] as $status) {
            $bills[$status] = $this->bill($status, 2);
        }
        $this->loginAs('vet');

        $page = $this->post('invoice/index', $this->range(5, 0) + ['unpaid' => 'on']);

        $this->assertListed($page, $bills[3]);
        $this->assertListed($page, $bills[6]);
        foreach ([0, 1, 2, 4, 5, 7] as $status) {
            $this->assertNotListed($page, $bills[$status], 'status ' . $status);
        }
        // positive control: without the filter all are listed
        $all = $this->post('invoice/index', $this->range(5, 0));
        foreach ($bills as $status => $id) {
            $this->assertListed($all, $id, 'status ' . $status . ' without filter');
        }
    }

    public function testModifiedListsOnlyModifiedBills(): void
    {
        $modified = $this->bill(4, 2, ['modified' => 1]);
        $plain = $this->bill(4, 2);
        $this->loginAs('admin');

        $page = $this->post('invoice/index', $this->range(5, 0) + ['modified' => 'on']);

        $this->assertListed($page, $modified);
        $this->assertNotListed($page, $plain);
        $this->assertListed($this->post('invoice/index', $this->range(5, 0)), $plain);
    }

    public function testModifiedAndUnpaidCanBeCombined(): void
    {
        $both = $this->bill(3, 2, ['modified' => 1]);
        $onlyModified = $this->bill(4, 2, ['modified' => 1]);
        $onlyUnpaid = $this->bill(3, 2);
        $this->loginAs('admin');

        $page = $this->post('invoice/index', $this->range(5, 0) + ['modified' => 'on', 'unpaid' => 'on']);

        $this->assertListed($page, $both);
        $this->assertNotListed($page, $onlyModified);
        $this->assertNotListed($page, $onlyUnpaid);
    }

    public function testSoftDeletedBillsNeverAppear(): void
    {
        $deleted = $this->bill(4, 2, ['deleted_at' => Factory::raw('NOW()')]);
        $kept = $this->bill(4, 2);
        $this->loginAs('vet');

        $page = $this->post('invoice/index', $this->range(5, 0));

        $this->assertNotListed($page, $deleted);
        $this->assertListed($page, $kept);
    }

    public function testTheRestrictBillsSettingLimitsTheRowsNewestFirst(): void
    {
        Factory::setting('RestrictBills', '2');
        $oldest = $this->bill(4, 200, ['created_at' => $this->daysAgo(200, 8)]);
        $middle = $this->bill(4, 200, ['created_at' => $this->daysAgo(200, 10)]);
        $newest = $this->bill(4, 200, ['created_at' => $this->daysAgo(200, 12)]);
        $this->loginAs('vet');

        $page = $this->post('invoice/index', $this->range(200, 200));

        $this->assertListed($page, $newest);
        $this->assertListed($page, $middle);
        $this->assertNotListed($page, $oldest, 'cut off by the limit of 2');
        // positive control: without the setting all three come
        Factory::setting('RestrictBills', '0');
        $page = $this->post('invoice/index', $this->range(200, 200));
        $this->assertListed($page, $oldest);
    }

    public function testTheRowShowsTheInvoiceNumberTheAmountAndTheOwner(): void
    {
        $owner = Factory::owner(['last_name' => 'Overviewowner']);
        $bill = Factory::bill($owner, 4, [
            'total_brut' => 123.45, 'cash' => 123.45, 'invoice_id' => 77, 'invoice_date' => Factory::raw('NOW()'),
            'created_at' => $this->daysAgo(1),
        ]);
        $this->loginAs('vet');

        $page = $this->get('invoice');

        $this->assertListed($page, $bill);
        $this->assertSee($page, 'Overviewowner');
        $this->assertSee($page, date('Y') . '00077');
        $this->assertSee($page, '123.45');
    }

    public function testOnlyAdminsGetTheEditLink(): void
    {
        $bill = $this->bill(4, 1);
        $this->loginAs('vet');
        $this->assertStringNotContainsString('admin_invoice/edit_bill/' . $bill, (string) $this->get('invoice')->getBody());

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStringContainsString('admin_invoice/edit_bill/' . $bill, (string) $this->get('invoice')->getBody());
    }
}
