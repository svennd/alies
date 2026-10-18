<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T158 - counting queries of Events_model. Source: application/models/Events_model.php
 *
 *  get_contacts(date)         all/ope events with created_at >= "<date> 23:59" and <= LAST_DAY (which is
 *                             midnight at the START of the last day)
 *  get_contacts_year(date)    the same over the year, mutates the DateTime
 *  get_open_reports(user)     own events, no_history 0, report != DONE, updated in the last 7 days
 *  get_event_count(user)      events as vet or support vet, created at most 365 days ago
 *  get_current_events(admin)  last 14 days, no_history 0; non-admin: own or support events only
 *  all_bill_products(bill)    lines of all events on a bill; false for 0
 *
 * The seeded events are all from the last days and belong to vet 2; the counters use user 6 (second vet)
 * and compare with a baseline, the date-based ones use the years 2017 and 2019.
 */
final class EventsAggregatesTest extends CodeIgniterDatabaseTestCase
{
    private const VET = Fixtures::USER_VET;
    private const VET2 = Fixtures::USER_VET2;

    private int $owner;
    private int $pet;
    private Events_model $events;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owner = Factory::owner();
        $this->pet = Factory::pet($this->owner);
        // Events_model calls itself through $this->events, so the alias must be 'events'
        $this->events = $this->model('Events_model', 'events');
    }

    protected function tearDown(): void
    {
        unset($this->ci->user);
        parent::tearDown();
    }

    private function event(string $createdAt, array $extra = []): int
    {
        return Factory::event($this->pet, 0, 0, $extra + ['created_at' => $createdAt, 'updated_at' => $createdAt]);
    }

    private function ago(string $interval): string
    {
        return $this->ci->db->query('SELECT DATE_SUB(NOW(), INTERVAL ' . $interval . ') AS d')->row()->d;
    }

    // ---- get_contacts -------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testContactsOfAMonthSkipFirstDayBeforeLastMinuteAndLastDayAfterMidnight(): void
    {
        $this->event('2019-03-01 12:00:00');                         // KNOWN BUG: first day before 23:59 is out
        $this->event('2019-03-01 23:59:00');                         // in
        $this->event('2019-03-15 10:00:00', ['type' => OPERATION]);  // in, operation
        $this->event('2019-03-31 00:00:00');                         // in (== LAST_DAY at 00:00)
        $this->event('2019-03-31 12:00:00');                         // KNOWN BUG: last day after midnight is out
        $this->event('2019-02-28 23:59:00');                         // previous month
        $this->event('2019-04-02 10:00:00', ['type' => OPERATION]);  // next month

        $result = $this->events->get_contacts(new DateTime('2019-03-01'));

        // all = the 3 "in" events; ope = the one operation
        $this->assertSame(3, $result['all']);
        $this->assertSame(1, $result['ope']);
    }

    public function testContactsOfAMonthWithoutEventsAreZero(): void
    {
        $this->assertSame(['all' => 0, 'ope' => 0], $this->events->get_contacts(new DateTime('2015-03-01')));
    }

    public function testContactsOfAMonthMiddleEventsAreCountedForAnyDayOfTheMonthArgument(): void
    {
        $this->event('2019-03-10 10:00:00');
        $this->event('2019-03-20 10:00:00', ['type' => OPERATION]);

        // the day inside the month only moves the lower border: from the 15th, only the 20th is left
        $this->assertSame(['all' => 1, 'ope' => 1], $this->events->get_contacts(new DateTime('2019-03-15')));
        // positive control: from the 1st both are in
        $this->assertSame(['all' => 2, 'ope' => 1], $this->events->get_contacts(new DateTime('2019-03-01')));
    }

    // ---- get_contacts_year --------------------------------------------------------------------

    #[Group('known-bug')]
    public function testContactsOfAYearHaveTheSameBoundaryBugs(): void
    {
        $this->event('2017-01-01 12:00:00');   // KNOWN BUG: out
        $this->event('2017-01-01 23:59:00');   // in
        $this->event('2017-06-15 10:00:00');   // in
        $this->event('2017-12-31 00:00:00');   // in
        $this->event('2017-12-31 12:00:00');   // KNOWN BUG: out
        $this->event('2016-12-31 23:59:00');   // out
        $this->event('2018-01-01 00:00:00');   // out

        $this->assertSame(3, $this->events->get_contacts_year(new DateTime('2017-08-08')));
    }

    public function testContactsOfAYearChangeTheDateTime(): void
    {
        $date = new DateTime('2017-08-08');

        $this->events->get_contacts_year($date);

        $this->assertSame('2017-01-01', $date->format('Y-m-d'));
    }

    // ---- get_open_reports ---------------------------------------------------------------------

    public function testOpenReportsCountOwnUnfinishedRecentEventsOnly(): void
    {
        $baseline = (int) $this->events->get_open_reports(self::VET2);
        $recent = $this->ago('1 DAY');

        $this->event($recent, ['vet' => self::VET2, 'report' => 0]);                       // counted
        $this->event($recent, ['vet' => self::VET2, 'report' => 1]);                       // counted
        $this->event($recent, ['vet' => self::VET2, 'report' => 2]);                       // finished
        $this->event($recent, ['vet' => self::VET2, 'report' => 0, 'no_history' => 1]);    // no history
        $this->event($this->ago('8 DAY'), ['vet' => self::VET2, 'report' => 0]);           // too old (updated_at)
        $this->event($recent, ['vet' => self::VET, 'report' => 0]);                        // another vet
        $this->event($recent, ['vet' => self::VET, 'vet_support_1' => self::VET2, 'report' => 0]); // support is not counted

        // +2
        $this->assertSame($baseline + 2, (int) $this->events->get_open_reports(self::VET2));
    }

    // ---- get_event_count ----------------------------------------------------------------------

    public function testEventCountCoversVetAndSupportRolesWithinAYear(): void
    {
        $baseline = (int) $this->events->get_event_count(self::VET2);
        $now = $this->ago('0 DAY');

        $this->event($now, ['vet' => self::VET2]);                              // 1
        $this->event($now, ['vet' => self::VET, 'vet_support_1' => self::VET2]); // 2
        $this->event($now, ['vet' => self::VET, 'vet_support_2' => self::VET2]); // 3
        $this->event($this->ago('364 DAY'), ['vet' => self::VET2]);             // 4: inside the year
        $this->event($this->ago('367 DAY'), ['vet' => self::VET2]);             // older than 365 days: out
        $this->event($now, ['vet' => self::VET]);                               // other vet: out

        $this->assertSame($baseline + 4, (int) $this->events->get_event_count(self::VET2));
    }

    // ---- get_current_events -------------------------------------------------------------------

    public function testCurrentEventsAdminSeesAllVetSeesOwnAndSupportEventsOnly(): void
    {
        $recent = $this->ago('2 DAY');
        $own = $this->event($recent, ['vet' => self::VET2]);
        $support1 = $this->event($recent, ['vet' => self::VET, 'vet_support_1' => self::VET2]);
        $support2 = $this->event($recent, ['vet' => self::VET, 'vet_support_2' => self::VET2]);
        $foreign = $this->event($recent, ['vet' => self::VET]);
        $noHistory = $this->event($recent, ['vet' => self::VET2, 'no_history' => 1]);
        $old = $this->event($this->ago('15 DAY'), ['vet' => self::VET2]);

        $this->ci->user = (object) ['id' => self::VET2];
        $vetIds = array_map('intval', array_column($this->events->get_current_events(false), 'id'));
        sort($vetIds);
        $expected = [$own, $support1, $support2];
        sort($expected);
        // the seed has no event of user 6, so this is exactly the three
        $this->assertSame($expected, $vetIds);

        $adminIds = array_map('intval', array_column($this->events->get_current_events(true), 'id'));
        foreach ([$own, $support1, $support2, $foreign] as $id) {
            $this->assertContains($id, $adminIds);
        }
        $this->assertNotContains($noHistory, $adminIds);
        $this->assertNotContains($old, $adminIds);
        // the seeded open events of the last 14 days are in the admin list as well
        $this->assertContains(6001, $adminIds);
        $this->assertNotContains(6001, $vetIds);
    }

    public function testCurrentEventsCarryTheBillStatusOrNull(): void
    {
        $bill = Factory::bill($this->owner, BILL_UNPAID);
        $withBill = Factory::event($this->pet, 0, $bill, ['vet' => self::VET2]);
        $without = Factory::event($this->pet, 0, 0, ['vet' => self::VET2]);

        $rows = [];
        foreach ($this->events->get_current_events(true) as $row) {
            $rows[(int) $row['id']] = $row;
        }

        $this->assertSame(BILL_UNPAID, (int) $rows[$withBill]['bill_status']);
        $this->assertNull($rows[$without]['bill_status']);
        $this->assertSame($this->owner, (int) $rows[$without]['owner_id']);
        $this->assertSame($this->pet, (int) $rows[$without]['pet_id']);
        $this->assertSame(self::VET2, (int) $rows[$without]['vet_id']);
    }

    // ---- all_bill_products --------------------------------------------------------------------

    public function testAllBillProductsIsFalseForTheDraftId(): void
    {
        $this->assertFalse($this->events->all_bill_products(BILL_DRAFT));
    }

    public function testAllBillProductsListsTheLinesOfEveryEventOnTheBill(): void
    {
        $bill = Factory::bill($this->owner, BILL_UNPAID);
        $first = Factory::event($this->pet, 0, $bill, ['location' => Fixtures::LOCATION_PRACTICE]);
        $second = Factory::event($this->pet, 0, $bill, ['location' => Fixtures::LOCATION_CAR]);
        $other = Factory::event($this->pet, 0, Factory::bill($this->owner, BILL_UNPAID));
        $p1 = Factory::product();
        $p2 = Factory::product();
        $stock = Factory::stock($p1);
        Factory::eventProduct($first, $p1, 2.5, ['stock_id' => $stock]);
        Factory::eventProduct($first, $p2, 1.0);
        Factory::eventProduct($second, $p2, 4.0, ['stock_id' => null]);
        Factory::eventProduct($other, $p1, 99.0);
        Factory::eventProcedure($first);   // procedures are not products

        $rows = $this->events->all_bill_products($bill);

        $this->assertCount(3, $rows);
        $byKey = [];
        foreach ($rows as $row) {
            $byKey[$row['product_id'] . '/' . (int) $row['location']] = $row;
        }
        $this->assertEqualsWithDelta(2.5, (float) $byKey[$p1 . '/1']['volume'], 0.0001);
        $this->assertSame($stock, (int) $byKey[$p1 . '/1']['stock_id']);
        $this->assertEqualsWithDelta(1.0, (float) $byKey[$p2 . '/1']['volume'], 0.0001);
        $this->assertEqualsWithDelta(4.0, (float) $byKey[$p2 . '/2']['volume'], 0.0001);
        $this->assertNull($byKey[$p2 . '/2']['stock_id']);
    }

    public function testAllBillProductsOfABillWithoutLinesIsAnEmptyArray(): void
    {
        $bill = Factory::bill($this->owner, BILL_UNPAID);
        Factory::event($this->pet, 0, $bill);

        $this->assertSame([], $this->events->all_bill_products($bill));
    }
}
