<?php

declare(strict_types=1);

/**
 * T368 Liquidate_model.   Source: application/models/Liquidate_model.php
 *
 * Rules in plain words:
 *  - get_unbilled: liquidate rows without bill_id, oldest created_at first; false when there are none.
 *  - count_unbilled: number of those rows.
 *  - assign_bill(ids, bill): empty id list gives false; sets bill_id only on the listed rows that have no bill yet.
 *  - get_bill_rows(bill): the rows of one bill with unit_sell ('' when the product is unknown) and location_name,
 *    ordered by created_at then id.
 * The seed may contain liquidate rows, so tests start by emptying the table inside their own transaction.
 */
final class LiquidateModelTest extends CodeIgniterDatabaseTestCase
{
    private $liquidate;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->ci->db->query('DELETE FROM liquidate');
        $this->liquidate = $this->model('Liquidate_model', 'liquidate_t368');
    }

    private function row(string $created, array $extra = []): int
    {
        return Factory::insert('liquidate', $extra + array(
            'product_id' => Fixtures::PRODUCT_VACCINE,
            'product_name' => 'Vaccin',
            'volume' => 1.5,
            'eol' => '2026-12-31',
            'lotnr' => 'LOT1',
            'reason' => 'verlopen',
            'user' => Fixtures::USER_VET,
            'location' => Fixtures::LOCATION_PRACTICE,
            'stock_id' => 1,
            'bill_id' => null,
            'created_at' => $created,
        ));
    }

    private function billOf(int $id): ?int
    {
        $v = $this->ci->db->where('id', $id)->get('liquidate')->row()->bill_id;

        return $v === null ? null : (int) $v;
    }

    public function testGetUnbilledIsFalseWithoutRows(): void
    {
        $this->assertFalse($this->liquidate->get_unbilled());
        $this->assertSame(0, $this->liquidate->count_unbilled());

        // rows that all have a bill do not count either
        $this->row('2025-01-01 10:00:00', array('bill_id' => 5));
        $this->assertFalse($this->liquidate->get_unbilled());
        $this->assertSame(0, $this->liquidate->count_unbilled());
    }

    public function testGetUnbilledReturnsUnbilledRowsOldestFirst(): void
    {
        $late = $this->row('2025-03-03 10:00:00');
        $early = $this->row('2025-01-01 10:00:00');
        $mid = $this->row('2025-02-02 10:00:00');
        $this->row('2025-01-15 10:00:00', array('bill_id' => 9));

        $rows = $this->liquidate->get_unbilled();

        $this->assertSame(array($early, $mid, $late), array_map('intval', array_column($rows, 'id')));
        $this->assertSame(3, $this->liquidate->count_unbilled());
    }

    public function testAssignBillWithAnEmptyListIsFalseAndChangesNothing(): void
    {
        $id = $this->row('2025-01-01 10:00:00');

        $this->assertFalse($this->liquidate->assign_bill(array(), 77));
        $this->assertNull($this->billOf($id));
    }

    public function testAssignBillSetsTheBillOnTheListedUnbilledRowsOnly(): void
    {
        $a = $this->row('2025-01-01 10:00:00');
        $b = $this->row('2025-01-02 10:00:00');
        $notListed = $this->row('2025-01-03 10:00:00');
        $already = $this->row('2025-01-04 10:00:00', array('bill_id' => 5));

        $ok = $this->liquidate->assign_bill(array($a, (string) $b, $already), 77);

        $this->assertTrue($ok);
        $this->assertSame(77, $this->billOf($a));
        $this->assertSame(77, $this->billOf($b));
        $this->assertNull($this->billOf($notListed));
        $this->assertSame(5, $this->billOf($already), 'a row that already has a bill is not reassigned');
        $this->assertSame(1, $this->liquidate->count_unbilled());
    }

    public function testAssignBillStaysTrueWhenNothingWasChanged(): void
    {
        $already = $this->row('2025-01-04 10:00:00', array('bill_id' => 5));

        $this->assertTrue($this->liquidate->assign_bill(array($already), 77));
        $this->assertSame(5, $this->billOf($already));
    }

    public function testGetBillRowsReturnsTheColumnsOrderedByCreatedAtThenId(): void
    {
        $product = Factory::product(array('unit_sell' => 'ml'));
        $second = $this->row('2025-02-01 10:00:00', array('product_id' => $product, 'product_name' => 'Tweede', 'bill_id' => 88));
        $first = $this->row('2025-01-01 10:00:00', array('product_id' => $product, 'product_name' => 'Eerste', 'bill_id' => 88));
        $tieA = $this->row('2025-03-01 10:00:00', array('product_id' => $product, 'bill_id' => 88));
        $tieB = $this->row('2025-03-01 10:00:00', array('product_id' => $product, 'bill_id' => 88));
        $this->row('2025-01-01 10:00:00', array('bill_id' => 99)); // other bill
        $this->row('2025-01-01 10:00:00'); // no bill

        $rows = $this->liquidate->get_bill_rows(88);

        $this->assertSame(array($first, $second, $tieA, $tieB), array_map('intval', array_column($rows, 'id')));
        $this->assertSame(
            array('id', 'product_id', 'product_name', 'volume', 'eol', 'lotnr', 'reason', 'created_at', 'unit_sell', 'location_name'),
            array_keys($rows[0])
        );
        $this->assertSame('Eerste', $rows[0]['product_name']);
        $this->assertSame($product, (int) $rows[0]['product_id']);
        $this->assertEqualsWithDelta(1.5, (float) $rows[0]['volume'], 0.001);
        $this->assertSame('2026-12-31', $rows[0]['eol']);
        $this->assertSame('LOT1', $rows[0]['lotnr']);
        $this->assertSame('verlopen', $rows[0]['reason']);
        $this->assertSame('2025-01-01 10:00:00', $rows[0]['created_at']);
        $this->assertSame('ml', $rows[0]['unit_sell']);
        $this->assertSame('Praktijk', $rows[0]['location_name']);
    }

    public function testUnitSellIsAnEmptyStringWhenTheProductIsUnknown(): void
    {
        $this->row('2025-01-01 10:00:00', array('product_id' => 987654, 'bill_id' => 66, 'location' => 987655));

        $rows = $this->liquidate->get_bill_rows(66);

        $this->assertCount(1, $rows);
        $this->assertSame('', $rows[0]['unit_sell']);
        $this->assertNull($rows[0]['location_name']);
    }

    public function testGetBillRowsOfABillWithoutRowsIsAnEmptyArray(): void
    {
        $this->assertSame(array(), $this->liquidate->get_bill_rows(424242));
    }

    public function testAssignedRowsShowUpInGetBillRows(): void
    {
        $a = $this->row('2025-01-01 10:00:00');
        $this->liquidate->assign_bill(array($a), 55);

        $this->assertSame(array($a), array_map('intval', array_column($this->liquidate->get_bill_rows(55), 'id')));
        $this->assertFalse($this->liquidate->get_unbilled());
    }
}
