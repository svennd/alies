<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once APPPATH . 'libraries/Pet_avatar.php';

final class PetAvatarTest extends TestCase
{
    private string $storagePath;
    private Pet_avatar $processor;

    protected function setUp(): void
    {
        $this->storagePath = sys_get_temp_dir() . '/alies_pet_avatar_' . bin2hex(random_bytes(8));
        $this->processor = new Pet_avatar(['storage_path' => $this->storagePath]);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storagePath)) {
            foreach (glob($this->storagePath . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->storagePath);
        }
    }

    public function testValidJpegAndPngSourcesProduceUniqueNormalizedImages(): void
    {
        $crop = $this->imageDataUri('jpeg', 600, 400);
        $jpeg = $this->sourceFile('jpeg', 20, 10);
        $png = $this->sourceFile('png', 10, 20);

        try {
            $first = $this->processor->store($jpeg, $crop);
            $second = $this->processor->store($png, $crop);

            $this->assertTrue($first['success']);
            $this->assertTrue($second['success']);
            $this->assertNotSame($first['filename'], $second['filename']);

            foreach ([$first, $second] as $result) {
                $path = $this->processor->path($result['filename']);
                $this->assertIsString($path);
                $this->assertFileExists($path);
                $this->assertSame('image/jpeg', (new finfo(FILEINFO_MIME_TYPE))->file($path));
                $this->assertSame([512, 512], array_slice(getimagesize($path), 0, 2));
            }
        } finally {
            unlink($jpeg['tmp_name']);
            unlink($png['tmp_name']);
        }
    }

    public function testInvalidEmptyDisguisedAndOversizedSourcesAreRejected(): void
    {
        $crop = $this->imageDataUri('jpeg', 20, 20);
        $empty = $this->plainSource('empty.jpg', '');
        $disguised = $this->plainSource('not-an-image.jpg', 'plain text');
        $oversizedPath = $this->storagePath . '_oversized.jpg';
        $handle = fopen($oversizedPath, 'wb');
        ftruncate($handle, 8388609);
        fclose($handle);

        try {
            $this->assertSame('invalid', $this->processor->store($empty, $crop)['error']);
            $this->assertSame('type', $this->processor->store($disguised, $crop)['error']);
            $this->assertSame('size', $this->processor->store([
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => $oversizedPath,
                'size' => 8388609,
            ], $crop)['error']);
        } finally {
            unlink($oversizedPath);
            unlink($empty['tmp_name']);
            unlink($disguised['tmp_name']);
        }
    }

    public function testUnsafeDimensionsAndInvalidCropAreRejected(): void
    {
        $huge = $this->plainSource('huge.png', $this->pngHeader(10000, 10000));
        $valid = $this->sourceFile('jpeg', 20, 20);

        try {
            $this->assertSame('dimensions', $this->processor->store($huge, $this->imageDataUri('jpeg', 20, 20))['error']);
            $this->assertSame('crop', $this->processor->store($valid, 'data:image/jpeg;base64,not-valid-base64!')['error']);
        } finally {
            unlink($huge['tmp_name']);
            unlink($valid['tmp_name']);
        }
    }

    public function testMissingStorageDirectoryIsCreatedAndUnsafeFilenameIsRejected(): void
    {
        $source = $this->sourceFile('png', 20, 20);

        try {
            $result = $this->processor->store($source, $this->imageDataUri('png', 20, 20));
            $this->assertTrue($result['success']);
            $this->assertDirectoryExists($this->storagePath);
            $this->assertFalse($this->processor->path('../outside.jpg'));
        } finally {
            unlink($source['tmp_name']);
        }
    }

    private function sourceFile(string $format, int $width, int $height): array
    {
        $path = tempnam(sys_get_temp_dir(), 'alies_avatar_source_');
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 41, 87, 132);
        imagefilledrectangle($image, 0, 0, $width, $height, $color);
        $format === 'png' ? imagepng($image, $path) : imagejpeg($image, $path, 90);
        imagedestroy($image);

        return ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'size' => filesize($path)];
    }

    private function plainSource(string $name, string $content): array
    {
        $path = sys_get_temp_dir() . '/alies_' . bin2hex(random_bytes(8)) . '_' . $name;
        file_put_contents($path, $content);
        return ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'size' => filesize($path)];
    }

    private function imageDataUri(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 190, 95, 44);
        imagefilledrectangle($image, 0, 0, $width, $height, $color);
        ob_start();
        $format === 'png' ? imagepng($image) : imagejpeg($image, null, 90);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return 'data:image/' . $format . ';base64,' . base64_encode($bytes);
    }

    private function pngHeader(int $width, int $height): string
    {
        $chunk = static function (string $type, string $data): string {
            return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        };

        $header = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', $header) . $chunk('IEND', '');
    }
}
