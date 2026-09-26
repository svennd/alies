<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once APPPATH . 'libraries/Typst_renderer.php';
require_once APPPATH . 'libraries/Document_settings.php';
require_once APPPATH . 'libraries/Document_service.php';
require_once APPPATH . 'models/Document_data_model.php';

final class TypstDocumentTest extends TestCase
{
    private $ci;
    private Document_service $service;
    private string $root;

    protected function setUp(): void
    {
        $this->ci = get_instance();
        $this->service = new Document_service();
        $this->root = sys_get_temp_dir() . '/typst-test-' . bin2hex(random_bytes(5));
        $this->ci->typst_renderer = new Typst_renderer(['temporary_path' => $this->root]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->root . '/render.lock')) unlink($this->root . '/render.lock');
        if (is_dir($this->root)) rmdir($this->root);
    }

    public function testAllThreeTemplatesCompileWithNotoSans(): void
    {
        $settings = $this->ci->document_settings->defaults();
        $settings['branding']['name'] = 'Dierenpraktijk Voorbeeld';
        foreach (Document_settings::TYPES as $type) {
            $pdf = $this->service->pdf($type, $this->service->sample($type), $settings);
            self::assertStringStartsWith('%PDF-', $pdf);
            self::assertStringContainsString('NotoSans', $pdf);
            self::assertSame(['render.lock'], array_values(array_diff(scandir($this->root), ['.', '..'])));
            // Optional QA output, deliberately outside the public document store.
            if ($dir = getenv('DOCUMENT_QA_DIR')) { file_put_contents($dir . '/' . $type . '.pdf', $pdf); }
        }
    }

    public function testReminderBatchesHaveExactlyOnePagePerLetter(): void
    {
        $settings = $this->ci->document_settings->defaults();
        $row = $this->service->sample('reminder')['letters'][0];
        foreach ([1, 5, 10] as $count) {
            $rows = [];
            for ($i = 0; $i < $count; $i++) { $rows[] = array_replace($row, ['pet' => 'Pet ' . ($i + 1)]); }
            $start = microtime(true);
            $pdf = $this->service->pdf('reminder', ['letters' => $rows], $settings);
            // Typst also asserts the final page count and each letter's physical page.
            self::assertStringStartsWith('%PDF-', $pdf);
            if ($dir = getenv('DOCUMENT_QA_DIR')) {
                file_put_contents($dir . '/reminders-' . $count . '.pdf', $pdf);
                file_put_contents($dir . '/timings.txt', "$count: " . round(microtime(true) - $start, 3) . " seconds\n", FILE_APPEND);
            }
        }
    }

    public function testOverflowIsRejectedWithoutLeavingTemporaryFiles(): void
    {
        $settings = $this->ci->document_settings->defaults();
        $settings['reminder']['body'] = str_repeat('This letter is too long. ', 300);
        try {
            $this->service->pdf('reminder', $this->service->sample('reminder'), $settings);
            self::fail('Overflow must not be delivered');
        } catch (RuntimeException $error) { self::assertSame('overflow:1', $error->getMessage()); }
        self::assertSame(['render.lock'], array_values(array_diff(scandir($this->root), ['.', '..'])));
    }

    public function testMetacharactersAreLiteralData(): void
    {
        $settings = $this->ci->document_settings->defaults();
        $settings['reminder']['body'] = '#read("/etc/passwd") *literal* $math$ <label> @name';
        $pdf = $this->service->pdf('reminder', $this->service->sample('reminder'), $settings);
        self::assertStringStartsWith('%PDF-', $pdf);
        if ($dir = getenv('DOCUMENT_QA_DIR')) file_put_contents($dir . '/literal.pdf', $pdf);
    }

    public function testLongTablesAndPaymentQr(): void
    {
        $settings = $this->ci->document_settings->defaults();
        $settings['branding']['name'] = 'Dierenpraktijk Voorbeeld';
        $settings['overview']['show_location'] = true;
        $settings['overview']['show_due'] = true;
        $invoice = $this->service->sample('invoice');
        $invoice['lines'] = [];
        for ($i = 1; $i <= 65; $i++) $invoice['lines'][] = ['group' => 'Zoë-Milo', 'description' => 'Consultatie ' . $i . ' — uitgebreide behandeling en vaccinatie', 'quantity' => '1,00', 'unit_price' => '40,00', 'tax' => '21%', 'total' => '40,00'];
        $invoice = array_replace($invoice, ['net' => '2.600,00', 'tax_total' => '546,00', 'total' => '3.146,00', 'tax_rows' => [['21%', '2.600,00', '546,00']], 'iban' => 'BE71096123456769', 'bank_name' => 'Example practice', 'qr_amount' => 3146]);
        $oldConf = $this->ci->conf ?? null;
        $this->ci->conf = ['iban' => ['value' => base64_encode($invoice['iban'])], 'nameiban' => ['value' => base64_encode($invoice['bank_name'])]];
        try {
            $pdf = $this->service->pdf('invoice', $invoice, $settings);
            self::assertStringStartsWith('%PDF-', $pdf);
            if ($dir = getenv('DOCUMENT_QA_DIR')) file_put_contents($dir . '/invoice-long-qr.pdf', $pdf);
        } finally { $this->ci->conf = $oldConf; }
        $overview = $this->service->sample('overview');
        $overview['vaccines'] = [];
        self::assertStringStartsWith('%PDF-', $this->service->pdf('overview', $overview, $settings));
        $overview['pet'] = 'Zoë met een bijzonder lange geregistreerde naam';
        $overview['vaccines'] = array_fill(0, 70, ['vaccine' => 'Uitgebreide vaccinatiecombinatie — beschermingsherhaling', 'date' => '20-09-2025', 'vet' => 'Alex Example', 'location' => 'Dierenpraktijk Voorbeeld', 'due' => '20-09-2026']);
        $pdf = $this->service->pdf('overview', $overview, $settings);
        self::assertStringStartsWith('%PDF-', $pdf);
        if ($dir = getenv('DOCUMENT_QA_DIR')) file_put_contents($dir . '/overview-long.pdf', $pdf);
    }

    public function testConcurrentRequestIsRejected(): void
    {
        mkdir($this->root, 0700);
        $lock = fopen($this->root . '/render.lock', 'c');
        flock($lock, LOCK_EX);
        try {
            $this->service->pdf('reminder', $this->service->sample('reminder'), $this->ci->document_settings->defaults());
            self::fail('Expected busy');
        } catch (RuntimeException $error) { self::assertSame('busy', $error->getMessage()); }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }
}
