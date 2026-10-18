<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/FilesTestCase.php';

/**
 * T240 - Chunked upload to an event (files/new_file_event, new_file_event_complete, get_file, preview).
 * Source: application/controllers/Files.php
 *
 * Chunks are appended to data/tmp/e{event}_{safe name}. Completing checks the CONTENT type (finfo) against the
 * allowed list (images, office files, text, video), moves the file to data/upload/e{event}_{file_name} and inserts
 * an events_upload row (filename, size, mime, user, location). A wrong type answers
 * {"success":false,"error":"wrong mime type"} and deletes the temp file. get_file sends the bytes as attachment,
 * preview inline with nosniff. File names are unique per test and removed afterwards.
 */
final class EventUploadTest extends FilesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    public function testATwoChunkUploadIsStoredAndRecorded(): void
    {
        $event = $this->event();
        $name = $this->tag . '_scan.png';

        $response = $this->uploadToEvent($event, $name, $this->png());

        $this->assertSame(['success' => true], $this->json($response));
        $path = $this->upload . 'e' . $event . '_' . $name;
        $this->assertFileExists($path);
        $this->assertSame($this->png(), file_get_contents($path), 'both chunks, in order');
        $this->assertFileDoesNotExist($this->tmp . 'e' . $event . '_' . $name, 'temp file moved');
        $row = $this->row('SELECT * FROM events_upload WHERE event = ?', [$event]);
        $this->assertNotNull($row);
        $this->assertSame($name, $row['filename']);
        $this->assertSame(strlen($this->png()), (int) $row['size']);
        $this->assertSame('image/png', $row['mime']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['user']);
        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $row['location']);
    }

    public function testGetFileReturnsTheSameBytesAsAttachment(): void
    {
        $event = $this->event();
        $name = $this->tag . '_back.png';
        $this->uploadToEvent($event, $name, $this->png());
        $id = (int) $this->row('SELECT id FROM events_upload WHERE event = ?', [$event])['id'];

        $response = $this->get('files/get_file/' . $id);

        // the stored mime is passed as CI3 force_download's boolean `set_mime` argument, so the type is the generic one
        $this->assertDownload($response, 'application/octet-stream', $name);
        $this->assertSame($this->png(), (string) $response->getBody());
    }

    public function testPreviewReturnsTheBytesInlineWithNosniff(): void
    {
        $event = $this->event();
        $name = $this->tag . '_view.png';
        $this->uploadToEvent($event, $name, $this->png());
        $id = (int) $this->row('SELECT id FROM events_upload WHERE event = ?', [$event])['id'];

        $response = $this->get('files/preview/' . $id);

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('image/png', $response->getHeaderLine('Content-Type'));
        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $this->assertStringContainsString('inline', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString($name, $response->getHeaderLine('Content-Disposition'));
        $this->assertSame($this->png(), (string) $response->getBody());
    }

    public function testAPhpFileIsRefusedAndItsTempFileRemoved(): void
    {
        $event = $this->event();
        $name = $this->tag . '_evil.php';

        $response = $this->uploadToEvent($event, $name, "<?php echo 'x'; ?>\n");

        $this->assertSame(['success' => false, 'error' => 'wrong mime type'], $this->json($response));
        $this->assertFileDoesNotExist($this->tmp . 'e' . $event . '_' . $name);
        $this->assertFileDoesNotExist($this->upload . 'e' . $event . '_' . $name);
        $this->assertRowCount(0, 'events_upload', ['event' => $event]);
    }

    public function testAnExecutableIsRefused(): void
    {
        $event = $this->event();
        $name = $this->tag . '_tool.exe';

        $response = $this->uploadToEvent($event, $name, "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xff\xff" . str_repeat("\x00", 64));

        $this->assertSame(['success' => false, 'error' => 'wrong mime type'], $this->json($response));
        $this->assertFileDoesNotExist($this->tmp . 'e' . $event . '_' . $name);
        $this->assertRowCount(0, 'events_upload', ['event' => $event]);
    }

    public function testACompletedUploadOfAnAcceptedTextFileWorksAsControl(): void
    {
        $event = $this->event();
        $name = $this->tag . '_note.txt';

        $response = $this->uploadToEvent($event, $name, "plain text note\nsecond line\n");

        $this->assertSame(['success' => true], $this->json($response));
        $this->assertSame('text/plain', $this->row('SELECT mime FROM events_upload WHERE event = ?', [$event])['mime']);
    }

    #[Group('known-bug')]
    public function testOnlyTheContentIsCheckedNotTheExtension(): void
    {
        $event = $this->event();
        $name = $this->tag . '_shell.php';

        $response = $this->uploadToEvent($event, $name, $this->png());

        // KNOWN BUG: a PNG named .php is accepted and stored as e<event>_<name>.php -- decide in CI4 whether to keep
        $this->assertSame(['success' => true], $this->json($response));
        $this->assertFileExists($this->upload . 'e' . $event . '_' . $name);
    }

    public function testCompletingWithoutChunksAnswersNoFile(): void
    {
        $event = $this->event();

        $response = $this->post('files/new_file_event_complete/' . $event, ['file_name' => $this->tag . '_none.png', 'file_size' => 5]);

        $this->assertStringContainsString('{"success":false,"error":"no file"}', (string) $response->getBody());
        $this->assertRowCount(0, 'events_upload', ['event' => $event]);
    }

    #[Group('known-bug')]
    public function testAFileNameWithSpacesIsSavedRawButRecordedSanitised(): void
    {
        $event = $this->event();
        $raw = $this->tag . ' my scan.png';
        $safe = $this->tag . '_my_scan.png';

        $response = $this->uploadToEvent($event, $raw, $this->png());

        $this->assertSame(['success' => true], $this->json($response));
        // KNOWN BUG: the file is moved to the raw name, the row stores the sanitised name -- decide in CI4 whether to keep
        $this->assertFileExists($this->upload . 'e' . $event . '_' . $raw);
        $this->assertFileDoesNotExist($this->upload . 'e' . $event . '_' . $safe);
        $row = $this->row('SELECT * FROM events_upload WHERE event = ?', [$event]);
        $this->assertSame($safe, $row['filename']);

        // so fetching it again cannot find the file
        $this->assertSame(404, $this->get('files/preview/' . $row['id'])->getStatusCode());
        $download = $this->get('files/get_file/' . $row['id']);
        $this->assertNotSame($this->png(), (string) $download->getBody());
    }
}
