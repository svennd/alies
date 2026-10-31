<?php

declare(strict_types=1);

require_once __DIR__ . '/FilesTestCase.php';

/**
 * T241 - Generic chunked upload (files/append/{id} and files/file_complete/{id}), used by the import screens.
 * Source: application/controllers/Files.php
 *
 * append adds a chunk to data/upload/tmp_{id}. file_complete checks the content type, then moves the file to
 * data/upload/stored/f{id}_{sanitised name} and answers {"success":true,"file":"f{id}_{name}"}. Without a temp
 * file it answers {"success":false,"error":"no file"}; a forbidden type is deleted with "wrong mime type".
 * The id is a large random number so that parallel test runs do not meet; the stored name carries the unique tag.
 */
final class GenericUploadTest extends FilesTestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
        $this->id = random_int(900000000, 2000000000);
    }

    protected function tearDown(): void
    {
        @unlink($this->upload . 'tmp_' . $this->id);
        foreach (glob($this->stored . 'f' . $this->id . '_*') ?: [] as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function append(string $content): void
    {
        $this->postMultipart('files/append/' . $this->id, [], ['data' => ['path' => $this->chunk($content), 'filename' => 'blob']]);
    }

    public function testTwoChunksAreJoinedAndStoredUnderTheSanitisedName(): void
    {
        $png = $this->png();
        $this->append(substr($png, 0, 30));
        $this->append(substr($png, 30));
        $this->assertSame($png, file_get_contents($this->upload . 'tmp_' . $this->id));

        $response = $this->post('files/file_complete/' . $this->id, ['file_name' => $this->tag . ' pic.png']);

        $stored = 'f' . $this->id . '_' . $this->tag . '_pic.png';
        $this->assertSame(['success' => true, 'file' => $stored], $this->json($response));
        $this->assertSame($png, file_get_contents($this->stored . $stored));
        $this->assertFileDoesNotExist($this->upload . 'tmp_' . $this->id);
    }

    public function testADirectoryTraversalInTheNameIsReducedToTheBaseName(): void
    {
        $this->append($this->png());

        $response = $this->post('files/file_complete/' . $this->id, ['file_name' => '../../' . $this->tag . '_x.png']);

        $stored = 'f' . $this->id . '_' . $this->tag . '_x.png';
        $this->assertSame(['success' => true, 'file' => $stored], $this->json($response));
        $this->assertFileExists($this->stored . $stored);
    }

    public function testCompletingWithoutATempFileAnswersNoFile(): void
    {
        $response = $this->post('files/file_complete/' . $this->id, ['file_name' => $this->tag . '_none.png']);

        $this->assertStringContainsString('{"success":false,"error":"no file"}', (string) $response->getBody());
        $this->assertSame([], glob($this->stored . 'f' . $this->id . '_*'));
    }

    public function testAForbiddenTypeIsRemoved(): void
    {
        $this->append("<?php echo 'x'; ?>\n");

        $response = $this->post('files/file_complete/' . $this->id, ['file_name' => $this->tag . '_evil.php']);

        $this->assertSame(['success' => false, 'error' => 'wrong mime type'], $this->json($response));
        $this->assertFileDoesNotExist($this->upload . 'tmp_' . $this->id);
        $this->assertSame([], glob($this->stored . 'f' . $this->id . '_*'));
    }
}
