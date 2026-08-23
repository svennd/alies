<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Pet_avatar.php';
require_once APPPATH . 'libraries/Pet_avatar_manager.php';

final class PetAvatarWorkflowTest extends CodeIgniterDatabaseTestCase
{
    private string $storagePath;
    private array $temporarySources = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->storagePath = sys_get_temp_dir() . '/alies_pet_avatar_workflow_' . bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        foreach ($this->temporarySources as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        if (is_dir($this->storagePath)) {
            foreach (glob($this->storagePath . '/*') ?: [] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            rmdir($this->storagePath);
        }
        parent::tearDown();
    }

    public function testBackendWorkflowCoversUploadFailureReplacementAndRemoval(): void
    {
        $pets = $this->model('Pets_model', 'avatar_workflow_pets');
        $logs = $this->model('Logs_model', 'avatar_workflow_logs');
        $processor = new Pet_avatar(['storage_path' => $this->storagePath]);
        $manager = new Pet_avatar_manager(compact('pets', 'logs', 'processor'));
        $pet = $this->ci->db
            ->select('id')
            ->where('deleted_at IS NULL', null, false)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get('pets')
            ->row_array();
        $this->assertNotNull($pet);
        $petId = (int) $pet['id'];
		$lastLog = $this->ci->db->select_max('id')->get('log')->row_array();
		$lastLogId = isset($lastLog['id']) ? (int) $lastLog['id'] : 0;
        $this->ci->db->where('id', $petId)->update('pets', ['avatar' => null]);

        $jpeg = $this->source('jpeg');
        $upload = $manager->save($petId, $jpeg, $this->crop('jpeg', 90));
        $this->assertSame('success', $upload['status']);
        $this->assertFileExists($processor->path($upload['filename']));
        $this->assertSame($upload['filename'], $this->currentAvatar($petId));

        $invalid = $this->plainSource('not an image');
        $rejected = $manager->save($petId, $invalid, $this->crop('jpeg', 90));
        $this->assertSame('invalid', $rejected['status']);
        $this->assertSame($upload['filename'], $this->currentAvatar($petId));

        $png = $this->source('png');
        $replacement = $manager->save($petId, $png, $this->crop('png', 180));
        $this->assertSame('success', $replacement['status']);
        $this->assertFileDoesNotExist($processor->path($upload['filename']));
        $this->assertFileExists($processor->path($replacement['filename']));
        $this->assertSame($replacement['filename'], $this->currentAvatar($petId));

        $removal = $manager->remove($petId);
        $this->assertSame('success', $removal['status']);
        $this->assertNull($this->currentAvatar($petId));
        $this->assertFileDoesNotExist($processor->path($replacement['filename']));

        $events = $this->ci->db
            ->select('event')
			->where('id >', $lastLogId)
            ->where_in('event', ['pet_avatar_upload', 'pet_avatar_replace', 'pet_avatar_remove'])
            ->like('msg', 'pet #' . $petId)
			->order_by('id', 'ASC')
            ->get('log')
            ->result_array();
        $this->assertSame(
            ['pet_avatar_upload', 'pet_avatar_replace', 'pet_avatar_remove'],
            array_column($events, 'event')
        );
    }

    private function currentAvatar(int $petId): ?string
    {
        $row = $this->ci->db->select('avatar')->where('id', $petId)->get('pets')->row_array();
        return $row['avatar'];
    }

    private function source(string $format): array
    {
        $path = tempnam(sys_get_temp_dir(), 'alies_avatar_workflow_source_');
        $this->temporarySources[] = $path;
        $image = imagecreatetruecolor(80, 60);
        $color = imagecolorallocate($image, 30, 120, 180);
        imagefilledrectangle($image, 0, 0, 80, 60, $color);
        $format === 'png' ? imagepng($image, $path) : imagejpeg($image, $path, 90);
        imagedestroy($image);
        return ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'size' => filesize($path)];
    }

    private function plainSource(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'alies_avatar_workflow_invalid_');
        $this->temporarySources[] = $path;
        file_put_contents($path, $content);
        return ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'size' => filesize($path)];
    }

    private function crop(string $format, int $hue): string
    {
        $image = imagecreatetruecolor(160, 160);
        $color = imagecolorallocate($image, $hue, 70, 130);
        imagefilledrectangle($image, 0, 0, 160, 160, $color);
        ob_start();
        $format === 'png' ? imagepng($image) : imagejpeg($image, null, 90);
        $bytes = ob_get_clean();
        imagedestroy($image);
        return 'data:image/' . $format . ';base64,' . base64_encode($bytes);
    }
}
