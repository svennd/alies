<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T159 - search and export queries of Events_model. Source: application/models/Events_model.php
 *
 * search_event: MySQL FULLTEXT in boolean mode on title (title mode) or title + anamnese (anamnese mode).
 * InnoDB fulltext indexes only see COMMITTED rows, and every test here runs in a rolled-back transaction,
 * so the searches run against the seeded events 6001 "Controle Rex", 6002 "Vaccinatie Mimi" / "Jaarlijkse
 * vaccinatie, alles in orde." and 6003 "Consultatie Bobby" / "Kreupel links voor." (the schema has the
 * FULLTEXT keys title, anamnese and title_2). The other queries use rows the tests create.
 */
final class EventsSearchExportTest extends CodeIgniterDatabaseTestCase
{
    private Events_model $events;
    private string $from;
    private string $to;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->events = $this->model('Events_model', 'events');
        $this->from = date('Y-m-d', strtotime('-40 days'));
        $this->to = date('Y-m-d', strtotime('+3 days'));
    }

    private function ids(array $rows): array
    {
        $ids = array_map('intval', array_column($rows, 'id'));
        sort($ids);

        return $ids;
    }

    // ---- search_event -------------------------------------------------------------------------

    public function testSeedHasTheFulltextIndexesTheSearchNeeds(): void
    {
        $indexes = $this->ci->db->query("SHOW INDEX FROM events WHERE Index_type = 'FULLTEXT'")->result_array();
        $names = array_unique(array_column($indexes, 'Key_name'));
        sort($names);

        $this->assertSame(['anamnese', 'title', 'title_2'], $names);
    }

    public function testTitleSearchOnlyLooksAtTheTitle(): void
    {
        $this->assertSame([6001], $this->ids($this->events->search_event('Controle', $this->from, $this->to)));
        // "Kreupel" is only in the anamnese of 6003: not found in title mode
        $this->assertSame([], $this->events->search_event('Kreupel', $this->from, $this->to));
        // positive control: found in anamnese mode
        $this->assertSame([6003], $this->ids($this->events->search_event('Kreupel', $this->from, $this->to, true)));
    }

    public function testAnamneseSearchLooksAtTitleAndAnamnese(): void
    {
        // 6002 has the word in title and anamnese, 6001/6003 neither
        $this->assertSame([6002], $this->ids($this->events->search_event('vaccinatie', $this->from, $this->to, true)));
        // title-only word, found through the combined index
        $this->assertSame([6003], $this->ids($this->events->search_event('Consultatie', $this->from, $this->to, true)));
    }

    public function testBooleanModeOperatorsWork(): void
    {
        $this->assertSame([6003], $this->ids($this->events->search_event('+Consultatie +Bobby', $this->from, $this->to)));
        $this->assertSame([], $this->events->search_event('+Controle -Rex', $this->from, $this->to));
        $this->assertSame([6003], $this->ids($this->events->search_event('Consult*', $this->from, $this->to)));
        // OR semantics without operators: 6001 and 6003
        $this->assertSame([6001, 6003], $this->ids($this->events->search_event('Controle Bobby', $this->from, $this->to)));
    }

    public function testSearchRespectsTheDateWindowAndNoHistory(): void
    {
        // seeded 6002 is 10 days old: a window that ends 20 days ago excludes it, a wider one has it
        $narrowTo = date('Y-m-d', strtotime('-20 days'));
        $this->assertSame([], $this->events->search_event('Vaccinatie', $this->from, $narrowTo));
        $this->assertSame([6002], $this->ids($this->events->search_event('Vaccinatie', $this->from, $this->to)));

        // no_history is checked on the real row, so an update inside the transaction hides the hit
        $this->ci->db->where('id', 6001)->update('events', ['no_history' => 1]);
        $this->assertSame([], $this->events->search_event('Controle', $this->from, $this->to));
    }

    public function testSearchResultColumnsJoinPetOwnerVetAndLocation(): void
    {
        $rows = $this->events->search_event('Controle', $this->from, $this->to);

        $this->assertCount(1, $rows);
        $this->assertSame('Controle Rex', $rows[0]['title']);
        $this->assertSame(Fixtures::PET_REX, (int) $rows[0]['pet_id']);
        $this->assertSame('Praktijk', $rows[0]['loc_name']);
        $this->assertSame(Fixtures::USER_VET, (int) $rows[0]['vet_id']);
        $this->assertSame('Victor', $rows[0]['vet_name']);
        $this->assertSame(1001, (int) $rows[0]['owner_id']);
        $this->assertSame(1, (int) $rows[0]['title_match']);
    }

    #[Group('known-bug')]
    public function testAnamneseSearchTitleMatchFlagIsHardCodedToTheWordFip(): void
    {
        $rows = $this->events->search_event('vaccinatie', $this->from, $this->to, true);

        // KNOWN BUG: the select has MATCH(title) AGAINST('fip') instead of the query, so title_match is 0
        // although "vaccinatie" is in the title of 6002.
        $this->assertSame([6002], $this->ids($rows));
        $this->assertSame(0, (int) $rows[0]['title_match']);
    }

    // ---- exports ------------------------------------------------------------------------------

    private function billingFixture(): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $e1 = Factory::event($pet);
        $e2 = Factory::event($pet);
        $other = Factory::event($pet);
        $p = Factory::product();
        $proc = Factory::procedure();

        // products: booking 1 (6%): 10 + 5.5 ; booking 2 (21%): 7 ; deleted code 3 (12%): 2 ; unknown booking 99: 1
        Factory::eventProduct($e1, $p, 1.0, ['unit_price' => 10.0, 'btw' => 6, 'booking' => 1]);
        Factory::eventProduct($e2, $p, 1.0, ['unit_price' => 5.5, 'btw' => 6, 'booking' => 1]);
        Factory::eventProduct($e2, $p, 1.0, ['unit_price' => 7.0, 'btw' => 21, 'booking' => 2]);
        Factory::eventProduct($e1, $p, 1.0, ['unit_price' => 2.0, 'btw' => 12, 'booking' => 3]);
        Factory::eventProduct($e1, $p, 1.0, ['unit_price' => 1.0, 'btw' => 0, 'booking' => 99]);
        Factory::eventProduct($other, $p, 1.0, ['unit_price' => 500.0, 'btw' => 6, 'booking' => 1]);
        // procedures: booking 2 (21%): 25 + 4 ; booking 1: 3
        Factory::eventProcedure($e1, $proc, 1.0, ['unit_price' => 25.0, 'btw' => 21, 'booking' => 2]);
        Factory::eventProcedure($e2, $proc, 2.0, ['unit_price' => 2.0, 'btw' => 21, 'booking' => 2]);
        Factory::eventProcedure($e2, $proc, 1.0, ['unit_price' => 3.0, 'btw' => 6, 'booking' => 1]);

        return [$owner, $pet, [$e1, $e2], $other, $p, $proc];
    }

    public function testBookingExportSumsPerBookingCode(): void
    {
        [, , $events] = $this->billingFixture();

        $products = [];
        foreach ($this->events->get_booking_export($events, PRODUCT) as $row) {
            $products[(string) $row['code']] = $row;
        }
        // 700000: 10 + 5.5 = 15.5 ; 701000: 7 ; 709999 (soft-deleted code is still joined): 2 ; unknown: 1 (code NULL)
        $this->assertCount(4, $products);
        $this->assertEqualsWithDelta(15.5, (float) $products['700000']['total_net'], 0.0001);
        $this->assertSame(6, (int) $products['700000']['btw']);
        $this->assertEqualsWithDelta(7.0, (float) $products['701000']['total_net'], 0.0001);
        $this->assertSame(21, (int) $products['701000']['btw']);
        $this->assertEqualsWithDelta(2.0, (float) $products['709999']['total_net'], 0.0001);
        $this->assertSame(12, (int) $products['709999']['btw']);
        $this->assertEqualsWithDelta(1.0, (float) $products['']['total_net'], 0.0001);
        $this->assertNull($products['']['btw']);

        $procedures = [];
        foreach ($this->events->get_booking_export($events, PROCEDURE) as $row) {
            $procedures[(string) $row['code']] = $row;
        }
        // 701000: 25 + 2*2 = 29 ; 700000: 3
        $this->assertCount(2, $procedures);
        $this->assertEqualsWithDelta(29.0, (float) $procedures['701000']['total_net'], 0.0001);
        $this->assertEqualsWithDelta(3.0, (float) $procedures['700000']['total_net'], 0.0001);
    }

    public function testAllItemsSumPerVatRate(): void
    {
        [, , $events] = $this->billingFixture();

        // products: 6% = 10 + 5.5 = 15.5 ; 21% = 7 ; 12% = 2 ; 0% = 1
        $products = $this->events->get_all_items($events, PRODUCT);
        $this->assertEqualsCanonicalizing([0, 6, 12, 21], array_map('intval', array_keys($products)));
        $this->assertEqualsWithDelta(15.5, $products[6], 0.0001);
        $this->assertEqualsWithDelta(7.0, $products[21], 0.0001);
        $this->assertEqualsWithDelta(2.0, $products[12], 0.0001);
        $this->assertEqualsWithDelta(1.0, $products[0], 0.0001);

        // procedures: 21% = 25 + 4 = 29 ; 6% = 3
        $procedures = $this->events->get_all_items($events, PROCEDURE);
        $this->assertEqualsCanonicalizing([6, 21], array_map('intval', array_keys($procedures)));
        $this->assertEqualsWithDelta(29.0, $procedures[21], 0.0001);
        $this->assertEqualsWithDelta(3.0, $procedures[6], 0.0001);

        // a single event only counts its own lines: e1 products 6% = 10
        $this->assertEqualsWithDelta(10.0, $this->events->get_all_items([$events[0]], PRODUCT)[6], 0.0001);
        // events without lines give an empty array
        $empty = Factory::event(Factory::pet(Factory::owner()));
        $this->assertSame([], $this->events->get_all_items([$empty], PRODUCT));
    }

    public function testPrintableItemsHaveTheColumnsTheInvoiceNeeds(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $event = Factory::event($pet);
        $product = Factory::product(['name' => 'Printproduct', 'unit_sell' => 'tablet']);
        $procedure = Factory::procedure(['name' => 'Printprocedure']);
        Factory::eventProduct($event, $product, 2.0, ['unit_price' => 4.0, 'btw' => 6]);
        Factory::eventProcedure($event, $procedure, 1.0, ['unit_price' => 30.0, 'btw' => 21, 'reduction_reason' => 'vriend']);

        $products = $this->events->get_printable_items([$event], PRODUCT);
        $procedures = $this->events->get_printable_items([$event], PROCEDURE);

        $this->assertCount(1, $products);
        $keys = array_keys($products[0]);
        sort($keys);
        $expected = ['btw', 'created_at', 'name', 'price_brut', 'price_net', 'product_id', 'reduction_reason', 'unit_price', 'unit_sell', 'volume'];
        $this->assertSame($expected, $keys);
        // net = 4 * 2 = 8.00 ; brut = 8 * 1.06 = 8.48
        $this->assertSame($product, (int) $products[0]['product_id']);
        $this->assertSame('Printproduct', $products[0]['name']);
        $this->assertSame('tablet', $products[0]['unit_sell']);
        $this->assertEqualsWithDelta(8.0, (float) $products[0]['price_net'], 0.0001);
        $this->assertEqualsWithDelta(8.48, (float) $products[0]['price_brut'], 0.0001);
        $this->assertEqualsWithDelta(4.0, (float) $products[0]['unit_price'], 0.0001);

        $this->assertCount(1, $procedures);
        $keys = array_keys($procedures[0]);
        sort($keys);
        $expected = ['btw', 'created_at', 'name', 'price_brut', 'price_net', 'procedures_id', 'reduction_reason', 'unit_price', 'volume'];
        $this->assertSame($expected, $keys);
        // net 30 ; brut 30 * 1.21 = 36.30
        $this->assertSame('Printprocedure', $procedures[0]['name']);
        $this->assertSame('vriend', $procedures[0]['reduction_reason']);
        $this->assertEqualsWithDelta(36.3, (float) $procedures[0]['price_brut'], 0.0001);
    }

    public function testProductsOfAnOwnerFollowBillsCreatedInTheWindow(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['name' => 'Fluf']);
        $billIn = Factory::bill($owner, BILL_PAID, ['created_at' => '2019-05-10 12:00:00']);
        $billOut = Factory::bill($owner, BILL_PAID, ['created_at' => '2019-07-01 12:00:00']);
        $foreignBill = Factory::bill(Factory::owner(), BILL_PAID, ['created_at' => '2019-05-10 12:00:00']);
        $product = Factory::product(['name' => 'Ownerproduct', 'unit_sell' => 'ml']);

        $eventIn = Factory::event($pet, 1, $billIn, ['created_at' => '2019-05-10 09:00:00']);
        $eventOut = Factory::event($pet, 1, $billOut);
        $eventForeign = Factory::event($pet, 1, $foreignBill);
        $eventOpen = Factory::event($pet, 0, 0);
        Factory::eventProduct($eventIn, $product, 1.5, ['created_at' => '2019-05-10 10:00:00']);
        Factory::eventProduct($eventIn, $product, 3.0, ['created_at' => '2019-05-10 11:00:00']);
        Factory::eventProduct($eventOut, $product, 9.0);
        Factory::eventProduct($eventForeign, $product, 8.0);
        Factory::eventProduct($eventOpen, $product, 7.0);

        $rows = $this->events->get_products_owner($owner, '2019-05-01', '2019-05-31');

        // newest line first: 3.0 (11:00) then 1.5 (10:00)
        $this->assertCount(2, $rows);
        $this->assertEqualsWithDelta(3.0, (float) $rows[0]['volume'], 0.0001);
        $this->assertEqualsWithDelta(1.5, (float) $rows[1]['volume'], 0.0001);
        $this->assertSame('Ownerproduct', $rows[0]['product_name']);
        $this->assertSame('ml', $rows[0]['unit_sell']);
        $this->assertSame($pet, (int) $rows[0]['pet_id']);
        $this->assertSame('Fluf', $rows[0]['pet_name']);
        $this->assertSame('2019-05-10 09:00:00', $rows[0]['event_date']);

        // positive control: a window around July gives the other bill's line
        $july = $this->events->get_products_owner($owner, '2019-06-25', '2019-07-05');
        $this->assertCount(1, $july);
        $this->assertEqualsWithDelta(9.0, (float) $july[0]['volume'], 0.0001);
        // another owner with no bills in the window
        $this->assertSame([], $this->events->get_products_owner(Factory::owner(), '2019-05-01', '2019-05-31'));
    }
}
