<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/FilesTestCase.php';

/**
 * T242 - Delete a file (files/delete_file/{id}).
 * Source: application/controllers/Files.php::delete_file
 *
 * The uploading user removes both the file and the events_upload row. When ANOTHER vet calls it, the file is
 * still deleted from disk but the row stays (the delete is restricted to `user = current user`).
 */
final class DeleteFileTest extends FilesTestCase
{
    public function testTheUploaderRemovesFileAndRow(): void
    {
        $event = $this->event();
        $id = $this->storedUpload($event, $this->tag . '_mine.png', $this->png(), 'image/png', Fixtures::USER_VET);
        $other = $this->storedUpload($event, $this->tag . '_other.png', $this->png(), 'image/png', Fixtures::USER_VET);
        $this->loginAs('vet');

        $response = $this->get('files/delete_file/' . $id);

        $this->assertStatus($response, 200);
        $this->assertFileDoesNotExist($this->upload . 'e' . $event . '_' . $this->tag . '_mine.png');
        $this->assertRowMissing('events_upload', ['id' => $id]);
        $this->assertFileExists($this->upload . 'e' . $event . '_' . $this->tag . '_other.png', 'other uploads stay');
        $this->assertRowExists('events_upload', ['id' => $other]);
    }

    #[Group('known-bug')]
    public function testAnotherVetDeletesTheFileButNotTheRow(): void
    {
        $event = $this->event();
        $name = $this->tag . '_theirs.png';
        $id = $this->storedUpload($event, $name, $this->png(), 'image/png', Fixtures::USER_VET);
        $this->loginAs('vet2');

        $this->get('files/delete_file/' . $id);

        // KNOWN BUG: unlink happens before the ownership check, so the file is gone while the row stays -- decide in CI4 whether to keep
        $this->assertFileDoesNotExist($this->upload . 'e' . $event . '_' . $name);
        $this->assertRowExists('events_upload', ['id' => $id]);
    }

    public function testDeletingAnUnknownIdWritesABrokenDeleteWarning(): void
    {
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $response = $this->get('files/delete_file/999999');

        $this->assertStatus($response, 200);
        $this->assertSame(1, $this->logCount('broken delete'));
    }
}
