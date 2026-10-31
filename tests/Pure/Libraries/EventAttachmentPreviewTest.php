<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EventAttachmentPreviewTest extends TestCase
{
    private Event_attachment_preview $preview;
    private array $paths = [];

    protected function setUp(): void
    {
        require_once APPPATH . 'libraries/Event_attachment_preview.php';
        $this->preview = new Event_attachment_preview();
    }

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testSupportedRasterContentIsAccepted(): void
    {
        $png = $this->temporaryFile(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $this->assertTrue($this->preview->supports('image/png'));
        $this->assertSame('image/png', $this->preview->inspect($png, 'image/png'));
    }

    public function testMissingSvgAndNonImageContentAreRejected(): void
    {
        $svg = $this->temporaryFile('<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $pdf = $this->temporaryFile("%PDF-1.4\n");

        $this->assertFalse($this->preview->inspect('/missing/event-image.jpg', 'image/jpeg'));
        $this->assertFalse($this->preview->inspect($svg, 'image/svg+xml'));
        $this->assertFalse($this->preview->inspect($pdf, 'application/pdf'));
        $this->assertFalse($this->preview->inspect($pdf, 'image/jpeg'));
    }

    // ---- T022: supported types, real content wins over the stored type ------------------------------

    private const JPEG = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
    private const GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    /** @return array<string, array{string, bool}> */
    public static function storedTypes(): array
    {
        return [
            'jpeg' => ['image/jpeg', true],
            'png' => ['image/png', true],
            'gif' => ['image/gif', true],
            'svg is never previewed (scripts)' => ['image/svg+xml', false],
            'webp' => ['image/webp', false],
            'pdf' => ['application/pdf', false],
            'upper case is not the same type' => ['IMAGE/PNG', false],
            'jpg is not a mime type' => ['image/jpg', false],
            'empty' => ['', false],
            'text' => ['text/plain', false],
        ];
    }

    #[DataProvider('storedTypes')]
    public function testSupportsOnlyJpegPngAndGif(string $mime, bool $supported): void
    {
        $this->assertSame($supported, $this->preview->supports($mime));
    }

    /** @return array<string, array{string, string}> */
    public static function realImages(): array
    {
        return [
            'jpeg' => [self::JPEG, 'image/jpeg'],
            'gif' => [self::GIF, 'image/gif'],
            'png' => [self::PNG, 'image/png'],
        ];
    }

    #[DataProvider('realImages')]
    public function testEachSupportedRealImageIsAccepted(string $base64, string $mime): void
    {
        $path = $this->temporaryFile(base64_decode($base64));

        $this->assertSame($mime, $this->preview->inspect($path, $mime));
    }

    public function testTheContentDecidesNotTheStoredType(): void
    {
        // the database says jpeg, the bytes are a png: the actual type is returned (and used as Content-Type)
        $png = $this->temporaryFile(base64_decode(self::PNG));

        $this->assertSame('image/png', $this->preview->inspect($png, 'image/jpeg'));
    }

    public function testASupportedStoredTypeWithOtherContentIsRejected(): void
    {
        $html = $this->temporaryFile('<html><script>alert(1)</script></html>');
        $svg = $this->temporaryFile('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $pdf = $this->temporaryFile("%PDF-1.4\n");

        $this->assertFalse($this->preview->inspect($html, 'image/png'));
        $this->assertFalse($this->preview->inspect($svg, 'image/png'));
        $this->assertFalse($this->preview->inspect($pdf, 'image/gif'));
    }

    public function testAnEmptyFileIsRejected(): void
    {
        $this->assertFalse($this->preview->inspect($this->temporaryFile(''), 'image/png'));
    }

    public function testADirectoryIsRejected(): void
    {
        $this->assertFalse($this->preview->inspect(sys_get_temp_dir(), 'image/png'));
    }

    public function testNothingIsReadWhenTheStoredTypeIsNotSupported(): void
    {
        $png = $this->temporaryFile(base64_decode(self::PNG));

        // a perfectly good png, but the record says pdf
        $this->assertFalse($this->preview->inspect($png, 'application/pdf'));
    }

    private function temporaryFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'event-preview-');
        file_put_contents($path, $contents);
        $this->paths[] = $path;
        return $path;
    }
}
