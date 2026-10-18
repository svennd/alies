<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T155 - Bills_model::calculate_bill(bill_id, bill_status).
 * Source: application/models/Bills_model.php, Events_model::get_all_items
 *
 * Sums price_net of all product and procedure lines of the events on the bill per VAT rate, stores
 * total_net, BTW_0/BTW_6/BTW_21 (the NET amounts per rate, not the VAT) and total_brut
 * (= net + number_format(BTW_6*0.06,2) + number_format(BTW_21*0.21,2)), and turns a DRAFT status into
 * PENDING. It returns [net, brut, ["0"=>..,"6"=>..,"21"=>..]].
 */
final class BillsCalculateTest extends CodeIgniterDatabaseTestCase
{
    private int $owner;
    private int $pet;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owner = Factory::owner();
        $this->pet = Factory::pet($this->owner);
    }

    private function bill(int $status = BILL_DRAFT): array
    {
        $bill = Factory::bill($this->owner, $status);
        $event = Factory::event($this->pet, 0, $bill);

        return [$bill, $event];
    }

    private function row(int $bill): array
    {
        return $this->ci->db->where('id', $bill)->get('bills')->row_array();
    }

    public function testMixOfRatesFillsTheColumnsAndTheReturnedShape(): void
    {
        [$bill, $event] = $this->bill();
        $second = Factory::event($this->pet, 0, $bill);
        $product = Factory::product();
        $procedure = Factory::procedure();
        // 6%: 10 * 2 = 20.00 (product) + 12.34 (procedure) = 32.34
        Factory::eventProduct($event, $product, 2.0, ['unit_price' => 10.0, 'btw' => 6]);
        Factory::eventProcedure($second, $procedure, 1.0, ['unit_price' => 12.34, 'btw' => 6]);
        // 0%: 3.33
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 3.33, 'btw' => 0]);
        // 21%: 10 (product) + 20 (procedure) = 30.00
        Factory::eventProduct($second, $product, 1.0, ['unit_price' => 10.0, 'btw' => 21]);
        Factory::eventProcedure($event, $procedure, 1.0, ['unit_price' => 20.0, 'btw' => 21]);
        // a line on an event of another bill must be ignored
        $foreign = Factory::event($this->pet, 0, Factory::bill($this->owner, BILL_DRAFT));
        Factory::eventProduct($foreign, $product, 1.0, ['unit_price' => 999.0, 'btw' => 6]);

        $model = $this->model('Bills_model', 'bills_calc');
        $result = $model->calculate_bill($bill, BILL_DRAFT);

        // net  = 3.33 + 32.34 + 30.00 = 65.67
        // VAT6 = 32.34 * 0.06 = 1.9404 -> 1.94 ; VAT21 = 30 * 0.21 = 6.30
        // brut = 65.67 + 1.94 + 6.30 = 73.91
        $this->assertEqualsWithDelta(65.67, $result[0], 0.0001);
        $this->assertEqualsWithDelta(73.91, $result[1], 0.0001);
        $this->assertSame(['0', '6', '21'], array_map('strval', array_keys($result[2])));
        $this->assertEqualsWithDelta(3.33, $result[2][0]['over'], 0.0001);
        $this->assertSame(0, $result[2][0]['calculated']);
        $this->assertEqualsWithDelta(32.34, $result[2][6]['over'], 0.0001);
        $this->assertSame('1.94', $result[2][6]['calculated']);
        $this->assertEqualsWithDelta(30.0, $result[2][21]['over'], 0.0001);
        $this->assertSame('6.30', $result[2][21]['calculated']);

        $row = $this->row($bill);
        $this->assertEqualsWithDelta(65.67, (float) $row['total_net'], 0.0001);
        $this->assertEqualsWithDelta(73.91, (float) $row['total_brut'], 0.0001);
        $this->assertEqualsWithDelta(3.33, (float) $row['BTW_0'], 0.0001);
        $this->assertEqualsWithDelta(32.34, (float) $row['BTW_6'], 0.0001);
        $this->assertEqualsWithDelta(30.0, (float) $row['BTW_21'], 0.0001);
    }

    public function testVatIsRoundedPerRate(): void
    {
        [$bill, $event] = $this->bill();
        $product = Factory::product();
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 0.25, 'btw' => 6]);
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 0.10, 'btw' => 21]);

        $result = $this->model('Bills_model', 'bills_calc2')->calculate_bill($bill, BILL_DRAFT);

        // VAT6 = 0.25 * 0.06 = 0.015 -> 0.02 ; VAT21 = 0.10 * 0.21 = 0.021 -> 0.02
        // net = 0.35 ; brut = 0.35 + 0.02 + 0.02 = 0.39  (the unrounded sum would be 0.386 -> 0.39 too,
        // so the per-rate strings are what proves the rounding)
        $this->assertSame('0.02', $result[2][6]['calculated']);
        $this->assertSame('0.02', $result[2][21]['calculated']);
        $this->assertEqualsWithDelta(0.39, $result[1], 0.0001);
        $this->assertEqualsWithDelta(0.39, (float) $this->row($bill)['total_brut'], 0.0001);
    }

    public function testDraftBecomesPendingAndOtherStatusesStay(): void
    {
        $model = $this->model('Bills_model', 'bills_calc3');
        $product = Factory::product();

        [$draft, $event] = $this->bill(BILL_DRAFT);
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 10.0, 'btw' => 6]);
        $model->calculate_bill($draft, BILL_DRAFT);
        $this->assertSame(BILL_PENDING, (int) $this->row($draft)['status']);

        [$unpaid, $event] = $this->bill(BILL_UNPAID);
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 10.0, 'btw' => 6]);
        $model->calculate_bill($unpaid, BILL_UNPAID);
        $this->assertSame(BILL_UNPAID, (int) $this->row($unpaid)['status']);

        // the status that is written follows the argument, not the stored row
        [$mixed, $event] = $this->bill(BILL_DRAFT);
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 10.0, 'btw' => 6]);
        $model->calculate_bill($mixed, BILL_ONHOLD);
        $this->assertSame(BILL_ONHOLD, (int) $this->row($mixed)['status']);
    }

    #[Group('known-bug')]
    public function testOtherRateIsCountedInNetButGetsNoVat(): void
    {
        [$bill, $event] = $this->bill();
        $product = Factory::product();
        Factory::eventProduct($event, $product, 1.0, ['unit_price' => 10.0, 'btw' => 12]);

        $result = $this->model('Bills_model', 'bills_calc4')->calculate_bill($bill, BILL_DRAFT);

        // KNOWN BUG: only 0/6/21 have a column; a 12% line is in total_net, but gets no VAT, so
        // brut = net = 10.00 and none of the BTW columns contains it.
        $this->assertEqualsWithDelta(10.0, $result[0], 0.0001);
        $this->assertEqualsWithDelta(10.0, $result[1], 0.0001);
        $row = $this->row($bill);
        $this->assertEqualsWithDelta(10.0, (float) $row['total_net'], 0.0001);
        $this->assertEqualsWithDelta(10.0, (float) $row['total_brut'], 0.0001);
        $this->assertEqualsWithDelta(0.0, (float) $row['BTW_0'] + (float) $row['BTW_6'] + (float) $row['BTW_21'], 0.0001);
    }

    #[Group('known-bug')]
    public function testVatOfOneThousandOrMoreBreaksTheGrossTotal(): void
    {
        [$bill, $event] = $this->bill();
        Factory::eventProduct($event, Factory::product(), 1.0, ['unit_price' => 5000.0, 'btw' => 21]);

        // KNOWN BUG: number_format(1050, 2) is "1,050.00" (thousands separator). Adding that string to a float
        // keeps only the leading "1" and raises a warning ("non-numeric value"). Right answer: 5000 + 1050 = 6050.00.
        $warnings = [];
        set_error_handler(static function (int $no, string $msg) use (&$warnings): bool {
            $warnings[] = $msg;

            return true;
        });
        try {
            $result = $this->model('Bills_model', 'bills_calc5')->calculate_bill($bill, BILL_DRAFT);
        } finally {
            restore_error_handler();
        }

        $this->assertEqualsWithDelta(5000.0, $result[0], 0.0001);
        $this->assertSame('1,050.00', $result[2][21]['calculated']);
        // wrong: net 5000 + 1 (the part of "1,050.00" before the comma) + 0 (no 6% lines)
        $this->assertEqualsWithDelta(5001.0, $result[1], 0.0001);
        $this->assertEqualsWithDelta(5001.0, (float) $this->row($bill)['total_brut'], 0.0001);
        $this->assertNotEmpty($warnings);

        // positive control: just below 1000 VAT it is correct. 4700 * 0.21 = 987.00 -> brut 5687.00
        [$bill2, $event2] = $this->bill();
        Factory::eventProduct($event2, Factory::product(), 1.0, ['unit_price' => 4700.0, 'btw' => 21]);
        $ok = $this->model('Bills_model', 'bills_calc5')->calculate_bill($bill2, BILL_DRAFT);
        $this->assertEqualsWithDelta(5687.0, $ok[1], 0.0001);
    }

    #[Group('known-bug')]
    public function testBillWithoutEventsThrowsATypeError(): void
    {
        $bill = Factory::bill($this->owner, BILL_DRAFT);

        // KNOWN BUG: get_all() gives false for nothing and array_map(fn, false) throws.
        $this->expectException(TypeError::class);
        $this->model('Bills_model', 'bills_calc6')->calculate_bill($bill, BILL_DRAFT);
    }

    public function testEventsWithoutLinesGiveZeroTotals(): void
    {
        [$bill] = $this->bill();

        $result = $this->model('Bills_model', 'bills_calc7')->calculate_bill($bill, BILL_DRAFT);

        $this->assertEqualsWithDelta(0.0, $result[0], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result[1], 0.0001);
        $this->assertSame(BILL_PENDING, (int) $this->row($bill)['status']);
    }
}
