<?php

declare(strict_types=1);

/**
 * T152 - Bills_model::get_open_bills(owner, current).
 * Source: application/models/Bills_model.php
 *
 * Open means status DRAFT, PENDING, UNPAID, INCOMPLETE, OVERDUE or ONHOLD. PAID, HISTORICAL and INVALID are
 * not open. The current bill and soft-deleted bills are left out, other owners never show up, and the
 * result is false when nothing is left.
 */
final class BillsOpenBillsTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
    }

    public function testReturnsEveryOpenStatusAndNothingElse(): void
    {
        $owner = Factory::owner();
        $open = [];
        foreach ([BILL_DRAFT, BILL_PENDING, BILL_UNPAID, BILL_INCOMPLETE, BILL_OVERDUE, BILL_ONHOLD] as $status) {
            $open[$status] = Factory::bill($owner, $status);
        }
        Factory::bill($owner, BILL_PAID);
        Factory::bill($owner, BILL_HISTORICAL);
        Factory::bill($owner, BILL_INVALID);

        $model = $this->model('Bills_model', 'bills_open');
        $result = $model->get_open_bills($owner);

        $this->assertIsArray($result);
        $ids = array_map('intval', array_column($result, 'id'));
        sort($ids);
        $expected = array_values($open);
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    public function testCurrentBillSoftDeletedAndOtherOwnersAreLeftOut(): void
    {
        $owner = Factory::owner();
        $other = Factory::owner();
        $current = Factory::bill($owner, BILL_PENDING);
        $kept = Factory::bill($owner, BILL_UNPAID);
        Factory::bill($owner, BILL_UNPAID, ['deleted_at' => Factory::raw('NOW()')]);
        $foreign = Factory::bill($other, BILL_UNPAID);

        $model = $this->model('Bills_model', 'bills_open2');

        $ids = array_map('intval', array_column($model->get_open_bills($owner, $current), 'id'));
        $this->assertSame([$kept], $ids);

        // positive control: without excluding, the current bill is there too
        $ids = array_map('intval', array_column($model->get_open_bills($owner), 'id'));
        sort($ids);
        $this->assertSame([$current, $kept], $ids);

        $ids = array_map('intval', array_column($model->get_open_bills($other), 'id'));
        $this->assertSame([$foreign], $ids);
    }

    public function testReturnsFalseWhenNothingIsOpen(): void
    {
        $owner = Factory::owner();
        Factory::bill($owner, BILL_PAID);
        $only = Factory::bill($owner, BILL_PENDING);

        $model = $this->model('Bills_model', 'bills_open3');

        $this->assertFalse($model->get_open_bills($owner, $only));
        $this->assertFalse($model->get_open_bills(Factory::owner()));
    }
}
