<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LegacyPdfCompatibilityTest extends TestCase
{
    public function testExistingPdfFileCacheAndMerger(): void
    {
        $ci = get_instance();
        $ci->load->library('pdf');
        $base = sys_get_temp_dir() . '/legacy_pdf_' . bin2hex(random_bytes(6));
        try {
            $file = $ci->pdf->create('<html><body>Legacy invoice baseline</body></html>', $base, PDF_FILE);
            self::assertStringStartsWith('%PDF-', file_get_contents($file));
            $hash = hash_file('sha256', $file);
            self::assertSame($file, $ci->pdf->create('<html><body>Must not replace cached invoice</body></html>', $base, PDF_FILE));
            self::assertSame($hash, hash_file('sha256', $file));
            $ci->pdf->merge_pdf($base . '-merged.pdf', [$file, $file]);
            self::assertStringStartsWith('%PDF-', file_get_contents($base . '-merged.pdf'));
        } finally {
            foreach ([$base . '.pdf', $base . '-merged.pdf'] as $path) { if (is_file($path)) unlink($path); }
        }
    }

    public function testExistingVaccinationCsvColumnsAndValues(): void
    {
        $ci = get_instance();
        $ci->lang->load('vet', 'dutch');
        $data = ['date_format' => '%Y-%m-%d', 'expiring_vacs' => [[
            'owner_id' => 1, 'first_name' => 'Example', 'last_name' => 'Client', 'street' => 'Street', 'nr' => '1', 'city' => 'Town', 'province' => '', 'zip' => '1000',
            'product_name' => 'Vaccine', 'disease' => 'Disease', 'injection_date' => '2025-01-01', 'redo_date' => '2026-01-01', 'pet_name' => 'Pet', 'pet_type' => CAT, 'owner_mail' => '', 'last_bill' => '', 'debts' => 0, 'vet_name' => 'Vet',
        ]]];
        $csv = $ci->load->view('vaccine/export', $data, true);
        self::assertStringContainsString('"last_name","street","nr","city","zip","disease","injection_date","pet_name","pet_type"', $csv);
        self::assertStringContainsString('"Client","Street","1","Town","1000","Disease","2025-01-01","Pet"', $csv);
    }

    public function testExistingInvoiceTemplateAndAutomaticFileGeneration(): void
    {
        require_once APPPATH . 'controllers/Invoice.php';
        $ci = get_instance();
        $ci->load->library('pdf');
        $ci->lang->load('vet', 'dutch');
        $oldConf = $ci->conf ?? null;
        $ci->conf = ['invoice_prefix' => ['value' => base64_encode('QA')]];
        $root = sys_get_temp_dir() . '/legacy-invoice-' . bin2hex(random_bytes(6)) . '/';
        $reflection = new ReflectionClass(Invoice::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $controller->load = $ci->load;
        $controller->pdf = new Pdf();
        $controller->conf = $ci->conf;
        $reflection->getProperty('invoice_storage_path')->setValue($controller, $root);
        $data = ['str_invoice_or_bill' => 'Invoice', 'fact_invoice_or_bill' => 'QA001', 'due_date_days' => 30,
            'bill' => ['id' => 1, 'invoice_id' => 1, 'invoice_date' => '2026-09-20 12:00:00', 'created_at' => '2026-09-20 12:00:00', 'status' => BILL_PAID, 'cash' => 48.4, 'card' => 0, 'transfer' => 0, 'transfer_verified' => 1, 'msg_invoice' => '', 'location' => ['name' => 'QA practice'], 'total_brut' => 48.4],
            'owner' => ['id' => 1, 'btw_nr' => '', 'invoice_addr' => '', 'first_name' => 'Example', 'last_name' => 'Client', 'street' => 'Street', 'nr' => '1', 'zip' => '1000', 'city' => 'Town'],
            'print' => [['pet' => ['name' => 'Milo', 'id' => 1], 'products' => [], 'procedures' => [['name' => 'Consultation', 'created_at' => '2026-09-20', 'volume' => 1, 'unit_price' => 40, 'btw' => 21, 'price_net' => 40]]]],
            'btw_details' => [21 => ['over' => 40, 'calculated' => 8.4]]];
        try {
            $file = $reflection->getMethod('generate_pdf')->invoke($controller, $data, PDF_FILE);
            self::assertStringStartsWith($root, $file);
            self::assertStringStartsWith('%PDF-', file_get_contents($file));
            $hash = hash_file('sha256', $file);
            self::assertSame($file, $controller->pdf->create('cache must be reused', substr($file, 0, -4), PDF_FILE));
            self::assertSame($hash, hash_file('sha256', $file));
        } finally {
            $ci->conf = $oldConf;
            foreach (glob($root . '*/*/*.pdf') ?: [] as $path) unlink($path);
            foreach (glob($root . '*/*', GLOB_ONLYDIR) ?: [] as $path) rmdir($path);
            foreach (glob($root . '*', GLOB_ONLYDIR) ?: [] as $path) rmdir($path);
            if (is_dir($root)) rmdir($root);
        }
    }
}
