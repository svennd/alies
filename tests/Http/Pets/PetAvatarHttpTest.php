<?php

declare(strict_types=1);

/**
 * T069 - pet avatar over HTTP. Source: Pets::save_avatar / remove_avatar / avatar_file, libraries/Pet_avatar*.php.
 * (Library and manager internals are covered by tests/Integration/PetAvatar* and tests/Pure/Libraries/PetAvatarTest.php.)
 *
 * Only POST changes an avatar (405 otherwise). A valid upload needs the source file (JPEG or PNG) and a
 * data-URI crop; the stored result is a 512x512 JPEG named pet_<32 hex>.jpg in data/stored/pets.
 * The result of each action is a flash message on the fiche.
 */
final class PetAvatarHttpTest extends HttpTestCase
{
    /** @var list<string> temp files made by the test */
    private array $tempFiles = [];
    /** @var list<int> pets whose stored avatar file must go in tearDown */
    private array $pets = [];

    protected function tearDown(): void
    {
        foreach ($this->pets as $pet) {
            $avatar = $this->find('pets', $pet)['avatar'] ?? null;
            if ($avatar !== null && preg_match('/\Apet_[a-f0-9]{32}\.jpg\z/', $avatar)) {
                @unlink(dirname(__DIR__, 3) . '/data/stored/pets/' . $avatar);
            }
        }
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function newPet(): int
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('needs the GD extension to build a test image');
        }
        $pet = Factory::pet(Factory::owner());
        $this->pets[] = $pet;

