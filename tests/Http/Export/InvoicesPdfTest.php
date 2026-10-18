<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T265 - export/pdf/{from}/{to} (Export::pdf, admin only): merge the stored invoice PDFs.
 *
 * Rules: for every bill with invoice_id and invoice_date inside the window the file
 * data/stored/.invoices/{Y}/{W}/bill_{year}{5 digit id}.pdf (Y and W from the invoice date) must exist.
 * When all exist the merged PDF is written to data/stored/.invoices/list{from}_{to}.pdf and sent inline.
 * When one is missing the response is only a warning with a link to invoice/get_bill/{bill id}.
 *
 * The stored PDFs are tiny hand-written one-page PDFs in the year 2016 (no other test uses that folder);
 * everything created here is removed in tearDown.
 */
final class InvoicesPdfTest extends HttpTestCase
{
    private string $root;
    /** @var list<string> files to delete */
    private array $files = [];
    /** @var list<string> directories that did not exist before */
    private array $dirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 3) . '/data/stored/.invoices/';
        $this->loginAs('admin');
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        foreach (array_reverse($this->dirs) as $dir) {
            @rmdir($dir);
        }
        parent::tearDown();
    }

    private function tinyPdf(): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 4\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";

        return $pdf;
    }

    /** Stores a PDF for an invoice, remembering what has to be cleaned up. */
    private function storePdf(int $invoiceId, string $invoiceDate): void
    {
        $time = strtotime($invoiceDate);
        $dir = $this->root . date('Y', $time) . '/' . date('W', $time) . '/';
        foreach ([$this->root . date('Y', $time) . '/', $dir] as $d) {
            if (!is_dir($d)) {
                mkdir($d, 0777, true);
                chmod($d, 0777);
                $this->dirs[] = $d;
            }
        }
        $file = $dir . 'bill_' . date('Y', $time) . str_pad((string) $invoiceId, 5, '0', STR_PAD_LEFT) . '.pdf';
        file_put_contents($file, $this->tinyPdf());
        chmod($file, 0666);
        $this->files[] = $file;
    }

    public function testWhenEveryPdfExistsOneMergedPdfIsReturnedAndWritten(): void
    {
        $owner = Factory::owner();
        Factory::bill($owner, 4, ['invoice_id' => 41, 'invoice_date' => '2016-02-10 10:00:00']);
        Factory::bill($owner, 4, ['invoice_id' => 42, 'invoice_date' => '2016-02-11 10:00:00']);
        $this->storePdf(41, '2016-02-10 10:00:00');
        $this->storePdf(42, '2016-02-11 10:00:00');
        $list = $this->root . 'list2016-02-01_2016-02-28.pdf';
        $this->files[] = $list;
        @unlink($list);

        $response = $this->get('export/pdf/2016-02-01/2016-02-28');

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('inline', $response->getHeaderLine('Content-Disposition'));
        $body = (string) $response->getBody();
        $this->assertStringStartsWith('%PDF', $body);
        $this->assertFileExists($list);
        $this->assertSame(file_get_contents($list), $body, 'the response is the written file');
        $this->assertSame(2, preg_match_all('#/Type\s*/Page\b(?!s)#', $body), 'one page per invoice');
    }

    public function testAMissingPdfGivesAWarningWithALinkToGenerateIt(): void
    {
        $owner = Factory::owner();
        $present = Factory::bill($owner, 4, ['invoice_id' => 43, 'invoice_date' => '2016-03-10 10:00:00']);
        $missing = Factory::bill($owner, 4, ['invoice_id' => 44, 'invoice_date' => '2016-03-11 10:00:00']);
        $this->storePdf(43, '2016-03-10 10:00:00');
        $list = $this->root . 'list2016-03-01_2016-03-31.pdf';
        $this->files[] = $list;
        @unlink($list);

        $response = $this->get('export/pdf/2016-03-01/2016-03-31');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('invoice/get_bill/' . $missing . "'", $body);
        $this->assertStringContainsString('bill_201600044.pdf', $body);
        $this->assertStringNotContainsString('invoice/get_bill/' . $present . "'", $body);
        $this->assertStringNotContainsString('%PDF', $body);
        $this->assertFileDoesNotExist($list, 'nothing is merged while a pdf is missing');
    }

    public function testBillsWithoutInvoiceNumberOrOutsideTheWindowAreNotRequired(): void
    {
        $owner = Factory::owner();
        Factory::bill($owner, 4, ['invoice_id' => 45, 'invoice_date' => '2016-04-10 10:00:00']);
        Factory::bill($owner, 1, ['invoice_date' => '2016-04-12 10:00:00']);                       // no invoice number
        Factory::bill($owner, 4, ['invoice_id' => 46, 'invoice_date' => '2016-05-12 10:00:00']);   // other month, no pdf
        $this->storePdf(45, '2016-04-10 10:00:00');
        $list = $this->root . 'list2016-04-01_2016-04-30.pdf';
        $this->files[] = $list;

        $response = $this->get('export/pdf/2016-04-01/2016-04-30');

        $this->assertStringStartsWith('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
    }

    #[Group('known-bug')]
    public function testAWindowWithoutInvoicesIsAServerError(): void
    {
        // KNOWN BUG: get_all() returns false and $list is never defined, merge_pdf() gets null (TypeError) — decide in CI4 whether to keep
        $this->assertStatus($this->get('export/pdf/2010-01-01/2010-01-02'), 500);
    }

    public function testOnlyAdminsMayExport(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export/pdf/2016-02-01/2016-02-28');

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('%PDF', (string) $response->getBody());
    }
}
