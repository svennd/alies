<?php

declare(strict_types=1);

/**
 * T148 - Invoice::store_bill_msg: the posted msg and msg_invoice are stored on the bill (no
 * output; the bill page calls it in the background). Other bills are not touched.
 */
final class StoreBillMsgTest extends HttpTestCase
{
    public function testTheMessagesAreStoredOnTheBill(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 1, ['msg' => 'old', 'msg_invoice' => 'old']);
        $other = Factory::bill($owner, 1, ['msg' => 'keep', 'msg_invoice' => 'keep']);
        $this->loginAs('vet');

        $response = $this->post('invoice/store_bill_msg/' . $bill, ['msg' => 'intern', 'msg_invoice' => 'voor de klant']);

        $this->assertStatus($response, 200);
        $this->assertSame('', trim((string) $response->getBody()));
        $row = $this->find('bills', $bill);
        $this->assertSame('intern', $row['msg']);
        $this->assertSame('voor de klant', $row['msg_invoice']);
        $this->assertSame('keep', $this->find('bills', $other)['msg']);
        $this->assertSame('keep', $this->find('bills', $other)['msg_invoice']);
    }

    public function testTheMessagesAppearOnTheBillPage(): void
    {
        $owner = Factory::owner();
        Factory::event(Factory::pet($owner), 0, 0);
        $bill = Factory::bill($owner, 1);
        $this->loginAs('vet');
        $this->post('invoice/store_bill_msg/' . $bill, ['msg' => 'zichtbaar-intern', 'msg_invoice' => 'zichtbaar-factuur']);

        $page = $this->get('invoice/get_bill/' . $bill);

        $this->assertSee($page, 'zichtbaar-intern');
        $this->assertSee($page, 'zichtbaar-factuur');
    }

    public function testAnonymousVisitorsCannotStoreMessages(): void
    {
        $bill = Factory::bill(Factory::owner(), 1, ['msg' => 'old']);

        $this->post('invoice/store_bill_msg/' . $bill, ['msg' => 'hacked', 'msg_invoice' => 'x']);

        $this->assertSame('old', $this->find('bills', $bill)['msg']);
        $this->loginAs('vet');
        $this->post('invoice/store_bill_msg/' . $bill, ['msg' => 'ok', 'msg_invoice' => 'x']);
        $this->assertSame('ok', $this->find('bills', $bill)['msg']);
    }
}