        return $pet;
    }

    private function imageBytes(string $type, int $width = 80, int $height = 60): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 200, 40, 40));
        ob_start();
        $type === 'png' ? imagepng($image) : imagejpeg($image, null, 90);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private function tempImage(string $type = 'jpeg'): string
    {
        $path = sys_get_temp_dir() . '/avatar_' . uniqid() . ($type === 'png' ? '.png' : '.jpg');
        file_put_contents($path, $this->imageBytes($type));
        $this->tempFiles[] = $path;

        return $path;
    }

    private function cropData(string $type = 'jpeg'): string
    {
        return 'data:image/' . $type . ';base64,' . base64_encode($this->imageBytes($type, 100, 100));
    }

    private function upload(int $pet, ?string $crop = null, ?string $sourcePath = null, string $mime = 'image/jpeg'): \Psr\Http\Message\ResponseInterface
    {
        return $this->postMultipart(
            'pets/save_avatar/' . $pet,
            ['pet_avatar_crop' => $crop ?? $this->cropData()],
            ['pet_avatar_source' => ['path' => $sourcePath ?? $this->tempImage(), 'type' => $mime]]
        );
    }

    private function avatarPath(string $avatar): string
    {
        return dirname(__DIR__, 3) . '/data/stored/pets/' . $avatar;
    }

    public function testAGetRequestIsRefusedWith405(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');

        $this->assertStatus($this->get('pets/save_avatar/' . $pet), 405);
        $this->assertStatus($this->get('pets/remove_avatar/' . $pet), 405);
        $this->assertNull($this->find('pets', $pet)['avatar']);
    }

    public function testAValidUploadStoresAJpegAndShowsASuccessMessage(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $logs = $this->logCount('pet_avatar_upload');

        $response = $this->upload($pet);

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $avatar = $this->find('pets', $pet)['avatar'];
        $this->assertMatchesRegularExpression('/\Apet_[a-f0-9]{32}\.jpg\z/', (string) $avatar);
        $this->assertFileExists($this->avatarPath($avatar));
        $this->assertSame($logs + 1, $this->logCount('pet_avatar_upload'));

        $fiche = $this->followRedirect($response);
        $this->assertStatus($fiche, 200);
        $this->assertSee($fiche, 'De dierenfoto werd toegevoegd.');
        $this->assertStringContainsString('pets/avatar_file/' . $pet, (string) $fiche->getBody(), 'the fiche shows the picture');
        $this->assertDontSee($this->get('pets/fiche/' . $pet), 'De dierenfoto werd toegevoegd.', 'a flash message is shown once');
    }

    public function testAvatarFileServesTheImageWithSafeHeaders(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $this->upload($pet);

        $file = $this->get('pets/avatar_file/' . $pet);

        $this->assertStatus($file, 200);
        $this->assertStringStartsWith('image/jpeg', $file->getHeaderLine('Content-Type'));
        $this->assertSame('nosniff', $file->getHeaderLine('X-Content-Type-Options'));
        $this->assertStringContainsString('no-cache', $file->getHeaderLine('Cache-Control'));
        $body = (string) $file->getBody();
        $info = getimagesizefromstring($body);
        $this->assertNotFalse($info);
        $this->assertSame(IMAGETYPE_JPEG, $info[2]);
        $this->assertSame([512, 512], [$info[0], $info[1]], 'the crop is scaled to 512 x 512');
        $this->assertSame(strlen($body), (int) $file->getHeaderLine('Content-Length'));
    }

    public function testAPngSourceAndPngCropAreAccepted(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');

        $response = $this->upload($pet, $this->cropData('png'), $this->tempImage('png'), 'image/png');

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $this->assertNotNull($this->find('pets', $pet)['avatar']);
        $this->assertStringStartsWith('image/jpeg', $this->get('pets/avatar_file/' . $pet)->getHeaderLine('Content-Type'), 'always stored as JPEG');
    }

    public function testAnotherUploadReplacesTheAvatarAndRemovesTheOldFile(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $this->upload($pet);
        $first = $this->find('pets', $pet)['avatar'];

        $response = $this->upload($pet);

        $second = $this->find('pets', $pet)['avatar'];
        $this->assertNotSame($first, $second);
        $this->assertFileDoesNotExist($this->avatarPath($first));
        $this->assertFileExists($this->avatarPath($second));
        $this->assertSee($this->followRedirect($response), 'De dierenfoto werd gewijzigd.');
    }

    public function testRemovingTheAvatarClearsItAndTheFileGives404(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $this->upload($pet);
        $avatar = $this->find('pets', $pet)['avatar'];
        $this->assertStatus($this->get('pets/avatar_file/' . $pet), 200, 'positive control');

        $response = $this->post('pets/remove_avatar/' . $pet);

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $this->assertNull($this->find('pets', $pet)['avatar']);
        $this->assertFileDoesNotExist($this->avatarPath($avatar));
        // the flash message lives for one request only, so read it before anything else
        $this->assertSee($this->followRedirect($response), 'De dierenfoto werd verwijderd.');
        $this->assertStatus($this->get('pets/avatar_file/' . $pet), 404);
    }

    public function testAPetWithoutAvatarGives404ForTheFile(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');

        $this->assertStatus($this->get('pets/avatar_file/' . $pet), 404);
        $this->assertStatus($this->get('pets/avatar_file/987654'), 404);
    }

    public function testAnInvalidCropShowsAnErrorAndKeepsTheAvatar(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $this->upload($pet);
        $before = $this->find('pets', $pet)['avatar'];

        $response = $this->upload($pet, 'data:image/jpeg;base64,@@@not-base64@@@');

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $this->assertSame($before, $this->find('pets', $pet)['avatar']);
        $this->assertFileExists($this->avatarPath($before));
        $this->assertSee($this->followRedirect($response), 'De uitsnede kon niet worden verwerkt');
    }

    public function testACropThatIsNotAnImageIsRejected(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');

        $response = $this->upload($pet, 'data:image/png;base64,' . base64_encode('this is not an image'));

        $this->assertNull($this->find('pets', $pet)['avatar']);
        $this->assertSee($this->followRedirect($response), 'De uitsnede kon niet worden verwerkt');
    }

    public function testASourceThatIsNotAnImageIsRejectedEvenWithAnImageMimeType(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');
        $text = sys_get_temp_dir() . '/avatar_' . uniqid() . '.jpg';
        file_put_contents($text, 'plain text, not a jpeg');
        $this->tempFiles[] = $text;

        $response = $this->upload($pet, null, $text, 'image/jpeg');

        $this->assertNull($this->find('pets', $pet)['avatar']);
        $this->assertSee($this->followRedirect($response), 'Alleen JPEG- en PNG-afbeeldingen zijn toegestaan.');
    }

    public function testWithoutASourceFileTheUploadIsRejected(): void
    {
        $pet = $this->newPet();
        $this->loginAs('vet');

        $response = $this->postMultipart('pets/save_avatar/' . $pet, ['pet_avatar_crop' => $this->cropData()]);

        $this->assertRedirectsTo($response, 'pets/fiche/' . $pet);
        $this->assertNull($this->find('pets', $pet)['avatar']);
        $this->assertSee($this->followRedirect($response), 'Het geselecteerde bestand is geen geldige afbeelding.');
    }

    public function testAnUnknownPetRedirectsHome(): void
    {
        $this->newPet();
        $this->loginAs('vet');
        $upload = $this->upload(987654);
        $remove = $this->post('pets/remove_avatar/987654');

        $this->assertRedirectsTo($upload, '');
        $this->assertRedirectsTo($remove, '');
        $this->assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM pets WHERE id = 987654'));
    }
}
