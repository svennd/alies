<?php

declare(strict_types=1);

/**
 * T128 - Events_model::is_modified($event) and Bills_model::is_bill_modified($bill).
 * An event is modified when any product or procedure line has price_ori_net != 0 (NULL counts as not modified).
 * A bill is modified when any event with payment = bill id is modified.
 */
final class EventsModifiedTest extends CodeIgniterDatabaseTestCase
{
    private $events;
    private $bills;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->ci->load->model('Events_model', 'events_t128');
        $this->ci->load->model('Bills_model', 'bills_t128');
        $this->events = $this->ci->events_t128;
        $this->bills = $this->ci->bills_t128;
    }

    private function event(int $payment = 0): int
    {
        return Factory::event(Factory::pet(Factory::owner()), 0, $payment);
    }

    public function testAnEventWithoutLinesIsNotModified(): void
    {
        $this->assertFalse($this->events->is_modified($this->event()));
    }

    public function testLinesWithoutOriginalPriceAreNotModified(): void
    {
        $event = $this->event();
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED);                                  // price_ori_net NULL
        Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['price_ori_net' => 0]);

        $this->assertFalse($this->events->is_modified($event));
    }

    public function testAProductWithAnOriginalPriceMakesTheEventModified(): void
    {
        $event = $this->event();
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['price_ori_net' => 20.00]);

        $this->assertTrue($this->events->is_modified($event));
    }

    public function testAProcedureWithAnOriginalPriceMakesTheEventModified(): void
    {
        $event = $this->event();
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED, 1.0, ['price_ori_net' => 0]);
        Factory::eventProcedure($event, Fixtures::PROCEDURE_CONSULT, 1.0, ['price_ori_net' => 5.00]);

        $this->assertTrue($this->events->is_modified($event));
    }

    public function testAModifiedLineOfAnotherEventDoesNotCount(): void
    {
        $event = $this->event();
        $other = $this->event();
        Factory::eventProduct($event, Fixtures::PRODUCT_TIERED);
        Factory::eventProduct($other, Fixtures::PRODUCT_TIERED, 1.0, ['price_ori_net' => 20.00]);

        $this->assertFalse($this->events->is_modified($event));
        $this->assertTrue($this->events->is_modified($other));
    }

    public function testABillIsModifiedWhenAnyOfItsEventsIs(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 1);
        $pet = Factory::pet($owner);
        $clean = Factory::event($pet, 0, $bill);
        $changed = Factory::event($pet, 0, $bill);
        Factory::eventProduct($clean, Fixtures::PRODUCT_TIERED);
        $this->assertFalse($this->bills->is_bill_modified($bill));

        Factory::eventProcedure($changed, Fixtures::PROCEDURE_CONSULT, 1.0, ['price_ori_net' => 30.00]);

        $this->assertTrue($this->bills->is_bill_modified($bill));
    }

    public function testAModifiedEventOfAnotherBillOrNoBillDoesNotCount(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 1);
        $otherBill = Factory::bill($owner, 1);
        $pet = Factory::pet($owner);
        Factory::event($pet, 0, $bill);
        $modifiedElsewhere = Factory::event($pet, 0, $otherBill);
        $modifiedOpen = Factory::event($pet, 0, 0);
        Factory::eventProduct($modifiedElsewhere, Fixtures::PRODUCT_TIERED, 1.0, ['price_ori_net' => 20.00]);
        Factory::eventProduct($modifiedOpen, Fixtures::PRODUCT_TIERED, 1.0, ['price_ori_net' => 20.00]);

        $this->assertFalse($this->bills->is_bill_modified($bill));
        $this->assertTrue($this->bills->is_bill_modified($otherBill));
    }

    public function testABillWithoutEventsIsNotModified(): void
    {
        $this->assertFalse($this->bills->is_bill_modified(Factory::bill(Factory::owner(), 1)));
    }
}
