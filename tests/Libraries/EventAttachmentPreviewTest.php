<?php

declare(strict_types=1);

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

    private function temporaryFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'event-preview-');
        file_put_contents($path, $contents);
        $this->paths[] = $path;
        return $path;
    }
}
