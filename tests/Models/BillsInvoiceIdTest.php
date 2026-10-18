<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T156 - Bills_model::set_invoice_id(bill_id, use_transaction).
 * Source: application/models/Bills_model.php
 *
 * invoice_id = (max invoice_id of all bills - soft-deleted ones included - whose created_at is in the
 * CURRENT year) + 1, and invoice_date = now. A bill that already has an invoice_id is not touched.
 * The seed has one bill with invoice_id 1 (7001, three days old); the tests plant a bill with a high
 * number in the current year so that the next number does not depend on it.
 */
final class BillsInvoiceIdTest extends CodeIgniterDatabaseTestCase
{
    private int $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owner = Factory::owner();
    }

    private function row(int $bill): array
    {
        return $this->ci->db->where('id', $bill)->get('bills')->row_array();
    }

    public function testFirstBillGetsMaxPlusOneAndAnInvoiceDate(): void
    {
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500, 'invoice_date' => Factory::raw('NOW()')]);
        $bill = Factory::bill($this->owner, BILL_UNPAID);
        $before = time();

        $this->model('Bills_model', 'bills_inv')->set_invoice_id($bill);

        $row = $this->row($bill);
        $this->assertSame(501, (int) $row['invoice_id']);
        $this->assertNotNull($row['invoice_date']);
        // invoice_date is written from the PHP clock; allow the clock difference of a test run
        $this->assertEqualsWithDelta($before, strtotime($row['invoice_date']), 5);
    }

    public function testCallingItAgainDoesNotChangeTheNumberOrTheDate(): void
    {
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500, 'created_at' => Factory::raw('NOW()')]);
        $bill = Factory::bill($this->owner, BILL_UNPAID);
        $model = $this->model('Bills_model', 'bills_inv2');

        $model->set_invoice_id($bill);
        $this->ci->db->where('id', $bill)->update('bills', ['invoice_date' => '2020-01-01 10:00:00']);
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 777]);
        $model->set_invoice_id($bill);
        $model->set_invoice_id($bill, false);

        $row = $this->row($bill);
        $this->assertSame(501, (int) $row['invoice_id']);
        $this->assertSame('2020-01-01 10:00:00', $row['invoice_date']);
    }

    public function testConsecutiveBillsGetConsecutiveNumbers(): void
    {
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500]);
        $a = Factory::bill($this->owner, BILL_UNPAID);
        $b = Factory::bill($this->owner, BILL_UNPAID);
        $model = $this->model('Bills_model', 'bills_inv3');

        $model->set_invoice_id($a, false);
        $model->set_invoice_id($b, false);

        $this->assertSame(501, (int) $this->row($a)['invoice_id']);
        $this->assertSame(502, (int) $this->row($b)['invoice_id']);
    }

    public function testSoftDeletedBillsStillCountTowardsTheMaximum(): void
    {
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500]);
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 900, 'deleted_at' => Factory::raw('NOW()')]);
        $bill = Factory::bill($this->owner, BILL_UNPAID);

        $this->model('Bills_model', 'bills_inv4')->set_invoice_id($bill);

        $this->assertSame(901, (int) $this->row($bill)['invoice_id']);
    }

    public function testOnlyBillsCreatedInTheCurrentYearDefineTheSequence(): void
    {
        // a high number from an earlier year must not matter ...
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 9000, 'created_at' => (date('Y') - 1) . '-06-15 12:00:00']);
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500, 'created_at' => date('Y') . '-01-01 12:00:00']);
        $bill = Factory::bill($this->owner, BILL_UNPAID);

        $this->model('Bills_model', 'bills_inv5')->set_invoice_id($bill);

        $this->assertSame(501, (int) $this->row($bill)['invoice_id']);
    }

    #[Group('known-bug')]
    public function testABillFromAnEarlierYearIsNumberedInTheCurrentYearsSequence(): void
    {
        Factory::bill($this->owner, BILL_PAID, ['invoice_id' => 500, 'created_at' => date('Y') . '-01-01 12:00:00']);
        $old = Factory::bill($this->owner, BILL_UNPAID, ['created_at' => (date('Y') - 3) . '-03-03 12:00:00']);

        $this->model('Bills_model', 'bills_inv6')->set_invoice_id($old);

        // KNOWN BUG (spec said otherwise): the sequence is looked up for date('Y'), not for the year of the
        // bill's own created_at, so an old bill continues this year's numbers (501), and the same
        // number can exist in two different years' sequences.
        $row = $this->row($old);
        $this->assertSame(501, (int) $row['invoice_id']);
        $this->assertSame(date('Y'), substr($row['invoice_date'], 0, 4));
    }

    public function testNoBillOfTheYearYetStartsAtOneOnlyWhenNoneExists(): void
    {
        // control for the COALESCE: remove the seeded invoice of this year, the first number is 1
        $this->ci->db->where('invoice_id IS NOT NULL', null, false)->update('bills', ['invoice_id' => null]);
        $bill = Factory::bill($this->owner, BILL_UNPAID);

        $this->model('Bills_model', 'bills_inv7')->set_invoice_id($bill);

        $this->assertSame(1, (int) $this->row($bill)['invoice_id']);
    }
}
