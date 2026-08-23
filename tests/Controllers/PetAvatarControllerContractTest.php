<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PetAvatarControllerContractTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = file_get_contents(APPPATH . 'controllers/Pets.php');
    }

    public function testAllAvatarActionsInheritTheAuthenticatedVetController(): void
    {
        $this->assertStringContainsString('class Pets extends Vet_Controller', $this->source);
        $this->assertMatchesRegularExpression('/function save_avatar\(int \$pet_id\).*?require_avatar_post\(\)/s', $this->source);
        $this->assertMatchesRegularExpression('/function remove_avatar\(int \$pet_id\).*?require_avatar_post\(\)/s', $this->source);
        $this->assertStringContainsString("method(true) !== 'POST'", $this->source);
    }

    public function testDeliveryResolvesThePetReferenceAndSetsPrivateSafeHeaders(): void
    {
        $this->assertMatchesRegularExpression(
            '/function avatar_file\(int \$pet_id\).*?fields\(\'avatar\'\)->get\(\$pet_id\).*?pet_avatar->path\(\$pet\[\'avatar\'\]\)/s',
            $this->source
        );
        $this->assertStringContainsString("set_content_type('image/jpeg')", $this->source);
        $this->assertStringContainsString("X-Content-Type-Options: nosniff", $this->source);
        $this->assertStringContainsString("Cache-Control: private, no-cache, must-revalidate", $this->source);
        $this->assertMatchesRegularExpression('/avatar_file.*?show_404\(\).*?show_404\(\)/s', $this->source);
    }

    public function testControllerDelegatesMutationWithoutAcceptingAStoragePath(): void
    {
        $this->assertStringContainsString('pet_avatar_manager->save(', $this->source);
        $this->assertStringContainsString('pet_avatar_manager->remove(', $this->source);
        $this->assertStringNotContainsString("input->post('path')", $this->source);
        $this->assertStringNotContainsString("input->get('path')", $this->source);
    }

    public function testApacheCannotServeStoredPetFilesDirectly(): void
    {
        $htaccess = file_get_contents(FCPATH . '.htaccess');
        $this->assertStringContainsString('RewriteRule ^data/stored/pets(?:/|$) - [F,L,NC]', $htaccess);
    }

    public function testProjectRequestLimitsAllowTheSpecifiedEightMegabyteImage(): void
    {
        $userIni = parse_ini_file(FCPATH . '.user.ini');
        $this->assertSame('8M', $userIni['upload_max_filesize']);
        $this->assertSame('12M', $userIni['post_max_size']);

		$htaccess = file_get_contents(FCPATH . '.htaccess');
		$this->assertStringContainsString('php_value upload_max_filesize 8M', $htaccess);
		$this->assertStringContainsString('php_value post_max_size 12M', $htaccess);
    }
}
