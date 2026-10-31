<?php

declare(strict_types=1);

/**
 * T369  Source: application/models/Register_in_model.php
 *
 * Rules in plain words:
 *  - Register_in_model::get_last_net_brut(pid) = [in_price, cat_price, 'd-m-Y'] of the delivery slip with the
 *    latest regdate (not the highest id); a NULL cat_price becomes 0; no slip at all = [0, 0, 0].
 *  - date_lookup(from, to) lists register_in rows with created_at strictly after "from 00:00" and strictly
 *    before "to 23:59" (so a row at exactly 00:00:00 of the first day, or after 23:59:00 of the last day, is missed).
 *  - get_delivery(date) lists the rows of the slips with that regdate.
 */
final class RegisterInDeliveryModelsTest extends CodeIgniterDatabaseTestCase
{
    private $registerIn;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->registerIn = $this->model('Register_in_model', 'register_in_t369');
    }

    private function slip(string $regdate): int
    {
        return Factory::insert('delivery_slip', [
            'vet' => Fixtures::USER_VET,
            'note' => '',
            'regdate' => $regdate,
            'location' => Fixtures::LOCATION_PRACTICE,
            'updated_at' => '2017-01-01 00:00:00',
            'created_at' => '2017-01-01 00:00:00',
        ]);
    }

    private function line(int $product, int $slip, float $in, ?float $cat, string $createdAt, array $extra = []): int
    {
        return Factory::insert('register_in', $extra + [
            'product' => $product,
            'volume' => 5,
            'eol' => '2019-01-01',
            'in_price' => $in,
            'cat_price' => $cat,
            'lotnr' => Factory::unique('LOT'),
            'supplier' => 'Supp',
            'delivery_slip' => $slip,
            'created_at' => $createdAt,
        ]);
    }

    // ---------------------------------------------------------------- get_last_net_brut

    public function testLastNetBrutTakesTheSlipWithTheLatestRegdateNotTheLatestId(): void
    {
        $product = Factory::product();
        $late = $this->slip('2017-03-05');
        $early = $this->slip('2017-01-10');
        // inserted late slip first, so the early slip has the higher register_in id
        $this->line($product, $late, 5.50, 7.25, '2017-03-05 10:00:00');
        $this->line($product, $early, 4.00, 6.00, '2017-01-10 10:00:00');

        $result = $this->registerIn->get_last_net_brut($product);

        $this->assertCount(3, $result);
        $this->assertEqualsWithDelta(5.50, (float) $result[0], 0.001);
        $this->assertEqualsWithDelta(7.25, (float) $result[1], 0.001);
        $this->assertSame('05-03-2017', $result[2]);
    }

    public function testLastNetBrutTurnsANullCatalogPriceIntoZero(): void
    {
        $product = Factory::product();
        $this->line($product, $this->slip('2017-03-05'), 5.50, null, '2017-03-05 10:00:00');

        $result = $this->registerIn->get_last_net_brut($product);

        $this->assertEqualsWithDelta(5.50, (float) $result[0], 0.001);
        $this->assertSame(0, $result[1]);
        $this->assertSame('05-03-2017', $result[2]);
    }

    public function testLastNetBrutOfAProductWithoutDeliveriesIsAllZero(): void
    {
        $product = Factory::product();

        $this->assertSame([0, 0, 0], $this->registerIn->get_last_net_brut($product));
    }

    public function testLastNetBrutIgnoresOtherProducts(): void
    {
        $product = Factory::product();
        $other = Factory::product();
        $this->line($other, $this->slip('2017-03-05'), 9.99, 9.99, '2017-03-05 10:00:00');

        $this->assertSame([0, 0, 0], $this->registerIn->get_last_net_brut($product));
    }

    // ---------------------------------------------------------------- date_lookup

    public function testDateLookupIsStrictAtBothEndsOfTheRange(): void
    {
        $product = Factory::product(['name' => Factory::unique('DateLookup')]);
        $slip = $this->slip('2017-06-10');
        $this->line($product, $slip, 1.0, null, '2017-06-09 23:59:59', ['lotnr' => 'before']);
        $this->line($product, $slip, 1.0, null, '2017-06-10 00:00:00', ['lotnr' => 'first-midnight']);
        $this->line($product, $slip, 1.0, null, '2017-06-10 00:00:01', ['lotnr' => 'first-second']);
        $this->line($product, $slip, 1.0, null, '2017-06-12 23:58:59', ['lotnr' => 'last-inside']);
        $this->line($product, $slip, 1.0, null, '2017-06-12 23:59:30', ['lotnr' => 'last-late']);
        $this->line($product, $slip, 1.0, null, '2017-06-13 00:00:00', ['lotnr' => 'after']);

        $rows = $this->registerIn->date_lookup('2017-06-10', '2017-06-12');
        $lots = [];
        foreach ($rows as $r) {
            if ($r['name'] === $this->ci->db->where('id', $product)->get('products')->row()->name) {
                $lots[] = $r['lotnr'];
            }
        }
        sort($lots);

        // KNOWN BUG: "first-midnight" (00:00:00 of the first day) and "last-late" (after 23:59:00 of the
        // last day) belong to the requested days but are not returned — decide in CI4 whether to keep
        $this->assertSame(['first-second', 'last-inside'], $lots);
    }

    public function testDateLookupJoinsProductAndOptionalWholesaleBruto(): void
    {
        $wh = Factory::wholesale(['bruto' => 12.34]);
        $linked = Factory::product(['name' => Factory::unique('Linked'), 'wholesale' => $wh, 'unit_buy' => 'ml', 'btw_buy' => 6, 'btw_sell' => 21]);
        $plain = Factory::product(['name' => Factory::unique('Plain')]);
        $slip = $this->slip('2017-07-01');
        $this->line($linked, $slip, 3.0, null, '2017-07-01 12:00:00', ['supplier' => 'S1', 'volume' => 2.5]);
        $this->line($plain, $slip, 4.0, null, '2017-07-01 12:00:00');

        $byName = [];
        foreach ($this->registerIn->date_lookup('2017-07-01', '2017-07-01') as $r) {
            $byName[$r['name']] = $r;
        }
        $names = array_map(fn ($id) => $this->ci->db->where('id', $id)->get('products')->row()->name, [$linked, $plain]);

        $this->assertEqualsWithDelta(12.34, (float) $byName[$names[0]]['bruto'], 0.001);
        $this->assertSame('ml', $byName[$names[0]]['unit_buy']);
        $this->assertSame('S1', $byName[$names[0]]['supplier']);
        $this->assertEqualsWithDelta(2.5, (float) $byName[$names[0]]['volume'], 0.001);
        $this->assertSame('2017-07-01', $byName[$names[0]]['regdate']);
        $this->assertNull($byName[$names[1]]['bruto'], 'LEFT JOIN: no wholesale link gives NULL');
    }

    // ---------------------------------------------------------------- get_delivery

    public function testGetDeliveryListsOnlyTheLinesOfSlipsWithThatRegdate(): void
    {
        $product = Factory::product(['name' => Factory::unique('Deliv')]);
        $slipA = $this->slip('2017-08-15');
        $slipB = $this->slip('2017-08-16');
        $this->line($product, $slipA, 2.0, null, '2017-08-15 09:00:00', ['lotnr' => 'LOT-A', 'volume' => 3]);
        $this->line($product, $slipB, 2.0, null, '2017-08-16 09:00:00', ['lotnr' => 'LOT-B']);

        $rows = $this->registerIn->get_delivery('2017-08-15');

        $this->assertCount(1, $rows);
        $this->assertSame('LOT-A', $rows[0]['lotnr']);
        $this->assertSame('2017-08-15', $rows[0]['regdate']);
        $this->assertEqualsWithDelta(3.0, (float) $rows[0]['volume'], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $rows[0]['in_price'], 0.001);
        $this->assertSame([], $this->registerIn->get_delivery('2017-08-17'));
    }
}
