<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/InvoiceHelper.php';

/**
 * T147 - Invoice::get_bill/{id}/2 (INVOICE_MAIL) -> mail_bill.
 *
 * What the code does: loads the Mail library, stores the invoice pdf, attaches it, then marks
 * bills mail=1 and answers {"result":true}. The sending itself is commented out in the code.
 *
 * Why this cannot be tested to the end here: the Mail library builds a PHPMailer with the
 * smtp_* / mail_from_* config values, which are empty in the testing environment, and
 * PHPMailer throws on an empty from-address. Mail mode therefore answers 500 BEFORE anything is
 * stored or changed. That boundary is pinned; the rest needs F07 (a mail config for the testing
 * environment) and is skipped with the facts from reading the code:
 *  KNOWN BUG: mail_bill() uses an undefined $bill_id, so update(['mail' => 1], null) marks EVERY
 *  bill in the table as mailed (see the skipped test).
 * No mail is ever sent (no SMTP host is configured, the send call is commented out).
 */
final class MailBillTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        InvoiceHelper::cleanUp();
        parent::tearDown();
    }

    /** @return array{bill: int, other: int, path: string} */
    private function paidBillAndAnotherBill(): array
    {
        $owner = Factory::owner(['mail' => 'nobody@example.invalid']);
        $number = random_int(70000, 79999);
        $bill = Factory::bill($owner, 4, ['total_brut' => 10.60, 'invoice_id' => $number, 'invoice_date' => Factory::raw('NOW()')]);
        Factory::event(Factory::pet($owner), 1, $bill);
        $other = Factory::bill(Factory::owner(), 1);
        $path = InvoiceHelper::register(InvoiceHelper::pdfPath(date('Y-m-d H:i:s'), 'bill_' . date('Y') . str_pad((string) $number, 5, '0', STR_PAD_LEFT)));

        return compact('bill', 'other', 'path');
    }

    public function testMailModeStopsWithAServerErrorWhenNoMailServerIsConfigured(): void
    {
        $s = $this->paidBillAndAnotherBill();
        $this->loginAs('vet');

        $response = $this->get('invoice/get_bill/' . $s['bill'] . '/2');

        $this->assertStatus($response, 500);
        $this->assertStringNotContainsString('"result"', (string) $response->getBody());
        $this->assertSame('0', (string) $this->find('bills', $s['bill'])['mail'], 'not marked as mailed');
        $this->assertSame('0', (string) $this->find('bills', $s['other'])['mail']);
        $this->assertFileDoesNotExist($s['path'], 'the pdf is only made after the mail library loaded');
    }

    public function testThePlainPageOfTheSameBillStillWorks(): void
    {
        $s = $this->paidBillAndAnotherBill();
        $this->loginAs('vet');

        $page = $this->get('invoice/get_bill/' . $s['bill']);

        $this->assertStatus($page, 200);
        $this->assertSame('0', (string) $this->find('bills', $s['bill'])['mail']);
    }

    #[Group('known-bug')]
    public function testMailModeAnswersResultTrueAndMarksEveryBillAsMailed(): void
    {
        // KNOWN BUG: the update has no bill id, so every bill gets mail=1 - decide in CI4 whether to keep
        $this->markTestSkipped('needs F07 stub: mail config (smtp_host, mail_from_email) for the testing environment; the Mail library throws without it');
    }
}
