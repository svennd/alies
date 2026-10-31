<?php

declare(strict_types=1);

require_once __DIR__ . '/FilesTestCase.php';

/**
 * T243 - Preview of a type that cannot be previewed (files/preview/{id}).
 * Source: application/controllers/Files.php::preview, libraries/Event_attachment_preview.php
 *
 * Only jpeg, png and gif are previewed, and only when the real file content agrees with the stored mime.
 * A PDF upload, an unknown id, a missing file and a png row whose file is not an image all answer 404.
 */
final class PreviewTest extends FilesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    public function testAPdfUploadIsNotPreviewed(): void
    {
        $event = $this->event();
        $id = $this->storedUpload($event, $this->tag . '_doc.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n", 'application/pdf');

        $this->assertStatus($this->get('files/preview/' . $id), 404);
    }

    public function testAnUnknownIdIsNotFound(): void
    {
        $this->assertStatus($this->get('files/preview/999999'), 404);
    }

    public function testAnImageRowWhoseFileIsMissingIsNotFound(): void
    {
        $event = $this->event();
        $id = Factory::insert('events_upload', [
            'event' => $event, 'filename' => $this->tag . '_gone.png', 'size' => 1, 'mime' => 'image/png', 'comment' => '',
            'user' => Fixtures::USER_VET, 'location' => 1, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);

        $this->assertStatus($this->get('files/preview/' . $id), 404);
    }

    public function testAPngRowWhoseContentIsNotAnImageIsNotFound(): void
    {
        $event = $this->event();
        $id = $this->storedUpload($event, $this->tag . '_fake.png', "<html><script>alert(1)</script></html>", 'image/png');

        $this->assertStatus($this->get('files/preview/' . $id), 404);
    }

    public function testARealPngIsPreviewedAsControl(): void
    {
        $event = $this->event();
        $id = $this->storedUpload($event, $this->tag . '_ok.png', $this->png(), 'image/png');

        $response = $this->get('files/preview/' . $id);

        $this->assertStatus($response, 200);
        $this->assertSame($this->png(), (string) $response->getBody());
    }
}
