<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('ci3-only')]
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
}
