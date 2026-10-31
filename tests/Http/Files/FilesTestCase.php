<?php

declare(strict_types=1);

/**
 * Shared plumbing of the file tests (T240-T243): unique names per test instance, cleanup of everything the
 * test (or the application on its behalf) left in data/upload, data/upload/stored and data/tmp.
 * The files folders are shared with other test runs, so names carry a unique tag and only those are removed.
 */
abstract class FilesTestCase extends HttpTestCase
{
    protected string $tag;
    protected string $upload;
    protected string $stored;
    protected string $tmp;
    private bool $createdStored = false;

    /** a valid 1x1 PNG */
    protected const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tag = 'u' . uniqid();
        $root = dirname(__DIR__, 3) . '/data/';
        $this->upload = $root . 'upload/';
        $this->stored = $root . 'upload/stored/';
        $this->tmp = $root . 'tmp/';
        if (!is_dir($this->stored)) {
            mkdir($this->stored, 0775, true);
            $this->createdStored = true;
        }
    }

    protected function tearDown(): void
    {
        foreach ([$this->upload, $this->stored, $this->tmp] as $dir) {
            foreach (glob($dir . '*' . $this->tag . '*') ?: [] as $file) {
                @unlink($file);
            }
        }
        if ($this->createdStored) {
            @rmdir($this->stored);
        }
        parent::tearDown();
    }

    protected function png(): string
    {
        return base64_decode(self::PNG_BASE64);
    }

    /** Writes a local temp file (the "chunk") for postMultipart; removed with the test. */
    protected function chunk(string $content): string
    {
        $path = sys_get_temp_dir() . '/' . $this->tag . '_chunk_' . uniqid();
        file_put_contents($path, $content);
        register_shutdown_function(static fn () => @unlink($path));

        return $path;
    }

    protected function event(): int
    {
        return Factory::event(Factory::pet(Factory::owner()), 0);
    }

    /** Uploads $content in two chunks like the browser does and completes it. Returns the complete response. */
    protected function uploadToEvent(int $event, string $fileName, string $content, ?int $size = null)
    {
        $half = intdiv(strlen($content), 2);
        foreach ([substr($content, 0, $half), substr($content, $half)] as $part) {
            $this->postMultipart('files/new_file_event/' . $event, ['file_name' => $fileName], ['data' => ['path' => $this->chunk($part), 'filename' => 'blob']]);
        }

        return $this->post('files/new_file_event_complete/' . $event, ['file_name' => $fileName, 'file_size' => $size ?? strlen($content)]);
    }

    /** An events_upload row plus the stored file, as if it had been uploaded. Returns the row id. */
    protected function storedUpload(int $event, string $filename, string $content, string $mime, int $user = Fixtures::USER_VET): int
    {
        file_put_contents($this->upload . 'e' . $event . '_' . $filename, $content);

        return Factory::insert('events_upload', [
            'event' => $event, 'filename' => $filename, 'size' => strlen($content), 'mime' => $mime, 'comment' => '',
            'user' => $user, 'location' => 1, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);
    }
}
