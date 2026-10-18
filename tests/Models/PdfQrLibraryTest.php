<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * T024 - application/libraries/Pdf.php (dompdf + merger) and Qr.php (EPC/SEPA payment QR code).
 *
 * Both extend CI_Model and read the account name and IBAN from the controller's settings, so they
 * need CodeIgniter: this test is in the model suite. Nothing is sent to a browser; PDF_STREAM and
 * PDF_DOWNLOAD call exit and are therefore only covered by the HTTP tests of the invoice screens.
 */
final class PdfQrLibraryTest extends TestCase
{
    private string $dir;
    private mixed $originalConf;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/alies-pdf-' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->dir);

        $ci = get_instance();
        $this->originalConf = $ci->conf ?? null;
        // the shape the Vet_Controller gives: name => ['value' => base64 encoded value]
        $ci->conf = [
            'nameiban' => ['value' => base64_encode('Test Praktijk')],
            'iban' => ['value' => base64_encode('BE68 5390 0754 7034')],
        ];
    }

    protected function tearDown(): void
    {
        get_instance()->conf = $this->originalConf;
        foreach (glob($this->dir . '*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);
    }

    private function pdf(): Pdf
    {
        require_once APPPATH . 'libraries/Pdf.php';

        return new Pdf();
    }

    private function qr(): Qr
    {
        require_once APPPATH . 'libraries/Qr.php';

        return new Qr();
    }

    // ---- Pdf --------------------------------------------------------------------------------

    public function testCreateAsFileWritesAPdfNextToTheGivenName(): void
    {
        $path = $this->pdf()->create('<h1>Factuur</h1><p>Totaal 10,00</p>', $this->dir . 'invoice', PDF_FILE);

        $this->assertSame($this->dir . 'invoice.pdf', $path);
        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-', (string) file_get_contents($path));
        $this->assertGreaterThan(500, filesize($path));
    }

    public function testAnExistingFileIsNotMadeAgainUnlessForced(): void
    {
        $pdf = $this->pdf();
        $path = $pdf->create('<p>first</p>', $this->dir . 'keep', PDF_FILE);
        $old = time() - 5000;
        touch($path, $old);
        clearstatcache();

        $again = $pdf->create('<p>second</p>', $this->dir . 'keep', PDF_FILE);
        clearstatcache();
        $this->assertSame($path, $again);
        $this->assertSame($old, filemtime($path), 'the file was left alone');

        $pdf->create('<p>second</p>', $this->dir . 'keep', PDF_FILE, true);
        clearstatcache();
        $this->assertGreaterThan($old, filemtime($path), 'the file was made again');
    }

    public function testStreamingAnExistingFileSendsItsBytesWithoutRendering(): void
    {
        $pdf = $this->pdf();
        $path = $pdf->create('<p>stream me</p>', $this->dir . 'stream', PDF_FILE);

        ob_start();
        try {
            $result = $pdf->create('<p>ignored</p>', $this->dir . 'stream', PDF_STREAM);
            $output = (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $this->assertNull($result);
        $this->assertSame(file_get_contents($path), $output);
    }

    public function testMergeCombinesThePagesOfSeveralPdfsInOrder(): void
    {
        $pdf = $this->pdf();
        $first = $pdf->create('<p>page one</p>', $this->dir . 'one', PDF_FILE);
        $second = $pdf->create('<p>page two</p>', $this->dir . 'two', PDF_FILE);
        $third = $pdf->create('<p>page three</p>', $this->dir . 'three', PDF_FILE);

        $merged = $this->pdf()->merge_pdf($this->dir . 'merged.pdf', [$first, $second, $third]);

        $this->assertSame($this->dir . 'merged.pdf', $merged);
        $content = (string) file_get_contents($merged);
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertSame(3, preg_match_all('#/Type\s*/Page(?![a-z])#', $content), 'three pages');
    }

    // ---- Qr ---------------------------------------------------------------------------------

    public function testCreateGivesAnSvgDataUri(): void
    {
        // the options ask for 'png', but the installed QR library ignores that and renders SVG
        // (dompdf draws it inside the invoice); the "png" setting has no effect
        $uri = $this->qr()->create(10.5, '+++123/4567/89012+++');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $svg = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')), true);
        $this->assertNotFalse($svg);
        $this->assertStringStartsWith('<?xml', $svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('viewBox="0 0 ', $svg);
    }

    public function testTheSameInputGivesTheSameCodeAndOtherInputAnotherOne(): void
    {
        // a new Qr for every code, as the invoice screen does
        $first = $this->qr()->create(10.5, 'A');

        $this->assertSame($first, $this->qr()->create(10.5, 'A'));
        $this->assertNotSame($first, $this->qr()->create(11.5, 'A'));
        $this->assertNotSame($first, $this->qr()->create(10.5, 'B'));
    }

    public function testAnAmountBelowOneCentIsRefused(): void
    {
        $qr = $this->qr();

        $this->expectException(InvalidArgumentException::class);

        $qr->create(0.0, 'A');
    }

    #[Group('known-bug')]
    public function testReusingOneInstanceMakesEveryCodeBiggerUntilItFails(): void
    {
        // KNOWN BUG (latent): the QR object keeps the data of earlier calls. The invoice screen makes one
        // code per request so nobody notices, but a loop over invoices with one Qr would break
        $qr = $this->qr();

        $first = $qr->create(10.5, 'A');
        $second = $qr->create(10.5, 'A');

        $this->assertGreaterThan(strlen($first), strlen($second), 'the same input gives a bigger code the second time');

        $qr->create(10.5, 'A');
        $this->expectException(\chillerlan\QRCode\Data\QRCodeDataException::class);
        $qr->create(10.5, 'A');
    }

    public function testWithoutAccountSettingsTheCodeCannotBeMade(): void
    {
        // quirk: without nameiban/iban in the settings the name and iban stay null and the SEPA data
        // object refuses them - the invoice screen only calls this when the settings exist
        get_instance()->conf = [];
        $qr = $this->qr();

        $this->expectException(TypeError::class);

        $qr->create(10.5, 'A');
    }
}
